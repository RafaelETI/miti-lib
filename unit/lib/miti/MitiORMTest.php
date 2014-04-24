<?php
class MitiORMTest extends PHPUnit_Framework_TestCase{
	protected $MitiORM;
	
	protected function setUp(){
		$this->MitiORM=new MitiORM('categorias');
	}
	
	public function testCriar(){
		$this->MitiORM->criar(array('id'=>2,'nome'=>'\'Tes\te"','status'=>'aaa'));
		
		$categorias=$this->MitiORM
			->definirCampos(array('nome','status'))
			->ler(array('id'=>array('=',2)))
			->obterAssoc();
		
		$this->assertSame(array('nome'=>'\'Tes\te"','status'=>'0'),$categorias);
	}
	
	public function testCriarValorVazio(){
		$this->setExpectedException('Exception','Valor vazio');
		$this->MitiORM->criar(array('id'=>6,'nome'=>''));
	}
	
	public function testCriarExcessoTamanho(){
		$this->setExpectedException('Exception','Limite de caractéres excedido');
		$this->MitiORM->criar(array('id'=>6,'nome'=>'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaab'));
	}
	
	public function testJuntar(){
		$categorias=$this->MitiORM
			->setJoins(array('join'))
			->setAliases(array('m'))
			->setOnTabelas(array('categorias'))
			->setTabelaChaves(array('id'))
			->setTabelasChaves(array('categoria'))
			->juntar(array('memoria'))
			->definirCampos(array('id'),array(array('descricao')))
			->ler(
				array('id'=>array('=','1')),
				array(array('descricao'=>array('like','hur')))
			)->obterAssoc();
		
		$this->assertSame('Ben Hur (1959)',$categorias['m_descricao']);
	}
	
	public function testOrdenar(){
		$categorias=$this->MitiORM
			->definirCampos(array('nome'))
			->ordenar(array('id'=>'desc'))
			->ler()
			->obterAssoc();
		
		$this->assertSame('\'Tes\te"',$categorias['nome']);
	}
	
	public function testLimitar(){
		$MitiORM=new MitiORM('memoria');
		
		$quantidade=$MitiORM
			->definirCampos(array('id'))
			->limitar(2,1)
			->ler()
			->obterQuantidade();
		
		$this->assertSame(2,$quantidade);
	}
	
	public function testTratarLeituraEscapar(){
		$quantidade=$this->MitiORM
			->definirCampos(array('id'))
			->ler(array('nome'=>array('=','\'Tes\te"')))
			->obterQuantidade();
		
		$this->assertSame(1,$quantidade);
	}
	
	public function testTratarLeituraWildcard(){
		$quantidade=$this->MitiORM
			->definirCampos(array('id'))
			->ler(array('nome'=>array('like','es')))
			->obterQuantidade();
		
		$this->assertSame(1,$quantidade);
	}
	
	public function testTratarLeituraSetType(){
		$quantidade=$this->MitiORM
			->definirCampos(array('id'))
			->ler(array('status'=>array('=','tes')))
			->obterQuantidade();
		
		$this->assertSame(1,$quantidade);
	}
	
	public function testAtualizar(){
		$this->MitiORM->atualizar(array('nome'=>'Teste2','status'=>''),2);
		
		$categorias=$this->MitiORM
			->definirCampos(array('nome','status'))
			->ler(array('id'=>array('=',2)))
			->obterAssoc();
		
		$this->assertSame(array('nome'=>'Teste2','status'=>null),$categorias);
	}
	
	public function testDeletar(){
		$this->MitiORM->deletar(2);
	}
	
	public function testDeletarArray(){
		$this->MitiORM->criar(array('id'=>3,'nome'=>'Aaa','status'=>0));
		$this->MitiORM->criar(array('id'=>4,'nome'=>'Bbb','status'=>0));
		
		$this->MitiORM->deletar(array('status'=>0));
		
		$quantidade=$this->MitiORM
			->definirCampos(array('id'))
			->ler(array('status'=>array('=',0)))
			->obterQuantidade();
		
		$this->assertSame(0,$quantidade);
	}
	
	public function testDeletarScalar(){
		$MitiORM=$this->criarRegistroMemoria();
		$MitiORM->deletar('d');
		
		$quantidade=$MitiORM
			->definirCampos(array('id'))
			->ler(array('id'=>array('=','d')))
			->obterQuantidade();
		
		$this->assertSame(0,$quantidade);
	}
	
	private function criarRegistroMemoria(){
		$MitiORM=new MitiORM('memoria');
		$MitiORM->criar(array('id'=>'d','descricao'=>'Teste','categoria'=>1));
		return $MitiORM;
	}
}
