<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=encoding">
<title>Insert title here</title>
</head>

<body>
<?php 
$fptr = fopen('table.html', 'w');
fwrite($fptr, "Hallo Welt!\n Jep");
fclose($fptr);
?>
</body>
</html>
