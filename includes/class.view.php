<?php //clase para vista
class pmview{
	private $head;
	private $header_top; //header top part
	private $page_title; //the title of the page itself
	public $header_title; //the html title tag
	private $with_jquery=false;
	private $with_jqueryui=false;
	private $with_jquerynum=false;
	private $header_custom; //the scripts and links defined by users
	private $header_bot; //header bottom par
	private $content;
	private $footer;
	private $permisosBarra; //array (seccion=>true/false)
	private $permisosMetodos; //string
	
	public function __construct($params=array()){
		$this->head=$this->defaultHeader(); //se escribe un header genérico
		if(@$params["jquery"]){
			$this->with_jquery=$params["jquery"];
			$this->addJQuery();
		}
		$this->permisosBarra=@$_SESSION["permisos"]["barra"];
		$this->permisosMetodos=@$_SESSION["permisos"]["metodos"];
	}
	
	public function output($params=array()){
		$login=(isset($params["login"])) ? $params["login"] : false ;
		if($login){
			$this->showHeader();
			$this->login();
			$this->showFooter();
		}else{
			$this->showHeader();
			$this->showFooter();
		}
	}
	
	public function showHeader(){
		echo $this->head;
	}
	public function showFooter(){
		echo $this->footer;
	}
	
	private function defaultHeader(){
		$this->header_top='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head><meta name="viewport" content="width=device-width, user-scalable=no"><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$this->page_title='ProMedic';
		$this->header_title='<title>ProMedic</title>';
		$this->header_bot='</head><body>';
		return $this->header_top.$this->header_title.$this->header_bot;
	}
	
	//Reescribir $this->head con lo nuevo (algo así como un commit);
	private function changeHeader(){
		$this->head=$this->header_top.$this->header_title.$this->header_custom.$this->header_bot;
	}
	
	//modificar el título de la página
	public function pageTitle($title){
		$this->header_title='<title>'.$title.'</title>';
		$this->page_title=$title;
		$this->changeHeader();
	}
	
	public function add2head($elements){
		if(!is_array($elements)){
			//si es string entonces se añade completo
			$this->header_custom.=$elements;
		}else{
			foreach($elements as $d){
				$this->header_custom.=$d;
			}
		}
		$this->changeHeader();
	}
	
	//add jquery al sistema
	public function addJQuery(){
		$this->with_jquery=true;
		$this->add2head('<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script><script type="text/javascript" src="js/design.js"></script>');
	}
	public function addJQueryNumeric(){
		$this->with_jquerynum=true;
		$this->add2head('<script type="text/javascript" src="js/jquery.numeric.js"></script><script>$(document).ready(function(e){$(".numerico").numeric();});</script>');
	}
	
	//add jquery datepicker
	public function addJQueryUI(){
		$this->with_jqueryui=true;
		$this->add2head('
			<script type="text/javascript" src="js/jquery-ui.min.js"></script>
			<link rel="stylesheet" type="text/css" href="css/jquery-ui.min.css" />
			<link rel="stylesheet" type="text/css" href="css/jquery-ui.structure.min.css" />
			<link rel="stylesheet" type="text/css" href="css/jquery-ui.theme.min.css" />
		');
	}
	
	/*PHP login interface*/
	public function login(){
		include("partes/loginform.php");
	}
	
	/*Sección de los listados*/
	
	/*Sección de los permisos*/
	public function permisoBarra($metodo){
		$perm=(@$this->permisosBarra[$metodo]) ? true : false;
		return $perm;
	}
	public function permisoMetodo($metodo){
		//$perm=strpos($_SESSION["permisos"]["metodos"],$metodo);
		$perm=explode("_",$_SESSION["permisos"]["metodos"]);
		$perm=in_array($metodo,$perm);
		return $perm;
	}
	private function perm($metodo){
		$perm=explode("_",$_SESSION["permisos"]["metodos"]);
		$perm=in_array($metodo,$perm);
		return $perm;
	}
	
	/*Sección de los elementos de pagina*/
	public function showBarra(){
		include("partes/barra.php");
	}
	
	public function botones($listado,$seccion){
		#para relacionar el permiso con la seccion
		$ctrlElim="";
		switch($seccion){
			case 'empresas': $ctrlElim="eem"; break;
			case 'usuarios': $ctrlElim="eus"; break;
			case 'pacientes': $ctrlElim="epa"; break;
			case 'examenes': $ctrlElim="eex"; break;
		}
		
		$nuevo="<input type='button' data-form='$listado' class='nuevo boton' value='Nuevo' />";
		$modificar="<input type='button' data-form='$listado' class='modificar boton flotante' value='Modificar' />";
		$formatos="<input type='button' data-form='$listado' class='formatos boton' value='Formatos' />";
		$carpetas="<input type='button' data-form='$listado' class='carpetas boton' value='Carpetas' />";
		$eliminar="<input type='button' data-form='$listado' data-ctrl='$ctrlElim' class='eliminar boton' value='Eliminar' />";
		$guardar="<input type='button' data-form='$listado' class='guardar boton' value='Guardar' />";
		$pdf="<input type='button' data-form='$listado' class='getpdfof boton' value='Obtener PDF' />";
		switch($seccion){
			case 'empresas':
				if($this->perm('cae')){echo $nuevo;}
				if($this->perm('mem')){echo $modificar;}
				if($this->perm('eem')){echo $eliminar;}
			break;
			case 'usuarios':
				if($this->perm('cau')){echo $nuevo;}
				if($this->perm('mus')){echo $modificar;}
				if($this->perm('eus')){echo $eliminar;}
			break;
			case 'pacientes':
				if($this->perm('cap')){echo $nuevo;}
				if($this->perm('mmp')){echo $modificar;}
				if($this->perm('epa')){echo $eliminar;}
			break;
			case 'examenes':
				if($this->perm('cse')){echo $nuevo;}
				if($this->perm('mme')){echo $modificar;}
				if($this->perm('dcc')){echo $carpetas;}
				if($this->perm('ifp')){echo $formatos;}
				if($this->perm('eex')){echo $eliminar;}
			break;
			case 'resultados':
				if( ($this->perm('re_') || $this->perm('gre')) and @$_GET["folio"]){echo $guardar;}
				echo $pdf;
			break;
		}
	}
	
	#el elemento de alerta
	public function showRespuesta(){
		$elem='<div class="respuesta"></div>';
		echo $elem;
	}
	
	/*Otras funciones*/
	public function vardump($v){
		var_dump($v);
	}
	public function printEmpresa(){
		echo $_SESSION["empresa"];
	}
	public function super(){
		return $_SESSION["super"];
	}
	public function echoSesVar($v){
		echo $_SESSION[$v];
	}
	public function centerElem($selector){
		echo "<style>$selector{text-align:center;}</style>";
	}
}
?>