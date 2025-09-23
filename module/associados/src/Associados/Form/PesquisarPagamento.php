<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class PesquisarPagamento extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $required = false, $empresa = false)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      

        if(!$empresa){
            //empresa
            $serviceEmpresa = $this->serviceLocator->get('Empresa');
            $empresas = $serviceEmpresa->getRecords('S', 'ativo', array('id', 'nome_fantasia'), 'nome_fantasia')->toArray();
            $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_fantasia'));
            $this->_addDropdown('empresa', 'Empresa:', false, $empresas, 'carregarCategoriasAssociado(this.value, "false");');
        }

        //categoria_associado
        if($empresa){
            $serviceCategoria = $this->serviceLocator->get('CategoriaAssociado');
            $categorias = $serviceCategoria->getRecords($empresa, 'empresa');
            $categorias = $serviceCategoria->prepareForDropDown($categorias, array('id', 'nome'));
            $this->_addDropdown('categoria_associado', 'Categoria:', false, $categorias, 'carregarAnuidades(this.value, "false");');
        }else{
            $this->_addDropdown('categoria_associado', 'Categoria:', false, array('' => 'Selecione uma empresa'), 'carregarAnuidades(this.value, "false");');
        } 

        $this->_addDropdown('anuidade', 'Anuidade:', $required, array('' => 'Selecione uma categoria')); 

        $this->_addDropdown('adimplente', 'Adimplente:', false, array('' => '-- Selecione --', 'S' => 'Sim', 'N' => 'Não')); 

        $this->genericTextInput('nome_completo', 'Nome:', false, '');

        $this->genericTextInput('cpf', 'CPF:', false, '');

        $this->_addDropdown('comprovante', 'Comprovante de pagamento:', false, array('' => '-- Selecione --', 'S' => 'Sim', 'N' => 'Não')); 

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

    public function setAnuidadeByCategoria($categoria){
        //buscar cidade
        $serviceAnuidade = $this->getServiceLocator()->get('AssociadoAnuidade');
        $anuidades = $serviceAnuidade->getRecordsFromArray(array('categoria' => $categoria), 'data_pagamento');
        $anuidades = $serviceAnuidade->prepareForDropDown($anuidades, array('id', 'descricao'));
        //Setando valores
        $anuidades = $this->get('anuidade')->setAttribute('options', $anuidades);
        
        return $anuidades;      
    }

    public function setCategoriasByEmpresa($empresa){
        //buscar cidade
        $serviceCategoria = $this->serviceLocator->get('CategoriaAssociado');
        $categorias = $serviceCategoria->getRecords($empresa, 'empresa');
        $categorias = $serviceCategoria->prepareForDropDown($categorias, array('id', 'nome'));
        //Setando valores
        $categorias = $this->get('categoria_associado')->setAttribute('options', $categorias);
        
        return $categorias;      
    }

    public function setData($data){
        if(isset($data['empresa'])){
            $this->setCategoriasByEmpresa($data['empresa']);
        }

        if(isset($data['categoria_associado'])){
            $this->setAnuidadeByCategoria($data['categoria_associado']);
        }
        
        parent::setData($data);
    }

 }
