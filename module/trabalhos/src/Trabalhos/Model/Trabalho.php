<?php
namespace Trabalhos\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;

class Trabalho Extends BaseTable {

    public function salvarTrabalho($trabalho, $arquivos, $idInscricao){
      $adapter = $this->getTableGateway()->getAdapter();
      $connection = $adapter->getDriver()->getConnection();
      $connection->beginTransaction();
      try {
        $trabalho['inscricao'] = $idInscricao;
        $trabalho['resumo'] = htmlspecialchars($trabalho['resumo']);
        $trabalho['comite_etica'] = $arquivos[0]['arquivo'];
        $trabalho['stricto_sensu'] = $arquivos[1]['arquivo'];
        $trabalho['mestrado_doutorado'] = $arquivos[2]['arquivo'];

        $idTrabalho = parent::insert($trabalho); 

        $tbIntegrantesTrabalho =  new TableGateway('tb_evento_trabalho_integrante', $adapter); 
        for ($i=1; $i <= 5; $i++) { 
          if (!empty($trabalho["nome$i"])){
            $tbIntegrantesTrabalho->insert(array(
              'trabalho' => $idTrabalho,
              'nome'     => $trabalho["nome$i"],
              'cpf'      => $trabalho["cpf$i"]
            ));
          }
        }
        $connection->commit();
        return true;
      } catch (Exception $e) {
        $connection->rollback();
        return false;
      }
    }

    public function deletarTrabalho($idInscricao){
      $adapter = $this->getTableGateway()->getAdapter();
      $connection = $adapter->getDriver()->getConnection();
      $connection->beginTransaction();
        try {
          $trabalho = parent::getRecord($idInscricao, 'inscricao');
          
          $tbArquivos = new TableGateway('tb_evento_trabalho_pdf', $adapter); 
          $tbArquivos->delete(array('trabalho' => $trabalho['id']));
          
          $tbIntegrantes = new TableGateway('tb_evento_trabalho_integrante', $adapter); 
          $tbIntegrantes->delete(array('trabalho' => $trabalho['id']));

          $tbEventoAvaliador = new TableGateway('tb_evento_trabalho_avaliador', $adapter); 
          $tbEventoAvaliador->delete(array('avaliacao' => $trabalho['id']));

          //deletar o trabalho 
          parent::delete(array('id' => $trabalho['id']));

          $connection->commit();
          return true;
        } catch (Exception $e) {
          $connection->rollback();
          return false;
        }
      return false;
    }

    public function getTrabalho($idTrabalho){
      
        return $this->getTableGateway()->select(function($select) use ($idTrabalho) {
            $select->join(
                    array('etc' => 'tb_evento_trabalho_categoria'),
                    'tb_evento_trabalho.categoria = etc.id',
                    array('nome_categoria' => 'categoria'),
                    'INNER'
                );

            $select->join(
                    array('e' => 'tb_evento'),
                    'e.id = etc.evento',
                    array('id_evento' => 'id', 'sigla_evento' => 'sigla', 'nome_evento' => 'nome', 'banner_evento'),
                    'INNER'
                );

            $select->where(array('tb_evento_trabalho.id' => $idTrabalho, 'tb_evento_trabalho.aprovado' => 'S'));
        })->current();
    }
}
