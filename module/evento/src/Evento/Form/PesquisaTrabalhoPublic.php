<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class PesquisaTrabalhoPublic extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $idEvento)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        
        $serviceCategoria = $this->serviceLocator->get('InscricaoTrabalhoCategoria');
        $categorias = $serviceCategoria->getRecords($idEvento, 'evento', array('id', 'categoria'), 'categoria')->toArray();
        $categorias = $serviceCategoria->prepareForDropDown($categorias, array('id', 'categoria'));
        $this->_addDropdown('categoria', 'Categoria:', false, $categorias);

        $this->genericTextInput('titulo', 'Título:', false);

        $this->genericTextInput('autores', 'Autores:', false);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

 }
