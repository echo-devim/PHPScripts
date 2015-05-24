<?php
/*********************
 * Author: Gregorio  *
 * Script: Notifiche *
 * Version: 1.0      *
 *********************/
 
 /*
 ISTRUZIONI:
 Nell'header della pagina che include questo script bisogna linkare il foglio di stile usando questa riga di codice:
 Per avere la barra a fondo pagina
 <link rel="stylesheet" type="text/css" href="PERCORSO/notifiche/res/notifiche_bardown.css">
 Per avere la barra in cima alla pagina
 <link rel="stylesheet" type="text/css" href="PERCORSO/notifiche/res/notifiche_barup.css">
 poi, nel codice php, includere lo script tramite codice:
 include("PERCORSO/notifiche.php");
 FUNZIONI:
 per mandare una notifica basta usare:
 notifica("nome_cookie", "nome_valore", "messaggio"[, durata in giorni, "indirizzo_ip"]);
 Ad esempio se voglio mandare una notifica ad un utente che ha come username Prova e password 123 allora userò:
 notifica("username;password", "Prova;123", 7);
 in questo modo, l'utente che ha i cookie username e password con i rispettivi valori Prova e 123 riceverà "messaggio".
 Questa notifica avrà una validità di 7 giorni, dopo di che sarà eliminata.
 Per mandare una notifica ad un utente che ha il cookie Prova a prescindere dal suo valore si può usare *IF_EXISTS*
 notifica("Prova", "*IF_EXISTS*", "messaggio");
 per mandare una notifica ad un utente con un determinato ip basta usare:
 notifica(NULL, NULL ,"messaggio", NULL, "indirizzo_ip");
 
 Per visualizzare invece le notifiche richiamare:
 CaricaNotifiche();
 */
 
class GestioneNotifiche {
 
/*Dichiarazioni*/
var $nome_tabella_notifiche;
var $scadenza_notifica;
var $avviso_notifiche;
var $cartella_script_notifiche;
var $ripeti_notifica;
 //Costruttore
function GestioneNotifiche() {
/* VARIABILI DI CONFIGURAZIONE */
	$this->nome_tabella_notifiche="notifiche"; //nome della tabella (che verrà creata in automatico) in cui saranno salvate le notifiche
	$this->scadenza_notifica=5; //Indica il numero di giorni di default per i quali si deve mantenere una notifica. Passati tot. giorni la notifica verrà considerata vecchia e verrà cancellata.
	$this->avviso_notifiche="[*num*] Notifiche"; //Messaggio che comparirà sulla barra delle notifiche. Al posto di *num* verrà automaticamente inserito il numero di notifiche da leggere.
	$this->ripeti_notifica=true; //Se settato a false evita di inviare una notifica ad un ip con messaggio identico ad una notifica precedentemente inviata al medesimo ip
	$this->cartella_script_notifiche="http://demophp.altervista.org/demo_notifiche/"; //Percorso dove risiede lo script
	//Creo la tabella per le notifiche se inesistente
	$sql=mysql_query("CREATE TABLE IF NOT EXISTS ".$this->nome_tabella_notifiche." (ID bigint(20) NOT NULL AUTO_INCREMENT, cookie_name text NOT NULL, cookie_value text NOT NULL, messaggio text NOT NULL, data bigint(20) NOT NULL, scadenza bigint(20) NOT NULL, ip text NOT NULL, PRIMARY KEY (ID));") OR die("Script notifiche\n<br>Err: ".mysql_errno()."<br>".mysql_error());
}

//Salvataggio di una nuova notifica
function notifica($cookie_name, $cookie_value, $message, $scadenza=NULL, $ip=NULL) {
	//Se cookie_value ha come valore: *IF_EXISTS*
	//significa che basta che cookie_name esista a prescindere dal suo valore
	$nome_tabella_notifiche=$this->nome_tabella_notifiche;
	$ripeti_notifica=$this->ripeti_notifica;
	$scadenza_notifica=$this->scadenza_notifica;
	if ($scadenza != NULL)
		$scadenza_notifica=time()+($scadenza*86400);
	else
		$scadenza_notifica=time()+($scadenza_notifica*86400);
	$invia_notifica=true;
	if ($ripeti_notifica===false) {
		$sql = mysql_query("SELECT * FROM $nome_tabella_notifiche WHERE cookie_name='$cookie_name' AND cookie_value='$cookie_value' AND messaggio='$message' AND ip='$ip';") or die("Errore controllo notifica #0");
		if (mysql_num_rows($sql) > 0)
			$invia_notifica=false;
	}
	if ($invia_notifica===true)
		mysql_query("INSERT INTO $nome_tabella_notifiche VALUES (NULL, '$cookie_name', '$cookie_value', '$message', '".time()."', '".$scadenza_notifica."', '$ip');") or die("Errore inserimento notifica #1"."<br>".mysql_error());
}

//Caricamento notifiche
function CaricaNotifiche() {
	$nome_tabella_notifiche=$this->nome_tabella_notifiche;
	$scadenza_notifica=$this->scadenza_notifica;
	$avviso_notifiche=$this->avviso_notifiche;
	$cartella_script_notifiche=$this->cartella_script_notifiche;
	//Eliminazione vecchie notifiche
	mysql_query("DELETE FROM $nome_tabella_notifiche WHERE scadenza<'".time()."';") or die("Errore pulizia vecchie notifiche #2");
	//Eliminazione notifica
	$elimina = (int) $_GET['elimina_notifica'];
	$this->EliminaNotifica($elimina);
	//Carico dal database tutte le notifiche
	$sql=mysql_query("SELECT * FROM $nome_tabella_notifiche ORDER BY ID DESC;");
	if (mysql_num_rows($sql)>0)
		$notifiche[mysql_num_rows($sql)];
		$j = 0;
		while ($dati = mysql_fetch_assoc($sql)) {
			$cookie_name = $dati['cookie_name'];
			$cookie_value = $dati['cookie_value'];
			//Se la notifica interessa il visitatore corrente, salvo i dati in un array che utilizzerò successivamente
			if ($this->UtenteVerificato($cookie_name, $cookie_value, $dati['ip'])===true) {
				$notifiche[$j] = array("ID" => $dati['ID'], "messaggio" => $dati['messaggio'], "data" => $dati['data']);
				$j++;
			}
		}
	//Eliminazione notifiche
	if ($_GET['elimina_notifica'] == "all") {
		if ($j > 0) {
			for ($i=0; $i<$j; $i++) {
				$this->EliminaNotifica($notifiche[$i]["ID"]);
			}
			$j=0;
		}
	}
	//Visualizzazione notifiche
	if ($j > 0) { //Se c'è almeno una notifica..
			echo("<div id=\"notifiche\" class=\"notifiche\" style=\"display:none;\">");
			echo("<img class=\"elimina_notifica\" src=\"".$cartella_script_notifiche."res/x_grey.png\" alt=Delete title=\"Elimina tutto\" style=\"float:right;width:10px;height:10px;margin-bottom:2px;\" onclick=\"delete_all();\"></img>");
			echo("<div id=\"contenitore_notifiche\">");
			echo("<table id=\"tabella_notifiche\">");
			for ($i=0; $i<$j; $i++) {
				echo("<tr><td>");
				echo(date("j/m/Y H:i", $notifiche[$i]["data"]));
				echo("<img class=\"elimina_notifica\" src=\"".$cartella_script_notifiche."res/x_black.png\" alt=Delete title=\"Elimina notifica\" style=\"float:right;width:10px;height:10px;\" onclick=\"document.location.href='?elimina_notifica=".$notifiche[$i]["ID"]."';\"></img>");
				echo("<br>".$notifiche[$i]["messaggio"]);
				echo("</td></tr>");
			}
			echo("</table></div>");
			echo("<span id=\"avviso_notifiche\" class=\"nascondi_notifiche\">Nascondi Notifiche</span>");
			echo("</div>");
			echo("<div id=\"notifiche\" class=\"avviso_notifiche\">");
			$avviso_notifiche=str_replace("*num*", $j, $avviso_notifiche);
			echo("<span id=\"avviso_notifiche\" class=\"mostra_notifiche\">".$avviso_notifiche."</span>");
			echo("</div>");
	}
}

function EliminaNotifica($elimina) {
	$nome_tabella_notifiche=$this->nome_tabella_notifiche;
	if ($elimina != NULL) {
		$sql = mysql_query("SELECT * FROM $nome_tabella_notifiche WHERE ID='$elimina';") or die("Errore eliminazione notifica #3<br>".mysql_error());
		$dati = mysql_fetch_assoc($sql);
		if ($this->UtenteVerificato($dati['cookie_name'], $dati['cookie_value'], $dati['ip'])===true)
			mysql_query("DELETE FROM $nome_tabella_notifiche WHERE ID='$elimina';") or die("Errore eliminazione notifica #3.2");
	}
}

function UtenteVerificato($cookie_name, $cookie_value, $ip) {
	//Verifico se la notifica interessa il visitatore corrente
	$cn = explode(';', $cookie_name);
	$cv = explode(';', $cookie_value);
	$utente_verificato=true;
	for ($i=0;$i<count($cn);$i++) {
		//echo("Condizione: (".$_COOKIE[$cn[$i]]." == NULL) OR ((".$_COOKIE[$cn[$i]]." != ".$cv[$i].") AND (".$cv[$i]." != \"*IF_EXISTS*\"))");
		if ((($_COOKIE[$cn[$i]] == NULL) OR (($_COOKIE[$cn[$i]] != $cv[$i]) AND ($cv[$i] != "*IF_EXISTS*"))) AND ($_SERVER['REMOTE_ADDR']!=$ip)) {
			$utente_verificato=false;
		}
	}
	return $utente_verificato;
}

} //Fine classe

/* FUNZIONI */
function notifica($cookie_name, $cookie_value, $message, $scadenza=NULL, $ip=NULL) {
	$notifica = new GestioneNotifiche();
	$notifica->notifica($cookie_name, $cookie_value, $message, $scadenza, $ip);
}

function CaricaNotifiche() {
	$notifica = new GestioneNotifiche();
	$notifica->CaricaNotifiche();
}
?>