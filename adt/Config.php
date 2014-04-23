<?php
class Config{
	public function __construct($Classe,$restrito,$raiz='',$sessao='login'){
		$this
			->erros()
			->sistema()
			->raiz($raiz)
			->banco()
			->sessao($restrito,$sessao)
			->autoload()
			->objeto($Classe);
	}
	
	private function erros(){
		error_reporting(E_ALL);
		ini_set('display_errors',1);
		
		return $this;
	}
	
	private function sistema(){
		define('SISTEMA','Miti Modelo 5.14.93');
		return $this;
	}
	
	private function raiz($raiz){
		define('RAIZ',$raiz);
		return $this;
	}
	
	private function banco(){
		//localhost:root:root:miti_modelo:latin1
		define('BD_SERVIDOR','localhost');
		define('BD_USUARIO','usuario');
		define('BD_SENHA','senha');
		define('BD_BANCO','banco');
		define('BD_CHARSET','latin1');
		
		return $this;
	}
	
	private function sessao($restrito,$sessao){
		session_start();
		
		if($restrito&&!isset($_SESSION[$sessao])){
			$_SESSION['status']='Voc� n�o est� autenticado';
			header('location:'.RAIZ.'main/login.php');
			exit;
		}
		
		return $this;
	}
	
	private function autoload(){
		function miti_autoload($classe){
			$pacotes=array('adt','lib/miti');
			
			foreach($pacotes as $v){
				if(file_exists(RAIZ.$v.'/'.$classe.'.php')){
					require RAIZ.$v.'/'.$classe.'.php';
					break;
				}
			}
		}
		
		spl_autoload_register('miti_autoload');
		
		return $this;
	}
	
	private function objeto($Classe){
		if(isset($_REQUEST['acao'])){
			try{
				$Objeto=new $Classe;
				$url=$Objeto->$_REQUEST['acao']();
				$_SESSION['status']=true;
				header('location:'.$this->garantirUrl($url));
				exit;
			}catch(Exception $e){
				$_SESSION['status']=$e->getMessage();
				header('location:'.$_SERVER['REQUEST_URI']);
				exit;
			}
		}
		
		return $this;
	}
	
	private function garantirUrl($url){
		if(!$url){
			return $_SERVER['REQUEST_URI'];
		}else{
			return $url;
		}
	}
}