<?php

 namespace Trabalhos\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class CategoriaTrabalho extends BaseForm {
     
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
        
        
        //categoria
        $this->genericTextInput('categoria', '* Categoria:', true, 'Nome da categoria');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }
 }
