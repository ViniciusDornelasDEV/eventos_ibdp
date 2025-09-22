<?php

 namespace Cliente\Form;
 
 use Cliente\Form\PessoaFisica as PessoaFisica; 
 
 class AlterarFisica extends PessoaFisica {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $campos)
    {

        parent::__construct($name, $serviceLocator, $campos);  

        $this->genericTextInput('senha', 'Alterar senha:', false);
        

    }
 }
