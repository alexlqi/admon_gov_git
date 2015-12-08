<?php

class sesion{
	//seccion de las variables
	public 		$logged=false;
	public 		$user_id=0;
	public 		$user="";
	public 		$categoria="";
	private 	$algo="md5";
	private 	$permisos=array();
	protected 	$dsn=array();

	public sesion($dsn){
		//se guarda y protege el $dsn
		$this->dsn=$dsn; //tiene que usarse el reader
	}

	private function hash($str){
		return hash($this->algo, $str);
	}

	public function login(){
		if(@$_SESSION["logged"]){
			// ya está logeado
		}else{
			//no está logeado
			if(@$_POST["user"]!="" and @$_POST["pass"]!=""){
				//intentar logearse si user y pass no están vacios
				if(true){
					//en la base de datos se pudo colocar
					$this->user=$_POST["user"];

					$_SESSION["logged"]=true;
					$this->logged=true;
				}else{
					$_SESSION["logged"]=false;
					$this->logged=false;
				}
			}else{
				//si no hay user o pass entonces mandar mensaje que no se pudo iniciar sesión
				//y volver a pedir los datos de inicio de sesión
			}
		}
	}

	public function logoff(){
		session_destroy();
	}

	public function permisos($permisosStr){
		$this->permisos=explode("_",$permisosStr);
	}


}

?>