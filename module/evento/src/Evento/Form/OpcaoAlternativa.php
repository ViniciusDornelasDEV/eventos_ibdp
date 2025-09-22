<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class OpcaoAlternativa extends BaseForm {
     
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
        
        $serviceOpcao = $this->serviceLocator->get('EventoOpcao');
        $opcoes = $serviceOpcao->getRecords($idEvento, 'evento')->toArray();
        $opcoes = $serviceOpcao->prepareForDropDown($opcoes, array('id', 'titulo'));
        $this->_addDropdown('evento_opcao', '* Opção:', true, $opcoes);
        
        //responsavel
        $this->genericTextInput('local', '* Local:', true, 'Local da atividade');
        
        //atividade
        $this->genericTextInput('atividade', '* Atividade:', true, 'Título da atividade');
        
        //observacoes
        $this->genericTextArea('descricao', 'Descrição:', false, false, true, 0, 2000, 'width: 100%;');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
