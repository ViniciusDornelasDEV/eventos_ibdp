<?php

 namespace Empresa\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Paypal extends BaseForm {
     
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
        
        $this->genericTextInput('paypal_pwd', '* PWD:', true, '');

        $this->genericTextInput('paypal_user', '* Paypal user:', true, '');

        $this->genericTextInput('paypal_signature', '* Signature:', false, '');
                
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
