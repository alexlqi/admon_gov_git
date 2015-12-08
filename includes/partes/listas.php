<?php //la sesión ya va a estar iniciada porque este es un archivo include
//sección de los permisos
$empresa=$_SESSION["empresa"];

//poner el elemento flotante de alerta
$view->showRespuesta();

$seccion=str_replace(".php","",str_replace("/","",$_SERVER["SCRIPT_NAME"]));
if(!$view->permisoBarra($seccion)){
	//indicar que no puede ver esta sección
	echo '<div class="no_auth" align="center">No tiene permiso para ver esta sección.</div>';
	exit;
}
// archivos a incluir
include("includes/config.php");
include("includes/class.forms.php");
$rBD=new formas($dsnReader);
$cliente=@$_SESSION["CLIENTE_ID"];
$cat=$_SESSION["cat"];
$relleno=false;
$folio=(@$_GET["folio"])? $_GET["folio"]:false;
$data=array();

//preparar para el ORDER BY
$orderby=" ";
$where_cliente=" WHERE t1.CLIENTE_ID=$cliente ";
$where_nombre=($nombre=@$_GET["nombre"])? " AND (t2.NOMBRE LIKE '%$nombre%' OR t2.APELLIDOS LIKE '%$nombre%') " : '' ;
$where_folio=($folio)? " AND t1.id=$folio " : '' ;
$where_fecha=" ";
switch($ctrlList){
	case 'resultados':
		$data=array();
		$th=array();
		if(@$_GET["fecha"]!=""){
			$fecha1=date("Y-m-d H:i:s",strtotime($_GET["fecha"]));
			$fecha2=date("Y-m-d H:i:s",strtotime($_GET["fecha"]." + 1 day -1 sec"));
			$where_fecha=" AND t1.FECHA_IN BETWEEN '$fecha1' AND '$fecha2' ";
			$sql="SELECT
				t1.id as FOLIO,
				t1.CLIENTE_ID,
				CONCAT(t2.NOMBRE,' ',t2.APELLIDOS) AS NOMBRE,
				t2.GENERO,
				date_format(t5.FECHA_ATENCION,'%d/%m/%Y') as FECHA_ATENCION,
				t3.NOMBRE as CONCEPTO,
				t1.*,
				t4.*
			FROM examenes t1
			INNER JOIN pacientes t2 ON t1.PACIENTE_ID=t2.PACIENTE_ID
			INNER JOIN conceptos t3 ON t1.CONCEPTO_ID=t3.CONCEPTO_ID
			LEFT JOIN resultados t4 ON t1.id=t4.EXAMEN_ID
			LEFT JOIN examenes_adminte t5 ON t1.id=t5.FOLIO_RASTREO
			$where_cliente
			$where_fecha
			$where_folio
			$orderby;";
		}else{
			$sql="SELECT
				t1.id as FOLIO,
				t1.CLIENTE_ID,
				CONCAT(t2.NOMBRE,' ',t2.APELLIDOS) AS NOMBRE,
				t2.GENERO,
				date_format(t5.FECHA_ATENCION,'%d/%m/%Y') as FECHA_ATENCION,
				t3.NOMBRE as CONCEPTO,
				t1.*,
				t4.*
			FROM examenes t1
			INNER JOIN pacientes t2 ON t1.PACIENTE_ID=t2.PACIENTE_ID
			INNER JOIN conceptos t3 ON t1.CONCEPTO_ID=t3.CONCEPTO_ID
			LEFT JOIN resultados t4 ON t1.id=t4.EXAMEN_ID
			LEFT JOIN examenes_adminte t5 ON t1.id=t5.FOLIO_RASTREO
			$where_cliente
			$where_nombre
			$where_folio
			$orderby;";
		}
		
		#preparamos toda la info
		$r=$rBD->query2array($sql);
		foreach($r["data"] as $id=>$d){
			//cambiamos el id del
			$rowId=$d["id"];
			//valores para rellenar campos
			$relleno[]=array(
				"EXAMEN_ID"=>$d["EXAMEN_ID"],
				"resultado"=>$d["resultado"],
				"razon_uno"=>$d["razon_uno"],
				"razon_dos"=>$d["razon_dos"],
				"hallazgos"=>$d["hallazgos"],
			);
			unset($d["EXAMEN_ID"],$d["razon_uno"],$d["razon_dos"],$d["hallazgos"],$d["resultado"]);
			
			//si no hay folio hay que poner el radio button
			if(!$folio){
				$data[$rowId]["modif"]="<input type='radio' class='check' name='folio' value='".$rowId."' />";
			}
			
			//valores para lista
			foreach($d as $c=>$v){					
				$data[$rowId][$c]=$v;
			}
			//AÑADIR LOS CAMPOS PARA LLENAR por el doctor
			if($_SESSION["cat"]=="medico"){
				if($folio){
					$data[$rowId]["resultado"]="<input type='hidden' name='p[$rowId][EXAMEN_ID]' value='".$rowId."'><select class='resultado' name='p[$rowId][resultado]'><option selected='selected' disabled='disabled' value=''>-- Elige una opción --</option><option value='CUMPLE CRITERIOS'>CUMPLE CRITERIOS</option><option value='CALIFICA PARA CIERTAS ÁREAS'>CALIFICA CIERTAS ÁREAS</option><option value='NO CUMPLE CRITERIOS'>NO CUMPLE CRITERIOS</option></select>";
					$data[$rowId]["razon_uno"]="<textarea class='prompt razon_uno' name='p[$rowId][razon_uno]'></textarea>";
					$data[$rowId]["razon_dos"]="<textarea class='prompt razon_dos' name='p[$rowId][razon_dos]'></textarea>";
					$data[$rowId]["hallazgos"]="<textarea class='prompt hallazgos' name='p[$rowId][hallazgos]'></textarea>";
				}else{
					$data[$rowId]["resultado"]="<span class='resultado'></span>";
					$data[$rowId]["razon_uno"]="<span class='razon_uno'></span>";
					$data[$rowId]["razon_dos"]="<span class='razon_dos'></span>";
					$data[$rowId]["hallazgos"]="<span class='hallazgos'></span>";
				}
			}else{
				$data[$rowId]["resultado"]="<span class='resultado'></span>";
				$data[$rowId]["razon_uno"]="<span class='razon_uno'></span>";
				$data[$rowId]["razon_dos"]="<span class='razon_dos'></span>";
				$data[$rowId]["hallazgos"]="<span class='hallazgos'></span>";
			}
		}
	break;
	case 'examenes':
		$data=array();
		$th=array();
		$orderby="ORDER BY t1.id DESC ";
		if(@$_GET["fecha"]!=""){
			$fecha1=date("Y-m-d H:i:s",strtotime($_GET["fecha"]));
			$fecha2=date("Y-m-d H:i:s",strtotime($_GET["fecha"]." + 1 day -1 sec"));
			$where_fecha=" AND t1.FECHA_IN BETWEEN '$fecha1' AND '$fecha2' ";
			$sql="SELECT
				t1.id as FOLIO,
				t1.CLIENTE_ID,
				t2.NOMBRE,
				t2.APELLIDOS,
				t2.FECHA_NACIMIENTO,
				t2.GENERO,
				t3.NOMBRE as CONCEPTO,
				t1.*
			FROM examenes t1
			INNER JOIN pacientes t2 ON t1.PACIENTE_ID=t2.PACIENTE_ID
			LEFT JOIN conceptos t3 ON t1.CONCEPTO_ID=t3.CONCEPTO_ID
			$where_cliente
			$where_fecha
			$orderby;";
		}else{
			$sql="SELECT
				t1.id as FOLIO,
				t1.CLIENTE_ID,
				t2.NOMBRE,
				t2.APELLIDOS,
				t2.FECHA_NACIMIENTO,
				t2.GENERO,
				t3.NOMBRE as CONCEPTO,
				t1.*
			FROM examenes t1
			INNER JOIN pacientes t2 ON t1.PACIENTE_ID=t2.PACIENTE_ID
			LEFT JOIN conceptos t3 ON t1.CONCEPTO_ID=t3.CONCEPTO_ID
			$where_cliente
			$where_nombre
			$orderby;";
		}
		$r=$rBD->query2array($sql);
		
		#preparamos toda la info
		foreach($r["data"] as $id=>$d){
			//cambiamos el id del
			$rowId=$d["id"];
			$data[$rowId]["check"]="<input type='checkbox' class='check' name='row[]' value='".json_encode($d)."' data-id='".$rowId."' />";
			foreach($d as $c=>$v){
				$data[$rowId][$c]=$v;
			}
		}
	break;
	case 'pacientes':
		//cambiar de t2 a t1
		$where_nombre=str_replace("t2.","t1.",$where_nombre);
		$sql="SELECT
			t1.*
		FROM pacientes t1
		$where_cliente
		$where_nombre
		$orderby;";
		$r=$rBD->query2array($sql);
		
		#preparamos toda la info
		$data=array();
		$th=array();
		foreach($r["data"] as $id=>$d){
			//cambiamos el id del
			$rowId=$d["PACIENTE_ID"];
			$data[$rowId]["check"]="<input type='checkbox' class='check' name='row[]' value='".json_encode($d)."' data-id='".$rowId."' />";
			foreach($d as $c=>$v){
				$data[$rowId][$c]=$v;
			}
		}
	break;
	case 'usuarios':
		$sql="SELECT
			*
		FROM usuarios t1
		$where_cliente
		$orderby;";
		$r=$rBD->query2array($sql);
		
		#preparamos toda la info
		$data=array();
		$th=array();
		
		foreach($r["data"] as $id=>$d){
			//cambiamos el id del
			$rowId=$d["id"];
			//eliminar los datos sensibles
			unset($d["PASSWORD"]);
			$data[$rowId]["check"]="<input type='checkbox' class='check' name='row[]' value='".json_encode($d)."' data-id='".$rowId."' />";
			foreach($d as $c=>$v){
				$data[$rowId][$c]=$v;
			}
		}
	break;
	case 'empresas':
		$sql="SELECT
			*
		FROM empresas t1
		$where_cliente
		$orderby;";
		$r=$rBD->query2array($sql);
		
		#preparamos toda la info
		$data=array();
		$th=array();
		
		foreach($r["data"] as $id=>$d){
			//cambiamos el id del
			$rowId=$d["CLIENTE_ID"];
			//eliminar los datos sensibles
			unset($d["PASSWORD"]);
			$data[$rowId]["check"]="<input type='checkbox' class='check' name='row[]' value='".json_encode($d)."' data-id='".$rowId."' />";
			foreach($d as $c=>$v){
				$data[$rowId][$c]=$v;
			}
		}
	break;
}

//sección de busquedas
switch($ctrlList){
	case 'resultados':
	case 'examenes':
?>
	<form action="<?php echo ROOT.$_SERVER["SCRIPT_NAME"]; ?>">
    	<input type="hidden" name="list" value="1" />
        <table>
        <tr>
        	<td>
            	<label>Nombre:</label><input type="text" class="nombre" name="nombre" value="<?php echo $nombre=(@$_GET["nombre"]!="")?$_GET["nombre"]:''; ?>" />
            </td>
            <td>
            	<label>Fecha:</label><input type="text" class="fecha" data-formato="yy-mm-dd" name="fecha" value="<?php echo $fecha=(@$_GET["fecha"]!="")?$_GET["fecha"]:date("Y-m-d"); ?>" />
            </td>
            <td>
            	<input type="submit" value="Buscar" />
            </td>
        </tr>
        </table>
    </form><br />
<?php 
	break;
	case 'pacientes':
?>
	<form action="<?php echo ROOT.$_SERVER["SCRIPT_NAME"]; ?>">
    	<input type="hidden" name="list" value="1" />
        <table>
        <tr>
        	<td>
            	<label>Nombre:</label><input type="text" class="nombre" name="nombre" value="<?php echo $nombre=(@$_GET["nombre"]!="")?$_GET["nombre"]:''; ?>" />
            </td>
            <td>
            	<input type="submit" value="Buscar" />
            </td>
        </tr>
        </table>
    </form><br />
<?php 
	break;
}
?>
<form id="formexcel" target="_blank" action="scripts/excel.php" method="post" style="display:none;">
    <input type="hidden" class="tablaexp" name="tabla" value="" />
    <input type="hidden" name="name" value="<?php echo $ctrlList; ?>" />
</form>
<form class="listado1" method="post" action="<?php echo ROOT.$_SERVER["SCRIPT_NAME"]."?modif=1"; ?>">
	<div class="wrap_botones">
		<?php $view->botones('.listado1',$ctrlList); ?>
        <input type="button" class="excel" value="Exportar a excel" />
        <?php if(!$folio and $_SESSION["cat"]=="medico" and $ctrlList=="resultados"){echo '<input type="button" data-form=".listado1" class="medicogre" value="Modificar Resultados">';}?>
    </div>
    <table>
        <tr>
            <th>Total: </th>
            <td><?php echo count($data); ?></td>
        </tr>
    </table>
    
    <?php //primer listado ?>
    <table id="principal" cellpadding="0" cellspacing="0" class="tbview sombra1">
        <?php 
        if(!@$r["err"] and count($data)>0){
            switch($ctrlList){
                case "resultados":
                    if(count($data)==0){break;}
                    
                    //sección del control de formulario
					echo '<input type="hidden" name="ctrl" value="gre" />';
					echo '<input type="hidden" name="id_usuario" value="'.$_SESSION["id_usuario"].'" />';
                    
                    $hide=(!$view->super())? array('id_usuario','CLIENTE_ID','PACIENTE_ID','CONCEPTO','GENERO','CONCEPTO_ID','id','FECHA_IN','FECHA_OUT','ESTATUS') : array(); //para ocultar los campos que no se tengan que ver
					if($_SESSION["empresa"]=="LUMI PEOPLE" and !@$_GET["ctrl"]){
						$hide=(!$view->super())? array('id_usuario','CLIENTE_ID','PACIENTE_ID','CONCEPTO','GENERO','CONCEPTO_ID','id','FECHA_IN','FECHA_OUT','ESTATUS','razon_uno','razon_dos','hallazgos') : array(); //para ocultar los campos que no se tengan que ver
					}
                    //escribe los titulos de las columnas
                    echo '<tr>';
                    foreach(reset($data) as $id=>$d){
                        if(!in_array($id,$hide)){
                            echo '<th>'.columnas($id).'</th>';
                        }
                    }
                    echo '</tr>';
                    
                    //escribe los registros
                    foreach($data as $row=>$set){
                        echo '<tr id="r'.$row.'">';
                        foreach($set as $id=>$d){
                            if(!in_array($id,$hide)){
								echo '<td>'.$d.'</td>';		
                            }
                        }
                        echo '</tr>';
                    }
                break;
                case "examenes":
                    if(count($data)==0){break;}
                    $hide=(!$view->super())? array('CLIENTE_ID','FECHA_NACIMIENTO','PACIENTE_ID','CONCEPTO_ID','id','FECHA_OUT') : array(); //para ocultar los campos que no se tengan que ver
                    //escribe los titulos de las columnas
                    echo '<tr>';
                    foreach(reset($data) as $id=>$d){
                        if(!in_array($id,$hide)){
                            echo '<th>'.columnas($id).'</th>';
                        }
                    }
                    echo '</tr>';
                    
                    //escribe los registros
                    foreach($data as $row=>$set){
                        echo '<tr>';
                        foreach($set as $id=>$d){
                            if(!in_array($id,$hide)){
                                echo '<td>'.$d.'</td>';
                            }
                        }
                        echo '</tr>';
                    }
                break;
                case 'pacientes':
                    $hide=(!$view->super())? array('PACIENTE_ID','CLIENTE_ID','MEDICO_ID','TIPO_SERVICIO_ID') : array(); //para ocultar los campos que no se tengan que ver
                    //escribe los titulos de las columnas
                    echo '<tr>';		
                    foreach(@reset($data) as $id=>$d){
                        if(!in_array($id,$hide)){
                            echo '<th>'.columnas($id).'</th>';
                        }
                    }
                    echo '</tr>';
                    
                    //escribe los registros
                    foreach($data as $row=>$set){
                        echo '<tr>';
                        foreach($set as $id=>$d){
                            if(!in_array($id,$hide)){
                                echo '<td>'.$d.'</td>';
                            }
                        }
                        echo '</tr>';
                    }
                break;
                case 'usuarios':
                    $hide=(!$view->super())? array('id','CLIENTE_ID','PASSWORD'):array(); //para ocultar los campos que no se tengan que ver
                    //quitar los campos sensibles
                    unset($data["PASSWORD"]);
                    //escribe los titulos de las columnas
                    echo '<tr>';
                    foreach(reset($data) as $id=>$d){
                        if(!in_array($id,$hide)){
                            echo '<th>'.columnas($id).'</th>';
                        }
                    }
                    echo '</tr>';
                    
                    //escribe los registros
                    foreach($data as $row=>$set){
                        echo '<tr>';
                        foreach($set as $id=>$d){
                            if(!in_array($id,$hide)){
                                echo '<td>'.$d.'</td>';
                            }
                        }
                        echo '</tr>';
                    }
                break;
                case 'empresas':
                    $hide=(!$view->super())? array():array(); //para ocultar los campos que no se tengan que ver
                    //quitar los campos sensibles
                    unset($data["PASSWORD"]);
                    //escribe los titulos de las columnas
                    echo '<tr>';
                    foreach(reset($data) as $id=>$d){
                        if(!in_array($id,$hide)){
                            echo '<th>'.columnas($id).'</th>';
                        }
                    }
                    echo '</tr>';
                    
                    //escribe los registros
                    foreach($data as $row=>$set){
                        echo '<tr>';
                        foreach($set as $id=>$d){
                            if(!in_array($id,$hide)){
                                echo '<td>'.$d.'</td>';
                            }
                        }
                        echo '</tr>';
                    }
                break;
            }
        }
        ?>
    </table>

    <table>
        <tr>
            <th>Total: </th>
            <td><?php echo count($data); ?></td>
        </tr>
    </table>
	
    <div class="wrap_botones">
		<?php $view->botones('.listado1',$ctrlList); ?>
        <input type="button" class="excel" value="Exportar a excel" />
        <?php if(!$folio and $_SESSION["cat"]=="medico" and $ctrlList=="resultados"){echo '<input type="button" data-form=".listado1" class="medicogre" value="Modificar">';}?>
    </div>
    
    <script type="text/javascript">
	var relleno=<?php echo json_encode($relleno); ?>;
	$(document).ready(function(e) {
		$(document).keyup(function(e) {
            $(".medicogre").click();
        });
		
		//datepickers
		if(true){
			date=new Date();
			$.each($(".fecha"),function(){
				_format=$(this).attr("data-formato");
				formato_fecha=(_format)?_format:'yy-mm-dd';
				$(this).datepicker({
					dateFormat:formato_fecha,
					changeYear:true,
					changeMonth:true,
					yearRange: "1940:"+date.getFullYear(),
				});
			});
		}
		
		$(".nombre").keyup(function(e){$(".fecha").val('');});
		
		//exportar a excel
		$(".excel").click(function(e) {
			$(".tablaexp").val($(".listado1").html());
            $("#formexcel").submit();
        });
				
		if(relleno){
			$.each(relleno,function(i,v){
				$.each(v,function(ii,vv){
					if($("#r"+v.EXAMEN_ID+" ."+ii+":not(textarea)")){
						$("#r"+v.EXAMEN_ID+" ."+ii).val(vv);
					}
					if($("#r"+v.EXAMEN_ID+" ."+ii).is("span")){
						if(ii=="resultado" & (vv==null || vv=="")){vv="EN PROGRAMACIÓN";}
						$("#r"+v.EXAMEN_ID+" ."+ii).html(vv);
					}
				});
			});
		}
		
		$("tr:not(.primero)").click(function(e) {
			notodas=true;
			_checkbox=$(this).find(".check");
			if(_checkbox.is(":checked")){
				if(e.target.className!="check"){
					_checkbox.prop("checked",false);
				}
			}else{
				if(e.target.className!="check"){
					_checkbox.prop("checked",true);
				}
			}
			
			$.each($(".check"),function(){
				if(!$(this).is(":checked")){
					notodas=false;
				}
			});
			if(notodas){
				$(".todos").prop("checked",true);
			}else{
				$(".todos").prop("checked",false);
			}
		});
		$(".nuevo").click(function(e) {
            url="<?php echo ROOT.$_SERVER["SCRIPT_NAME"]."?add=1"; ?>";
			window.location=url;
        });
		$(".modificar").click(function(e) {
			form=$(this).attr('data-form');
            cont=false;
			$.each($("input:checked"),function(){
				cont=true;
				return false;
			});
			if(cont){$(form).submit();}
        });
		$(".medicogre").click(function(e) {
            form=$(this).attr('data-form');
			url='<?php echo ROOT.$_SERVER["SCRIPT_NAME"]."?list=1"; ?>';
            cont=false;
			$.each($("input:checked"),function(){
				cont=true;
				return false;
			});
			$(form).attr("method","get");
			$(form).attr("action",url);
			if(cont){$(form).submit();}
        });
		$(".formatos").click(function(e) {
			form=$(this).attr('data-form');
			$(form).attr("action","<?php echo ROOT."/formatos.php"; ?>");
			$(form).attr("target","_blank");
            cont=false;
			$.each($("input:checked"),function(){
				cont=true;
				return false;
			});
			if(cont){$(form).submit();}
        });
		$(".carpetas").click(function(e) {
			form=$(this).attr('data-form');
			$(form).attr("action","<?php echo ROOT."/carpetas.php"; ?>");
			$(form).attr("target","_blank");
            cont=false;
			$.each($("input:checked"),function(){
				cont=true;
				return false;
			});
			if(cont){$(form).submit();}
        });
		$(window).scroll(function(e) {			
            var windscroll= $(window).scrollTop();
			offset=$(".getpdfof").offset();
			if(windscroll>=300){
				$(".getpdfof").addClass('boton_hover');
			}else{
				$(".getpdfof").removeClass('boton_hover');
			}
        });
		$(".getpdfof").click(function(e) {
			form=$(this).attr('data-form');
			$(form).attr("action","<?php echo PDF_ROOT; ?>");
			$(form).attr("method","get");
			$(form).attr("target","_blank");
			$(form).prepend('<input id="genemp" type="hidden" name="emp" value="<?php echo $empresa; ?>" />');
            cont=false;
			$.each($("input:checked"),function(){
				cont=true;
				return false;
			});
			if(cont){$(form).submit();}
			el=document.getElementById("genemp");
			$(el).remove();
			$(form).attr("action","<?php echo ROOT."/resultados.php?modif=1"; ?>");
        });
		$(".eliminar").click(function(e) {
			if( confirm("¿Está seguro que quiere eliminar este registro?") )
			{
				target=$(this).attr("data-form");
				ctrl=$(this).attr("data-ctrl");
				ids=Array();
				$.each($(target+" input:checked").not(".todos"),function(i,v){
					ids[i]=$(this).attr("data-id");
				});
				enthalpy.ajax(
					'scripts/ajaxForm.php',
					{
						id:ids,
						ctrl:ctrl,
					},
					'POST',
					function(r){
						if(!r.err){
							enthalpy.alerta(r.msg);
							setTimeout(function(){window.location='<?php echo ROOT.$_SERVER["SCRIPT_NAME"]."?list=1"; ?>';},
							1500);
						}else{
							console.log(r.data);
							enthalpy.alerta(r.msg);
						}
					}
				);
			}
        });
		$(".guardar").click(function(e) {
			form=$(this).attr('data-form');
			$(form).attr("action","<?php echo ROOT."/carpetas.php"; ?>");
			$(form).attr("target","_blank");
			datos=$(form).serialize();
            cont=true;
			if(cont){
				//$(form).submit();
				enthalpy.ajax(
					'scripts/ajaxForm.php',
					datos,
					'POST',
					function(r){
						if(!r.err){
							alert("Datos guardados exitosamente");
							console.log(r);
							window.location='<?php echo ROOT.$_SERVER["SCRIPT_NAME"]."?list=1"; ?>';
						}else{
							alert(r.msg);
							console.log(r);
						}
					}
				);
			}
        });
		$(".todos").change(function(e) {
            if($(this).is(":checked")){
				$(".check").prop('checked',true);
			}else{
				$(".check").prop('checked',false);
			}
        });
	});
	</script>
</form>