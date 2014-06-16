<?php
class MitiTratamento{
	public function requerer($caminho){
		$hash=md5(file_get_contents($caminho));
		
		$partes=explode('.',$caminho);
		$extensao=array_pop($partes);
		
		if($extensao==='js'){
			$html="<script src='$caminho?hash=$hash'></script>\n";
		}else if($extensao==='css'){
			$html="<link rel='stylesheet' type='text/css' href='$caminho?hash=$hash' />\n";
		}
		
		return $html;
	}
	
	public function garantirValor($original,$novo){
		if($original){
			return $original;
		}
		
		return $novo;
	}
	
	public function garantirIndices($vetor,array $indices){
		foreach($indices as $i){
			if(!isset($vetor[$i])){
				$vetor[$i]='';
			}
		}
		
		return $vetor;
	}
	
	public function garantirArquivo($arquivo,$caminho){
		if(!$arquivo){
			$arquivo=file_get_contents($caminho);
		}
		
		return $arquivo;
	}
	
	public function htmlSpecialChars($valores,$charset='iso-8859-1'){
		if(!$valores){
			return;
		}
		
		if(is_array($valores)){
			$valores=$this->htmlSpecialCharsArray($valores,$charset);
		}else{
			$valores=$this->htmlSpecialCharsScalar($valores,$charset);
		}
		
		return $valores;
	}
	
	private function htmlSpecialCharsArray($valores,$charset){
		foreach($valores as $i=>$v){
			$valores[$i]=htmlspecialchars($v,ENT_QUOTES,$charset);
		}
		
		return $valores;
	}
	
	private function htmlSpecialCharsScalar($valores,$charset){
		return htmlspecialchars($valores,ENT_QUOTES,$charset);
	}
	
	public function encurtar($valores,$tamanho=5){
		if(!$valores){
			return;
		}
	
		if(is_array($valores)){
			$valores=$this->encurtarArray($valores,$tamanho);
		}else{
			$valores=$this->encurtarScalar($valores,$tamanho);
		}
		
		return $valores;
	}
	
	private function encurtarArray($valores,$tamanho){
		foreach($valores as $i=>$v){
			if(strlen($v)>$tamanho+2){
				$valores[$i]=substr($v,0,$tamanho).'...';
			}
		}
		
		return $valores;
	}
	
	private function encurtarScalar($valores,$tamanho){
		if(strlen($valores)>$tamanho+2){
			$valores=substr($valores,0,$tamanho).'...';
		}
		
		//a cobertura do teste unitario pede o retorno aqui
		return $valores;
	}
	
	public function removerAcentos($valores){
		if(!$valores){
			return;
		}
	
		$acentos=array(
			'�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�',
			'�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�',
			'�','�','�','�','�','�','�','�','�','�','�','�'
		);
		
		$normais=array(
			'a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o',
			'o','u','u','u','u','c','A','A','A','A','A','E','E','E','E','I','I',
			'I','I','O','O','O','O','O','U','U','U','U','C'
		);
		
		if(is_array($valores)){
			$valores=$this->removerAcentosArray($valores,$acentos,$normais);
		}else{
			$valores=$this->removerAcentosScalar($valores,$acentos,$normais);
		}
		
		return $valores;
	}
	
	private function removerAcentosArray($valores,$acentos,$normais){
		foreach($valores as $i=>$v){
			$valores[$i]=str_replace($acentos,$normais,$v);
		}
		
		return $valores;
	}
	
	private function removerAcentosScalar($valores,$acentos,$normais){
		return str_replace($acentos,$normais,$valores);
	}
}