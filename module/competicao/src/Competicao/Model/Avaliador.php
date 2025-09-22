<?php
namespace Competicao\Model;
use Application\Model\BaseTable;
use Zend\Crypt\Password\Bcrypt;

class Avaliador Extends BaseTable {
    public function getAvaliadores($params){
      return $this->getTableGateway()->select(function($select) use ($params) {

        if(isset($params['nome']) && !empty($params['nome'])){
          $select->where->like('nome', '%'.$params['nome'].'%');
        }

        if(isset($params['ativo']) && !empty($params['ativo'])){
          $select->where(array('ativo' => $params['ativo']));
        }

        $select->order('nome');
      });
    }

    public function getUserData($params) {
      $rowset = $this->getTableGateway()->select(function($select) use ($params) {
          $select->join(
            array('t' => 'tb_usuario_tipo'), 
            't.id = tb_competicao_avaliador.id_usuario_tipo', 
            array('perfil')
          );
        
          $select->where($params);
          
      }); 
      if (!$row = $rowset->current()) {
          return false;
      }
      return $row;
  }

  public function importarAvaliadores($objPHPExcel){
    $adapter = $this->getTableGateway()->getAdapter();
    $connection = $adapter->getDriver()->getConnection();
    $connection->beginTransaction();
    try {
      $sheet = $objPHPExcel->getSheet(0); 
      $highestRow = $sheet->getHighestRow(); 
      
      $retorno = array(
        'avaliadores'    => 0,
        'status'    => false
      );
      $bCrypt = new Bcrypt();
      for ($row = 3; $row <= $highestRow; $row++){
        $rowData = $sheet->rangeToArray('A'.$row.':'.'N'.$row,
          NULL,
          true,
          true,
          false
        );
        $rowData = $rowData[0];
        if(!empty($rowData[1]) && !empty($rowData[3])){
          $senha = $bCrypt->create(strtoupper(trim($rowData[3])));
          parent::insert(array(
            'nome'    =>  $rowData[1],
            'email'   =>  trim($rowData[3]),
            'senha'   =>  $senha
          ));
          $retorno['avaliadores']++;
        }

      }
      $retorno['status'] = true;
      $connection->commit();
      return $retorno;
    } catch (Exception $e) {
        $connection->rollback();
        return $retorno;
    }
    return $retorno;
  }

}
