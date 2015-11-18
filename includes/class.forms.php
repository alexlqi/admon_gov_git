<?php //clase para manipulación de la base de datos para los formularios
class formas extends PDO{
	public function __construct($dsn){
		parent::__construct($dsn[0],$dsn[1],$dsn[2],$dsn[3]);
		$this->query("SET NAMES utf8;");
	}
	
	//variable de respuesta
	private $resp=array("err"=>false,"data"=>array(),"sql"=>"","msg"=>"");
	
	//funcion de query to option
	public function query2opt($sql,$ident=array()){
		$r=$this->resp;
		if(count($ident)>0){
			try
			{
				$res=$this->query($sql);
				$r["data"] = $res->rowCount()>0 ? $this->array2opt($res->fetchAll(PDO::FETCH_ASSOC),$ident) : '<option selected="selected" disabled="disabled" value="">No hay elementos</option>';
			}
			catch(PDOException $e)
			{
				$r["err"]=true;
				$r["sql"]=$sql;
				$r["msg"]=$e->getMessage();
			}
		}else{
			$r["err"]=true;
			$r["msg"]="No ha pasado la matriz para cotejar la infoprmación para el par de option value";
		}
		
		return $r;
	}
	
	//funcion de query a array
	public function query2array($sql){
		$r=$this->resp;
		try
		{
			$res=$this->query($sql);
			$r["data"] = $res->rowCount()>0 ? $res->fetchAll(PDO::FETCH_ASSOC) : array();
			$r["err"] = $res->rowCount()>0 ? false : true;
			$r["msg"] = $res->rowCount()>0 ? '' : 'Conjunto vacio';
		}
		catch(PDOException $e)
		{
			$r["err"]=true;
			$r["sql"]=$sql;
			$r["msg"]=$e->getMessage();
		}
		
		return $r;
	} #end query2array();
	
	//función para insertar en la base de datos se debe de poder actualizar en el mismo para evitar el uso del update y sacar el Id y todo eso
	public function insertarArray($arr,$tabla,$info=array(),$update=false){
		#formamos la sentencia de insert
		$r=$this->resp;
		$sql=$this->array2insert($arr,$tabla,$update);
		try
		{
			$this->beginTransaction();
			$this->exec($sql);
			$r["data"]=$this->lastInsertId();
			$this->commit();
		}
		catch(PDOException $e)
		{
			$this->rollBack();
			$r["err"]=true;
			$r["msg"]=$e->getMessage();
			$r["sql"]=$sql;
		}
		return $r;
	}
	
	//insertar desde sql string
	public function insertarSql($sql){
		
		$r=$this->resp;
		try
		{
			$this->beginTransaction();
			if(!is_array($sql)){
				$this->exec($sql);
			}else{
				$cols=$sql;
				foreach($cols as $sql){
					$this->exec($sql);
				}
			}
			$r["data"]=$this->lastInsertId();
			$this->commit();
		}
		catch(PDOException $e)
		{
			$this->rollBack();
			$r["err"]=true;
			$r["msg"]=$e->getMessage();
			$r["sql"]=$sql;
		}
		return $r;
	}
	
	//funcion para checar si existe
	private function existe($data,$tabla){
		# $data es para todos los datos $tabla es para decir en que tabla buscar
		try
		{
			$this->query("SELECT * FROM $tabla WHERE $data;");
		}
		catch(PDOException $e)
		{
		}
		#regresará true o false
		return $r;
	}
	
	//funcion para leer los atributos del campo
	private function leertabla($tabla){
		
	}
	
	public function verificarDatos($arr,$tabla){
		$r=$this->query("SHOW COLUMNS FROM $tabla;");
		$r=$r->fetchAll(PDO::FETCH_ASSOC);
		return $r;
	}
	
	//función para crear un sql de un array de campo valor simple
	public function array2insert($data, $tabla,$update=false){
		# $data debe ser array para que funcione
		$r=false;
		$campos="";
		$values="";
		if(is_array($data)){
			foreach($data as $c=>$v){
				$campos.="$c,";
				$values.="'".trim($v)."',";
			}
			$campos=trim($campos,",");
			$values=trim($values,",");
			if($update){
				# CON update
				$r="INSERT INTO $tabla ($campos) VALUES ($values);";
			}else{
				# SIN
				$r="INSERT INTO $tabla ($campos) VALUES ($values);";
			}
			$r;
		}
		
		return $r;
	}
	
	private function array2opt($arr,$d){
		$r="";
		if(is_array($arr)){
			foreach($arr as $i=>$v){
				$r.='<option value="'.$v[$d[0]].'">'.$v[$d[1]].'</option>';
			}
		}else{
			$r=false;
		}
		return $r;
	}
	
	public function arrSqlInsertUpdate($arr,$tabla,$priKey){
		//campos values
		$campos="";
		$valores="";
		foreach($arr as $c=>$v){
			$campos.="$c,";
			$valores.="'$v',";
		}
		
		//update set
		unset($arr[$priKey]);
		$set="";
		foreach($arr as $c=>$v){
			$set.="$c='$v',";
		}
		
		$campos=trim($campos,",");
		$valores=trim($valores,",");
		$set=trim($set,",");
		
		$str="INSERT INTO $tabla ($campos) VALUES ($valores) ON DUPLICATE KEY UPDATE $set;";
		return $str;
	}
	
	public function arrSqlUpdate($arr,$tabla,$priKey){
		
		//update set
		$priKeyStr=" $priKey = ".$arr[$priKey]." ";
		unset($arr[$priKey]);
		$set="";
		foreach($arr as $c=>$v){
			$set.="$c='$v',";
		}
		$set=trim($set,",");
		
		$str="UPDATE $tabla SET $set WHERE $priKeyStr;";
		return $str;
	}
	
	public function sqlMoves($sec,$user,$acc,$reg,$emp,$cli){
		$sql="
		INSERT INTO movimientos
			(seccion,usuario,accion,registro,empresa,CLIENTE_ID)
		VALUES
			('$sec','$user','$acc','$reg','$emp','$cli');";
		return $sql;
	}
	
	//función para hacer el remote query 2 array
	public function remote2array($url, $dsn, $sql){
		echo http_post_fields($url,array("dsn"=>$dsn),$sql);
		//return http_post_fields($url,array("dsn"=>$dsn),$sql);
	} #end query2array();
	public function __desctruct(){
		parent::__destruct();
	}
}
?>