<?php

 namespace Competicao\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Faculdade extends BaseForm {
     
   public function __construct($name)
    {

        parent::__construct($name);      
        
        $this->genericIntegerInput('numero', '* Nº: ', true);

        $this->genericTextInput('nome', '* Nome: ', true);

        $this->_addDropdown('ativo', '* Status: ', true, array('S' => 'Ativo', 'N' => 'Inativo'));      

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
