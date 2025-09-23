<?php

 namespace Cliente\Form;
 
 use Application\Form\Base as BaseForm; 
 use Application\Validator\Cpf;
 
 class CategoriaCliente extends BaseForm {
     
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

        //nome
        $this->genericTextInput('nome', '* Nome:', true, 'Nome da categoria');
        
        //
        $this->_addDropdown('apenas_associados', 'Apenas associados:', false, array('N' => 'Não', 'S' => 'Sim'));
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }
 }
