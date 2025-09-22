<?php

namespace Application\Model;

use Zend\Db\Metadata\Metadata;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Predicate;
use Zend\ServiceManager\ServiceManager;

/**
 * This is base table object inherited by all child table objects.
 * Say hello and make your introductions. 
 *
 * @author  Vinicius Silva <vinicius.s.dornelas@gmail.com>
 * @version 1.0
 */
class BaseTable {

    /**
     * Table Gateway.
     * 
     * @var mixed
     * @access private
     */
    public $tableGateway;

    /**
     * Service Locator.
     * 
     * @var mixed
     * @access private
     */
    private $serviceLocator;


    /**
     * __construct function.
     * 
     * @access public
     * @param TableGateway $tableGateway
     * @return void
     */
    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }


    /**
     * setServiceLocator function.
     * 
     * @access public
     * @param ServiceManager $serviceLocator
     * @return void
     */
    public function setServiceLocator(ServiceManager $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * getServiceLocator function.
     * 
     * @access public
     * @return void
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }
    
    public function getIdentity($property = null) {
        $storage = $this->getServiceLocator()->get('session');
        
        if (!$storage) {
            return false;
        }
        
        $data = $storage->read();
                        
        if ($property && isset($data[$property])) {
            return $data[$property];
        }
        
        return $data;
    }

    /**
     * getTableGateway function.
     * 
     * @access public
     * @return void
     */
    public function getTableGateway() {
        return $this->tableGateway;
    }

    /**
     * Fetch all records in this table.
     * 
     * @access public
     * @return void
     */
    public function fetchAll($fields = array('*')) {
        return $this->getTableGateway()->select(function($select) use ($fields) {
            $select->columns($fields);
        });

        /*$resultSet = $this->getTableGateway()->select();
        $resultSet->columns(array('id', 'uf'));*/
        return $resultSet;
    }

    public function prepareForDropDown($data, array $campos, $concatenar = false, $preparedArray = array('' => '-- selecione --')){
        if($data){
            if($concatenar){
                foreach ($data as $record) {
                    $preparedArray[$record[$campos[0]]] = $record[$campos[1]].$concatenar.$record[$campos[2]];
                }
            }else{
                foreach ($data as $record) {
                    $preparedArray[$record[$campos[0]]] = $record[$campos[1]];
                }
            }
        }

        return $preparedArray;
    }

    public function getNextInsertId($table) {
         $dbName = $this->getTableGateway()->getAdapter()->getDriver()->getConnection()->getCurrentSchema();
        $sql = "SHOW TABLE STATUS FROM $dbName LIKE '".$table."'" ;
        $rowset = $this->getTableGateway()->getAdapter()->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        if(!$row = $rowset->current()){
            return false;
        }

        return $row;
    }

    /**
     * Get an individual record based on an ID.
     * You also have the option for searching a particular column.
     * 
     * @access public
     * @param mixed $value
     * @param string $column (default: 'id')
     * @return void
     */
    public function getRecord($value, $column = 'id') {
        $rowset = $this->getTableGateway()->select(array($column => $value));
        if (!$row = $rowset->current()) {
            return false;
        }

        return $row;
    }

    /**
     * Gets a record set based on an ID.
     * You also have the option for searching a particular column.
     * 
     * @access public
     * @param mixed $value
     * @param string $column (default: 'id')
     * @return void
     */
    public function getRecords($value, $column = 'id', $fields = array('*'), $orderByString = false) {

        $rowset = $this->getTableGateway()->select(function($select) use ($fields, $value, $column, $orderByString) {
            $select->columns($fields);
            $select->where(array($column => $value));
            if ($orderByString) {
                    $select->order($orderByString);
            }
        });
        
        return $rowset;
    }

    /**
     * Format the obfuscated value into a valid
     * ORDER BY value.
     * 
     * @access public
     * @param mixed $orderByString (default: null)
     * @return void
     */
    public function formatOrderBy($orderByString = null) {
        if (!$orderByString) {
            return false;
        }
        $array = explode('__', $orderByString);
        return $array[0] . ' ' . strtoupper($array[1]);
    }

    /**
     * Get a result set based on criteria array.
     * 
     * @access 	public
     * @param 	array $where (default: array())
     * @return 	void
     */
    public function getRecordsFromArray(array $where = array(), $orderByString = false, $fields = array('*')) {
        $self = $this;
        $records = $this->getTableGateway()->select(function(Select $select) use ($where, $orderByString, $self, $fields) {
            $select->columns($fields);
            if (!empty($where))
                $select->where($where);

            if ($orderByString) {
                $select->order($orderByString);
            }
        });

        return $records;
    }
    

    /**
     * Get result set based on like operators.
     * 
     * @access public
     * @param array $criteria
     * @return void
     */
    public function getRecordsFromLike(array $criteria) {
        return $this->getTableGateway()->select(function(Select $select) use ($criteria) {

                    $predicates = array();

                    foreach ($criteria as $column => $value) {
                        $predicate = new Predicate\Predicate;
                        $predicates[] = $predicate->like($column, $value);
                    }

                    if (count($predicates)) {
                        $select->where(array(
                            new Predicate\PredicateSet($predicates)
                        ));
                    }
                });
    }

    /**
     * Get an individual record based on where array.
     * 
     * @access public
     * @param array $where (default: array())
     * @return void
     */
    public function getRecordFromArray(array $where = array()) {
        $rowset = $this->getTableGateway()->select($where);

        if (!$row = $rowset->current()) {
            return false;
        } 

        return $row;
    }

    /**
     * Save a record.
     * 
     * @access public
     * @return int
     */
    public function save($record) {
        $id = isset($record['id']) ? (int) $record['id'] : false;
        $data = $this->sanitizeObjectForEntry($record);

        if (count($data)) {
            if (!$id) {
                $this->getTableGateway()->insert($data);
                return $this->getTableGateway()->getLastInsertValue();
            } else {

                $res = $this->getTableGateway()->update($data, array('id' => $id));
                
                return $res;
            }
        }
    }
    
    
    /**
     * Updates a record.
     * 
     * @access public
     * @return int
     */
    public function update($data, array $where) {
        $data = $this->sanitizeObjectForEntry($data);       
        $res = $this->getTableGateway()->update($data, $where);               
        return $res;
        
    }
    

    /**
     * Save a record for a logged in user.
     * 
     * @access public
     * @return int
     */
    public function saveForCurrentUser($record) {          

        $user = $this->getIdentity();
                
        if(!$user)
            throw new \Exception('User not logged in');

        $record['user'] = $user['id'];
                        
        return $this->save($record);

    }
    
    /**
     * Get a record for current user
     * 
     * @access public
     * @return int
     */
    public function getRecordForCurrentUser($record_id) {          

        $user = $this->getIdentity();
        
        if(!$user)
            throw new \Exception('User not logged in');
        
        $res = $this->getRecordFromArray(array(
            'user'=>$user['id'],
            'id'=>$record_id,
        ));
        
        return $res;

    }
    
    /**
     * Get a result set based on criteria array, for the current user.
     * 
     * @access 	public
     * @param 	array $where (default: array())
     * @return 	void
     */
    
    public function getRecordsForCurrentUser(array $params = array (), $orderByString = null) {          

        $user = $this->getIdentity();
        
        if(!$user)
            throw new \Exception('User not logged in');        
        
        $params['user'] = $user['id'];
        
        $res = $this->getRecordsFromArray($params, $orderByString);
        
        return $res;

    }

    /**
     * Save a record.
     * 
     * @access public
     * @return int
     */
    public function insert($record) {

        $data = $this->sanitizeObjectForEntry($record);

        if (count($data)) {
            $this->getTableGateway()->insert($data);
            return $this->getTableGateway()->getLastInsertValue();
        }
    }

    /**
     * Delete a record. The $whereArray is VERY important here!
     * 
     * @access public
     * @param mixed $id
     * @return void
     */
    public function delete(array $whereArray = array()) {
       return $this->getTableGateway()->delete($whereArray);
    }

    public function sanitizeObjectForEntry($object) {
        $metadata = new Metadata($this->getTableGateway()->getAdapter());
        $tableColumns = array_flip(@$metadata->getColumnNames($this->getTableGateway()->getTable()));
        foreach ($object as $key => $val) {
            if (!array_key_exists($key, $tableColumns)) {
                unset($object[$key]);
            } else {

                if ($object[$key] == '') {
                    $object[$key] = null;
                } else {
                    if($key != 'codigo_embed'){
                        $object[$key] = strip_tags($object[$key]);
                    }
                    $minusculo = array('senha', 'arquivo', 'banner_evento', 'certificado_1', 'certificado_2', 'certificado_3', 'mensagem_cadastro_novo', 'mensagem_cadastro_antigo', 'mensagem_pagamento', 'mensagem_transferencia', 'observacoes', 
                        'descricao_categoria', 'resumo', 'mensagem', 'dados_deposito', 'codigo_embed', 'comprovante', 'descricao', 'certificado');
                    if(!in_array($key, $minusculo)){
                        $object[$key] = mb_convert_case($object[$key], MB_CASE_UPPER, 'UTF-8');
                        
                    }
                }
            }
        }

        return $object;
    }

    public function eliminaMascaras($value){
        $value = preg_replace('[^0-9]', '', $value);
        $value = str_pad($value, 11, '0', STR_PAD_LEFT);
        $value = str_replace(".", "", $value);
        $value = str_replace("-", "", $value);
        return $value;
    }

    function mask($val, $mask)
    {
     $maskared = '';
     $k = 0;
     for($i = 0; $i<=strlen($mask)-1; $i++)
     {
     if($mask[$i] == '#')
     {
     if(isset($val[$k]))
     $maskared .= $val[$k++];
     }
     else
     {
     if(isset($mask[$i]))
     $maskared .= $mask[$i];
     }
     }
     return $maskared;
    }


}
