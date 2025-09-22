<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Categoria extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $empresa = false)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        
        if(!$empresa){
            $serviceEmpresa = $this->serviceLocator->get('Empresa');
            $empresas = $serviceEmpresa->getRecords('S', 'ativo', array('id', 'nome_fantasia'), 'nome_fantasia')->toArray();
            $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_fantasia'));
            $this->_addDropdown('empresa', '* Empresa:', true, $empresas);
        }

        $this->genericTextInput('nome', '* Nome da categoria:', true, '');

        $this->addImageFileInput('certificado', 'Certificado: ', true, false, false, false, false, 'image/png, image/jpeg, application/pdf');

        $this->genericTextInput('validade_certificado', 'Validade do certificado:', false, '');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

    public function setData($data){
        $data->validade_certificado = parent::converterData($data->validade_certificado);
     
        parent::setData($data);
    }

 }
