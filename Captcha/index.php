<html>
<head><title>Test</title>
</head>
<body>
<div>
<img src="captcha.php" style="border: 1px solid black;"></img>
<br />
<input type=button value="Refresh" onclick="javascript:document.images[0].src='captcha.php?'+new Date();" />
</div>
</body>
</html>