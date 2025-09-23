<?php
namespace Associados\Model;
use Application\Model\BaseTable;
use Zend\Db\Sql\Expression;

class Alternativa Extends BaseTable {

    public function getRespostasByQuestao($questao){
        return $this->getTableGateway()->select(function($select) use ($questao) {

            $select->join(
                    array('qa' => 'tb_questionario_alternativa_associado'),
                    'qa.alternativa = tb_questionario_questao_alternativa.id',
                    array('quantidade' => new Expression('COUNT(qa.alternativa)')),
                    'left'
                );

            $select->where(array('tb_questionario_questao_alternativa.questao' => $questao, 'tb_questionario_questao_alternativa.tipo' => 'F'));
            $select->group('qa.alternativa');
            $select->order('tb_questionario_questao_alternativa.ordem');
        });
    }

    public function getRespostasByQuestionario($questionario){
        return $this->getTableGateway()->select(function($select) use ($questionario) {

            $select->join(
                    array('qq' => 'tb_questionario_questao'),
                    'qq.id = tb_questionario_questao_alternativa.questao',
                    array('enunciado')
                );

            $select->join(
                    array('qa' => 'tb_questionario_alternativa_associado'),
                    'qa.alternativa = tb_questionario_questao_alternativa.id',
                    array('quantidade' => new Expression('COUNT(qa.alternativa)')),
                    'left'
                );

            $select->where(array('qq.questionario' => $questionario, 'tb_questionario_questao_alternativa.tipo' => 'F'));
            $select->group('qa.alternativa');
            $select->order('qq.ordem, tb_questionario_questao_alternativa.ordem');
        });
    }
}
