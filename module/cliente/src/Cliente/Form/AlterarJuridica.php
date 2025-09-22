<?php

 namespace Cliente\Form;
 
 use Cliente\Form\PessoaJuridica as PessoaJuridica; 
 
 class AlterarJuridica extends PessoaJuridica {
     
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
