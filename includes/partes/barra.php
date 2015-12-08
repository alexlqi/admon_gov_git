<?php //damos de alta la variable global $view en esta instancia
global $view; 
$permisos=$_SESSION["permisos"];
?>
<style>
#barra{
	padding:10px;
	color:#3F51B5;
	background-color:#FFF;
}
#nav{
	background-color:#7CB342;
}
.logout{
	cursor:pointer;
	display:inline-block;
	padding:5px;
	background-color:#7CB342;
	border:2px solid #7CB342;
	color:#000;
	font-weight:bold;
}
.logout:hover{
	background-color:#FFF;
	color:#000;
}
.boton_nav{
	display:inline-block;
	padding:1%;
	cursor:pointer;
	font-weight:bold;
	font-size:1.1em;
}
.boton_nav:hover{
	color:#3F51B5;
	background-color:#FFF;
}
.dd_menu{
	position:absolute;
	z-index:2;
	margin-top:10px;
	min-width:200px;
	display:none;
	font-weight:normal;
	color:#FFF;
	background-color:#3F51B5;
}
ul.dd_menu li{
	padding:4px 10px;
}
ul.dd_menu li:hover{
	color:#3F51B5;
	background-color:#FFF;
}
</style>
<script type="text/javascript">
$(document).ready(function(e) {
	$("")
    $(".logout").click(function(e) {
        enthalpy.ajax(
			'scripts/logout.php',
			{},
			'GET',
			function(r){
				location.reload();
			}
		);
    });
	$(".boton_nav").click(function(e) {
		_this=$(this);
		time=100;
		if(!_this.find(".dd_menu").is(":visible")){
			$(".dd_menu").slideUp(time);
			setTimeout(function(){
				_this.find(".dd_menu").slideDown(time);
			},time);
		}else{
			_this.find(".dd_menu").slideUp(time);
		}
    });
	$(".dd_menu").click(function(e) {
        e.stopImmediatePropagation();
    });
});
</script>
<div id="top">
    <div id="barra">
        <table class="full_w">
            <tr>
            	<td align="left" valign="middle">
                	<a href="index.php"><h1><?php $view->printEmpresa(); ?> (<?php echo @$_SESSION["USUARIO"] ?>)</h1></a>
                </td>
            <?php if($view->super()){ ?>
                <td align="center">
				<?php if(@$_POST["CLIENTE_ID"]){
                    $_SESSION["CLIENTE_ID"]=$_POST["CLIENTE_ID"];
                }?>
                	<form id="s_cliente" action="" method="post">
                    	<label>Actual: <?php echo @$_SESSION["empresas"][$_SESSION["CLIENTE_ID"]]; ?></label>
                    	<select name="CLIENTE_ID" onchange="document.getElementById('s_cliente').submit();">
                        	<option selected="selected" disabled="disabled">Cambiar cliente</option>
                            <?php foreach($_SESSION["empresas"] as $id=>$d){
								echo '<option value="'.$id.'">'.$d.'</option>';
							}?>
                        </select>
                    </form>
                </td>
            <?php } ?>
                <td align="right" valign="middle">
                	<span class="logout redondeado">Cerrar Sesión</span>
                </td>
            </tr>
        </table>
    </div>
	<div id="nav" class="sombra1">
    <?php if(@$permisos["barra"]["empresas"]){ ?>
    	<div class="boton_nav">
            <span class="boton_name">Empresas</span>
            <ul class="dd_menu sombra1">
                <li><a href="empresas.php?add=1">Nueva Empresa</a></li>
                <li><a href="empresas.php?list=1">Listado de empresas</a></li>
                <li><a href="usuarios.php?add=1">Nuevo Usuario</a></li>
                <li><a href="usuarios.php?list=1">Listado de usuarios</a></li>
                <li><a href="usuarios.php?modif=1">Modificar Usuario</a></li>
            </ul>
        </div>
    <?php } ?>
    <?php if(@$permisos["barra"]["pacientes"]){ ?>
    	<div class="boton_nav">
            <span class="boton_name">Pacientes</span>
            <ul class="dd_menu">
                <li><a href="pacientes.php?add=1">Nuevo Paciente</a></li>
                <li><a href="pacientes.php?list=1">Listado de pacientes</a></li>
            </ul>
        </div>
    <?php } ?>
    <?php if(@$permisos["barra"]["examenes"]){ ?>
        <div class="boton_nav">
            <span class="boton_name">Exámenes</span>
            <ul class="dd_menu">
                <li><a href="examenes.php?add=1">Nuevo Examen</a></li>
                <li><a href="examenes.php?list=1">Listado de exámenes</a></li>
            </ul>
        </div>
    <?php } ?>
	<?php if(@$permisos["barra"]["resultados"]){ ?>
        <div class="boton_nav">
            <a href="resultados.php?list=1" class="boton_name">Resultados</a>
        </div>
    <?php } ?>    
    </div>
</div>