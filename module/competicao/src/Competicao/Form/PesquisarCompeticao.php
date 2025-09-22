<?php

 namespace Competicao\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class PesquisarCompeticao extends BaseForm {
     
   public function __construct($name)
    {

        parent::__construct($name);      
        
        $this->genericTextInput('nome', 'Nome da competição: ', false);

        $this->_addDropdown('ativo', 'Status:', false, array('S' => 'Ativa', 'N' => 'Inativa'));      

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
