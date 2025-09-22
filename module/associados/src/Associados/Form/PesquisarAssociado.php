<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class PesquisarAssociado extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $empresa = false)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        
        if(!$empresa){
            $serviceEmpresa = $this->serviceLocator->get('Empresa');
            $empresas = $serviceEmpresa->getRecords('S', 'ativo', array('id', 'nome_fantasia'), 'nome_fantasia')->toArray();
            $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_fantasia'));
            $this->_addDropdown('empresa', 'Empresa:', false, $empresas);
        }

        $this->genericTextInput('nome_completo', 'Nome completo:', false, '');

        $this->genericTextInput('cpf', 'CPF:', false, '');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
