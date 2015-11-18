<?php
#funciones para el sistema de admon_gov

function listar(&$elem1,$key){
	global $work_chart_tmp;
	#echo "$key";
	#var_dump($elem1);
	#$work_chart_tmp[];
}

function arbol_str($arr, $n = 0) {
    $max = $n;
    @ksort($arr);
    foreach ($arr as $item) {
        if (is_array($item)) {
            $max = max($max, arbol_str($item, $n + 1));
        }else{
        	# si no es array me trae el valor del item
        	$pos=$max-2; # indica la posición del arbol
        	if($pos>0){
        		for ($i=0; $i < $pos; $i++) { 
	        		# code...
	        		echo "     |";
	        	}
	        	echo "\n";
        		for ($i=0; $i < $pos; $i++) { 
	        		# code...
	        		echo "     ";
	        		echo "|";
	        	}
	        	echo "---";
	        	echo $item;
        	}else{
				echo $item;
        	}
        	echo "<br>";
        }
    }
    return $max;
}

function arbol_admon_presupuesto(){
	global $bd, $gv1;

	$work_unit=array();
	$work_chart=array();

	try {
		$sql="SELECT 
			t1.ID_UNIT,
		    if(t2.MONTO>0,concat(t1.NOMBRE,' (Presupuesto $',SUM(t2.MONTO),')'),concat(t1.NOMBRE,' (Presupuesto $0)')) as NOMBRE
		FROM work_unit t1
		LEFT JOIN work_unit_resource t2 ON t1.ID_UNIT=t2.ID_UNIT
		GROUP BY t1.ID_UNIT;";
		$res=$bd->query($sql);
		#var_dump($res->fetchAll(PDO::FETCH_ASSOC));
		foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $value) {
			# code...
			$work_unit[$value["ID_UNIT"]]["nombre"]=$value["NOMBRE"];
		}
	} catch (Exception $e) {
		echo $e->getMessage();
	}

	# una vez teniendo todos los work units, tenemos que escribir la ruta de cada work unit
	# se debe concatenar el padre por cada work unit hasta tener una rut así top_1_3_5

	$ruta="";
	$ID_UNIT_PADRE="ctrl";

	###NOTA: top debería cambiarse por el ID o nombre del gobierno por ejemplo top=Apodaca
	foreach ($work_unit as $ID_UNIT => $value) {
		try {
			$sql="SELECT * FROM work_chart WHERE ID_UNIT=$ID_UNIT;";
			$res=$bd->query($sql);
			$data=$res->fetchAll(PDO::FETCH_ASSOC);
			$ID_UNIT_PADRE=$data[0]["ID_UNIT_PADRE"];
			if($ID_UNIT_PADRE==NULL){
				$ruta="top_".$ID_UNIT; # a partir de la segunda se escribe el $ID_UNIT_PADRE_$ruta
			}else{
				$ruta=$ID_UNIT_PADRE."_".$ID_UNIT; # en la primera vez se escribe el ID_UNIT_PADRE_ID_UNIT
			}
		} catch (Exception $e) {
			echo $e->getMessage();
			break;
		}

		# while para escribir la ruta
		while($ID_UNIT_PADRE!=NULL){
			try {
				$sql="SELECT * FROM work_chart WHERE ID_UNIT=$ID_UNIT_PADRE;";
				$res=$bd->query($sql);
				$data=$res->fetchAll(PDO::FETCH_ASSOC);
				$ID_UNIT_PADRE=$data[0]["ID_UNIT_PADRE"];
				if($ID_UNIT_PADRE==NULL){
					$ruta="top_".$ruta; # a partir de la segunda se escribe el $ID_UNIT_PADRE_$ruta
				}else{
					$ruta=$ID_UNIT_PADRE."_".$ruta; # a partir de la segunda se escribe el $ID_UNIT_PADRE_$ruta
				}
			} catch (Exception $e) {
				echo $e->getMessage();
				break 2;
			}		
		}

		$work_unit[$ID_UNIT]["ruta"]=$ruta;
	}

	# se escribe el work chart
	foreach ($work_unit as $ID_UNIT => $d) {
		#se separa la ruta con explode y se crean arrays
		$rs=explode("_", $d["ruta"]); # Ruta Split
		$rs[0]="'".$rs[0]."'";
		//echo "\$work_chart[".implode('][', $rs)."][\"nombre\"]=".$d["nombre"]."<br>";
		eval("\$work_chart[".implode("][", $rs)."][\"nombre\"]='".$d["nombre"]."';");
		#la forma en que funciona es que cada nivel tiene su nombre y un indice numerico que a su vez tiene lo mismo que el padre, hasta que es la ultima rama ya no tiene indica numerico, solamente el nombre
	}

	$gv1=$work_chart;

	echo "<pre>";
	arbol_str($work_chart);
	echo "</pre>";
}

function arbol_admon(){
	global $bd;
	
	$work_unit=array();
	$work_chart=array();

	try {
		$sql="SELECT 
			*
		FROM work_unit;";
		$res=$bd->query($sql);
		#var_dump($res->fetchAll(PDO::FETCH_ASSOC));
		foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $value) {
			# code...
			$work_unit[$value["ID_UNIT"]]["nombre"]=$value["NOMBRE"];
		}
	} catch (Exception $e) {
		echo $e->getMessage();
	}

	# una vez teniendo todos los work units, tenemos que escribir la ruta de cada work unit
	# se debe concatenar el padre por cada work unit hasta tener una rut así top_1_3_5

	$ruta="";
	$ID_UNIT_PADRE="ctrl";

	###NOTA: top debería cambiarse por el ID o nombre del gobierno por ejemplo top=Apodaca
	foreach ($work_unit as $ID_UNIT => $value) {
		try {
			$sql="SELECT * FROM work_chart WHERE ID_UNIT=$ID_UNIT;";
			$res=$bd->query($sql);
			$data=$res->fetchAll(PDO::FETCH_ASSOC);
			$ID_UNIT_PADRE=$data[0]["ID_UNIT_PADRE"];
			if($ID_UNIT_PADRE==NULL){
				$ruta="top_".$ID_UNIT; # a partir de la segunda se escribe el $ID_UNIT_PADRE_$ruta
			}else{
				$ruta=$ID_UNIT_PADRE."_".$ID_UNIT; # en la primera vez se escribe el ID_UNIT_PADRE_ID_UNIT
			}
		} catch (Exception $e) {
			echo $e->getMessage();
			break;
		}

		# while para escribir la ruta
		while($ID_UNIT_PADRE!=NULL){
			try {
				$sql="SELECT * FROM work_chart WHERE ID_UNIT=$ID_UNIT_PADRE;";
				$res=$bd->query($sql);
				$data=$res->fetchAll(PDO::FETCH_ASSOC);
				$ID_UNIT_PADRE=$data[0]["ID_UNIT_PADRE"];
				if($ID_UNIT_PADRE==NULL){
					$ruta="top_".$ruta; # a partir de la segunda se escribe el $ID_UNIT_PADRE_$ruta
				}else{
					$ruta=$ID_UNIT_PADRE."_".$ruta; # a partir de la segunda se escribe el $ID_UNIT_PADRE_$ruta
				}
			} catch (Exception $e) {
				echo $e->getMessage();
				break 2;
			}		
		}

		$work_unit[$ID_UNIT]["ruta"]=$ruta;
	}

	# se escribe el work chart
	foreach ($work_unit as $ID_UNIT => $d) {
		#se separa la ruta con explode y se crean arrays
		$rs=explode("_", $d["ruta"]); # Ruta Split
		$rs[0]="'".$rs[0]."'";
		//echo "\$work_chart[".implode('][', $rs)."][\"nombre\"]=".$d["nombre"]."<br>";
		eval("\$work_chart[".implode("][", $rs)."][\"nombre\"]='".$d["nombre"]."';");
		#la forma en que funciona es que cada nivel tiene su nombre y un indice numerico que a su vez tiene lo mismo que el padre, hasta que es la ultima rama ya no tiene indica numerico, solamente el nombre
	}



	echo "<pre>";
	arbol_str($work_chart);
	echo "</pre>";
}

?>