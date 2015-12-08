<?php 
//sección de los permisos
$seccion=str_replace(".php","",str_replace("/","",$_SERVER["SCRIPT_NAME"]));
if(!$view->permisoBarra($seccion)){
	//indicar que no puede ver esta sección
	echo '<div class="no_auth" align="center">No tiene permiso para ver esta sección.</div>';
	exit;
}

//poner el elemento flotante de alerta
$view->showRespuesta();

include("includes/config.php");
include("includes/class.forms.php");
include("includes/class.table.php");
$rBD=new formas($dsnReader);
$wBD=new formas($dsnWriter);
$TE=new listas();

//sección del post para el json
$jsonPost=array();
$post=(isset($_POST["row"])) ? $_POST["row"] : array();
foreach($post as $d){$jsonPost[]=(array) json_decode($d);}
$jsonPost=json_encode($jsonPost);

$cliente=(isset($_SESSION["sd"]))? $_SESSION["sd"] : 0 ;

//datos para superadmin
if($view->super()){
	$empresas=$rBD->query2opt("SELECT CLIENTE_ID, NOMBRE FROM empresas;",array("CLIENTE_ID","NOMBRE"));
	$pacientes=$rBD->query2array("SELECT * FROM pacientes;");
	$conceptos=$rBD->query2array("SELECT * FROM conceptos;");
}else{
	$where_pax=" WHERE CLIENTE_ID=".$_SESSION["CLIENTE_ID"]." AND PACIENTE_ID NOT IN (SELECT PACIENTE_ID FROM examenes WHERE CLIENTE_ID=".$_SESSION["CLIENTE_ID"].")";
	$where_con=" WHERE CLIENTE_ID=".$_SESSION["CLIENTE_ID"];
	$sql="SELECT 
		*, 
		CONCAT(NOMBRE,' ',APELLIDOS) as NOMBRE 
	FROM pacientes
	$where_pax;";
	$pacientes=array();
	$pacientes=$rBD->query2array($sql);
	$conceptos=$rBD->query2array("SELECT * FROM conceptos $where_con;");
}
foreach($conceptos["data"] as $id=>$v){unset($conceptos["data"][$id]["id"]);}
?>
<script type="text/javascript">
var labelw=0;
// Script para el alta de empresas y busqueda de clientes
var urlRemotoLab="http://192.232.212.191/~promedic/remoto/link.php";
var urlRemotoMulti="http://192.232.212.191/~promedic/remoto/multi.php";
var urlLocalLab="<?php echo $root."includes/link.php"; ?>";
var urlLocalMulti="<?php echo $root."includes/multi.php"; ?>";
var empresas=[]; //es el array de objetos con todas las empresas 
var IdEmpMtx; //relaciona el [cliente Id] con la posicion del array
var pacientes=enthalpy.json2array(<?php echo json_encode(mejorArr($pacientes["data"],'PACIENTE_ID')); ?>);
var conceptos=enthalpy.json2array(<?php echo json_encode(mejorArr($conceptos["data"],'PACIENTE_ID')); ?>);
	$(document).ready(function(e) {
		//para procesar el json
		jsonPost=<?php echo $jsonPost; ?>;
		$.each(jsonPost,function(i,v){
			//i es para el form y v es para los valores del json
			$.each(v,function(ii,vv){
				console.log(ii);
				$($(".paquete").get(i)).find("."+ii).val(vv);
			});			
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
		
		//mismos tamaños pára todos los labels
		$.each($("label"),function(){
			labelw=($(this).width()>labelw) ? $(this).width() : labelw ;
		});
		$("label").width(labelw);
		
		
		//detener todos lo forms para usar ajax
		$("form").submit(function(e) {
            e.preventDefault();
			e.stopPropagation();
			cont=true;
			$.each($(".requerido"),function(){
				if($(this).val()=="" || $(this).val()=='undefined'){
					cont=false;
				}
			});
			if($(".submit").is(":disabled")){false}
			if(cont){
				enthalpy.alerta("Cargando...",true);
				$(".submit").prop("disabled",true);
				data=$(this).serialize();
				enthalpy.ajax('scripts/s_formas.php',data,'POST',function(r){
					console.log(r);
					if(!r.err){
						enthalpy.alerta(r.msg);
						if(r.url){
							window.location=r.url;
						}else{
							location.reload();
						}
					}else{
						enthalpy.alerta(r.msg);
						console.log(r.msg);
						$(".submit").prop("disabled",false);
					}
					if(typeof r == "undefined"){$(".submit").prop("disabled",false)}
				});
			}else{
				enthalpy.alerta("Hay elementos requeridos incompletos");
			}
        });
		
		//buscar empresas al iniciar la pagina AJAX
		todaEmp();
		
		//buscar todas las empresas con un botón dirigido o en general
		$(".todaEmp").click(function(e) {
			puntero=$(this).attr("data-form");
			if(puntero == ''){lugar='.empresas';}else{lugar=puntero+" .empresas";}
            todaEmp(lugar);
        });
		
		//script para la búsqueda
		$(".busqueda").keyup(function(e) {
			if(e.keyCode==13){
				if($(this).val()!=""){
					datos={sql:'buscaEmp',hint:$(this).val(),output:'opt'};
					enthalpy.ajax(
						urlRemotoLab,
						datos,
						'POST',
						function(r){
							//r.data contiene todos los elementos
							$(".empresas").html(enthalpy.json2option(r.data,mtx));
						}
					);
				}
			}
        });
		
		//Para cuando se elige una empresa
		$(".empresas").change(function(e) {
			puntero=$(this).attr("data-form");
			_val=$(this).find("option:selected").val();
			enthalpy.rellenarCampos(empresas[IdEmpMtx[_val]],puntero);
			
			//sacar los conceptos de cada uno
			datos={sql:'conceptos',hint:_val,output:'opt'};
			enthalpy.ajax(
				urlRemotoLab,
				datos,
				'POST',
				function(r){
					//r.data contiene todos los elementos
					//console.log(r);
					$(".conceptos").html('');
					$.each(r.data,function(i,v){
						  $(".conceptos").append('<label>'+v.CLAVE+' -> </label><input type="hidden" name="b[CONCEPTO_ID]['+v.CONCEPTO_ID+']" value="'+v.CONCEPTO_ID+'" /><label>GENERO:</label><select name="b[GENERO]['+v.CONCEPTO_ID+']"><option value="Indistinto">Indistinto</option><option value="Masculino">Masculino</option><option value="Femenino">Femenino</option></select><input type="hidden" name="b[NOMBRE]['+v.CONCEPTO_ID+']" value="'+v.NOMBRE+'" /><br />');
					});
				}
			);
        });
		
		$(".codigo").conEnter(function(elem){
			//console.log(elem.val());
			puntero=elem.attr("data-form");
			datos={sql:'rastrear',hint:elem.val()};
			enthalpy.ajax(
				urlLocalMulti,
				datos,
				'POST',
				function(r){
					console.log(r);
					matriz=r.data;
					enthalpy.rellenarCampos(matriz[0],puntero);
				}
			);
		});
		
		$(".permiso").click(function(e) {
            $(this).find("input:checkbox").click();
			str=check2string(".perm");
			$(".permstr").val(str);
        });
		
    });//termina document ready

function check2string(selector){
	var str="";
	$.each($(selector+":checked"),function(i,v){
		str=str+$(this).val()+"_";
	});
	str = str.substring(0, str.length - 1);
	return str;
}

function todaEmp(lugar){
	if(typeof lugar == 'undefined'){lugar=".empresas"}
	//script para buscar todas las empresas y ponerlas en las casillas de empresa
	datos={sql:'empresas',hint:'',output:'opt'};
	mtx={value:'CLIENTE_ID',nombre:'NOMBRE'};
	enthalpy.ajax(
		urlRemotoLab,
		datos,
		'POST',
		function(r){
			//r.data contiene todos los elementos
			empresas=r.data;
			IdEmpMtx=enthalpy.cotejarArrId(r.data,'CLIENTE_ID');
			$(lugar).html(enthalpy.json2option(r.data,mtx));
		}
	);
}

//funcion de traer pacientes a un array para cada empresa
function paxDeEmp(emp){
	emp=$(emp).find("option:selected").val();
	$(".PACIENTE_ID").html('<option disabled="disabled" selected="selected">Elige una empresa</option>');
	$.each(pacientes,function(i,v){
		if(v.CLIENTE_ID==(emp)){
			$(".PACIENTE_ID").append('<option value="'+v.PACIENTE_ID+'">'+v.NOMBRE+' '+v.APELLIDOS+'</option>');
			//$(".GENERO").val(v.GENERO);
		}
	});	
}
function concDeEmp(emp){
	emp=$(emp).find("option:selected").val();
	$(".CONCEPTO_ID").html('<option disabled="disabled" selected="selected">Elige una empresa</option>');
	$.each(conceptos,function(i,v){
		if(v.CLIENTE_ID==(emp)){
			$(".CONCEPTO_ID").append('<option value="'+v.CONCEPTO_ID+'">'+v.NOMBRE+'</option>');
		}
	});	
}
function concDeEmpGen(formulario){
	_f=$(formulario);
	cli=_f.find(".CLIENTE_ID").val();
	gen=_f.find(".GENERO").val();	
	$(".CONCEPTO_ID").html('<option disabled="disabled" selected="selected">Elige un servicio</option>');
	$.each(conceptos,function(i,v){
		if(v.CLIENTE_ID==cli && (v.GENERO=="Indistinto" || v.GENERO==gen)){
			$(".CONCEPTO_ID").append('<option value="'+v.CONCEPTO_ID+'">'+v.NOMBRE+'</option>');
		}
	});	
}
function generoDePax(pax,target){
	target = (typeof target != "undefined") ? target : document;
	pax=$(pax).find("option:selected").val();
	$.each(pacientes,function(i,v){
		if(v.PACIENTE_ID==(pax)){
			$(target).find(".GENERO").val(v.GENERO);
		}
	});
	concDeEmpGen('#servicios');
}
</script>
<div>
<?php if(in_array($_SESSION["cat"],array('super'))){ ?>
    <h3>Buscador de empresas</h3>
    <label>Búsqueda:</label>
    <input class="busqueda" type="text" placeholder="Nombre, Razon Social, RFC..." /><br />
    <label>ID de empresa</label><select class="empresas" name="EMPRESA_ID" data-form=""></select>
    <input type="button" class="todaEmp" data-form="" value="Todas" /><br />
    <?php switch($ctrlForm){ 
        case'altaempresa': ?>
    <form id="altaEmp" action="" method="post">
        <h3>Alta de empresas en el servicio de multitudinarios</h3>
        <input type="hidden" name="ctrl" value="altaempresa" />
        <label>Nombre</label><input type="text" class="NOMBRE" name="a[NOMBRE]" style="width:400px;" /><br />
        <label>R.F.C.</label><input type="text" class="RFC" name="a[RFC]" /><br />
        <label>ID de empresa</label><select class="empresas" name="a[CLIENTE_ID]" data-form="#altaEmp"></select>
        <input type="button" class="todaEmp" data-form="#altaEmp" value="Todas" /><br />
        <label>Elige el Genero del concepto:</label><br />
        <div class="conceptos" style="display:inline-block;"></div><br />
        <input type="submit" /><input type="reset" />
    </form>
    <?php break; ?>
    <?php case'altauser': ?>
    <form id="altaUser" action="" method="post">
        <h3>Dar de alta a un usuario en una empresa registrada</h3>
        <input type="hidden" name="ctrl" value="altausuario" />
        <label>Empresa</label><select name="CLIENTE_ID"><?php echo $empresas["data"]; ?></select><br />
        <label>Nombre</label><input type="text" class="NOMBRE requerido" name="NOMBRE" style="width:400px;" /><br />
        <label>Apellidos</label><input type="text" class="APELLIDOS" name="APELLIDOS" style="width:400px;" /><br />
        <label>Usuario</label><input type="text" class="USUARIO requerido" name="USUARIO" /><br />
        <label>Password</label><input type="text" class="PASSWORD requerido" name="PASSWORD" /><br />
        <label>Email</label><input type="text" class="EMAIL" name="EMAIL" /><br />
        <label>Categoría</label><select name="CATEGORIA"><?php echo array2opt($catCuentas); ?></select><br />
        <input type="submit" /><input type="reset" />
    </form>
    <?php break; ?>
    <?php case'altapaciente': ?>
    <h3>Alta de paciente</h3>
    <form action="" method="post">
        <input type="hidden" name="ctrl" value="altapaciente" />
        <div class="campoForm">
        	<label>Empresa</label><select name="CLIENTE_ID"><option disabled="disabled" selected="selected">Elige una empresa</option><?php echo $empresas["data"]; ?></select><br />
        </div>
        <div class="campoForm">
        <label>Nombre</label><input type="text" name="NOMBRE" /><br />
     	</div>
        <div class="campoForm">
        <label>Apellidos</label><input type="text" name="APELLIDOS" /><br />
        </div>
        <input type="hidden" name="MEDICO_ID" value="21" />
        <input type="hidden" name="TIPO_SERVICIO_ID" value="3" />
        <label>Género</label><select name="GENERO"><?php echo array2opt($genero); ?></select><br />
        <input type="submit" /><input type="reset" />
    </form>
    <?php break; ?>
    <?php case'solexam': ?>
    <h3>Solicitar Examen Médico</h3>
    <form id="servicios" action="" method="post">
        <input type="hidden" name="ctrl" value="solexam" />
    <?php if($_SESSION["super"]){ ?>
        <label>Empresa</label><select class="CLIENTE_ID" name="CLIENTE_ID" onchange="paxDeEmp(this); concDeEmp(this);"><option disabled="disabled" selected="selected">Elige una empresa</option><?php echo $empresas["data"]; ?></select><br />
    <?php }else{ ?>
        <input type="text" class="CLIENTE_ID" name="CLIENTE_ID" value="<?php $view->echoSesVar("CLIENTE_ID"); ?>" /><br />
    <?php } ?>
        <label>Paciente</label><select class="PACIENTE_ID" name="PACIENTE_ID" onchange="generoDePax(this,'#servicios');"></select><br />
        <label>Género</label><select class="GENERO" onchange="concDeEmpGen('#servicios');"><?php echo array2opt($genero); ?></select><br />
        <label>Servicio</label><select class="CONCEPTO_ID" name="CONCEPTO_ID"></select><br />
        <input type="hidden" name="FECHA_IN" value="<?php echo date("Y-m-d H:i:s"); ?>" />
        <input class="submit" type="submit" /><input type="reset" />
    </form>
    <?php break; 
    } //termina el switch ?>
    
    <h3>Prueba de rastreo</h3>
    <label>Folio de rastreo</label><input type="text" class="codigo" data-form="#rastrear" />
    <form id="rastrear" method="post">
        <input type="hidden" name="ctrl" value="rastrear" />
        <label>NOMBRE</label><input type="text" class="NOMBRE" name="NOMBRE" /><br />
        <label>APELLIDOS</label><input type="text" class="APELLIDOS" name="APELLIDOS" /><br />
        <label>GENERO</label><input type="text" class="GENERO" name="GENERO" /><br />
        <label>MEDICO_ID</label><input type="text" class="MEDICO_ID" name="MEDICO_ID" /><br />
        <label>TIPO_SERVICIO_ID</label><input type="text" class="TIPO_SERVICIO_ID" name="TIPO_SERVICIO_ID" /><br />
        <label>CONCEPTO_ID[1]</label><input type="text" class="CONCEPTO_ID[1]" name="CONCEPTO_ID[1]" /><br />
        <label>CLAVE[1]</label><input type="text" class="CLAVE[1]" name="CLAVE[1]" /><br />
        <label>DESCRIPCION[1]</label><input type="text" class="DESCRIPCION[1]" name="DESCRIPCION[1]" /><br />
        <label>CANTIDAD[1]</label><input type="text" class="CANTIDAD[1]" name="CANTIDAD[1]" /><br />
        <label>PRECIO[1]</label><input type="text" class="PRECIO[1]" name="PRECIO[1]" /><br />
        <label>ESTATUS_OT_ID[1]</label><input type="text" class="ESTATUS_OT_ID[1]" name="ESTATUS_ID_ID[1]" /><br />
        <input type="submit" /><input type="reset" />
    </form>
    <h2>Área de los listados</h2>
    <?php //Listados 
    #$id_emp=(isset($_GET[""]) and @$_GET[""]!="") ? $_GET[""] : 'todas';
    $tabla=(isset($_GET["t"]) and @$_GET["t"]!="") ? $_GET["t"] : false ;
    $whEmp=(isset($_GET["emp"]) and @$_GET["emp"]!="") ? 'CLIENTE_ID='.$_GET["emp"] : '' ;
    $whPax=(isset($_GET["pax"]) and @$_GET["pax"]!="") ? 'PACIENTE_ID='.$_GET["pax"] : '' ;
    $conWh='';
    foreach($_GET as $ind=>$val){
        if(in_array($i,$buscarGets)){
            $conWh='WHERE';
            break;
        }
    }
    //listados completos para el admin y super usuario
    $empArr=$rBD->query2array("SELECT * FROM empresas;");
?>
<?php }else{  //para los demás usuarios (NO SUPER)?>

	<?php switch($ctrlForm){ 
      case'altaempresa': ?>
    <h3>Alta de empresas en el servicio de multitudinarios</h3>    
    <form id="altaEmp" action="" method="post">
        <input type="hidden" name="ctrl" value="altaempresa" />
        <label>Nombre</label><input type="text" class="NOMBRE" name="a[NOMBRE]" style="width:400px;" /><br />
        <label>R.F.C.</label><input type="text" class="RFC" name="a[RFC]" /><br />
        <label>ID de empresa</label><select class="empresas" name="a[CLIENTE_ID]" data-form="#altaEmp"></select>
        <input type="submit" class="todaEmp" data-form="#altaEmp" value="Todas" /><br />
        <label>Elige el Genero del concepto:</label><br />
        <div class="conceptos" style="display:inline-block;"></div><br />
        <input type="submit" /><input type="reset" />
    </form>
<?php break; ?>

<?php case'altauser': ?>
    <h3>Alta de usuario</h3>
    <form id="altaUser" action="" method="post">        
        <input type="hidden" name="ctrl" value="altausuario" />
		<input type="hidden" name="CLIENTE_ID" value="<?php $view->echoSesVar("CLIENTE_ID"); ?>" />
        <div class="campoForm">
            <label>Nombre</label><input type="text" class="NOMBRE requerido" name="NOMBRE" style="width:400px;" />
        </div>
        <div class="campoForm">
            <label>Apellidos</label><input type="text" class="APELLIDOS" name="APELLIDOS" style="width:400px;" />
		</div>
        <div class="campoForm">
            <label>Usuario</label><input type="text" class="USUARIO requerido" name="USUARIO" />
		</div>
        <div class="campoForm">
            <label>Password</label><input type="text" class="PASSWORD requerido" name="PASSWORD" />
		</div>
        <div class="campoForm">
            <label>Email</label><input type="text" class="EMAIL" name="EMAIL" />
        </div>
        <div class="campoForm">
        	<label>Categoría</label><select name="CATEGORIA"><?php echo array2opt($catCuentas); ?></select>
        </div>
        <div class="campoForm">
        	<h3 style="text-align:center;">PERMISOS</h3>
            <h4>Examenes</h4>
            <div class="permiso"><input type="checkbox" class="perm" value="gre" /><span>Guardar Resultados</span></div>
            <h4>Crear y/o guardar:</h4>
            <div class="permiso"><input type="checkbox" class="perm" value="cse" /><span>Solicitar Examen</span></div>
			<div class="permiso"><input type="checkbox" class="perm" value="cae" /><span>Alta de Empresas</span></div>
            <div class="permiso"><input type="checkbox" class="perm" value="cau" /><span>Alta de Usuario</span></div>
            <div class="permiso"><input type="checkbox" class="perm" value="cap" /><span>Alta de Paciente</span></div>
            <h4>Ver:</h4>
			<div class="permiso"><input type="checkbox" class="perm" value="vlp" /><span>Listado de Pacientes</span></div>
            <div class="permiso"><input type="checkbox" class="perm" value="vle" /><span>Listado de Exámenes</span></div>
            <div class="permiso"><input type="checkbox" class="perm" value="vlr" /><span>Listado de Resultados</span></div>
            <h4>Modificar:</h4>
            <div class="permiso"><input type="checkbox" class="perm" value="mus" /><span>Modificar Usuario</span></div>
            <div class="permiso"><input type="checkbox" class="perm" value="mem" /><span>Modificar Empresa</span></div>
			<div class="permiso"><input type="checkbox" class="perm" value="mmp" /><span>Modificar Paciente</span></div>
            <div class="permiso"><input type="checkbox" class="perm" value="mme" /><span>Modificar Examen</span></div>
            <h4>Eliminar:</h4>
            <div class="permiso"><input type="checkbox" class="perm" value="eem" /><span>Eliminar Empresa</span></div>
            <div class="permiso"><input type="checkbox" class="perm" value="eus" /><span>Eliminar Usuario</span></div>
            <div class="permiso"><input type="checkbox" class="perm" value="epa" /><span>Eliminar Paciente</span></div>
            <div class="permiso"><input type="checkbox" class="perm" value="eex" /><span>Eliminar Examen</span></div>            
            <h4>Formatos y Carpetas:</h4>
            <div class="permiso"><input type="checkbox" class="perm" value="dcc" /><span>Crear Carpetas Dropbox</span></div>
            <div class="permiso"><input type="checkbox" class="perm" value="ifp" /><span>Imprimir Formatos</span></div>
            <input type="hidden" name="PERMISOS" class="permstr requerido" value="" />
        </div>
        <div class="campoForm">
        	<input type="submit" /><input type="reset" />
        </div>
    </form>
<?php break; ?>

<?php case'modifusuarios':?>
    <h3>Alta de usuario</h3>
    <form id="altaUser" action="" method="post">        
        <input type="hidden" name="ctrl" value="modifusuario" />
	<?php foreach($post as $d){ ?>
        <div class="paquete">
            <input type="hidden" class="id" name="id" value="" />
            <input type="text" class="CLIENTE_ID" name="CLIENTE_ID" value="" />
           
            <div class="campoForm">
                <label>Nombre</label><input type="text" class="NOMBRE" name="NOMBRE" style="width:400px;" />
            </div>
            <div class="campoForm">
                <label>Apellidos</label><input type="text" class="APELLIDOS" name="APELLIDOS" style="width:400px;" />
            </div>
            <div class="campoForm">
                <label>Usuario</label><input type="text" class="USUARIO" name="USUARIO" />
            </div>
            <div class="campoForm">
                <label>Password</label><input type="text" class="PASSWORD" name="PASSWORD" />
            </div>
            <div class="campoForm">
                <label>Email</label><input type="text" class="EMAIL" name="EMAIL" />
            </div>
            <div class="campoForm">
                <label>Categoría</label><select name="CATEGORIA"><?php echo array2opt($catCuentas); ?></select>
            </div>
            <div class="campoForm">
               	<h3 style="text-align:center;">PERMISOS</h3>
                <h4>Examenes</h4>
                <div class="permiso"><input type="checkbox" class="perm" value="gre" /><span>Guardar Resultados</span></div>
                <h4>Crear y/o guardar:</h4>
                <div class="permiso"><input type="checkbox" class="perm" value="cse" /><span>Solicitar Examen</span></div>
                <div class="permiso"><input type="checkbox" class="perm" value="cae" /><span>Alta de Empresas</span></div>
                <div class="permiso"><input type="checkbox" class="perm" value="cau" /><span>Alta de Usuario</span></div>
                <div class="permiso"><input type="checkbox" class="perm" value="cap" /><span>Alta de Paciente</span></div>
                <h4>Ver:</h4>
                <div class="permiso"><input type="checkbox" class="perm" value="vlp" /><span>Listado de Pacientes</span></div>
                <div class="permiso"><input type="checkbox" class="perm" value="vle" /><span>Listado de Exámenes</span></div>
                <div class="permiso"><input type="checkbox" class="perm" value="vlr" /><span>Listado de Resultados</span></div>
                <h4>Modificar:</h4>
                <div class="permiso"><input type="checkbox" class="perm" value="mus" /><span>Modificar Usuario</span></div>
                <div class="permiso"><input type="checkbox" class="perm" value="mem" /><span>Modificar Empresa</span></div>
                <div class="permiso"><input type="checkbox" class="perm" value="mmp" /><span>Modificar Paciente</span></div>
                <div class="permiso"><input type="checkbox" class="perm" value="mme" /><span>Modificar Examen</span></div>
                <h4>Eliminar:</h4>
                <div class="permiso"><input type="checkbox" class="perm" value="eem" /><span>Eliminar Empresa</span></div>
                <div class="permiso"><input type="checkbox" class="perm" value="eus" /><span>Eliminar Usuario</span></div>
                <div class="permiso"><input type="checkbox" class="perm" value="epa" /><span>Eliminar Paciente</span></div>
                <div class="permiso"><input type="checkbox" class="perm" value="eex" /><span>Eliminar Examen</span></div>            
                <h4>Formatos y Carpetas:</h4>
                <div class="permiso"><input type="checkbox" class="perm" value="dcc" /><span>Crear Carpetas Dropbox</span></div>
                <div class="permiso"><input type="checkbox" class="perm" value="ifp" /><span>Imprimir Formatos</span></div>
                <input type="hidden" name="PERMISOS" class="permstr" value="" />
            </div>
        </div>
    <?php } ?>
        <div class="campoForm">
        	<input type="submit" /><input type="reset" />
        </div>
    </form>
<?php break; ?>

<?php case'altapaciente': ?>
    <h3>Alta de paciente</h3>
    <form action="" method="post">
        <input type="hidden" name="ctrl" value="altapaciente" />
        <input type="hidden" name="MEDICO_ID" value="21" />
        <input type="hidden" name="TIPO_SERVICIO_ID" value="3" />
        <input type="hidden" class="CLIENTE_ID" name="CLIENTE_ID" value="<?php $view->echoSesVar("CLIENTE_ID"); ?>" />
        <div class="campoForm">
        	<label>Nombre</label><input class="requerido" type="text" name="NOMBRE" />
        </div>
        <div class="campoForm">
        	<label>Apellidos</label><input class="requerido" type="text" name="APELLIDOS" />
        </div>
        <div class="campoForm">
        	<label>Fecha Nacimiento</label><input class="fecha requerido" data-formato="dd/mm/yy" type="text" name="FECHA_NACIMIENTO" />
        </div>
        <div class="campoForm">
        	<label>Género</label><select name="GENERO"><?php echo array2opt($genero); ?></select>
        </div>
        <div class="campoForm" align="right">
        	<input type="submit" /><input type="reset" />
        </div>
    </form>
<?php break; ?>

<?php case'solexam': ?>
    <h3>Solicitar Examen Médico</h3>
    <form id="servicios" action="" method="post">        
        <input type="hidden" name="ctrl" value="solexam" />
        <div class="campoForm">
		<?php if($_SESSION["super"]){ ?>
            <label>Empresa</label><select class="CLIENTE_ID" name="CLIENTE_ID" onchange="paxDeEmp(this); concDeEmp(this);"><option disabled="disabled" selected="selected">Elige una empresa</option><?php echo $empresas["data"]; ?></select><br />
        <?php }else{ ?>
            <input type="hidden" class="CLIENTE_ID" name="CLIENTE_ID" value="<?php $view->echoSesVar("CLIENTE_ID"); ?>" /><br />
        <?php } ?>
        </div>
        <div class="campoForm">
        	<label>Paciente</label><select class="PACIENTE_ID requerido" name="PACIENTE_ID" onchange="generoDePax(this,'#servicios');"><?php echo array2optMod($pacientes["data"],array('PACIENTE_ID','NOMBRE')); ?></select><br />
        </div>
        <div class="campoForm">
       		<label>Género</label><select class="GENERO" onchange="concDeEmpGen('#servicios');"><?php echo array2opt($genero); ?></select><br />
        </div>
        <div class="campoForm">
        	<label>Servicio</label><select class="CONCEPTO_ID requerido" name="CONCEPTO_ID"><?php echo array2optMod($conceptos["data"],array('CONCEPTO_ID','NOMBRE')); ?></select><br />
        </div>
        <div class="campoForm">
        	<label>Fecha del examen</label><input type="text" class="fecha" name="FECHA_IN" readonly="readonly" value="<?php echo date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +1 day")); ?>" />
        </div>
        <div class="campoForm" align="right">
        	<input class="submit" type="submit" value="Solicitar" /><input type="reset" />
        </div>
    </form>
<?php break; ?>

<?php case'modifexamenes': ?>
    <h3>Solicitar Examen Médico</h3>
    <form id="servicios" action="" method="post">        
        <input type="hidden" name="ctrl" value="solexam" />
    <?php foreach($post as $d){ ?>
    <div class="paquete">
        <input type="hidden" name="id" class="id" />
        <input type="hidden" name="FECHA_IN" value="<?php echo date("Y-m-d H:i:s"); ?>" />
        <div class="campoForm">
		<?php if($_SESSION["super"] || $_SESSION["cat"]=="admin"){ ?>
            <label>Empresa</label><select class="CLIENTE_ID" name="CLIENTE_ID" onchange="paxDeEmp(this); concDeEmp(this);"><option disabled="disabled" selected="selected">Elige una empresa</option><?php echo $empresas["data"]; ?></select><br />
        <?php }else{ ?>
            <input type="hidden" class="CLIENTE_ID" name="CLIENTE_ID" value="<?php $view->echoSesVar("CLIENTE_ID"); ?>" /><br />
        <?php } ?>
        </div>
        <div class="campoForm">
        <label>Paciente</label><select class="PACIENTE_ID" name="PACIENTE_ID" onchange="generoDePax(this,'#servicios');"><?php echo array2optMod($pacientes["data"],array('PACIENTE_ID','NOMBRE')); ?></select><br />
        </div>
        <div class="campoForm">
        <label>Género</label><select class="GENERO" onchange="concDeEmpGen('#servicios');"><?php echo array2opt($genero); ?></select><br />
        </div>
        <div class="campoForm">
        <label>Servicio</label><select class="CONCEPTO_ID" name="CONCEPTO_ID"><?php echo array2optMod($conceptos["data"],array('CONCEPTO_ID','NOMBRE')); ?></select><br />
        </div>
     </div>
     <?php } ?>
        <div class="campoForm" align="right">
        <input type="submit" value="Solicitar" /><input type="reset" />
        </div>
    </form>
    <?php break; 
    } //termina el switch 
}//if apra saber si es SU ?>
</div>
