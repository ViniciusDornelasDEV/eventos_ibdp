<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Promocao extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name)
    {

        parent::__construct($name);      
        
        $this->genericTextInput('codigo', '* Código promocional:', true);
        

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }


 }
