<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class PagarInscricao extends BaseForm {
     
   public function __construct($name)
    {

        parent::__construct($name);      
        
        $this->_addDropdown('forma_pagamento', '* Tipo:', true, array('' => '-- Selecione --', '5' => 'PayPal', '6' => 'Cielo', '8' => 'Transferência bancária', '10' => 'Devolução', '11' => 'Empenho'));      

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
