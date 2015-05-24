<?php
/* SCRIPT GUARDIAN */
class Guardian {
/* Dichiarazioni */
var $nome_tabella_log;
var $nome_tabella_ban;
var $password_admin;
var $visualizza_dettagli;
var $durata_salvataggio;
var $colore_sfondo_tabella;
var $durata_login_guardian;
var $log_ban;
var $script_notifiche;
var $righe_max;
var $database_url;
var $abilita_statistiche;
var $default_ban;
var $filtro_attacchi;

/* Costruttore */
function Guardian() {
	/* VARIABILI DI CONFIGURAZIONE */
	$this->nome_tabella_log = "guardian_log"; //Nome della tabella (che verrà creata automaticamente) usata per salvare i log
	$this->nome_tabella_ban = "guardian_ban"; //Nome della tabella (che verrà creata automaticamente) usata per salvare i dati di ban
	$this->password_admin="admin";
	$this->visualizza_dettagli=true; //Tra i dettagli sono presenti i cookie, l'user agent e l'url referer
	$this->durata_salvataggio=10; //Indica per quanti giorni bisogna tenere salvati i log prima che vengano eliminati
	$this->colore_sfondo_tabella=""; //Se non specificato la tabella assumera dei colori dinamici generati in base all'ip
	$this->durata_login_guardian=5; //Numero che indica (dopo aver effettuato il login) dopo quanti giorni rimostrare la pagina del login
	$this->default_ban=300; //Numero di giorni di default di ban se non specificato al momento del ban
	$this->log_ban=true; //Se settato a true logga i tentativi di accesso al sito da parte di un utente bannato
	$this->abilita_statistiche=true; //Mostra un box con le statistiche riguardo le visite del sito
	$this->filtro_attacchi=true; //Se settato a true blocca e segnala possibili attacchi al sito
	$this->script_notifiche="../demo_notifiche/notifiche.php"; //PLUG-IN: percorso dello script delle notifiche per mandare notifiche ad uno specifico ip
	$this->righe_max=300; //Numero massimo di righe della tabella dei log
	$this->database_url="config.php";
}

function Registra() {
	$nome_tabella_log=$this->nome_tabella_log;
	$password_admin=$this->password_admin;
	$visualizza_dettagli=$this->visualizza_dettagli;
	$durata_salvataggio=$this->durata_salvataggio;
	$colore_sfondo_tabella=$this->colore_sfondo_tabella;
	$durata_login=$this->durata_login;
	$filtro_attacchi=$this->filtro_attacchi;
	//Rilevamento dati
	$ip = $_SERVER['REMOTE_ADDR'];
	$get = $_GET;
	$post = $_POST;
	$cookie = $this->filtra($_SERVER['HTTP_COOKIE']);//$_COOKIE;
	$referer = $this->filtra($_SERVER['HTTP_REFERER']);
	$useragent = $this->filtra($_SERVER['HTTP_USER_AGENT']);
	$url = $this->filtra($_SERVER['SCRIPT_URL']);
	$data = time();
	//Creo la tabella per il log se inesistente
	$sql=mysql_query("CREATE TABLE IF NOT EXISTS $nome_tabella_log (ID bigint(20) NOT NULL AUTO_INCREMENT, data int(20) NOT NULL, ip text NOT NULL, locazione text NOT NULL, attivita text NOT NULL, PRIMARY KEY (ID));") OR die("Script guardian\n<br>Err: ".mysql_errno()."<br>".mysql_error());
	//Pulizia dai vecchi log
	$scaduti = $data - ($durata_salvataggio*86400);
	mysql_query("DELETE FROM $nome_tabella_log WHERE data<'$scaduti';") or die("Errore script guardian<br>Errore riscontrato nella pulizia dei log");
	//Pannello admin
	if (substr($url, -12) == "guardian.php") {
		$r=$this->AdminLog();
		if ($r===true)
			exit;
	}
	//Registro l'attività del visitatore corrente
	$tipo_attacco=""; //Variabile in cui memorizzo il possibile tipo di attacco effettuato
	//Dati GET
	if (count($get)>0) {
		$attivita .= "Sta inviando dei dati alla pagina tramite metodo GET:<br><br>";
		foreach($get as $chiave => $valore){
			$attivita.= "<b>Parametro:</b> ".$this->filtra($chiave)."<br><b>Valore:</b> ".$this->filtra($valore)."<br><br>";
			if (strpos($valore, "document.cookie") > (int)0)
				$tipo_attacco="xss";
			else if ((strpos($valore, "' OR ") > (int)0) OR (strpos($valore, "' AND ") > (int)0) OR (strpos($valore, "' UNION ") > (int)0))
				$tipo_attacco="sql";
		}
	}
	//Dati POST
	if (count($post)>0) {
		$attivita .= "Sta inviando dei dati alla pagina tramite metodo POST:<br><br>";
		foreach($post as $chiave => $valore){
			$attivita.= "<b>Parametro:</b> ".$this->filtra($chiave)."<br><b>Valore:</b> ".$this->filtra($valore)."<br><br>";
			if (strpos($valore, "document.cookie") > (int)0)
				$tipo_attacco="xss";
			else if ((strpos($valore, "' OR ") > (int)0) OR (strpos($valore, "' AND ") > (int)0) OR (strpos($valore, "' UNION ") > (int)0))
				$tipo_attacco="sql";
		}
	}
	
	if ($filtro_attacchi===true)
		if ($tipo_attacco=="xss") {
			$attivita = "<span style=\"color:#FF0000;\">Rilevato possibile attacco di Cross-Site Scripting</span><br><br>".$attivita;
		}else if ($tipo_attacco=="sql") {
			$attivita = "<span style=\"color:#FF0000;\">Rilevato possibile attacco di SQL-Injection</span><br><br>".$attivita;
		}
	if ((count($get) == 0) AND (count($post) == 0)) {
		$attivita .= "Sta visualizzando la pagina<br><br>";
	}
	if ($visualizza_dettagli===true) {
		$attivita .= "#dettagli#<br><b>Cookie:</b> $cookie<br><b>User-Agent:</b> $useragent<br><b>Referer:</b> $referer<br>";
		
	}
	//Salvataggio su database
	mysql_query("INSERT INTO $nome_tabella_log VALUES (NULL, '$data', '$ip', '$url', '$attivita');") or die("Errore script guardian<br>Errore inserimento log nel database!");
	//Blocco dai possibili attacchi
	if ($filtro_attacchi===true)
		if ($tipo_attacco!=NULL) {
		echo ("<div style=\"margin:auto; padding:10px; font-size:16px; font-family: 'Arial', sans-serif; width:300px; border:1px solid black\">");
		echo("E' stato rilevato un <b>tentativo di attacco</b> al sito da parte tua.<br>Il caricamento della pagina &egrave stato bloccato.");
		die("</div>");
		}
}

function filtra($var) {
	$var=str_replace("'","&#39;",$var);
	$var=htmlentities($var);
	$var=str_replace("#dettagli#", "#\dettagli#", $var);
	return $var;
}

function AdminLog() {
	$filtro_attacchi=$this->filtro_attacchi;
	$password_admin=$this->password_admin;
	$nome_tabella_log=$this->nome_tabella_log;
	$colore_sfondo_tabella=$this->colore_sfondo_tabella;
	$durata_login_guardian=$this->durata_login_guardian;
	$nome_tabella_ban=$this->nome_tabella_ban;
	$script_notifiche=$this->script_notifiche;
	$abilita_statistiche=$this->abilita_statistiche;
	$default_ban=$this->default_ban;
	$admin = $_COOKIE['admin'];
	$pass = $_POST['passadmin'];
	$ip = $this->filtra($_GET["ip"]);
	$periodo = $this->filtra($_GET["periodo"]);
	$attv = $this->filtra($_GET["attv"]);
	$tipo = $_GET["tipodati"];
	echo("<html><head><title>Amministrazione</title>");
	?>
<script type="text/javascript">
function MostraDati() {
	if (document.forms["Filtri"].attv.value=="dati")
		document.forms["Filtri"].tipodati.style.display="block";
	else
		document.forms["Filtri"].tipodati.style.display="none";
		document.forms["Filtri"].tipodati.selectedIndex=0;
}
function parseGetVars()
{
  // creo una array
  var args = new Array();
  // individuo la query (cioè tutto quello che sta a destra del ?)
  // per farlo uso il metodo substring della proprietà search
  // dell'oggetto location
  var query = window.location.search.substring(1);
  // se c'è una querystring procedo alla sua analisi
  if (query)
  {
    // divido la querystring in blocchi sulla base del carattere &
    // (il carattere & è usato per concatenare i diversi parametri della URL)
    var strList = query.split('&');
    // faccio un ciclo per leggere i blocchi individuati nella querystring
    for(str in strList)
    {
      // divido ogni blocco mediante il simbolo uguale
      // (uguale è usato per l'assegnazione del valore)
      var parts = strList[str].split('=');
      // inserisco nella array args l'accoppiata nome = valore di ciascun
      // parametro presente nella querystring
      args[unescape(parts[0])] = unescape(parts[1]);
    }
  }
  return args;
}</script>
	<?php
	if (($admin == NULL) AND ($pass == NULL)) { //Mostro il form di login
		echo("</head><body><br><br><br><div style=\"border:1px solid silver; font-size:12px;width:180px;margin:auto;\">");
		echo("<form action=\"\" method=POST>");
		echo("Password:<br><input type=password name=passadmin /> <input type=submit value=Login /></form>");
		echo("</div>");
	} else if (($pass != NULL) AND ($admin == NULL)) {
		if ($pass == $password_admin) {
			setcookie("admin", md5($password_admin), time()+($durata_login_guardian*86400));
			header("Location: ".$_SERVER['REQUEST_URI']);
		} else
			echo("Errore, password errata.");
	} else if ($admin == md5($password_admin)) {
		echo("<link rel=\"stylesheet\" type=\"text/css\" href=\"res/guardian.css\">");
		echo("</head><body>");
		//Pannello laterale admin
		echo("<div style=\"float:left;position:absolute;width:225px;\">");
		echo ("<div class=\"pannello_admin\">");
		//Filtro per ip
		echo("<form method=GET action=\"\" name=\"Filtri\">Mostra solo le attivit&agrave dell'ip seguente:");
		echo("<br><input type=text name=ip value=\"$ip\"/>");
		echo("<br><br>Tipo di attivit&agrave:<br><select name=attv onChange=\"MostraDati();\"><option selected=\"selected\" value=\"tutto\">tutto</option><option value=\"dati\">invio dati</option><option value=\"visualizzazione\">visualizzazione</option><option value=\"attacchi\">possibili attacchi</option></select>");
		echo("<select name=\"tipodati\" style=\"display:none;\"><option selected=\"selected\" value=\"qualsiasi\">qualsiasi</option><option value=\"get\">get</option><option value=\"post\">post</option></select>");
		echo("<br>Mostra log di:<br><select name=periodo><option selected=\"selected\" value=\"oggi\">oggi</option><option value=\"settimana\">ultimi 7 giorni</option><option value=\"mese\">ultimi 30 giorni</option><option value=\"anno\">anno attuale</option><option value=\"tutto\">tutto</option></select>");
		echo("<br><br>");
		echo("<input type=submit value=Filtra class=\"pulsante\" /></form>");
		echo("</div>");
		?>
<script type="text/javascript">
// Recupero i valori passati con GET
// Per farlo creo una variabile cui assegno come valore
// il risultato della funzione vista in precedenza
var get = parseGetVars();
var attv = get['attv'];
var periodo = get['periodo'];
var tipo = get['tipodati'];
var i=0;
switch(attv) {
	case "dati":
		i=1;
		document.forms["Filtri"].tipodati.style.display="block";
		break;
	case "visualizzazione":
		i=2;
		break;
	case "attacchi":
		i=3;
		break;
	default:
		i=0;
		break;
}
document.forms["Filtri"].attv.selectedIndex=i;
switch(tipo) {
	case "get":
		i=1;
		break;
	case "post":
		i=2;
		break;
	default:
		i=0;
		break;
}
document.forms["Filtri"].tipodati.selectedIndex=i;

switch(periodo) {
	case "settimana":
		i=1;
		break;
	case "mese":
		i=2;
		break;
	case "anno":
		i=3;
		break;
	case "tutto":
		i=4;
		break;
	default:
		i=0;
		break;
}
document.forms["Filtri"].periodo.selectedIndex=i;
</script>
		<?php
		//Pannello ban
		echo("<div class=\"pannello_admin\" style=\"\";>");
		echo("<b>IP bannati:</b><br>");
		echo("<table class=\"tabella_ban\">");
		//Aggiunta/Eliminazione ban
		$ban = $this->filtra($_GET['ipban']);
		$num = (int) $_GET['numban'];
		$unban = $this->filtra($_GET['unban']);
		if (($ban != NULL) AND ($num != NULL)) {
			$sql=mysql_query("SELECT * FROM $nome_tabella_ban WHERE ip='$ban';");
			if (mysql_num_rows($sql)==0)
				mysql_query("INSERT INTO $nome_tabella_ban VALUES (NULL, '".(time()+($num*86400))."', '$ban');") or die("Errore nell'aggiunta del ban<br>".mysql_error());
			else
				mysql_query("UPDATE $nome_tabella_ban SET data='".(time()+($num*86400))."' WHERE ip='$ban';") or die("Errore nell'aggiornamento del ban<br>".mysql_error());
		}
		if ($unban != NULL)
			mysql_query("DELETE FROM $nome_tabella_ban WHERE ip='".$unban."';") or die("Errore eliminazione ban<br>".mysql_error());
		//Caricamento ip bannati
		$sql = mysql_query("SELECT * FROM $nome_tabella_ban ORDER BY ID DESC;") or die("Errore caricamento ip bannati");
		echo("<tr><td><b>IP</b></td><td>Scadenza ban</td></tr>");
		while ($dati = mysql_fetch_assoc($sql)) {
			echo("<tr><td><a title=\"Rimuovi ban\" href=\"?unban=".$dati["ip"]."\">".$dati["ip"]."</a></td><td>".date("j/m/Y",$dati["data"])."</td></tr>");
		}
		echo("</table>");
		echo("<br><form method=GET action=\"\">Banna l'ip seguente:");
		echo("<br><input type=text name=ipban />");
		echo("<br>Numero di giorni di ban: <input type=text name=numban value=\"$default_ban\" style=\"width:40px;\" /><br><br>");
		echo("<input type=submit value=Banna class=\"pulsante\" /></form>");
		echo("</div>");
		//Box per mandare notifiche a determinati ip
		if ($script_notifiche != NULL) {
			include($script_notifiche);
			$ip_notifica = $this->filtra($_POST['notifica_ip']);
			$mex_notifica = $this->filtra($_POST['mex_notifica']);
			$num_notifica = (int) $this->filtra($_POST['num_notifica']);
			echo ("<div class=\"pannello_admin\" style=\"\">");
			echo("<form method=POST action=\"\">Invia notifica all'ip seguente:");
			echo("<br><input type=text name=\"notifica_ip\" />");
			echo("<br><br>Messaggio della notifica:<br><textarea name=\"mex_notifica\" style=\"width:200px;height:40px;resize:none;\"></textarea>");
			echo("<br><br>Numero di giorni di validit&agrave della notifica: <input type=text name=\"num_notifica\" style=\"width:40px;\" /><br><br>");
			echo("<input type=submit value=Invia class=\"pulsante\" /></form>");
			if (($ip_notifica != NULL) AND ($mex_notifica != NULL)) {
				notifica(NULL, NULL, $mex_notifica, $num_notifica, $ip_notifica);
				echo("<p style=\"padding:0;margin:0;font-size:11px;\">Notifica inviata.</p>");
			}
			echo("</div>");
		}
		//Pannello statistiche
		if ($abilita_statistiche === true) {
			$sql=mysql_query("SELECT * FROM $nome_tabella_log;") or die("Errore statistiche 1");
			$tot_caricamenti=mysql_num_rows($sql);
			$sql=mysql_query("SELECT DISTINCT ip FROM $nome_tabella_log;") or die("Errore statistiche 2");
			$tot_visite=mysql_num_rows($sql);
			$sql=mysql_query("SELECT * FROM $nome_tabella_log WHERE data>'".(mktime(0,0,0,date("n",time()), date("j",time()), date("Y",time())))."';") or die("Errore statistiche 3");
			$oggi_caricamenti=mysql_num_rows($sql);
			$sql=mysql_query("SELECT DISTINCT ip FROM $nome_tabella_log WHERE data>'".(mktime(0,0,0,date("n",time()), date("j",time()), date("Y",time())))."';") or die("Errore statistiche 4");
			$oggi_visite=mysql_num_rows($sql);
			$sql=mysql_query("SELECT * FROM $nome_tabella_log WHERE data>'".(mktime(0,0,0,date("n",time()), 1, date("Y",time())))."';") or die("Errore statistiche 5");
			$mese_caricamenti=mysql_num_rows($sql);
			$sql=mysql_query("SELECT DISTINCT ip FROM $nome_tabella_log WHERE data>'".(mktime(0,0,0,date("n",time()), 1, date("Y",time())))."';") or die("Errore statistiche 6");
			$mese_visite=mysql_num_rows($sql);
			if ($filtro_attacchi===true) {
				$sql=mysql_query("SELECT * FROM $nome_tabella_log WHERE attivita LIKE '%Rilevato possibile attacco%' AND data>'".(mktime(0,0,0,date("n",time()), date("j",time()), date("Y",time())))."';") or die("Errore statistiche 3.1");
				$oggi_attacchi=mysql_num_rows($sql);
				$sql=mysql_query("SELECT * FROM $nome_tabella_log WHERE attivita LIKE '%Rilevato possibile attacco%' AND data>'".(mktime(0,0,0,date("n",time()), 1, date("Y",time())))."';") or die("Errore statistiche 5.1");
				$mese_attacchi=mysql_num_rows($sql);
				$sql=mysql_query("SELECT * FROM $nome_tabella_log WHERE attivita LIKE '%Rilevato possibile attacco%';") or die("Errore statistiche 1.1");
				$tot_attacchi=mysql_num_rows($sql);
			}
			echo ("<div class=\"pannello_admin\" style=\"\">");
			echo("<b>Pannello Statistiche</b><br><br>");
			echo("<b>Giorno corrente:</b><br>");
			echo("Visitatori (unici): ".$oggi_visite);
			echo("<br>Caricamenti pagine: ".$oggi_caricamenti);
			if ($filtro_attacchi===true)
				echo("<br>Attacchi ricevuti: ".$oggi_attacchi);
			echo("<br><br><b>Mese corrente:</b><br>");
			echo("Visitatori (unici): ".$mese_visite);
			$media = (int)($mese_visite/date("j", time()));
			echo("<br>Media visitatori: ".$media);
			echo("<br>Caricamenti pagine: ".$mese_caricamenti);
			if ($filtro_attacchi===true)
				echo("<br>Attacchi ricevuti: ".$mese_attacchi);
			echo("<br><br><b>Statistiche totali:</b><br>");
			echo("Visitatori (unici): ".$tot_visite);
			echo("<br>Caricamenti pagine: ".$tot_caricamenti);
			if ($filtro_attacchi===true)
				echo("<br>Attacchi ricevuti: ".$tot_attacchi);
			echo("</div>");
		}
		echo("</div>"); //Fine contenitore
		//Mostra tutti i log
		//Controllo filtri
		if ($ip != NULL)
			$condizioni = "ip='$ip'";
		if ($periodo != NULL)
			if ($condizioni != NULL)
				$condizioni .= " AND ";
			switch ($periodo) {
				case "settimana":
					$condizioni .= "data>'".(time()-(86400*7))."'";
					break;
				case "mese":
					$condizioni .= "data>'".(time()-(86400*30))."'";
					break;
				case "anno":
					$anno = mktime(0,0,0,1,1,date('Y',time()));
					$condizioni .= "data>'".$anno."'";
					break;
				case "tutto":
					$condizioni .= "data>'0'";
					break;
				default:
					$condizioni .= "data>'".(mktime(0,0,0,date("n",time()), date("j",time()), date("Y",time())))."'";
					break;
			}
		if ($attv != NULL) {
			if ($condizioni != NULL)
				$condizioni .= " AND ";
			switch($attv) {
				case "dati":
					$condizioni .= "attivita LIKE 'Sta inviando dei dati %'";
					break;
				case "visualizzazione":
					$condizioni .= "attivita LIKE 'Sta visualizzando la pagina%'";
					break;
				case "attacchi":
					$condizioni .= "attivita LIKE '%Rilevato possibile attacco%'";
					break;
				default:
					$condizioni .= "attivita LIKE '%'";
					break;
			}
		}
		if ($tipo != NULL) {
			if ($condizioni != NULL)
				$condizioni .= " AND ";
			switch($tipo) {
				case "get":
					$condizioni .= "attivita LIKE 'Sta inviando dei dati alla pagina tramite metodo GET:%'";
					break;
				case "post":
					$condizioni .= "attivita LIKE 'Sta inviando dei dati alla pagina tramite metodo POST:%'";
					break;
				default:
					$condizioni .= "attivita LIKE '%'";
					break;
			}
		}
		if (($ip == NULL) AND ($periodo == NULL) AND ($attv == NULL)) {
			$condizioni = "data>'".(mktime(0,0,0,date("n",time()), date("j",time()), date("Y",time())))."'";
		}
		$sql = mysql_query("SELECT * FROM $nome_tabella_log WHERE ".$condizioni." ORDER BY ID DESC LIMIT 0,".$this->righe_max.";") or die("Errore ".mysql_errno() . "<br>" .mysql_error());
		echo("<div class=\"contenitore_log\">");
		if (mysql_num_rows($sql)>0) {
			echo("<table class=\"log_table\">");
			while ($dati = mysql_fetch_assoc($sql)) {
				if ($colore_sfondo_tabella == NULL) {
					$colore = explode(".", $dati["ip"]);
					$color = "#".dechex($colore[0]) . dechex($colore[2]) . dechex($colore[3]);
					$color = $this->schiarisci($color);
				} else
					$color = $colore_sfondo_tabella;
				$log_ip=$dati["ip"];
				if (mysql_num_rows(mysql_query("SELECT * FROM $nome_tabella_ban WHERE ip='$log_ip';"))>0)
					$log_ip=$log_ip."<br>[Bannato]";
				echo("<tr><td style=\"background-color: ".$color.";text-align:center;\">".date("j/m/Y H:i",$dati["data"])."</td><td style=\"background-color: ".$color.";text-align:center;\">".$log_ip."</td><td style=\"background-color: ".$color.";text-align:center;\"><div class=\"locazione\">".str_replace("/","/<br>",$dati["locazione"])."</div></td>");
				$attivita=$dati["attivita"];
				$attivita=str_replace("&amp;#39;", "&#39;", $attivita);
				$div = "<input type=button class=\"pulsante\" value=\"Mostra/Nascondi dettagli\" onclick=\"javascript:var div = document.getElementById('dettagli".$dati["ID"]."'); ((div.style.display=='block') ? div.style.display='none' : div.style.display='block');\" />";
				$div .= "<br><div class=\"dettagli\" id=\"dettagli".$dati["ID"]."\" style=\"display:none;\">";
				$attivita=str_replace("#dettagli#", $div, $attivita);
				echo("<td style=\"background-color: ".$color.";\">");
				echo("<div class=\"attivita\">");
				echo($attivita."</div></div></td></tr>");
			}
			echo("</table>");	
		} else
			echo("<table class=\"log_table\"><tr><td>Nessun log disponibile</td></tr></table>");
		echo("</div>");
		return true;
	}
	echo("</body></html>");
	return false;
}

function schiarisci($color) {
	$color=str_replace("#","",$color);
	$r = hexdec(substr($color, 0, 2));
	$g = hexdec(substr($color, 2, 2));
	$b = hexdec(substr($color, 4, 2));
	if (($r+100) <= 255)
		$r+=100;
	if (($g+100) <= 255)
		$g+=100;
	if (($b+100) <= 255)
		$b+=100;
	return "#".dechex($r).dechex($g).dechex($b);
}

function Ban() {
	$log_ban=$this->log_ban;
	$nome_tabella_ban=$this->nome_tabella_ban;
	$nome_tabella_log=$this->nome_tabella_log;
	//Creo la tabella per i dati di ban se inesistente
	$sql=mysql_query("CREATE TABLE IF NOT EXISTS $nome_tabella_ban (ID bigint(20) NOT NULL AUTO_INCREMENT, data int(20) NOT NULL, ip text NOT NULL, PRIMARY KEY (ID));") OR die("Script guardian (ban)\n<br>Err: ".mysql_errno()."<br>".mysql_error());
	//Eliminazione vecchi ban
	mysql_query("DELETE FROM $nome_tabella_ban WHERE data<'".time()."';") or die("Errore pulizia ban scaduti");
	//Verifico se il visitatore corrente è bannato
	$sql = mysql_query("SELECT * FROM $nome_tabella_ban WHERE ip='".$_SERVER['REMOTE_ADDR']."';");
	$dati=mysql_fetch_assoc($sql);
	if ((mysql_num_rows($sql)>0) OR ($_COOKIE['banned']=="true")) { //mostro messaggio di ban
		setcookie("banned","true",time()+($num*86400));
		echo ("<div style=\"margin:auto; padding:10px; font-size:16px; font-family: 'Arial', sans-serif; width:300px; border:1px solid black\">");
		echo("Sei stato <b>bannato</b> da questo sito.<br>Il ban scadr&agrave in data:<br>".date("j/m/Y", $dati["data"])." alle ore ".date("H:i", $dati["data"]));
		echo("</div>");
		$attivita = "E&#39; stato bloccato poich&egrave bannato";
		$url=$this->filtra($_SERVER['SCRIPT_URL']);
		$ip=$_SERVER['REMOTE_ADDR'];
		$data=time();
		if ($log_ban===true)
			mysql_query("INSERT INTO $nome_tabella_log VALUES (NULL, '$data', '$ip', '$url', '$attivita');") or die("Errore script guardian<br>Errore inserimento log nel database #2!<br>".mysql_error());
		die("");
	}
}

} //Fine classe

/* Funzioni */
function Ban() {
	$guardian = new Guardian();
	$guardian->Ban();
}

function Registra() {
	$guardian = new Guardian();
	$guardian->Registra();
}

if (substr($_SERVER['SCRIPT_URL'], -12) == "guardian.php") {
	$guardian = new Guardian();
	include($guardian->database_url);
}
//Ban();
Registra();
?>
