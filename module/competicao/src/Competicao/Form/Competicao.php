<?php

 namespace Competicao\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Competicao extends BaseForm {
     
   public function __construct($name)
    {

        parent::__construct($name);      
        
        //nome
        $this->genericTextInput('nome', '* Nome: ', true);

        //descricao
        $this->genericTextArea('descricao', '* Descrição: ', true);

        //início
        $this->genericTextInput('inicio', '* Início: ', true);

        //fim
        $this->genericTextInput('fim', '* Fim: ', false);

        //ativo
        $this->_addDropdown('ativo', '* Status: ', true, array('N' => 'Inativa', 'S' => 'Ativa'));      

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

     public function setData($data){
        $data->inicio = parent::converterData($data->inicio);
        $data->fim = parent::converterData($data->fim);
        $data->descricao = html_entity_decode($data->descricao);
        parent::setData($data);
    }

 }
