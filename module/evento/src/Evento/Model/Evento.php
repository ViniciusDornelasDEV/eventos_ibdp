<?php
namespace Evento\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;

class Evento Extends BaseTable {

    public function getEventoById($idEvento, $empresa = false){
        return $this->getTableGateway()->select(function($select) use ($idEvento, $empresa) {

            $select->join(
                    array('c' => 'tb_cidade'),
                    'c.id = cidade',
                    array('nome_cidade' => 'nome'),
                    'left'
                );

           	$select->join(
           			array('e' => 'tb_estado'),
           			'e.id = c.estado',
           			array('uf'),
           			'left'
           		);
            $select->where(array('tb_evento.id' => $idEvento));

            if($empresa){
              $select->where(array('empresa' => $empresa));
            }

        })->current();
    }

    public function replicarEvento($idEvento){
      $adapter = $this->getTableGateway()->getAdapter();
      $connection = $adapter->getDriver()->getConnection();
      $connection->beginTransaction();
        try {
          //replicar o evento
          $evento = (array)parent::getRecord($idEvento);
          unset($evento['id']);
          $idNovoEvento = parent::insert($evento);
          
          //replicar campos
          $tbCampos =  new TableGateway('tb_inscricao_campos_evento', $adapter); 
          $campos = $tbCampos->select(array('evento' => $idEvento));

          foreach ($campos as $campo) {
              $tbCampos->insert(array(
                'evento'  =>  $idNovoEvento,
                'inscricao_campos'  =>  $campo['inscricao_campos'],
                'aparecer'          =>  $campo['aparecer'],
                'obrigatorio'       =>  $campo['obrigatorio'],
                'label'             =>  $campo['label']
              ));
          }

          //replicar quantidade de inscritos e promoções de associados
          $tbPromocaoAssociado =  new TableGateway('tb_associado_promocao', $adapter); 
          $tbClienteCategoria = new TableGateway('tb_evento_cliente_categoria', $adapter);
          $quantidadeInscritos = $tbClienteCategoria->select(array('evento' => $idEvento));
          foreach ($quantidadeInscritos as $quantidade) {
            $tbClienteCategoria->insert(array(
              'evento'                      => $idNovoEvento, 
              'cliente_categoria'           => $quantidade['cliente_categoria'], 
              'quantidade_maxima_inscritos' => $quantidade['quantidade_maxima_inscritos'],
              'descricao_categoria'         => $quantidade['descricao_categoria']
            ));

            //replicar a promoção
            $idNovaCategoria = $tbClienteCategoria->getLastInsertValue();
            $categoriasPromocao = $tbPromocaoAssociado->select(array('categoria_evento' => $quantidade->id));
            foreach ($categoriasPromocao as $categoriaPromocao) {
              $tbPromocaoAssociado->insert(array(
                'categoria_associado' =>  $categoriaPromocao->categoria_associado,
                'categoria_evento'    =>  $idNovaCategoria,
                'status_associado'    =>  $categoriaPromocao->status_associado,
                'desconto'            =>  $categoriaPromocao->desconto
              ));
            }
          }

          //replicar valor da inscrição
          $tbValorInscricao = new TableGateway('tb_evento_cliente_valor', $adapter);
          $valorEvento = $tbValorInscricao->select(array('evento' => $idEvento));
          foreach ($valorEvento as $valor) {
            $tbValorInscricao->insert(array(
              'evento'  =>  $idNovoEvento,
              'evento_cliente_categoria'  => $valor->evento_cliente_categoria,
              'valor_inscricao'           =>  $valor->valor_inscricao,
              'data_inicio_valor'         => $valor->data_inicio_valor,
              'data_fim_valor'            =>  $valor->data_fim_valor
            ));
          }

          //replicar promoção comum
          $tbPromocao =  new TableGateway('tb_evento_codigo_promocional', $adapter);
          $promocoes = $tbPromocao->select(array('evento' => $idEvento));
          foreach ($promocoes as $promocao) {
            $tbPromocao->insert(array(
              'evento'                =>  $idNovoEvento,
              'codigo_promocional'    =>  $promocao->codigo_promocional,
              'desconto'              =>  $promocao->desconto,
              'quantidade_maxima'     =>  $promocao->quantidade_maxima,
              'quantidade_utilizada'  =>  0
            ));
          }

          //replicar atividades
          $tbOpcoes =  new TableGateway('tb_evento_opcao', $adapter);
          $tbAlternativas = new TableGateway('tb_evento_opcao_alternativa', $adapter);
          $opcoes = $tbOpcoes->select(array('evento' => $idEvento));
          foreach ($opcoes as $opcao) {
            $tbOpcoes->insert(array(
              'evento'  =>  $idNovoEvento,
              'titulo'  =>  $opcao->titulo,
              'data'    =>  $opcao->data
            ));
            $idOpcao = $tbOpcoes->getLastInsertValue();

            //replicar alternativas
            $alternativas = $tbAlternativas->select(array('evento_opcao' => $opcao->id));
            foreach ($alternativas as $alternativa) {
              $tbAlternativas->insert(array(
                'evento_opcao'  =>  $idOpcao,
                'atividade'     =>  $alternativa->atividade,
                'descricao'     =>  $alternativa->descricao,
                'local'         =>  $alternativa->local
              ));
            }
          }

          //replicar certificado
          $tbCertificado =  new TableGateway('tb_evento_certificado', $adapter);
          $certificados = $tbCertificado->select(array('evento' => $idEvento));
          foreach ($certificados as $certificado) {
            $tbCertificado->insert(array(
              'evento'            =>  $idNovoEvento,
              'nome_campo'        =>  $certificado->nome_campo,
              'cor'               =>  $certificado->cor,
              'posicao_x'         =>  $certificado->posicao_x,
              'posicao_y'         =>  $certificado->posicao_y,
              'tamanho_fonte'     =>  $certificado->tamanho_fonte,
              'centralizar'       =>  $certificado->centralizar,
              'maximo_caracteres' =>  $certificado->maximo_caracteres
            ));
          }


          $connection->commit();
        return $idNovoEvento;
        } catch (Exception $e) {
          $connection->rollback();
          return false;
        }
      return false;
    }
}
