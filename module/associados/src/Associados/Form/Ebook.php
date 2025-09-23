<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Ebook extends BaseForm {
     
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

        $this->addImageFileInput('imagem_capa', '* Imagem de capa: ', true, false, false, false, false, 'image/png, image/jpeg');
        
        $this->addImageFileInput('arquivo', '* PDF: ', true, false, false, false, false, 'application/pdf');

        $this->genericTextInput('link', 'Link: ', false, 'LINK');

        $this->_addDropdown('ativo', 'Ativo:', false, array('S' => 'Sim', 'N' => 'Não'));

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
