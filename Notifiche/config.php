<?php
$db = mysql_connect("localhost", "user", "pass") or die("Connessione persa");
mysql_select_db("my_demophp", $db) or die("Errore nella selezione del database");
?>
