<?php
class Config{
	public function __construct($Classe,$restrito,$raiz='',$sessao='login'){
		$this
			->ambiente()
			->charset()
			->erro()
			->sistema()
			->raiz($raiz)
			->banco()
			->sessao($restrito,$sessao)
			->autoload()
			->objeto($Classe)
		;
	}
	
	private function ambiente(){
		define('AMBIENTE',1);
		return $this;
	}
	
	private function charset(){
		header('Content-Type: text/html; charset=iso-8859-1');
		return $this;
	}
	
	private function erro(){
		error_reporting(E_ALL|E_STRICT);
		ini_set('display_errors',AMBIENTE);
		
		return $this;
	}
	
	private function sistema(){
		if(AMBIENTE===0){
			define('SISTEMA','Miti Modelo');
		}else if(AMBIENTE===1){
			define('SISTEMA','Miti Modelo 5.17.101');
		}
		
		return $this;
	}
	
	private function raiz($raiz){
		define('RAIZ',$raiz);
		return $this;
	}
	
	private function banco(){
		if(AMBIENTE===0){
			define('BD_SERVIDOR','localhost');
			define('BD_USUARIO','usuario');
			define('BD_SENHA','senha');
			define('BD_BANCO','banco');
			define('BD_CHARSET','latin1');
		}else if(AMBIENTE===1){
			define('BD_SERVIDOR','localhost');
			define('BD_USUARIO','usuario');
			define('BD_SENHA','senha');
			define('BD_BANCO','banco');
			define('BD_CHARSET','latin1');
		}
		
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
	
	public static function verificarSessao($sessao='login'){
		if(!isset($_SESSION[$sessao])){
			throw new Exception('Voc� n�o tem permiss�o');
		}
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
		if(isset($_REQUEST['metodo'])){
			$this->tratarRequisicao();
			
			try{
				$Objeto=new $Classe;
				$Objeto->$_REQUEST['metodo']();
				header('location:'.$_REQUEST['url']);
				exit;
			}catch(Exception $e){
				$_SESSION['status']=$e->getMessage();
			}
		}
		
		return $this;
	}
	
	private function tratarRequisicao(){
		unset($_POST['metodo']);
		unset($_POST['url']);
		unset($_GET['metodo']);
		unset($_GET['url']);
		
		if(!isset($_REQUEST['url'])){
			$_REQUEST['url']=$_SERVER['REQUEST_URI'];
		}
	}
}
