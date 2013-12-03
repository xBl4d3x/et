<?php
if(!headers_sent()){
	header( "HTTP/1.1 500 Internal Server Error" );
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Rewrite engine off</title>
</head>
<body>
<h1 style="margin: 100px auto;text-align: center;">ET Platform</h1>
<div style="margin: 30px auto; text-align:center;border:1px solid #000000;background: #99CCFF;font-weight: bold;font-size: 20px;width:600px;">
	Your HTTP server rewrite engine is not working.<br/>
	Please check web server settings.
</div>
</body>
</html>
