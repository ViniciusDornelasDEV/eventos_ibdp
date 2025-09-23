<?php

 namespace Trabalhos\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class EnviarPoster extends BaseForm {
     
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
        $this->_addDropdown('categoria', '* Categoria:', false, $categorias);

        $this->genericTextInput('titulo', '* Título:', false);

        $this->genericTextArea('autores', '* Autores:', false);

        $this->genericTextInput('afiliacao', '* Afiliação:', false);

        $this->genericTextInput('descritor1', '* Descritor:', false);

        $this->genericTextInput('descritor2', '* Descritor:', false);

        $this->genericTextInput('descritor3', 'Descritor:', false);

        $this->genericTextInput('apoio_financeiro', 'Apoio financeiro:', false);

        $this->genericTextArea('resumo', '* Resumo: ', false, false, false, 0, 900000, 'width: 100%;');

        $this->addPDF('poster', 'Pôster (apenas PDF): ');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

    public function setData($data){
        $data['resumo'] = html_entity_decode($data['resumo']);
        parent::setData($data);
    }

 }
