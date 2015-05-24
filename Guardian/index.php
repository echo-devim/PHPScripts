<?php
include("config.php");
include("guardian.php");
?>
<html>
<head>
<title>Esempio log</title>
<link rel="stylesheet" type="text/css" href="../demo_notifiche/res/notifiche_bardown.css">
<script type="text/javascript" src="../demo_notifiche/res/jquery.js"></script>
<script type="text/javascript" src="../demo_notifiche/res/scroll.js"></script>
</head>
<body>
<form method=POST action="">
Nome: <input type=text name=nome /><br />
Cognome: <input type=text name=cognome /><br />
<input type=submit value="Invia" />
</form>
<br><br>
<?php
echo("Ciao ".$_POST['nome']);
echo("<br><br><b>Le tue attività in questa pagina verranno registrate. Per accedere al pannello admin</b>");
echo(" <a href=guardian.php>cliccare qui</a> (password: admin)");
echo("<br>Il ban di un ip è disabilitato.");
include("../demo_notifiche/notifiche.php");
CaricaNotifiche();

?>
</body>
</html>
