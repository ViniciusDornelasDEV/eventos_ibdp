<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class ValorEvento extends BaseForm {
     
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
        $categorias = $serviceCategoria->prepareForDropDown($categorias, array('id_categoria', 'nome_categoria'));
        $this->_addDropdown('evento_cliente_categoria', '* Categoria:', true, $categorias);

        //valor
        $this->genericTextInput('valor_inscricao', '* Valor da inscrição:', true);

        //data_inicio
        $this->_addGenericDateElement('data_inicio_valor', '* Início da promoção:', true);

        //data_fim
        $this->_addGenericDateElement('data_fim_valor', '* Término da promoção:', true);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

    public function setData($data){
        $data->data_inicio_valor = parent::converterData($data->data_inicio_valor);
        $data->data_fim_valor = parent::converterData($data->data_fim_valor);
        $data->valor_inscricao = parent::numberInsertMysql($data->valor_inscricao);

        parent::setData($data);
    }
 }
