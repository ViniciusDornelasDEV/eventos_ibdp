<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Mensagem extends BaseForm {
     
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
        
        
        //categoria
        $serviceCategoria = $this->serviceLocator->get('QuantidadeCategoria');
        $categorias = $serviceCategoria->getQuantidadeInscricoesCategoriaByEvento($idEvento)->toArray();

        $categorias = $serviceCategoria->prepareForDropDown($categorias, array('id', 'nome_categoria'), false, array('T' => 'TODAS'));
        $this->_addDropdown('evento_cliente_categoria', 'Categoria:', false, $categorias);
        
        //pago
        $this->_addDropdown('pago', 'Pago: ', false, array('' => '-- Selecione --', 'S' => 'Pago', 'N' => 'Não pago'));

        //título
        $this->genericTextInput('assunto', '* Assunto:', true, 'Assunto do email');

        //mensagem
        $this->genericTextArea('mensagem', '* Mensagem:', true, false, true, 0, 2000, 'width: 100%;');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }
 }
