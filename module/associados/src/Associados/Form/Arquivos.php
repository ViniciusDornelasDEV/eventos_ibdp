<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Arquivos extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $empresa)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      

        //pesquisar categorias da empresa
        $serviceCategoria = $this->serviceLocator->get('CategoriaAssociado');
        $categorias = $serviceCategoria->getRecordsFromArray(array('empresa' => $empresa, 'ativo' => 'S'), 'nome')->toArray();
        $categorias = $serviceCategoria->prepareForDropDown($categorias, array('id', 'nome'), false, array('T' => 'Todas'));
        $this->_addDropdown('categoria', '* Categoria:', true, $categorias);

        $this->genericTextInput('descricao', '* Descrição:', true, '');

        $this->_addDropdown('status_associado', '* Status do associado:', true, 
            array('T' => 'Todos', 'A' => 'Adimplente', 'I' => 'Inadimplente')
        );

        $this->addImageFileInput('arquivo', '* Arquivo: ', true, false, false, false, false, 'image/png, image/jpeg, application/pdf');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
