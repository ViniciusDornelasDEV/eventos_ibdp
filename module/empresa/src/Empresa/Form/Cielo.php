<?php

 namespace Empresa\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Cielo extends BaseForm {
     
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
        
        $this->genericTextInput('merchant_id', ' Merchant id:', true, '00000000-0000-0000-0000-000000000000');
                
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
