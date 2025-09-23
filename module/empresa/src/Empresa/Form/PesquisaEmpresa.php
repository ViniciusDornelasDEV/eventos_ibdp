<?php

 namespace Empresa\Form;
 
 use Application\Form\Base as BaseForm; 
 use Application\Validator\Cnpj;
 
 class PesquisaEmpresa extends BaseForm {
     
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
        
        $this->genericTextInput('nome_fantasia', 'Nome:', false, 'Nome fantasia');

        $this->textInputCnpj('cnpj', 'Cnpj:', false, 'CNPJ');
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

        $this->getInputFilter()->get('cnpj')->getValidatorChain()->addValidator(new Cnpj());
    }

 }
