<?php
/* PERSONALIZZAZIONE UTENTE */
/* Script commenti */
$nome_tabella_commenti="commenti"; //Nome della tabella che verrà creata nel database per memorizzare i commenti
$cartella="http://example.org/demo_commenti/commenti/"; //Locazione della cartella contenente lo script (ATTENZIONE: Non va inserito il www prima del nome del sito, es. "http://example.com" e non "http://www.example.com")
$massimo_num_caratteri=1000; //Massimo numero di caratteri che è possibile scrivere in un commento
$attiva_notifica_email=false; //Attiva la notificazione tramite email per i nuovi commenti inseriti
$email_notifica="esempio@finto.it"; //E-mail a cui inviare la notifica di un nuovo commento, per inserire più indirizzi email basta usare il punto e virgola (es. "esempio1@a.it;esempio2@b.it")
$titolo_notifica="Nuovo commento!"; //Titolo dell'email di notifica che verrà inviata
$commenti_per_pagina=5; //Numero di commenti da visualizzare per pagina
$massimo_numero_pagine=100; //Numero massimo di pagine
$larghezza_contenitore_commenti=500; //Larghezza in pixel del box dei commenti
$attiva_link=true; //Attiva il tag per inserire link -> [url]link[/url]
$attiva_smile=true; //Attiva la sostituzione degli smile al testo tipo: :-), ;-), ecc.
$attiva_moderazione=false; //Se vale true i commenti dovranno prima essere approvati dall'admin, se vale false vengono pubblicati tutti i commenti direttamente
$attiva_antiflood=false; //Attiva l'antiflood, ovvero dopo $soglia_antiflood messaggi inviati dallo stesso utente (si basa per ip) non ne fa inviare altri
$soglia_antiflood=3; //Indica il massimo numero di messaggi che uno stesso utente può inviare consecutivamente
$colore_nick_admin="#800000"; //Colore del nick dell'admin, possibili colori: (red, white, black, yellow, ecc.) o inserire l'esadecimale corrispondete (es. #FF0000)
$attiva_captcha=true; //Attiva la verifica captcha. Viene mostrata un'immagine con un codice per essere sicuri che chi commenta sia una persona fisica.
$url = "index.php"; //pagina a cui reindirizzare dopo che ci si è loggati come admin
$password_admin = "password"; // Password per loggarsi come admin
$durata_login=5; //Indica la durata in login in giorni, di default vale per 5 giorni
//Trucco: la durata è in giorni, ma si può facilmente convertire in ore, ad esempio se si mette
//$durata_login=1/24 la durata è di un ora, quindi per farlo ad esempio di due ore si può usare
//$durata_login=(1/24)*2
 //Possibili caratteri che andranno a costituire il codice mostrato
 $lunghezza_min_nick=3; //Lunghezza minima di caratteri che deve avere il nickname
 $lunghezza_min_com=3; //Lunghezza minima di caratteri che deve avere il commento
 /* Script captcha */
$caratteri = array(/*'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',*/
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
			'1', '2', '3', '4', '5', '6', '7', '8', '9');
$lunghezza_codice=7; //Numero di caratteri da cui sarà composto il codice
$salvataggio_database=false; /* Se settato su true il codice verrà salvato lato server nel database in un'apposita tabella, 
se settato su false il codice verrà salvato lato client su un cookie (possibile manipolazione da parte dell'utente, perciò si raccomanda
di usare la password di protezione) */
$password_cookie = $password_admin; //Utilizzata solamente se il salvataggio del codice avviene su cookie (cioè se salvataggio_database=false)
$larghezza = 90; //Larghezza dell'immagine di output (in pixel)
$altezza = 30; //Altezza dell'immagine di output (in pixel) (almeno più di 10 pixel)
$attiva_disturbo_linee=true; //Attiva il filtro disturbo linee
$numero_linee=2;
$attiva_disturbo_cerchi=false; //Attiva il filtro disturbo cerchi
$numero_cerchi=10;
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
$spaziatura=12; //Valore che indica la distanza tra una lettera e la successiva, maggiore è e più le lettere saranno distanziate
$font_captcha="Verdana.TTF"; //Font del testo che verrà visualizzato nell'immagine
?>