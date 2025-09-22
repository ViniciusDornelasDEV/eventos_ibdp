<?php
namespace Associados\Model;
use Application\Model\BaseTable;
use Zend\Db\Sql\Expression;

class Questionario Extends BaseTable {

    public function getQuestionarios($params = false){
        return $this->getTableGateway()->select(function($select) use ($params) {

            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = empresa',
                    array('nome_fantasia'),
                    'left'
                );

            if(isset($params['empresa'])){
                $select->where(array('empresa' => $params['empresa']));
            }

            $select->order('data_inicio DESC');
        });
    }

    public function getQuestoes($idQuestionario){
        $questoes = $this->getTableGateway()->select(function($select) use ($idQuestionario) {

            $select->join(
                array('qq' => 'tb_questionario_questao'),
                'qq.questionario = tb_questionario.id',
                array('id_questao' => 'id', 'ordem_questao' => 'ordem', 'enunciado')
            );

            $select->join(
                array('qqa' => 'tb_questionario_questao_alternativa'),
                'qqa.questao = qq.id',
                array('id_alternativa' => 'id', 'titulo_alternativa' => 'titulo', 'tipo_alternativa' => 'tipo', 'ordem_alternativa' => 'ordem'),
                'LEFT'
            );

            $select->where(array('tb_questionario.id' => $idQuestionario));
            $select->order('qq.ordem, qqa.ordem');
        });

        $idQuestao = 0;
        $preparedArray = array();
        foreach ($questoes as $questao) {
            if($idQuestao != $questao->id_questao){
                $idQuestao = $questao->id_questao;
                $preparedArray[$idQuestao] = array();
                $preparedArray[$idQuestao]['alternativas'] = array();
            }

            $preparedArray[$idQuestao]['id'] = $questao->id_questao;
            $preparedArray[$idQuestao]['ordem'] = $questao->ordem_questao;
            $preparedArray[$idQuestao]['enunciado'] = $questao->enunciado;
            $preparedArray[$idQuestao]['nome'] = $questao->nome;
            $preparedArray[$idQuestao]['descricao'] = $questao->descricao;
            
            if(!empty($questao->id_alternativa)){
                $preparedArray[$idQuestao]['alternativas'][$questao->id_alternativa] = array(
                    'id'                    =>  $questao->id_alternativa,
                    'titulo_alternativa'    =>  $questao->titulo_alternativa,
                    'tipo_alternativa'      =>  $questao->tipo_alternativa,
                    'ordem_alternativa'     =>  $questao->ordem_alternativa
                );  
            }
        }
        return $preparedArray;
    }

    public function getAvaliacaoAberta($associado){
        return $this->getTableGateway()->select(function($select) use ($associado) {
            $select->join(
                    array('qa' => 'tb_questionario_associado'),
                    new Expression('qa.questionario = tb_questionario.id AND qa.associado = ?', $associado->id),
                    array('id_resposta' => 'id'),
                    'LEFT'
                );

            $date = date('Y-m-d');
            $select->where('"'.$date.'" >= data_inicio');
            $select->where('"'.$date.'" <= data_fim');

            $select->where->isNull('qa.id');

            $select->where(array('empresa' => $associado->empresa));

        });
    }

    public function getRespondidosByAssociado($idAssociado){
        return $this->getTableGateway()->select(function($select) use ($idAssociado) {
            $select->join(
                    array('qa' => 'tb_questionario_associado'),
                    'qa.questionario = tb_questionario.id', 
                    array('id_resposta' => 'id'),
                    'INNER'
                );
            $select->where(array('associado' => $idAssociado));
            $select->order('data_inicio DESC');

        });
    }

    public function getRespondidasByQuestionario($idQuestionario){
        return $this->getTableGateway()->select(function($select) use ($idQuestionario) {
            $select->join(
                    array('qa' => 'tb_questionario_associado'),
                    'qa.questionario = tb_questionario.id', 
                    array('id_resposta' => 'id', 'quantidade' => new Expression('COUNT(qa.id)')),
                    'INNER'
                );

            $select->where(array('tb_questionario.id' => $idQuestionario));

        })->current();
    }
}
