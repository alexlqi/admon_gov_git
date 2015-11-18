<?php //archivo de configuración para os formularios

$dsnReader=array(
	'mysql:host=localhost; dbname=admon_gov; charset=utf8;',
	'entropyd_reader',
	'reader1',
	array(
		PDO::ATTR_EMULATE_PREPARES=>false,
		PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION
	)
);

$dsnWriter=array(
	'mysql:host=localhost; dbname=admon_gov; charset=utf8;',
	'root',
	'',
	array(
		PDO::ATTR_EMULATE_PREPARES=>false,
		PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION
	)
);

$dsnAdmin=array(
	'mysql:host=localhost; dbname=promedic_laboratorio; charset=utf8;',
	'promedic_dw',
	'ngq1a',
	array(
		PDO::ATTR_EMULATE_PREPARES=>false,
		PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION
	),
);

?>