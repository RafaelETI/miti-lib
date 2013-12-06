<?php
class MitiValidacao{
	public function tamanho($valor,$tamanho){
		if($valor==''){return null;}
	
		if(strlen($valor)!=$tamanho){throw new Exception('O valor deve conter '.$tamanho.' caract�res');}
	}
	
	public function email($valor){
		if($valor==''){return null;}
	
		if(preg_match('/^\w{2,}@\w{2,}\.\w{2,}$/',$valor)==false){throw new Exception('O e-mail � inv�lido');}
	}
	
	public function vazio($valor){
		$MitiParcialidade=new MitiParcialidade();
		$MitiParcialidade->preparar($valor);
		
		foreach($valor as $v){
			if($MitiParcialidade->parcializar($v)==true){continue;}
		
			if($v==''){throw new Exception('Informe um valor');}
		}
	}
	
	public function upload($file,$tipo,$peso,$imagem=false,$prop_min=0.33,$prop_max=3){
		//a tag form deve conter "enctype='multipart/form-data'", e o "name" deve conter "[]" (upload multiplo)
		if($_FILES[$file]['name'][0]==''){return null;}
		
		foreach($_FILES[$file]['name'] as $i=>$v){
			//geral
			if($_FILES[$file]['name'][$i]==''){throw new Exception('N�o foi feito o upload de um arquivo');}
			if(strpos($_FILES[$file]['type'][$i],$tipo)===false){throw new Exception('O tipo do arquivo � inv�lido');}
			if($_FILES[$file]['size'][$i]>$peso){throw new Exception('O arquivo excede o tamanho permitido');}
			
			//imagens
			if($imagem==true){
				$tamanho=getimagesize($_FILES[$file]['tmp_name'][$i]);
				$proporcao=$tamanho[0]/$tamanho[1];
				if($proporcao<$prop_min){throw new Exception('A propor��o da imagem � inv�lida, excedendo verticalmente');}
				if($proporcao>$prop_max){throw new Exception('A propor��o da imagem � inv�lida, excedendo horizontalmente');}
			}
		}
	}
	
	public function cpf($cpf){
		if($cpf==''){return null;}
		
		//validacao de quantidade e tipo de caracteres
		if(strlen($cpf)!=11||preg_match('/[0-9]/',$cpf)==false){throw new Exception('O CPF � inv�lido');}
		
		//validacao de sequencia de numeros iguais
		for($i=1,$y=$cpf[0];$i<=10;$i++){
			if($y!=$cpf[$i]){break;}
			
			if($i==10){throw new Exception('O CPF � inv�lido');}
		}
		
		//validacao de digitos verificadores
		for($t=9;$t<11;$t++){
			for($d=0,$c=0;$c<$t;$c++){
				$d+=$cpf[$c]*(($t+1)-$c);
			}
			
			$d=((10*$d)%11)%10;
			
			if($cpf[$c]!=$d){throw new Exception('O CPF � inv�lido');}
		}
	}
	
	public function cnpj($cnpj){
		if($cnpj==''){return null;}
		
		//validacao de quantidade e tipo de caracteres e sequencia de zeros
		if(strlen($cnpj)!=14||preg_match('/[0-9]/',$cnpj)==false||$cnpj=='00000000000000'){throw new Exception('O CNPJ � inv�lido');}
		
		//validacao de digitos verificadores
		$p=array(
			array('x'=>5,'i'=>array(11,4),'p'=>12),
			array('x'=>6,'i'=>array(12,5),'p'=>13)
		);
		
		for($y=0;$y<=1;$y++){
			for($i=0,$x=$p[$y]['x'],$soma=0;$i<=$p[$y]['i'][0];$i++){
				if($i==$p[$y]['i'][1]){$x=9;}
				$soma+=$cnpj[$i]*$x--;
			}
			
			$resto=$soma%11;
			if($resto<2){$digito=0;}else{$digito=11-$resto;}
			
			if($cnpj[$p[$y]['p']]!=$digito){throw new Exception('O CNPJ � inv�lido');}
		}
	}
}
?>