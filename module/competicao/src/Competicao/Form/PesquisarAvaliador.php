<?php

 namespace Competicao\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class PesquisarAvaliador extends BaseForm {
     
   public function __construct($name)
    {

        parent::__construct($name);      
        
        $this->genericTextInput('nome', 'Nome do avaliador: ', false);

        $this->_addDropdown('ativo', 'Status:', false, array('S' => 'Ativo', 'N' => 'Inativo'));      

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
