<?php

 namespace Trabalhos\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class VisualizarTrabalho extends BaseForm {
     
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
        
        $this->genericTextInput('nome_evento', '* Evento:', true);

        $this->genericTextInput('nome_completo', '* Nome:', true);

        $this->genericTextInput('cpf', '* CPF:', true);

        $this->genericTextInput('nome_categoria', '* Categoria:', true);
        
        $this->genericTextInput('titulo_trabalho', '* Título:', true);

        $this->genericTextArea('autores_trabalho', '* Coautores:', true);

        $this->genericTextArea('afiliacao', '* Afiliação:', true);

        $this->genericTextInput('descritor1', '* Descritor:', true);

        $this->genericTextInput('descritor2', '* Descritor:', true);

        $this->genericTextInput('descritor3', 'Descritor:', false);

        $this->genericTextInput('apoio_financeiro', 'Apoio financeiro:', false);

        $this->genericTextArea('resumo_trabalho', '* Resumo: ', true, false, false, 0, 900000, 'width: 100%;');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }
    
    public function setData($data){
        $data->resumo_trabalho = html_entity_decode($data->resumo_trabalho);
        parent::setData($data);
    }


 }
