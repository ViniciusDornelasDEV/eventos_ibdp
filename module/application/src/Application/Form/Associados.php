<?php

 namespace Application\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Associados extends BaseForm {
     
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

        //uf
        $serviceEstadoCivil = $this->getServiceLocator()->get('EstadoCivil');
        $estados = $this->getServiceLocator()->get('Estado')->fetchAll(array('id', 'nome'))->toArray();
        $estados = $serviceEstadoCivil->prepareForDropDown($estados, array('id', 'nome'));
        $this->_addDropdown('estado', 'Estado: ', false, $estados, 'CarregaCidade(this.value, "false");');

        //cidade
        $this->_addDropdown('cidade', 'Cidade: ', false, array('' => '-- Escolha o estado --'));

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

    public function setCidadesByEstado($estado){
        //buscar cidade
        $serviceCidade = $this->serviceLocator->get('Cidade');
        $cidades = $serviceCidade->getRecords($estado, 'estado');
        $cidades = $serviceCidade->prepareForDropDown($cidades, array('id', 'nome'));
        //Setando valores
        $cidades = $this->get('cidade')->setAttribute('options', $cidades);
        
        return $cidades;      
    }

    public function setData($data){
        if(isset($data['estado'])){
            $this->setCidadesByEstado($data['estado']);
        }

        if(isset($data['cidade']) && !isset($data['estado'])){
            //find estado by cidade
            $cidade = $this->serviceLocator->get('Cidade')->getRecord($data['cidade']);
            $data['estado'] = $cidade['estado'];
            $this->setCidadesByEstado($data['estado']);
        }
        
        parent::setData($data);
    }

 }
