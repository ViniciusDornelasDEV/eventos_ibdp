<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Questionario extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        
        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->getRecords('S', 'ativo', array('id', 'nome_fantasia'), 'nome_fantasia')->toArray();
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_fantasia'));
        $this->_addDropdown('empresa', '* Empresa: ', true, $empresas);

        $this->genericTextInput('nome', '* Nome: ', true, '');

        $this->genericTextInput('data_inicio', '* Início: ', true, '');

        $this->genericTextInput('data_fim', '* Término: ', true, '');

        $this->genericTextArea('descricao', '* Descrição: ', true, '');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

    public function setData($data){
        $data->data_inicio = parent::converterData($data->data_inicio);
        $data->data_fim = parent::converterData($data->data_fim);
        $data->descricao = html_entity_decode($data->descricao);

        parent::setData($data);
    }

 }
