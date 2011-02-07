<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=encoding">
<title>Insert title here</title>
</head>

<body>
<?php 
require_once 'qtl_functions.php';
$inputArray = array(array(0=>array(0=>array('start'=>10,'end'=>20,'chr'=>10),1=>array('start'=>20,'end'=>30,'chr'=>11)),1=>array(0=>array('start'=>10,'end'=>20,'chr'=>10),0=>array('start'=>11,'end'=>21,'chr'=>1))));
mapSynGroups($inputArray,$groups2);
?>
</body>
</html>
