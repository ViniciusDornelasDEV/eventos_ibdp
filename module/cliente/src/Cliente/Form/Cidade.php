<?php

 namespace Cliente\Form;
 
 use Application\Form\Base as BaseForm; 

 class Cidade extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $required = false)
    {
        
        $this->setServiceLocator($serviceLocator);
        
        parent::__construct($name);


        //uf
        $serviceEstado = $this->getServiceLocator()->get('Estado');
        $estados = $serviceEstado->fetchAll(array('id', 'uf'))->toArray();
        $estados = $serviceEstado->prepareForDropDown($estados, array('id', 'uf'));
        
        if($required){
            $prefixo = '* ';
        }else{
            $prefixo = '';
        }
        $this->_addDropdown('estado', $prefixo.'Estado:', $required, $estados, 'CarregaCidade(this.value);');
        
        //cidade
        $this->_addDropdown('cidade', $prefixo.' Cidade:', $required, array('' => '-- Selecione --'));
         
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

    public function setCidadesByEstado($estado){
        //buscar cidade
        $serviceCidade = $this->serviceLocator->get('Cidade');
        $cidades = $serviceCidade->getRecords($estado, 'estado', array('*'), 'ordem, nome');
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
            $data['estado'] = $cidade->estado;
            $this->setCidadesByEstado($data['estado']);
        }

        $data->data_nascimento = parent::converterData($data->data_nascimento);
        
        parent::setData($data);
    }
 }
