<?php
namespace Evento\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;

class Trabalho Extends BaseTable {

    public function salvarTrabalho($trabalho, $arquivos, $idInscricao){
      $adapter = $this->getTableGateway()->getAdapter();
      $connection = $adapter->getDriver()->getConnection();
      $connection->beginTransaction();
        try {
          //salvar o trabalho
          $trabalho['inscricao'] = $idInscricao;
          $trabalho['resumo'] = htmlspecialchars($trabalho['resumo']);
          $idTrabalho = parent::insert($trabalho);

          //salvar os arquivos
          $tbArquivos =  new TableGateway('tb_evento_trabalho_pdf', $adapter); 
          foreach ($arquivos as $arquivo) {
            $tbArquivos->insert(array(
              'trabalho'  => $idTrabalho,
              'nome'      => $arquivo['nome'],
              'arquivo'   => $arquivo['arquivo']
            ));
          }
          
          $connection->commit();
          return true;
        } catch (Exception $e) {
          $connection->rollback();
          return false;
        }
      return false;
    }

    public function deletarTrabalho($idInscricao){
      $adapter = $this->getTableGateway()->getAdapter();
      $connection = $adapter->getDriver()->getConnection();
      $connection->beginTransaction();
        try {
          //pesquisr o trabalho
          $trabalho = parent::getRecord($idInscricao, 'inscricao');

          //deletar os arquivos
          $tbArquivos =  new TableGateway('tb_evento_trabalho_pdf', $adapter); 
          $tbArquivos->delete(array('trabalho' => $trabalho['id']));

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
