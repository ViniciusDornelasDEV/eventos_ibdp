<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class EnviarTrabalho extends BaseForm {
     
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
        $this->_addDropdown('categoria', '* Categoria:', true, $categorias);

        $this->genericTextInput('titulo', '* Título:', true);

        $this->genericTextArea('autores', '* Autores:', true);

        $this->genericTextArea('resumo', '* Resumo: ', true, false, false, 0, 900000, 'width: 100%;');

        $this->addMultiplePDF('arquivos', 'Arquivos (apenas PDF): ');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }

    public function setData($data){
        $data['resumo'] = html_entity_decode($data['resumo']);
        parent::setData($data);
    }

 }
