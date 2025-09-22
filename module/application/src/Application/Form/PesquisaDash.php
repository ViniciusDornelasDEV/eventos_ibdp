<?php

 namespace Application\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class PesquisaDash extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $params = array())
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        

        $serviceEvento = $this->serviceLocator->get('Evento');
        if(!isset($params['empresa'])){
            $eventos = $serviceEvento->getRecordsFromArray(array(), 'data_inicio DESC, id DESC')->toArray();
        }else{
            $eventos = $serviceEvento->getRecords($params['empresa'], 'empresa', array('id', 'nome'), 'data_inicio DESC, id DESC');
        }
        $eventos = $serviceEvento->prepareForDropDown($eventos, array('id', 'nome'));
        $this->_addDropdown('evento', 'Evento: ', false, $eventos, '', 'width: 100%;text-align: center;');            

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
