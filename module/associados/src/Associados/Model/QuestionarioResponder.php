<?php
namespace Associados\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;

class QuestionarioResponder Extends BaseTable {
    public function responderQuestionario($dados, $arrayInsert){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        
        try {
            $serviceQuestionarioAssociado = new TableGateway('tb_questionario_associado', $adapter);
            $serviceQuestionarioAssociado->insert(array(
                'associado'     =>  $arrayInsert['associado'],
                'questionario'  =>  $arrayInsert['questionario']
            ));
            $arrayInsert['questionario'] = $serviceQuestionarioAssociado->getLastInsertValue();

            foreach ($dados as $key => $value) {
                if(strpos($key, 'text') !== false){
                    echo $value;
                    if(!empty($value)){
                        $idAlternativa = str_replace('text_', '', $key);
                        $arrayInsert['alternativa'] = $idAlternativa;
                        $arrayInsert['resposta_aberta'] = $value;
                        parent::insert($arrayInsert);
                        unset($arrayInsert['resposta_aberta']);
                    }
                }else{
                    $arrayInsert['alternativa'] = $value;
                    parent::insert($arrayInsert);
                }
            }

            $connection->commit();
            return true;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
        return false;
    }

    public function getRespostasQuestionario($idQuestionario){
        return $this->getTableGateway()->select(function($select) use ($idQuestionario) {
            $select->join(
                    array('qa' => 'tb_questionario_associado'),
                    'qa.id = tb_questionario_alternativa_associado.questionario', 
                    array('id_resposta' => 'id', 'id_questionario' => 'questionario'),
                    'INNER'
                );

            $select->join(
                    array('qqa' => 'tb_questionario_questao_alternativa'),
                    'qqa.id = tb_questionario_alternativa_associado.alternativa', 
                    array('tipo_questao' => 'tipo', 'id_questao' => 'questao'),
                    'INNER'
                );

            
            $select->where(array('qa.id' => $idQuestionario));

        });
    }

}
