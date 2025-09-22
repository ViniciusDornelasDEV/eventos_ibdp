<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class PesquisaTrabalho extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $empresa)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        

        //empresa
        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->fetchAll(array('id', 'nome_fantasia'))->toArray();
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_fantasia'));
        $this->_addDropdown('empresa', 'Empresa:', false, $empresas);

        //evento
        $serviceEvento = $this->serviceLocator->get('Evento');
        if(empty($empresa)){
            $eventos = $serviceEvento->fetchAll(array('id', 'nome'))->toArray();
        }else{
            $eventos = $serviceEvento->getRecords($empresa, 'empresa', array('id', 'nome'));
        }
        $eventos = $serviceEmpresa->prepareForDropDown($eventos, array('id', 'nome'));
        $this->_addDropdown('evento', 'Evento: ', false, $eventos);        

        //status
        $this->_addDropdown('status', 'Status: ', false, array('E' => 'Enviado', 'A' => 'A enviar'));
        
        //aprovado
        $this->_addDropdown('aprovado', 'Aprovado: ', false, array('' => '-- Selecione --', 'N' => 'A aprovar', 'S' => 'Aprovado'));
        
        //nome
        $this->genericTextInput('nome', 'Nome:', false, 'Nome do inscrito');

        $this->addHiddenInput('cliente', false, array('id' => 'idCliente'));
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
