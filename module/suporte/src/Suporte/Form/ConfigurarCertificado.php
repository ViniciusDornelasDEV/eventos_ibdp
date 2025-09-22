<?php

 namespace Suporte\Form;
 
 use Application\Form\Base as BaseForm; 
 use Application\Validator\Cpf;
 
 class ConfigurarCertificado extends BaseForm {
     
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
        
        //nome_campo
        $campos = array('' => '-- Selecione --', 'silga' => 'Sigla do evento', 'nome_evento' => 'Nome do evento',
            'nome_completo' => 'Nome completo', 'nome_certificado' => 'Nome no certtificado', 'cpf' => 'CPF'
        );
       
        $this->_addDropdown('nome_campo', '* Campo:', true, $campos);
        
        //cor
        $this->genericTextInput('cor', '* Cor:', true, '#000000');
        $this->setMinMaxLenght('cor', true, 7, 7);

        //posicao_x => largura
        $this->genericTextInput('posicao_x', '* Posição de início X:', true);
        
        //posicao_y => altura
        $this->genericTextInput('posicao_y', '* Posição de início Y:', true);

        //tamanho_fonte
        $this->genericTextInput('tamanho_fonte', '* Tamanho da fonte:', true);
        
        //centralizar
        $this->_addDropdown('centralizar', 'Centralizar:', false, array('N' => 'Não', 'S' => 'Sim'));

        //maximo_caracteres
        $this->genericTextInput('maximo_caracteres', 'Limite de caracteres:', false);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
