<?php
namespace Cliente\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\Crypt\Password\Bcrypt;

class Cliente Extends BaseTable {

    public function insert($dados){
        $adapter = $this->tableGateway->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            $idCliente = parent::insert($dados);

            if(isset($dados['sabendo_aberta'])){
                $tbFicouSabendo = new TableGateway('tb_cliente_ficou_sabendo', $adapter);
                foreach ($dados['sabendo_aberta'] as $sabendo) {
                    //inserir cliente, como ficou sabendo
                    $dados = array(
                            'cliente'           =>  $idCliente,
                            'ficou_sabendo'     =>  $sabendo
                        );
                    $tbFicouSabendo->insert($dados);
                }
            }

            $connection->commit();
            return $idCliente;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
        $connection->rollback();
        return false;

    }

    public function update($dados, array $where){
        $adapter = $this->tableGateway->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            parent::update($dados, $where);

            if(isset($dados['senha']) && !empty($dados['senha'])){
                $bcrypt = new bcrypt();
                $dados['senha'] = $bcrypt->create($dados['senha']);
                $tbUsuario = new TableGateway('tb_usuario', $adapter);
                $tbUsuario->update(array('senha' => $dados['senha']), array('cliente' => $where['id']));
            }

            if(isset($dados['sabendo_aberta'])){
                $tbFicouSabendo = new TableGateway('tb_cliente_ficou_sabendo', $adapter);
                $tbFicouSabendo->delete(array('cliente' => $where['id']));
                foreach ($dados['sabendo_aberta'] as $sabendo) {
                    //inserir cliente, como ficou sabendo
                    $dados = array(
                            'cliente'           =>  $where['id'],
                            'ficou_sabendo'     =>  $sabendo
                        );
                    $tbFicouSabendo->insert($dados);
                }
            }

            $connection->commit();
            return true;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
        $connection->rollback();
        return false;
    }


    public function checkin($inscricao, $dados, $cliente){
        $adapter = $this->tableGateway->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            if(isset($dados['cpf'])){
                if($dados['cpf'] == $cliente['cpf']){
                    unset($dados['cpf']);
                }
            }
            parent::update($dados, array('id' => $inscricao->cliente));

            $tbInscricao = new TableGateway('tb_inscricao', $adapter);
            $tbInscricao->update(array('compareceu' => 'S'), array('id' => $inscricao->id));
            $connection->commit();
            return true;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
        $connection->rollback();
        return false;
    }

    public function getClienteByCpf($cpf){
        return $this->getTableGateway()->select(function($select) use ($cpf) {

            $select->join(
                    array('c' => 'tb_cidade'),
                    'c.id = cidade',
                    array('nome_cidade' => 'nome', 'estado'),
                    'LEFT'
                );

            $select->join(
                    array('e' => 'tb_estado'),
                    'e.id = c.estado',
                    array('nome_estado' => 'nome'),
                    'LEFT'
                );

            $select->where(array('cpf' => $cpf));
        })->current();
    }

    public function getClienteById($id){
        return $this->getTableGateway()->select(function($select) use ($id) {

            $select->join(
                    array('c' => 'tb_cidade'),
                    'c.id = cidade',
                    array('nome_cidade' => 'nome', 'estado'),
                    'LEFT'
                );

            $select->join(
                    array('e' => 'tb_estado'),
                    'e.id = c.estado',
                    array('nome_estado' => 'nome'),
                    'LEFT'
                );

            $select->where(array('tb_cliente.id' => $id));
        })->current();
    }
}

?>