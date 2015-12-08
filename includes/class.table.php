<?php //clase para los listados

function httpurl($v){
	return http_build_query(v);
}

class tablas{
	private $table='';
	private $arr;
	private $show=array();
	private $actual=array();
	private $previo=array();
	public $p="";
	private $rpp;
	public $titles=false;
	
	##revisar que sea un array y que si hay un array dentro de un array entonces se relacione a través de una matriz
	public function __construct($cfg=array('rpp'=>25)){
		$this->rpp=@$cfg["rpp"];
	}
	
	public function load($arr=array()){	
		if(!empty($arr)){
			#se limpia toda la tabla que habia estado en memoria
			$this->clearTbl();
			$this->arr=$arr;
			
			#se asigna el arrary completo a la memoria
			$_SESSION["tblArr"]=$this->arr;
		}else{
			#cargará la info de Session
			#se procesa el array y se coloca la tabla
			if($_SESSION["tblArr"]!=$arr){
				$this->arr=$arr;
				$_SESSION["tblArr"]=$arr;
				$this->actual=$this->organizar($this->arr);
				$this->setActual($arr);
			}else{
				$this->arr=(!@empty($_SESSION["tblArr"])) ? $_SESSION["tblArr"] : array();
			}
		}
		
		if(@!empty($_GET["k"])){
			$this->detalles($_GET["k"]);
		}else{
			if(@$_GET["t"]!=""){
				$this->setActual($_SESSION["tblArr"][$_GET["t"]]);
				$this->actual=$this->organizar($_SESSION["tblArr"][$_GET["t"]]);
			}else{
				$this->setActual($_SESSION["tblArr"]);
				$this->actual=$this->organizar($_SESSION["tblArr"]);
			}
		}
	}
	
	private function setPrevio($arr){$_SESSION["previo"]=$arr;}
	private function setActual($arr){$_SESSION["actual"]=$arr;}	
	private function getPrevio(){return @$_SESSION["previo"];}
	private function getActual(){return @$_SESSION["actual"];}
	
	public function detalles($arr){
		$a=$this->getActual();
		$p=$this->getPrevio();
		
		$data=$a;
		
		foreach($arr as $k){
			if(isset($data[$k])){
				$data=$data[$k];
			}
		}
		$d[end($arr)]=$data;
		
		$this->setActual($d[end($arr)]);
		$this->actual=$this->organizar($d[end($arr)]);
	}
	
	private function organizar($arr){
		$org=array();
		$arrData=true;
		
		##arrData un tipo de array donde ningun nieto es array
		foreach($arr as $i=>$v){
			if(is_array($v)){
				foreach($v as $vv){
					if(is_array($vv)){
						$arrData=false;
						break 2;
					}
				}
			}else{
				$arrData=false;
				break;
			}
		}
		
		foreach($arr as $i=>$v){
			if(!is_array($v)){
				#Si no es array entonces
				$org[$i]=$v;
			}else{
				if($arrData){
					foreach($v as $ii=>$vv){
						$org[$i][$ii]=$vv;
					}
				}else{
					$org[$i]='<a href="?'.urldecode(http_build_query($_GET)).'&k[]='.$i.'">Ver Detalles</a>';
				}
				
			}
			
		}
		
		return $org;
	}
	
	public function clearTbl(){
		$_SESSION["tblArr"]=$_SESSION["show"]=array();
	}
	
	public function verArray(){
		echo "<pre>"; var_dump($this->arr); echo "</pre>";
	}
	
	public function show($opt='html'){
		if(empty($this->actual)){
			return 'No hay datos para mostrar';
			break;
		}
		$this->limpiar();
		$tabla="";
		$tabla.=$this->titles();
		$tabla.=$this->paginas();
		$tabla.=$this->encabezado();
		$tabla.=$this->renglones();
		$tabla.=$this->pie();
		switch($opt){
			case 'html':
				echo $tabla;
			break;
			case 'array':
				return $tabla;
			break;
		}
	}
	
	public function get2url($unset=array()){
		if(!empty($unset)){
			foreach($unset as $u){
				unset($_GET[$u]);
			}
		}
		if($this->titles){
			return '<a href="?'.http_build_query($_GET).'">Regresar</a>';
		}else{
			return '';
		}
	}
	
	private function getK(){
		$str="";
		if(@!empty($_GET["k"])){
			$str=implode('&k[]=',$_GET["k"]);
			$str="&k[]=".$str;
		}
		return $str;
	}
	
	private function paginas(){
		$rpp=$this->rpp;
		$total=0;
		foreach($this->actual as $d){
			if(is_array($d)){$total++;}
		}
		if($total==0){return ''; break;}
		$paginas=ceil(($total/$rpp));
		$pageCtrl="";
		
		$anidado=(@!empty($_GET["k"]))?:'';
		
		if($paginas==1){
			#$pageCtrl.='<a href="?p=1'.$this->getK().'">1</a>';
		}else{
			$pageCtrl.='<a href="?p=1'.$this->getK().'">1</a>';
			if(@$_GET["p"]>2 and @$_GET["p"]<($paginas-1)){
				$pageCtrl.='<a href="?p='.($_GET["p"]-2).$this->getK().'">...</a>';
				for($i=-1;$i<=1;$i++){
					$pageCtrl.='<a href="?p='.($i+$_GET["p"]).$this->getK().'">'.($i+$_GET["p"]).'</a>';
				}
				$pageCtrl.='<a href="?p='.($_GET["p"]+2).$this->getK().'">...</a>';
			}elseif(@$_GET["p"]>=($paginas-1)){
				$pageCtrl.='<a href="?p='.($paginas-3).$this->getK().'">...</a>';
				$pageCtrl.='<a href="?p='.($paginas-2).$this->getK().'">'.($paginas-2).'</a>';
				$pageCtrl.='<a href="?p='.($paginas-1).$this->getK().'">'.($paginas-1).'</a>';
			}else{
				$pageCtrl.='<a href="?p=2">2</a>';
				$pageCtrl.='<a href="?p=3">3</a>';
				$pageCtrl.='<a href="?p='.(@$_GET["p"]+2).$this->getK().'">...</a>';
			}
			$pageCtrl.='<a href="?p='.$paginas.$this->getK().'">'.$paginas.'</a>';
		}
		
		return $pageCtrl;
	}
	
	public function titles(){
		$str="";
		if($this->titles * !isset($_GET["t"])){
			foreach(array_keys($this->getActual()) as $t){
				$str.='<a href="?t='.$t.'">'.$t.'</a>';
			}
		}else{
			$str=$this->get2url(array("t"));
		}
		return $str;
	}
	
	private function encabezado(){
		$actual=$this->actual;
		$arr=is_array(end($actual))? end($actual) : $actual;
		$heads=array_keys($arr);
		$html='<table>';
		$html.='<tr><th><input type="checkbox" class="select all" /></th>';
		$k=(@!empty($_GET["k"]))? end($_GET["k"])."<br />": '';
		foreach($heads as $v){
			$html.="<th>$k$v</th>";
		}
		$html.='</tr>';
		return $html;
	}
	
	public function addCell($arr){
		$tabla=$this->getActual();
		foreach($arr as $i=>$d){
			if(isset($tabla[$i])){
				if(is_array($d)){
					foreach($d as $ii=>$dd){
						$tabla[$i][$ii]=$dd;
					}
				}else{
					$tabla[$i]['reciente']=$d;
				}
			}
		}
		
		$this->setActual($tabla);
	}
	
	private function renglones(){
		$pags=(isset($_GET["p"]))?$_GET["p"]:1;
		$linf=(($pags-1)*$this->rpp+1);
		$lsup=$pags*$this->rpp;
		#echo  "$linf - $lsup";
		$html='';
		$actual=$this->actual;
		if(is_array(end($actual))){
			$contar=1;
			foreach($actual as $i=>$v){
				if($contar>=$linf and $contar<=$lsup){
					$html.='<tr><td class="select s'.$i.'" data-row="'.$i.'"><input type="checkbox" /></td>';
					
					foreach($v as $ii=>$vv){
						$html.="<td>$vv</td>";
					}
					$html.='</tr>';
				}
				$contar++;
			}
		}else{
			$html.='<tr><td class="select s1" data-row="1"><input type="checkbox" /></td>';
			$contar=1;
			foreach($actual as $ii=>$vv){
				$html.="<td>$vv</td>";
			}
			$contar++;
			$html.='</tr>';
		}
		return $html;
	}
	
	private function pie(){
		$html='</table>';
		if(@!empty($_GET["k"])){
			echo '<button onclick="javascript: window.history.back();">Regresar</button>';
		}
		return $html;
	}
	
	private function limpiar(){
		$this->table='';
	}
}
?>