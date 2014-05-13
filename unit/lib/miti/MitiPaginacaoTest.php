<?php
class MitiPaginacaoTest extends PHPUnit_Framework_TestCase{
	public function testGetNumReg(){
		$MitiPaginacao=new MitiPaginacao(1,1,7);
		$this->assertSame(1,$MitiPaginacao->getNumReg());
	}
	
	public function testGetInicio(){
		$MitiPaginacao=new MitiPaginacao(15,5,10);
		$this->assertSame(60,$MitiPaginacao->getInicio());
	}
	
	public function testCriarComNenhumRegistro(){
		$MitiPaginacao=new MitiPaginacao(1,1,3);
		$MitiPaginacao->setTotal(0);
		
		$this->assertSame(
			'N�o h� registros para esta busca',
			$MitiPaginacao->criar('?pg=','off','on')
		);
	}
	
	public function testCriarComPoucosRegistros(){
		$MitiPaginacao=new MitiPaginacao(1,1,1);
		$MitiPaginacao->setTotal(1);
		
		$this->assertSame(
			'<span class="off">Primeira</span>'
			.'<span class="off">Anterior</span>'
			.'<span class="on">1</span>'
			.'<span class="off">Pr�xima</span>'
			.'<span class="off">�ltima</span>',
		
			$MitiPaginacao->criar('?pg=','off','on')
		);
	}
	
	public function testCriarComMuitosRegistros(){
		$MitiPaginacao=new MitiPaginacao(10,2,5);
		$MitiPaginacao->setTotal(100);
		
		$this->assertSame(
			'<a href="?pg=1">Primeira</a>'
			.'<a href="?pg=1">Anterior</a>'
			.'<a href="?pg=1">1</a>'
			.'<span class="on">2</span>'
			.'<a href="?pg=3">3</a>'
			.'<a href="?pg=4">4</a>'
			.'<a href="?pg=3">Pr�xima</a>'
			.'<a href="?pg=10">�ltima</a>',
		
			$MitiPaginacao->criar('?pg=','off','on')
		);
	}
}