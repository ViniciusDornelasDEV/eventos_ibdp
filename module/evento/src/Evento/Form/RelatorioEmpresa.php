<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class RelatorioEmpresa extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $idEmpresa)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        
        
        //empresa
        $serviceEvento = $this->serviceLocator->get('Evento');
        $eventos = $serviceEvento->getRecords($idEmpresa, 'empresa')->toArray();

        $eventos = $serviceEvento->prepareForDropDown($eventos, array('id', 'nome'));
        $this->_addDropdown('evento', '* Evento:', true, $eventos);
        

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }
 }
