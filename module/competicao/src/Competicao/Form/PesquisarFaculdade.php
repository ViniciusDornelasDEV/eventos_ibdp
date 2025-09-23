<?php

 namespace Competicao\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class PesquisarFaculdade extends BaseForm {
     
   public function __construct($name)
    {

        parent::__construct($name);      
        
        $this->genericTextInput('nome', 'Nome da faculdade: ', false);

        $this->_addDropdown('ativo', 'Status:', false, array('S' => 'Ativo', 'N' => 'Inativo'));      

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
