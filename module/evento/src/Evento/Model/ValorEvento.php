<?php
namespace Evento\Model;
use Application\Model\BaseTable;

class ValorEvento Extends BaseTable {
    public function getValoresByEvento($idEvento, $idValor = false){
        return $this->getTableGateway()->select(function($select) use ($idEvento, $idValor) {

            $select->join(
                    array('c' => 'tb_cliente_categoria'),
                    'c.id = evento_cliente_categoria',
                    array('nome_categoria' => 'nome', 'id_categoria' => 'id', 'apenas_associados'),
                    'inner'
                );

            $select->join(
                    array('ec' => 'tb_evento_cliente_categoria'),
                    'ec.evento = tb_evento_cliente_valor.evento AND ec.cliente_categoria = tb_evento_cliente_valor.evento_cliente_categoria',
                    array('descricao_categoria'),
                    'inner'
                );
            $select->where('ec.quantidade_maxima_inscritos > quantidade_inscritos');
            $select->where(array('tb_evento_cliente_valor.evento' => $idEvento));

            if($idValor){
                $select->where(array('tb_evento_cliente_valor.id' => $idValor));
            }

            $select->order('c.nome, data_inicio_valor');

        }); 
    }

    public function getValoresByCadEvento($idEvento, $idValor = false, $idEventoCategoria = false){
        return $this->getTableGateway()->select(function($select) use ($idEvento, $idValor, $idEventoCategoria) {
            $select->join(
                    array('c' => 'tb_cliente_categoria'),
                    'c.id = evento_cliente_categoria',
                    array('nome_categoria' => 'nome', 'id_categoria' => 'id'),
                    'inner'
                );

            $select->join(
                    array('ec' => 'tb_evento_cliente_categoria'),
                    'ec.evento = tb_evento_cliente_valor.evento AND ec.cliente_categoria = tb_evento_cliente_valor.evento_cliente_categoria',
                    array('descricao_categoria', 'quantidade_maxima_inscritos', 'quantidade_inscritos'),
                    'inner'
                );
            
            $select->where(array('tb_evento_cliente_valor.evento' => $idEvento));

            if($idValor){
                $select->where(array('tb_evento_cliente_valor.id' => $idValor));
            }

            if($idEventoCategoria){
                $select->where(array('ec.id' => $idEventoCategoria));
            }

            $select->order('c.nome, data_inicio_valor');

        }); 
    }

    public function getValoresPeriodo($dados){
        $adapter = $this->tableGateway->getAdapter();
        $sql = 'SELECT * 
        FROM tb_evento_cliente_valor 
        WHERE evento = '.$dados['evento'].' AND evento_cliente_categoria = '.$dados['evento_cliente_categoria'].' AND 
            (data_inicio_valor BETWEEN "'.$dados['data_inicio_valor'].'" AND "'.$dados['data_fim_valor'].'" OR 
            data_fim_valor BETWEEN "'.$dados['data_inicio_valor'].'" AND "'.$dados['data_fim_valor'].'");';

        $sql = str_replace('`', '', $sql);
        
        $resultSet = $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }

    public function getValoresByEventoAndDate($idEvento, $data, $idCategoria = false){
        return $this->getTableGateway()->select(function($select) use ($idEvento, $data, $idCategoria) {

            $select->join(
                    array('c' => 'tb_cliente_categoria'),
                    'c.id = evento_cliente_categoria',
                    array('nome_categoria' => 'nome', 'id_categoria' => 'id'),
                    'inner'
                );

            $select->join(
                    array('ec' => 'tb_evento_cliente_categoria'),
                    'ec.evento = tb_evento_cliente_valor.evento AND ec.cliente_categoria = tb_evento_cliente_valor.evento_cliente_categoria',
                    array('descricao_categoria', 'id_evento_categoria' => 'id'),
                    'inner'
                );

            $select->where(array('tb_evento_cliente_valor.evento' => $idEvento));
            
            $select->where('data_inicio_valor <= "'.$data.'"');
            $select->where('data_fim_valor >= "'.$data.'"');
            $select->where('ec.quantidade_maxima_inscritos > quantidade_inscritos');

            $select->order('c.nome');
            if($idCategoria){
                $select->where(array('tb_evento_cliente_valor.evento_cliente_categoria' => $idCategoria));
            }

        }); 
    }

}
