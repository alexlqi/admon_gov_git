<?php
include("includes/config.php");
include("includes/class.forms.php");
include("includes/funciones.php");
?>
<html>
<head>
</head>
<body>
<?php 
try {
	$bd=new formas($dsnWriter);
} catch (PDOException $e) {
	echo $e->getMessage();
}

$gv1=NULL;
#arbol_admon();
arbol_admon_presupuesto();

include "includes/diagram/class.diagram.php";

$g = new Diagram();
$g->SetRectangleBorderColor(124, 128, 239);
$g->SetRectangleBackgroundColor(194, 194, 239);
$g->SetFontColor(255, 255, 255);
$g->SetBorderWidth(0);
$g->SetData($gv1);
//$g->Draw();

var_dump($gv1);

?>
</body>
</html>