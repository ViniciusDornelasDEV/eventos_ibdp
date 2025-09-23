<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class QuantidadeInscricoes extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        
        
        //categoria
        $serviceCategoria = $this->serviceLocator->get('CategoriaCliente');
        $categorias = $serviceCategoria->fetchAll(array('id', 'nome'))->toArray();
        $categorias = $serviceCategoria->prepareForDropDown($categorias, array('id', 'nome'));
        $this->_addDropdown('cliente_categoria', '* Categoria:', true, $categorias);
        
        //quantidade
        $this->genericTextInput('quantidade_maxima_inscritos', 'Quantidade máxima de inscritos:', true);

        $this->genericTextArea('descricao_categoria', '* Descrição da categoria:', true, 'Ex: Apresentar comprovante no ato de credenciamento.', true, 0, 2000, 'width: 100%;');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }
 }
