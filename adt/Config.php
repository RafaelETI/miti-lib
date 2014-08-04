<?php
/**
 * Miti API, 2014
 * 
 * @author Rafael Barros <admin@rafaelbarros.eti.br>
 * @link https://github.com/RafaelETI/MitiAPI
 */
namespace adt;

/**
 * Configura��o do sistema
 * 
 * Esse arquivo deve ser requerido, e a classe deve ser instanciada, no come�o
 * de todas as p�ginas do sistema.
 * 
 * Como � nessa classe que � definida a fun��o de autoload, ela � a �nica em que
 * o arquivo deve ser requerido manualmente.
 */
class Config{
	/**
	 * Chama todos os m�todos de configura��o da classe
	 * 
	 * � aqui que se parametriza algumas configura��es do sistema, o que � feito
	 * por p�gina.
	 * 
	 * @api
	 * 
	 * @param string $Classe Nome da classe respons�vel por tratar requisi��es
	 * na p�gina. Com isso, apenas uma classe, no m�ximo, fica respons�vel por
	 * p�gina. Caso tenha o valor vazio, a p�gina n�o trata nenhuma requisi��o.
	 * 
	 * @param bool $restrito Define se a p�gina � restrita ao acesso, ou seja,
	 * se apenas pode ser acessada se o usu�rio possuir uma sess�o ativa.
	 * 
	 * @param string $sessao Nome da sess�o.
	 */
	public function __construct($Classe,$restrito,$sessao='usuario'){
		$this
			->ambiente()
			->sistema()
			->banco()
			->erro()
			->timezone()
			->charset()
			->raiz()
			->sessao($restrito,$sessao)
			->autoload()
			->requisicao($Classe)
		;
	}
	
	/**
	 * Configura o ambiente do sistema
	 * 
	 * Por conven��o, o ambiente de produ��o � o de valor zero, e os outros s�o
	 * incrementados em um.
	 * 
	 * Por padr�o essa classe tem defini��es para dois ambientes. Caso queira-se
	 * adicionar mais, deve-se adicionar novas configura��es nos outros m�todos
	 * cab�veis.
	 * 
	 * H� a inten��o de que esse seja o �nico ponto de manuten��o ao trocar o
	 * sistema de ambiente. Muito cuidado para n�o enviar esse arquivo para a
	 * produ��o, estando configurado para o desenvolvimento! Nesse caso, muito
	 * prov�vel que as configura��es de banco de dados estejam erradas e os
	 * erros sejam mostrados na tela. Sempre conferir o sistema on-line quando
	 * subir esse arquivo.
	 * 
	 * @return Config
	 */
	private function ambiente(){
		define('AMBIENTE',1);
		return $this;
	}
	
	/**
	 * Configura o nome e a vers�o do sistema
	 * 
	 * Recomenda-se chamar essa constante em algum lugar vis�vel de todas as
	 * interfaces para que possa-se identificar facilmente se o sistema est�
	 * configurado para o ambiente de produ��o ou n�o.
	 * 
	 * @return Config
	 */
	private function sistema(){
		if(AMBIENTE===0){
			define('SISTEMA','Miti API');
		}else{
			define('SISTEMA','Miti API 1.2.13');
		}
		
		return $this;
	}
	
	/**
	 * Configura a conex�o com o banco de dados
	 * 
	 * No caso do MySQL, ele aceita, dentre outros, os charsets latin1 e utf8
	 * (escritos dessa forma).
	 * 
	 * Se o servidor do banco de dados for o mesmo de onde o sistema est�
	 * hospedado, usar localhost.
	 * 
	 * @return Config
	 */
	private function banco(){
		if(AMBIENTE===0){
			define('BD_SERVIDOR','servidor');
			define('BD_USUARIO','usuario');
			define('BD_SENHA','senha');
			define('BD_BANCO','banco');
			define('BD_CHARSET','charset');
		}else if(AMBIENTE===1){
			define('BD_SERVIDOR','servidor');
			define('BD_USUARIO','usuario');
			define('BD_SENHA','senha');
			define('BD_BANCO','banco');
			define('BD_CHARSET','charset');
		}
		
		return $this;
	}
	
	/**
	 * Configura como o PHP trata os erros do sistema
	 * 
	 * � uma das principais configura��es de seguran�a. Nunca mostre os erros
	 * emitidos pelo PHP diretamente na tela, no ambiente de produ��o!
	 * 
	 * @return Config
	 */
	private function erro(){
		error_reporting(-1);
		
		if(AMBIENTE===0){
			ini_set('display_errors',0);
		}else{
			ini_set('display_errors',1);
		}
		
		return $this;
	}
	
	/**
	 * Configura a timezone do PHP
	 * 
	 * Vide a lista de timezones que o PHP suporta:
	 * {@link http://php.net/manual/en/timezones.php}.
	 * 
	 * @return Config
	 */
	private function timezone(){
		date_default_timezone_set('America/Sao_Paulo');
		return $this;
	}
	
	/**
	 * Configura o tipo do conte�do e o charset da p�gina
	 * 
	 * Dessa forma o charset j� � definido no cabe�alho do HTTP, portanto, n�o
	 * h� a necessidade de usar a meta tag do HTML para isso.
	 * 
	 * @return Config
	 */
	private function charset(){
		header('content-type:text/html; charset=iso-8859-1');
		return $this;
	}
	
	/**
	 * Configura os caminhos para o diret�rio raiz do sistema
	 * 
	 * Tanto da perspectiva do sistema operacional, quanto da internet.
	 * 
	 * Para o ambiente de produ��o, caso o sistema n�o esteja na raiz do dom�nio,
	 * deve-se complementar o caminho para se chegar at� ele no c�digo.
	 * 
	 * @return Config
	 */
	private function raiz(){
		if(AMBIENTE===0){
			define('RAIZ_OS',$_SERVER['DOCUMENT_ROOT']);
			define('RAIZ_WEB','http://'.$_SERVER['HTTP_HOST']);
		}else if(AMBIENTE===1){
			define('RAIZ_OS',$_SERVER['DOCUMENT_ROOT'].'/MitiAPI');
			define('RAIZ_WEB','http://'.$_SERVER['HTTP_HOST'].'/MitiAPI');
		}
		
		return $this;
	}
	
	/**
	 * Verifica a sess�o do usu�rio, � n�vel de p�gina
	 * 
	 * Deve-se escolher o local de destino do redirecionamento em caso de
	 * restri��o, pois o que est� por padr�o pode, facilmente, n�o ser o desejado.
	 * 
	 * @param bool $restrito
	 * @param string $sessao
	 * @return Config
	 */
	private function sessao($restrito,$sessao){
		session_start();
		
		if($restrito&&!isset($_SESSION[$sessao])){
			$_SESSION['status']='Voc� n�o est� autenticado.';
			header('location:'.RAIZ_WEB);
			exit;
		}
		
		return $this;
	}
	
	/**
	 * Verifica a sess�o do usu�rio, � n�vel de m�todo
	 * 
	 * Chamar esse m�todo em todos os m�todos que precisem de uma sess�o ativa
	 * para serem executados, visto que podem existir p�ginas que n�o estejam
	 * fechadas para a sess�o, e que podem estar configuradas para receberem
	 * requisi��es para determinada classe. � uma segunda camada de prote��o.
	 * 
	 * Lembrar que, em caso de haverem v�rios usu�rios compartilhando o mesmo
	 * nome de sess�o no sistema, deve-se verificar se o usu�rio que est�
	 * executando o procedimento tem permiss�o.
	 * 
	 * @api
	 * @param string $sessao
	 * @throws \Exception
	 */
	public static function verificarSessao($sessao='usuario'){
		if(!isset($_SESSION[$sessao])){
			throw new \Exception('Voc� n�o tem permiss�o.');
		}
	}
	
	/**
	 * Configura a fun��o de autoload de classes
	 * 
	 * Para os nomes dos namespaces devem haver pastas de mesmo nome, come�ando
	 * da ra�z do sistema, tendo, por fim, um arquivo com o mesmo nome da classe.
	 * 
	 * Todos os nomes devem respeitar as mesmas caixas altas e baixas, tanto no
	 * c�digo, quanto no sistema de arquivos.
	 * 
	 * @return Config
	 */
	private function autoload(){
		spl_autoload_register(function($Classe){require RAIZ_OS."/$Classe.php";});
		return $this;
	}
	
	/**
	 * Configura o recebimento de requisi��es na p�gina
	 * 
	 * Esse procedimento pretende ser gen�rico o suficiente para todas as
	 * situa��es.
	 * 
	 * Ele � ativado caso exista uma vari�vel de nome "metodo" na requisi��o.
	 * 
	 * A classe requisitada � a que responde pela p�gina.
	 * 
	 * Em caso de sucesso, atendendo ao design pattern POST/Redirect/GET
	 * ({@link http://en.wikipedia.org/wiki/Post/Redirect/Get}), ele gera uma
	 * requisi��o GET. Em caso de erro, ele n�o redireciona, para que as
	 * informa��es da requisi��o n�o sejam perdidas.
	 * 
	 * A vari�vel "url" da requisi��o � a que define para onde ser� feita a
	 * requisi��o GET, em caso de sucesso.
	 * 
	 * @param string $Classe
	 * @return Config
	 */
	private function requisicao($Classe){
		if(isset($_REQUEST['metodo'])){
			$requisicao=$this->tratarRequisicao();
			
			try{
				$Objeto=new $Classe;
				$_SESSION['status']=$Objeto->$_REQUEST['metodo']($requisicao);
				header("location:{$_REQUEST['url']}");
				exit;
			}catch(\Exception $e){
				$_SESSION['status']=$e->getMessage();
			}
		}
		
		return $this;
	}
	
	/**
	 * Trata as vari�veis da requisi��o
	 * 
	 * As vari�veis "metodo" e "url" s�o eliminadas para que o par�metro passado
	 * ao m�todo tenha apenas valores importantes � ele.
	 */
	private function tratarRequisicao(){
		if(!isset($_REQUEST['url'])){
			$_REQUEST['url']=$_SERVER['HTTP_REFERER'];
		}
		
		$requisicao=$_REQUEST;
		unset($requisicao['metodo']);
		unset($requisicao['url']);
		
		return $requisicao;
	}
}
