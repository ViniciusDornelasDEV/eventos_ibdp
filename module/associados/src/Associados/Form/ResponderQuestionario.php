<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class ResponderQuestionario extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $questoes)
    {

        parent::__construct($name);      
            

        foreach ($questoes as $questao) {
            $opcoes = array();

            foreach ($questao['alternativas'] as $alternativa) {
                if($alternativa['tipo_alternativa'] == 'F'){
                    $opcoes[$alternativa['id']] = $alternativa['titulo_alternativa'];
                }
            }
            
            $this->_addRadio('radio_'.$questao['id'], $questao['enunciado'], true, $opcoes);
            
            foreach ($questao['alternativas'] as $alternativa) {
                if($alternativa['tipo_alternativa'] == 'A'){
                    $this->genericTextArea('text_'.$alternativa['id'], '', false, '');
                }
            }
        }

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
