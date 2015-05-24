<html>
<head><title>Esempio notifiche</title>
<link rel="stylesheet" type="text/css" href="res/notifiche_bardown.css">
<script type="text/javascript" src="res/jquery.js"></script>
<script type="text/javascript" src="res/scroll.js"></script>
</head>
<body>
<?php
include("config.php");
include("notifiche.php");
$notifica=$_GET['notifica'];
if ($notifica=="true"){
	notifica(NULL, NULL, "Questa è una notifica di prova. Essendo solo una <u>demo</u> tra cinque minuti questa notifica verrà cancellata automaticamente.", 0.00347222, $_SERVER['REMOTE_ADDR']);
	
	/*
	setcookie("user", "abc", time()+300);
	setcookie("pass", "def", time()+300);
	notifica("user;pass", "abc;def", "Questa è una notifica di prova. Essendo solo una demo tra cinque minuti questa notifica non sarai più in grado di vederla.");
	*/
}
CaricaNotifiche();
?>

<input type=button value="Mandami una notifica!" style="text-align:center;font-size:100px;" onclick="javascript:document.location.href='?notifica=true';" />
<br><br><br>
<span style="font-size:100px;">Questa <br>è <br>solamente <br>una <br>pagina <br>di <br>prova</span>
</body>
</html>
