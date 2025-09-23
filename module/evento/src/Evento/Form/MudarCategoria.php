<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class MudarCategoria extends BaseForm {
     
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
        
        
        //categoria
        $serviceValor = $this->serviceLocator->get('ValorEvento');
        $data = date('Y-m-d');
        $categorias = $serviceValor->getValoresByEventoAndDate($idEvento, $data)->toArray();
        $categorias = $serviceValor->prepareForDropDown($categorias, array('evento_cliente_categoria', 'nome_categoria'));
        $this->_addDropdown('cliente_categoria', 'Categoria:', false, $categorias);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }
 }
