<?php

 namespace Trabalhos\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class VincularAvaliador extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $autores = array())
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        
        $serviceUsuario = $this->serviceLocator->get('Usuario');
        $avaliadores = $serviceUsuario->getRecords('S', 'avaliador')->toArray();
        
        $cpfs = array_column($autores, 'cpf');
        foreach ($avaliadores as $key => $avaliador) {
            if (in_array($avaliador['login'], $cpfs)) {
                unset($avaliadores[$key]);
            }
        }
        
        $avaliadores = $serviceUsuario->prepareForDropDown($avaliadores, array('id', 'nome'));
        
        $this->_addDropdown('avaliador1', 'Avaliador escrita (*):', false, $avaliadores);

        $this->_addDropdown('avaliador2', 'Avaliador escrita (*):', false, $avaliadores);

        $this->_addDropdown('avaliador3', 'Avaliador oral:', false, $avaliadores);

        $this->_addDropdown('avaliador4', 'Avaliador oral:', false, $avaliadores);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
