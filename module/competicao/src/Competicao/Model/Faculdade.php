<?php
namespace Competicao\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;

class Faculdade Extends BaseTable {
    public function getFaculdades($params){
      return $this->getTableGateway()->select(function($select) use ($params) {

        if(isset($params['nome']) && !empty($params['nome'])){
          $select->where->like('nome', '%'.$params['nome'].'%');
        }

        if(isset($params['ativo']) && !empty($params['ativo'])){
          $select->where(array('ativo' => $params['ativo']));
        }

        $select->order('numero');
      });
    }

    public function importarFaculdades($objPHPExcel){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
          $sheet = $objPHPExcel->getSheet(0); 
          $highestRow = $sheet->getHighestRow(); 
          $tbOrador = new TableGateway('tb_competicao_orador', $adapter);
          
          $retorno = array(
            'faculdades'  => 0,
            'oradores'    => 0,
            'status'    => false
          );
          $idFaculdade = false;
          for ($row = 1; $row <= $highestRow; $row++){
            $rowData = $sheet->rangeToArray('A'.$row.':'.'N'.$row,
                                            NULL,
                                            true,
                                            true,
                                            false);
            $rowData = $rowData[0];
            //faculdade
            if(!empty($rowData[0])){
              $retorno['faculdades']++;
              $idFaculdade = parent::insert(array('nome' => $rowData[0]));
            }else{
              //orador
              if(!empty($rowData[1]) && !empty(!empty($rowData[2]))){
                $retorno['oradores']++;
                $tbOrador->insert(array(
                  'faculdade'   =>  $idFaculdade,
                  'nome'        =>  $rowData[1],
                  'email'       =>  $rowData[2]
                ));
              }
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
