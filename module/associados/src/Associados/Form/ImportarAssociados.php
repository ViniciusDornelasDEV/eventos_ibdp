<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm;
 
 class ImportarAssociados extends BaseForm {
     
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

        //empresa
        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->getRecords('S', 'ativo', array('id', 'nome_fantasia'), 'nome_fantasia')->toArray();
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_fantasia'));
        $this->_addDropdown('empresa', '* Empresa:', true, $empresas, 'carregarCategoriasAssociado(this.value, "true");');

        $this->_addDropdown('categoria_associado', '* Categoria:', true, array('Selecione uma empresa'));

        $this->addImageFileInput('planilha', '* Planilha: ', true);
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }
 }
