<?php
namespace Evento\Model;
use Application\Model\BaseTable;

class InscricaoCamposEvento Extends BaseTable {
    public function camposDefaultByEvento($idEvento, array $campos){
       $connection = $this->getTableGateway()->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try{
            foreach ($campos as $campo) {
                $dados = array(
                            'evento'            => $idEvento,
                            'inscricao_campos'  => $campo['id'],
                            'label'             => $campo['label']
                        );
                if($campo['id'] == 1){
                    $dados['obrigatorio'] = 'S';
                }    

                $this->insert($dados);
            }
        }catch(Exception $e){    
            $connection->rollback();
            return false;
        }

        $connection->commit();
                    
        return true;
    }

    public function getCamposByEvento($idEvento){
        return $this->getTableGateway()->select(function($select) use ($idEvento) {
            $select->columns(array('aparecer', 'label_campo' => 'label', 'obrigatorio', 'id_campo_evento' => 'id'));
            $select->join(
                    array('c' => 'tb_inscricao_campos'),
                    'c.id = inscricao_campos',
                    array('nome_campo'),
                    'inner'
                );
            $select->where(array('evento' => $idEvento));
        }); 
    } 

}
