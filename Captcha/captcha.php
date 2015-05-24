<?
/********************
 * Author: Gregorio *
 * Script: Captcha  *
 * Version: 1.0     *
 ********************/
 
 /*
 ISTRUZIONI:
 Includere lo script nella propria pagina web tramite la seguente stringa:
 <img src="PERCORSO/captcha.php" alt="captcha" style="border:1px solid black;"></img>
 Questa riga di codice mostrerà il captcha nel punto in cui verrà inserita.
 Se captcha.php si trova nella stessa cartella del file php in cui volete includere lo script, si può anche omettere PERCORSO.
 
 Verifica del codice:
 - da database:
	basta usare le seguenti istruzioni:
	$ip = $_SERVER['REMOTE_ADDR'];
	$sql = mysql_query("SELECT * FROM captcha WHERE ip='$ip';");
	$dati = mysql_fetch_assoc($sql);
	ora $dati['codice'] conterrà il codice generato dal captcha
 - da cookie:
	utilizzare:
	$codice = $_COOKIE['codice'];
	per motivi di sicurezza il codice è criptato, per eseguire il confronto utilizzare:
	if (md5("codice utente".$password_cookie) == $codice) { //se il codice utente è uguale al codice captcha..
 */
 
 /* VARIABILI DI CONFIGURAZIONE */
 //Possibili caratteri che andranno a costituire il codice mostrato
$caratteri = array(/*'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',*/
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
			'1', '2', '3', '4', '5', '6', '7', '8', '9');
$lunghezza_codice=7; //Numero di caratteri da cui sarà composto il codice
$salvataggio_database=true; /* Se settato su true il codice verrà salvato lato server nel database in un'apposita tabella, 
se settato su false il codice verrà salvato lato client su un cookie (possibile manipolazione da parte dell'utente, perciò si raccomanda
di usare la password di protezione) */
$password_cookie = "123"; //Utilizzata solamente se il salvataggio del codice avviene su cookie (cioè se salvataggio_database=false)
$larghezza = 90; //Larghezza dell'immagine di output (in pixel)
$altezza = 30; //Altezza dell'immagine di output (in pixel) (almeno più di 10 pixel)
$attiva_disturbo_linee=true; //Attiva il filtro disturbo linee
$numero_linee=1;
$attiva_disturbo_cerchi=true; //Attiva il filtro disturbo cerchi
$numero_cerchi=40;
$text_size=12; //Grandezza testo
/* Settaggi salvataggio database */
$nome_tabella_captcha = "captcha"; //Nome della tabella in cui verranno salvati i codici
$colore_sfondo = "F0F0F0"; //Colore (in esadecimale) dello sfondo (NOTA: non va inserito il # prima del codice esadecimale)
$colora_testo=true; //se settato a true tutti i caratteri del codice saranno colorate diversamente
$colore_testo="696969"; //Colore del testo di default in caso la colorazione casuale dei singoli caratteri fosse disabilitata
$usa_colori_chiari=false; //Se settato a true i colori del testo tenderanno ad essere più chiari (ideale per sfondi scuri) altrimenti se a false
//i colori del testo tenderanno ad essere più scuri (ideale per sfondi chiari)
$colora_linee=true; //impostazione per colorare ciascuna linea di un colore diverso
$colore_linee="696969"; //colore di default delle righe se l'opzione precedente viene settata a false
$colora_cerchi=true; //impostazione per colorare ciascun cerchio di un colore diverso
$colore_cerchi="696969"; //colore di default dei cerchi se l'opzione precedente viene settata a false
$dimensione_cerchi=1; //Grandezza dei cerchi (1 si ha un punto)
$sfasatura_testo=10; //altezza della sfasatura del testo (0 per testo allineato, maggiori valori assume maggiore sarà la sfasatura)
$rotazione_testo=20; //raggio di rotazione in gradi che casualmente un carattere può assumere
$spaziatura=10; //Valore che indica la distanza tra una lettera e la successiva, maggiore è e più le lettere saranno distanziate
$font_captcha="Verdana.TTF"; //Font del testo che verrà visualizzato nell'immagine
 /* --------------------------- */

//Compongo il codice
for ($i=0; $i<$lunghezza_codice; $i++) {
	$num=rand(0, count($caratteri));
	$text.=$caratteri[$num];
}
if ($salvataggio_database === false) {
	//setto il cookie con il codice criptato
	setcookie("codice", md5($text.$password_cookie), time()+600, "/"); //validità di 10 minuti
} else {
	//Inserisco il codice nel database
	include("config.php");
	$ip = $_SERVER['REMOTE_ADDR'];
	$data = time()+600; //validità di 10 minuti
	//In caso non esistesse la tabella la creo
	$sql=mysql_query("CREATE TABLE IF NOT EXISTS $nome_tabella_captcha (ID int(20) NOT NULL AUTO_INCREMENT, ip text NOT NULL, codice text NOT NULL, data bigint(20) NOT NULL, PRIMARY KEY (ID))") OR die("Err: ".mysql_errno()."<br>".mysql_error());
	//Vedo se all'ip corrente è stato assegnato già un codice
	$sql=mysql_query("SELECT * FROM $nome_tabella_captcha WHERE ip='$ip';") or die("Error select#1");
	if (mysql_num_rows($sql)==0) //se ancora non aveva un codice assegnato, salvo il codice
		mysql_query("INSERT INTO $nome_tabella_captcha VALUES (NULL, '$ip', '$text', '$data');") or die("Error insert#2");
	else //Altrimenti aggiorno con il codice corrente
		mysql_query("UPDATE $nome_tabella_captcha SET codice='$text',data='$data' WHERE ip='$ip';") or die("Error update#3");
	//Cancello captcha scaduti
	$data_attuale=time();
	mysql_query("DELETE FROM $nome_tabella_captcha WHERE data<='$data_attuale';") or die("Error delete#4");
}
header("Content-type: image/png");
$im = @imagecreate($larghezza, $altezza) or die("Cannot Initialize new GD image stream");
//Colore di sfondo
$red=substr($colore_sfondo, 0, 2);
$green=substr($colore_sfondo, 2, 2);
$blue=substr($colore_sfondo, 4, 2);
$background_color = imagecolorallocate($im, hexdec($red), hexdec($green), hexdec($blue));
$x=((int)($larghezza/2))-((((int)($lunghezza_codice/2))*$spaziatura)+((int)($text_size)/2));
for ($i=0;$i<strlen($text);$i++) {
	$meta = (int)($sfasatura_testo/2);
	$y = ((int)($altezza/2)+((int)($text_size)/2))+ rand(-1*($meta), $meta); //Altezza casuale - effetto scritta non allineata
	//0,0,0 nero => 255, 255, 255 bianco
	if ($colora_testo===false) {
		$red=substr($colore_testo, 0, 2);
		$green=substr($colore_testo, 2, 2);
		$blue=substr($colore_testo, 4, 2);
		$text_color = imagecolorallocate($im, hexdec($red), hexdec($green), hexdec($blue));
	} else {
		//Se per lo sfondo ho utilizzato un colore scuro, tenderò a mostrare caratteri di colore chiaro
		if ($usa_colori_chiari===true) {
			$text_color = imagecolorallocate($im, rand(55,255), rand(55,255), rand(55,255)); //colori chiari
		} else { //altrimenti se lo sfondo è chiaro userò colori più scuri
			$text_color = imagecolorallocate($im, rand(0,200), rand(0,200), rand(0,200)); //colori scuri
		}
	}
	$meta = (int) ($rotazione_testo/2);
	imagettftext($im, $text_size, rand(-1*($meta),$meta), $x, $y, $text_color, $font_captcha, substr($text,$i,1));
	$x = $x+$spaziatura;
}
//Filtro disturbo linee
if ($attiva_disturbo_linee===true) {
	for ($i=0;$i<$numero_linee;$i++) {
			if ($colora_linee===false) {
				$red=substr($colore_linee, 0, 2);
				$green=substr($colore_linee, 2, 2);
				$blue=substr($colore_linee, 4, 2);
				$line_color = imagecolorallocate($im, hexdec($red), hexdec($green), hexdec($blue));
			} else
				$line_color = imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
		imageLine($im, 0, rand(1, $altezza-1), $larghezza, rand(1,$altezza-1), $line_color);
	}
}

//Filtro disturbo cerchi
if ($attiva_disturbo_cerchi===true) {
	for ($i=0;$i<$numero_cerchi;$i++) {
			if ($colora_cerchi===false) {
				$red=substr($colore_cerchi, 0, 2);
				$green=substr($colore_cerchi, 2, 2);
				$blue=substr($colore_cerchi, 4, 2);
				$circle_color = imagecolorallocate($im, hexdec($red), hexdec($green), hexdec($blue));
			} else
				$circle_color = imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
		imageEllipse($im, rand(0, ($larghezza-15)),rand(1, $altezza-10), $dimensione_cerchi, $dimensione_cerchi, $circle_color);
	}
}

imagepng($im);
imagedestroy($im);
?>
    