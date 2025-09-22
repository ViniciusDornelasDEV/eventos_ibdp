<?php

 namespace Suporte\Form;
 
 use Application\Form\Base as BaseForm; 
 use Application\Validator\Cpf;
 
 class Checkin extends BaseForm {
     
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
        
        $this->textInputCpf('cpf', 'CPF: ', false, '', 'form-control input-lg');
        
        $this->genericTextInput('nome_completo', 'Nome completo:', false);

        $this->addHiddenInput('idCliente', false, array('id' => 'idCliente'));
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

        $this->getInputFilter()->get('cpf')->getValidatorChain()->addValidator(new Cpf());
    }

 }
