<?php
include("settings.php");
?>
<html>
<head>
<title>Login</title>
<link rel="stylesheet" type="text/css" href="<?php echo($cartella); ?>res/stile1/commenti.css">
</head>
<body>

<span style="top: 50%;left: 50%;margin-top: -200px;margin-left: -100px;position: absolute;">
<?php
$pass = $_POST['pass'];
echo("<div id=\"commenti\" style=\"width:200px;\">");
if ($pass==$password_admin) {
	setcookie("admin", md5($password_admin), time()+(86400*$durata_login), "/");
	echo("Login riuscito!<br>Attendere il reindirizzamento..");
	header("Refresh: 2;URL=\"".$url."\"");
} else {
	echo("<form action=\"\" method=POST>");
	echo("Password: <input type=password name=pass /> <input type=submit id=\"pulsante\" value=\"Login\" />");
	echo("</form>");
}
?>
</span>
</body>
</html>