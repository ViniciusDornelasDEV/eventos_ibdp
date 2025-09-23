<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Continuada extends BaseForm {
     
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

        $this->genericTextInput('nome', 'Nome: ', true, 'NOME');

        $this->genericTextArea('descricao', '* Descrição continuada: ', true, false, false, 0, 900000, 'width: 100%');

        $this->genericTextInput('palestrantes', 'Palestrantes: ', true, 'PALESTRANTES');

        $this->genericTextInput('link_vimeo', 'Link do Vimeo: ', true, 'VIMEO');

        $this->genericTextInput('moderador', 'Moderador: ', true, 'MODERADOR');

        $this->addImageFileInput('imagem', '* Imagem de capa: ', true, false, false, false, false, 'image/png, image/jpeg');

        $this->_addDropdown('ativo', 'Ativo:', false, array('S' => 'Sim', 'N' => 'Não'));

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

    public function setData($data){
        $data->descricao = html_entity_decode($data->descricao);
        parent::setData($data);
    }

 }
