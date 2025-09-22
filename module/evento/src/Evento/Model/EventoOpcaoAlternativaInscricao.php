<?php
namespace Evento\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;

class EventoOpcaoAlternativaInscricao Extends BaseTable {

    public function inserirAtividades($idInscricao, $opcoesSelecionadas){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            foreach ($opcoesSelecionadas as $opcao) {
                if(!empty($opcao)){
                    $this->insert(array(
                                    'inscricao'         =>  $idInscricao,
                                    'opcao_alternativa' =>  $opcao
                                ));
                }
            }

            $tbInscricao = new TableGateway('tb_inscricao', $adapter);
            $tbInscricao->update(array('inscricao_status' => 2), array('id' => $idInscricao));
             
            $connection->commit();
            return true;
        } catch (Exception $e) {
            $connection->rollback();
        }
        return false;
    }
}
