<?php
namespace Miti;

class SQL
{
    private $config;
    private $banco;
    private $sql = [];
    private $alias;
    private $tabela;
    private $campos;
    private $pk;
    private $tipos = [];
    private $tamanhos = [];
    private $anulaveis = [];
    private $selecoes = '';
    private $juncoes = '';
    private $filtros = '';
    private $grupos = '';
    private $ordens = '';
    private $limite;

    public function __construct(array $config, $tabela, $alias)
    {
        $this->config = $config;
        $this->banco = new Banco($config);
        $this->alias = $alias;
        $this->tabela = $tabela;

        $this->mapearCampos();
    }

    public function setBanco(Banco $banco)
    {
        $this->banco = $banco;
        return $this;
    }

    public function getBanco()
    {
        return $this->banco;
    }

    private function mapearCampos()
    {
        $this->campos = $this->banco->requisitar("select * from $this->tabela")->mapear();
        $this->setPk()->setTipos()->setAnulaveis()->setTamanhos();
    }

    private function setPk()
    {
        foreach ($this->campos as $campo) {
            if ($campo->flags & 2) {
                $this->pk = $campo->orgname;
                break;
            }
        }

        return $this;
    }

    public function getPk()
    {
        return $this->pk;
    }

    private function setTipos()
    {
        foreach ($this->campos as $campo) {
            if ($campo->flags & 32768) {
                $this->tipos[$campo->orgname] = 'float';
            } else {
                $this->tipos[$campo->orgname] = 'string';
            }
        }

        return $this;
    }

    public function getTipos()
    {
        return $this->tipos;
    }

    private function setAnulaveis()
    {
        foreach ($this->campos as $campo) {
            if ($campo->flags & 1) {
                $this->anulaveis[$campo->orgname] = false;
            } else {
                $this->anulaveis[$campo->orgname] = true;
            }
        }

        return $this;
    }

    public function getAnulaveis()
    {
        return $this->anulaveis;
    }

    private function setTamanhos()
    {
        foreach ($this->campos as $campo) {
            if ($this->tipos[$campo->orgname] === 'string') {
                $this->tamanhos[$campo->orgname] = $campo->length / 3;
            } else {
                $this->tamanhos[$campo->orgname] = $campo->length;
            }
        }

        return $this;
    }

    public function getTamanhos()
    {
        return $this->tamanhos;
    }

    public function criar(array $tupla)
    {
        $sql = '';
        $sql = $this->montarCampos($sql, $tupla);
        $sql = $this->montarValores($sql, $tupla);
        return $this->banco->requisitar($sql);
    }

    private function montarCampos($sql, array $tupla)
    {
        $sql = "insert into $this->tabela (";

        foreach ($tupla as $campo => $valor) {
            $campos[] = $campo;
        }

        return $sql . implode(', ', $campos) . ')';
    }

    private function montarValores($sql, array $tupla)
    {
        $this->validar($tupla);
        $tupla = $this->tratar($tupla);

        $sql .= ' values (';

        foreach ($tupla as $valor) {
            $values[] = $valor;
        }

        return $sql . implode(', ', $values) . ')';
    }

    public function atualizar(array $tupla)
    {
        $sql = $this->montarAtribuicoes($tupla).' where '.$this->filtros;
        return $this->banco->requisitar($sql);
    }

    private function montarAtribuicoes(array $tupla)
    {
        $this->validar($tupla);
        $tupla = $this->tratar($tupla);

        $sql = "update $this->tabela $this->alias set ";

        foreach ($tupla as $campo => $valor) {
            $atribuicoes[] = "$campo = $valor";
        }

        return $sql . implode(', ', $atribuicoes);
    }

    private function validar(array $tupla)
    {
        foreach ($tupla as $campo => $valor) {
            if (!$this->anulaveis[$campo] && empty($valor)) {
                throw new \UnexpectedValueException("Valor vazio para o campo '$campo'");
            }

            if (strlen($valor) > $this->tamanhos[$campo]) {
                throw new \UnexpectedValueException("Limite de caractéres excedido para o campo '$campo'");
            }
        }
    }

    public function deletar()
    {
        $sql = "delete $this->alias from $this->tabela $this->alias where $this->filtros";
        return $this->banco->requisitar($sql);
    }

    private function tratar(array $tupla)
    {
        foreach ($tupla as $campo => &$valor) {
            if (empty($valor)) {
                $valor = 'null';
            } else {
                if ($this->tipos[$campo] === 'string') {
                    $valor = '"' . $this->banco->escapar($valor) . '"';
                } else {
                    settype($valor, $this->tipos[$campo]);
                }
            }
        }

        return $tupla;
    }

    public function selecionar($alias, $campo, $aliasCampo = '', $funcao = '%s')
    {
        if ($this->selecoes) {
            $separador = ', ';
        } else {
            $separador = '';
        }
        
        $campo = sprintf($funcao, "$alias.$campo");
        
        if ($aliasCampo) {
            $aliasCampo = " as $aliasCampo";
        }
        
        $this->selecoes .= "$separador $campo $aliasCampo ";
        return $this;
    }

    public function juntar($externa, $alias, $aliasCampo, $campo, $aliasCampoExterna, $campoExterna, $juncao = 'join')
    {
        $this->sql[$alias] = new SQL($this->config, $externa, $alias);
        $this->juncoes .= "$juncao $externa $alias on $aliasCampo.$campo = $aliasCampoExterna.$campoExterna ";

        return $this;
    }

    public function juntarEsquerda($externa, $alias, $aliasCampo, $campo, $aliasCampoExterna, $campoExterna)
    {
        $this->juntar($externa, $alias, $aliasCampo, $campo, $aliasCampoExterna, $campoExterna, 'left join');
        return $this;
    }

    public function juntarDireita($externa, $alias, $aliasCampo, $campo, $aliasCampoExterna, $campoExterna)
    {
        $this->juntar($externa, $alias, $aliasCampo, $campo, $aliasCampoExterna, $campoExterna, 'right join');
        return $this;
    }

    public function filtrar($alias, $campo, $operador, $valor, $funcao = '%s', $separador = '')
    {
        $valor = $this->tratarLeitura($alias, $campo, $operador, $valor);
        $campo = sprintf($funcao, "$alias.$campo");
        $this->filtros .= "$separador $campo $operador $valor ";
        return $this;
    }

    public function eFiltrar($alias, $campo, $operador, $valor, $funcao = '%s')
    {
        $this->filtrar($alias, $campo, $operador, $valor, $funcao, 'and');
        return $this;
    }

    public function ouFiltrar($alias, $campo, $operador, $valor, $funcao = '%s')
    {
        $this->filtrar($alias, $campo, $operador, $valor, $funcao, 'or');
        return $this;
    }

    private function tratarLeitura($alias, $campo, $operador, $valor)
    {
        if ($alias === $this->alias) {
            $tipos = $this->tipos;
        } else {
            $tipos = $this->sql[$alias]->getTipos();
        }
        
        if ($operador === 'like') {
            $valor = "'%{$this->banco->escapar($valor)}%'";
        } elseif ($tipos[$campo] === 'string') {
            $valor = "'{$this->banco->escapar($valor)}'";
        } else {
            settype($valor, $tipos[$campo]);
        }

        return $valor;
    }

    public function agrupar($alias, $campo)
    {
        if ($this->grupos) {
            $separador = ', ';
        } else {
            $separador = '';
        }
        
        $this->grupos .= "$separador $alias.$campo ";
        return $this;
    }

    public function ordenar($alias, $campo, $ordem)
    {
        if ($this->ordens) {
            $separador = ', ';
        } else {
            $separador = '';
        }
        
        $this->ordens .= "$separador $alias.$campo $ordem ";
        return $this;
    }

    public function ordenarAleatoriamente()
    {
        $this->ordens = 'rand()';
        return $this;
    }

    public function limitar($quantidade, $inicio = '')
    {
        if (!$quantidade) {
            return $this;
        }
        
        if ($inicio) {
            $inicio .= ', ';
        }
        
        $this->limite = $inicio.$quantidade;
        return $this;
    }

    public function ler()
    {
        $this->filtros = $this->concatenarClausula('where', $this->filtros);
        $this->grupos = $this->concatenarClausula('group by', $this->grupos);
        $this->ordens = $this->concatenarClausula('order by', $this->ordens);
        $this->limite = $this->concatenarClausula('limit', $this->limite);

        $sql =
            "select $this->selecoes"
            . "from $this->tabela $this->alias "
            . $this->juncoes
            . $this->filtros
            . $this->grupos
            . $this->ordens
            . $this->limite
        ;

        return $this->banco->requisitar($sql);
    }

    private function concatenarClausula($clausula, $propriedade)
    {
        if ($propriedade && strpos($propriedade, $clausula) === false) {
            $propriedade = "$clausula $propriedade";
        }

        return $propriedade;
    }

    public function zerar()
    {
        $this->selecoes = '';
        $this->juncoes = '';
        $this->filtros = '';
        $this->grupos = '';
        $this->ordens = '';
        $this->limite = '';

        return $this;
    }
}
