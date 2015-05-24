<?php
$db = mysql_connect("localhost", "username", "password") or die("Connessione fallita.");
mysql_select_db("database", $db) or die("Errore nella selezione database.");
?>