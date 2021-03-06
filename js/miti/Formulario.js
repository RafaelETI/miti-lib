function MitiFormulario(){
	this.contar = function(id, quantidade){
		var elemento = MitiElemento.getId(id);
		
		elemento.onkeyup = function(){
			var valor = elemento.value;
			var resto = quantidade - valor.length;
			
			var contagem = MitiElemento.getId(elemento.id + '_miticontar');
			contagem.innerHTML = resto;
		};
	};
	
	this.confirmarSubmit = function(){
		var submits = MitiElemento.getClass('mitisubmit');
		var inputs;
		
		for(var x=0; x < submits.length; x++){
			inputs = submits[x].getElementsByTagName('input');
			submits[x].onsubmit = confirmarSubmit(inputs);
		}
		
		function confirmarSubmit(inputs){
			return function(){return confirm('Tem certeza (' + inputs[0].value + ')?');};
		}
	};
	
	this.confirmarClick = function(){
		var clicks = MitiElemento.getClass('miticlick');
		
		for(var x = 0; x < clicks.length; x++){
			clicks[x].onclick = function(){
				return confirm('Tem certeza (' + this.title + ')?');
			};
		}
	};
	
	this.validar = function(){
		var mitivalidacao = MitiElemento.getClass('mitivalidacao')[0];
		
		if(mitivalidacao){
			mitivalidacao.onsubmit = function(){
				var vazios = MitiElemento.getClass('mitivazio');
				
				for(var i = 0; i < vazios.length; i++){
					if(!vazios[i].value){
						alert('Valor vazio');
						vazios[i].style.borderColor = 'red';
						vazios[i].focus();
						return false;
					}else{
						vazios[i].style.borderColor = 'blue';
					}
				}
			};
		}
	};
}
