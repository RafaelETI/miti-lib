<?php
class ARSexos extends AR{
	protected $tabela='sexos';
	
	protected $tipos=array(
		'id'=>'number',
		'nome'=>'string'
	);
	
	protected $anulaveis=array(
		'id'=>false,
		'nome'=>false
	);
	
	protected $tamanhos=array(
		'id'=>3,
		'nome'=>10
	);
	
	protected $pk='id';
}
?>
