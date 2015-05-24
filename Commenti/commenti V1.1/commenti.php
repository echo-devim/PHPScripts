<?php
/********************
 * Author: Gregorio *
 * Script: Commenti *
 * Version: 1.1     *
 ********************/
 
 /*
 ISTRUZIONI:
 Nell'header della pagina che include questo script bisogna linkare il foglio di stile usando questa riga di codice:
 <link rel="stylesheet" type="text/css" href="PERCORSO/commenti/res/stile1/commenti.css">
 per il secondo stile basta usare:
 <link rel="stylesheet" type="text/css" href="PERCORSO/commenti/res/stile2/commenti.css">
 poi, nel codice php, dove si vuole inserire il box dei commenti usare:
 $ID_COMMENTI=N; //ID della discussione, può assumere come valori interi da 1 in poi
 //Ogni ID ha associata una discussione diversa
 include("PERCORSO/commenti/commenti.php");
 */

function GestisciCommenti($ID_COMMENTI) {
/* VARIABILI DI CONFIGURAZIONE */
include("settings.php");
/* MAIN */
//Creazione tabella dei commenti se non esistente
$sql=mysql_query("CREATE TABLE IF NOT EXISTS $nome_tabella_commenti (ID bigint(20) NOT NULL AUTO_INCREMENT, code bigint(20) NOT NULL, ip text NOT NULL, nick text NOT NULL, data bigint(20) NOT NULL, email text NOT NULL, comment text NOT NULL, approvato binary(1) NOT NULL, PRIMARY KEY (ID))") OR die("Err: ".mysql_errno()."<br>".mysql_error());
//connessi al database
echo("<div id=\"commenti\" style=\"width:".$larghezza_contenitore_commenti."px;\">");
//Raccolta dati
$nick = filtra($_POST['nick']);
$email = filtra($_POST['email']);
$com = filtra($_POST['com']);
//Taglio il commento se è troppo lungo
if (strlen($com) > $massimo_num_caratteri)
	$com=substr($com,0,$massimo_num_caratteri);
$usercode = md5($_POST['captcha'].$password_cookie);
$codice = $_COOKIE['codice'];
$data = time();
/*session_start();
$err = filtra($_SESSION['err']);
session_destroy();*/
$ip = $_SERVER['REMOTE_ADDR'];
$pagina = (int) $_GET['com_pag'];
$delete = (int) $_GET['delete'];
$approvacom = (int) $_GET['approva'];
$pass_admin=$_COOKIE['admin'];
//Verifico se si è admin
if ($pass_admin==md5($password_admin))
	$admin=true;
else
	$admin=false;
//Eliminazione commento (solo se si è admin)
if (($admin===true) AND ($delete>0)) {
	$sql=mysql_query("DELETE FROM $nome_tabella_commenti WHERE ID=".($delete-1).";") or die("Errore cancellazione");
}
//Approvazione commento (solo se si è admin)
if (($admin===true) AND ($approvacom>0)) {
	$sql=mysql_query("UPDATE $nome_tabella_commenti SET approvato='1' WHERE ID=".($approvacom-1).";") or die("Errore approvazione");
}
//Gestione pulsanti di navigazione per le pagine
if (($attiva_moderazione===true) AND ($admin===false))
	$sql=mysql_query("SELECT * FROM $nome_tabella_commenti WHERE code=$ID_COMMENTI AND approvato=1;") or die("Errore nella query #1.1");
else
	$sql=mysql_query("SELECT * FROM $nome_tabella_commenti WHERE code=$ID_COMMENTI;") or die("Errore nella query #1.2");
$num_pagine=mysql_num_rows($sql)/$commenti_per_pagina;
$pulsante_avanti=false;
$pulsante_indietro=true;
if (($pagina<=0) OR ($pagina>$num_pagine) OR ($pagina>$massimo_numero_pagine)){
	$pagina=0;
	$pulsante_indietro=false;
}
if ($pagina<$num_pagine-1) {
	$pulsante_avanti=true;
}
$mesi = array(1=>'gennaio', 'febbraio', 'marzo', 'aprile',
                'maggio', 'giugno', 'luglio', 'agosto',
                'settembre', 'ottobre', 'novembre','dicembre');
$pubblica_commento=true;
//Analisi possibili errori
if (($nick==NULL) AND ($email==NULL) AND ($com==NULL) AND ($usercode==md5($password_cookie))) {
	$pubblica_commento=false;
} else if (strlen($nick) < $lunghezza_min_nick) {
	$err="Nick troppo corto, la lunghezza minima consentita &egrave di ".$lunghezza_min_nick." caratteri.";
	$pubblica_commento=false;
} else if (strrpos($email, "@") === false) {
	$err="Inserire un\'e-mail valida.";
	$pubblica_commento=false;
} else if (strlen($com) < $lunghezza_min_com) {
	$err="Commento troppo corto, la lunghezza minima consentita &egrave di ".$lunghezza_min_com." caratteri.";
	$pubblica_commento=false;
} //Verifica captcha
else if (($attiva_captcha===true) AND ($usercode!=$codice)) {
	$pubblica_commento=false;
	$err="Errore captcha, reinserire il codice.";
}
//Anti-flood
if (($attiva_antiflood===true) AND ($pubblica_commento===true)) {
	$sql=mysql_query("SELECT * FROM $nome_tabella_commenti WHERE code=$ID_COMMENTI ORDER BY ID DESC LIMIT 0,".$soglia_antiflood.";");
	//se l'ip corrente è quello degli ultimi 3 commenti blocco l'inserimento
	$pubblica_commento=false;
	while ($righe = mysql_fetch_assoc($sql)) {
		if ($ip != $righe['ip'])
			$pubblica_commento=true;
	}
	if ($pubblica_commento===false)
		$err="Non puoi inserire pi&ugrave di tre commenti consecutivi.";
}
//if (($nick < $lunghezza_min_nick) OR (strrpos($email, "@") === false) OR ($com < $lunghezza_min_com) OR ($ID_COMMENTI==0)) {
if ($pubblica_commento===false) {
	//Mostro commenti
	$sql=mysql_query("SELECT * FROM $nome_tabella_commenti WHERE code=$ID_COMMENTI ORDER BY ID DESC LIMIT ". ($pagina*$commenti_per_pagina) . ", ".(2^63-1).";") or die("Errore nella query");
	if (mysql_num_rows($sql) > 0) {
		echo("<table id=\"tab_commenti\">");
		$j=0;
		while(($row = mysql_fetch_array($sql)) AND ($j<$commenti_per_pagina)) {
			
			//In caso fosse attiva la moderazione, visualizzo solo i commenti approvati
			$approvato=$row['approvato'];
			if (($attiva_moderazione===false) OR ($admin===true) OR ($approvato==1)) {
				$j++;
				echo("<tr><td>");
				$giorno=date("j", $row['data']);
				$ind_mese=date("n", $row['data']);
				$mese=$mesi[$ind_mese];
				$anno=date("Y", $row['data']);
				$tempo=date("H:i", $row['data']);
				//Evidenzio nick admin con il colore preimpostato
				$nickname=$row['nick'];
				$nickname=str_replace("[admin]", "<span style=\"color:$colore_nick_admin;\">", $nickname);
				$nickname=str_replace("[/admin]", "</span>",$nickname);
				echo("<span");
				//Privilegio admin: Mostro email di chi ha commentato
				if ($admin===true)
					echo(" title=\"".$row['email']."\" ");
				echo(">");
				echo("<b>". $nickname ."</b>");
				echo("</span>");
				//Privilegio admin: Cancellazione commento e approvazione commento
				if ($admin===true) {
					if (($attiva_moderazione===true) AND ($approvato==0))
						echo(" - (<i>Commento ancora non approvato</i>)");
					echo("<br><div style=\"width:");
					if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) //Ottimizzazione IE
						echo(strval($larghezza_contenitore_commenti-40));
					else
						echo(strval($larghezza_contenitore_commenti-20));
					echo("px;text-align:right;position:absolute;\">");
					if (($attiva_moderazione===true) AND ($approvato==0))
						echo("<input type=button value=V title=\"Approva\" id=\"pulsante\" style=\"font-size:10px;width:20px;\" onclick=\"javascript:document.location.href='?approva=". ($row['ID']+1) . "';\"/></a>");
					echo(" <input type=button value=X title=\"Elimina\" id=\"pulsante\" style=\"font-size:10px;width:20px;\" onclick=\"javascript:document.location.href='?delete=". ($row['ID']+1) . "';\"/></a>");
					echo("</div>");
				}
				//Elaborazione del commento e sostituzione di smile e tag
				$commento=$row['comment'];
				//Sostituzioni base
				$commento=str_replace("\n","<br>", $commento);
				$commento=str_replace("[b]", "<b>", $commento);
				$commento=str_replace("[/b]", "</b>", $commento);
				$commento=str_replace("[i]", "<i>", $commento);
				$commento=str_replace("[/i]", "</i>", $commento);
				$commento=str_replace("[u]", "<u>", $commento);
				$commento=str_replace("[/u]", "</u>", $commento);
				//Sostituzioni avanzate (opzionali)
				if ($attiva_link===true) {
					//Sostituzione link
					$start=strpos($commento, "[url]");
					$fine=strpos($commento, "[/url]");
					if (strrpos($commento, "[url]")!==false) {
						$url=substr($commento, $start+5, ($fine)-($start+5));
						$commento=str_replace("[url]".$url."[/url]", "<a href=\"".$url."\">".$url."</a>",$commento);
					}
				}
				if ($attiva_smile===true) {
					//Sostituzione smile
					$commento=str_replace(":-)", "<img src=\"".$cartella."res/felice.png\" alt=\":-)\"></img>", $commento);
					$commento=str_replace(":-(", "<img src=\"".$cartella."res/triste.png\" alt=\":-(\"></img>", $commento);
					$commento=str_replace(";-)", "<img src=\"".$cartella."res/occhiolino.png\" alt=\";-)\"></img>", $commento);
					$commento=str_replace(":arg:", "<img src=\"".$cartella."res/arrabbiato.png\" alt=\">:-|\"></img>", $commento);
					$commento=str_replace(":-D", "<img src=\"".$cartella."res/sorriso.png\" alt=\":-D\"></img>", $commento);
					$commento=str_replace(":-/", "<img src=\"".$cartella."res/uffa.png\" alt=\":-/\"></img>", $commento);
				}
				echo("<p>" . $commento . "</p>");
				echo("<span style=\"font-size:12px; \"><i>Postato il $giorno $mese $anno alle $tempo</i>");
				//Privilegio admin: visualizzazione ip
				if ($admin===true)
					echo(" - IP: ".$row['ip']);
				echo("</span></td></tr>");
			} //Fine if - controllo moderazione
		}
		echo("</table>");
		//Pulsanti di navigazione
		echo("<h2 style=\"font-size:12px;text-align:right;\">");
		if ($pulsante_indietro===true) {
			echo("<input type=button value=\"<<\" id=\"pulsante\" style=\"font-size:10px;width:25px;\" onclick=\"javascript:document.location.href='?com_pag=0';\"/></a> ");
			echo("<input type=button value=\"<\" id=\"pulsante\" style=\"font-size:10px;width:20px;\" onclick=\"javascript:document.location.href='?com_pag=". ($pagina-1) . "';\"/></a> ");
		}
		if ($pulsante_avanti===true) {
			echo("<input type=button value=\">\" id=\"pulsante\" style=\"font-size:10px;width:20px;\" onclick=\"javascript:document.location.href='?com_pag=" . ($pagina+1) . "';\"/></a> ");
			echo("<input type=button value=\">>\" id=\"pulsante\" style=\"font-size:10px;width:25px;\" onclick=\"javascript:document.location.href='?com_pag=" . (ceil($num_pagine)-1) . "';\"/></a>");	//Arrotondo per eccesso e sottraggo 1	
		}
		echo("</h2>");
	}
	//Mostro eventuale messaggio d'errore
	if ($err != NULL)
		echo("<br><span style=\"color:red;font-size:12px;\">$err</span><br>");
	//Modulo nuovo commento
	echo("<br><div id=\"nuovo_commento\"><form action=\"\" method=POST name=form>");
	echo("Nome<br><input type=text name=nick maxlength=15 style=\"width:70%;\"/><br><br>");
	echo("E-mail (Non visibile)<br><input type=text name=email maxlength=30 style=\"width:70%;\"/><br><br>");
	//captcha
	if ($attiva_captcha===true) {
		echo("<img name=captcha src=\"".$cartella."captcha.php?".time()."\" alt=captcha style=\"border: 1px solid black;\"></img><br><br>");
		echo("Codice captcha");
		echo("<br><input type=text name=captcha maxlength=30 style=\"width:70%;\"/><br><br>");
	}
	//commento
	echo("Commento<br>");
	echo("<a href=\"javascript:document.form.com.value += ':-)'; document.form.com.focus();\"><img src=\"".$cartella."res/felice.png\" alt=\":-)\" title=\":-)\" border=\"0\"></a>");
	echo("<a href=\"javascript:document.form.com.value += ';-)'; document.form.com.focus();\"><img src=\"".$cartella."res/occhiolino.png\" alt=\";-)\" title=\";-)\" border=\"0\"></a>");
	echo("<a href=\"javascript:document.form.com.value += ':-D'; document.form.com.focus();\"><img src=\"".$cartella."res/sorriso.png\" alt=\":-D\" title=\":-D\" border=\"0\"></a>");
	echo("<a href=\"javascript:document.form.com.value += ':-('; document.form.com.focus();\"><img src=\"".$cartella."res/triste.png\" alt=\":-(\" title=\":-(\" border=\"0\"></a>");
	echo("<a href=\"javascript:document.form.com.value += ':-/'; document.form.com.focus();\"><img src=\"".$cartella."res/uffa.png\" alt=\":-/\" title=\":-/\" border=\"0\"></a>");
	echo("<a href=\"javascript:document.form.com.value += ':arg:'; document.form.com.focus();\"><img src=\"".$cartella."res/arrabbiato.png\" alt=\":arg:\" title=\":arg:\" border=\"0\"></a>");
	echo(" <input type=button value=\"Grassetto\" id=\"pulsante\" style=\"font-size:10px;\" onclick=\"javascript:document.form.com.value += '[b][/b]'; document.form.com.focus();\" />");
	echo("<input type=button value=\"Corsivo\" id=\"pulsante\" style=\"font-size:10px;\" onclick=\"javascript:document.form.com.value += '[i][/i]'; document.form.com.focus();\" />");
	echo("<input type=button value=\"Sottolineato\" id=\"pulsante\" style=\"font-size:10px;\" onclick=\"javascript:document.form.com.value += '[u][/u]'; document.form.com.focus();\" />");
	if ($attiva_link===true)
		echo("<input type=button value=\"Link\" id=\"pulsante\" style=\"font-size:10px;\" onclick=\"javascript:document.form.com.value += '[url]http://www.example.com[/url]'; document.form.com.focus();\" />");
	echo("<br><textarea name=com rows=5 style=\"width:70%;\"></textarea>");
	echo("<br><br><input type=submit value=Invia id=\"pulsante\" />");
	echo("</form></div>");
	if ($attiva_moderazione===true)
		echo("<h5 style=\"font-size:12px\">Il commento dovr&agrave prima essere approvato dall'admin</h5>");
} else {
	//Aggiungo dati al database solo se il codice captcha è corretto
	$mod=1; //Codice per l'approvazione dei commenti
	if (($admin===false) AND ($attiva_moderazione===true))
		$mod=0; //=commento da approvare
	//Se sta pubblicando un commento un admin, ne tengo conto
	if ($admin===true)
		$nick = "[admin]".$nick."[/admin]";
	else
		$nick=str_replace("[admin]", "",$nick);
	$sql = mysql_query("INSERT INTO $nome_tabella_commenti VALUES (NULL, '$ID_COMMENTI', '$ip', '$nick', '$data', '$email', '$com', '".$mod."');") or die("Errore inserimento query");
	//Notifica all'admin del nuovo commento via email
	if ($attiva_notifica_email===true) {
		$messaggio=($nick." (".$ip.") ha appena pubblicato un nuovo commento:\n\n".$com."\n\n\nDettagli: ID_COMMENTI=".$ID_COMMENTI);
		//Invio l'email per ciascun indirizzo email specificato
		$m=explode(";",$email_notifica);
		for ($n=0;$n<count($m);$n++)
			mail($m[$n], $titolo_notifica, $messaggio);
	}
	header("Location: ".$_SERVER['REQUEST_URI']);
}

echo("</div>");
}//Fine funzione

/* FUNZIONE per filtrare i dati */
function filtra($var) {
	$var=htmlentities($var);
	$var=str_replace("'","\'",$var);
	$var=str_replace("javascript:", "javascrìpt:",$var);
	return $var;
}

//Lancio funzione per la gestione dei commenti
GestisciCommenti($ID_COMMENTI);
?>