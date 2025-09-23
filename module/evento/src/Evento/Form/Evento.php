<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Evento extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        
        //sigla
        $this->genericTextInput('sigla', '* Sigla:', true, 'Sigla do evento');
        
        //nome
        $this->genericTextInput('nome', '* Nome:', true, 'Nome do evento');
        
        //responsavel
        $this->genericTextInput('responsavel', '* Responsável:', true, 'Nome do respnsável');

        //email_responsavel
        $this->addEmailElement('email_responsavel', 'Email do responsável:', false, 'Email');

        //fone_responsavel
        $this->genericTextInput('fone_responsavel', 'Telefone:', false, '(00) 00000-0000');

        //estado
        $serviceEstado = $this->serviceLocator->get('Estado');
        $estados = $serviceEstado->fetchAll(array('id', 'uf'))->toArray();
        $estados = $serviceEstado->prepareForDropDown($estados, array('id', 'uf'));
        $this->_addDropdown('estado', '* Estado:', true, $estados, 'CarregaCidade(this.value);');
        
        //cidade
        $this->_addDropdown('cidade', '* Cidade:', true, array('' => '-- Selecione --'));

        //local
        $this->genericTextInput('local', '* Local:', true, 'Local do evento');

        //data_inicio
        $this->_addGenericDateElement('data_inicio', '* Data de início:', true);

        //data_fim
        $this->_addGenericDateElement('data_fim', '* Data de término:', true);

        //empresa
        $empresas = $this->serviceLocator->get('Empresa')->fetchAll(array('id', 'nome_fantasia'))->toArray();
        $empresas = $serviceEstado->prepareForDropDown($empresas, array('id', 'nome_fantasia'));
        $this->_addDropdown('empresa', '* Empresa:', true, $empresas);
        
        //site_evento
        $this->genericTextInput('site_evento', '* Website do evento:', true, 'www.exemplo.com');
        
        //dias_vencimento_boleto
        $this->genericTextInput('dias_vencimento_boleto', '* Dias para vencimento do boleto (após a inscrição):', true);
        
        //taxa_boleto
        $this->genericTextInput('taxa_boleto', '* Taxa do boleto:', true);
    
        //multa_vencimento
        $this->genericTextInput('multa_vencimento', '* Multa após o vencimento:', true,' 2%');

        //imagem
        $this->addImageFileInput('banner_evento', 'Banner do evento: ', false);

        //certificado
        $this->addImageFileInput('certificado_1', 'Certificado 1: ', false);
        $this->addImageFileInput('certificado_2', 'Certificado 2: ', false);
        $this->addImageFileInput('certificado_3', 'Certificado 3: ', false);
        
        //enviar_trabalho
        $this->_addDropdown('enviar_trabalho', 'Enviar trabalhos:', false, array('N' => 'Não', 'S' => 'Sim'));

        //ativo
        $this->_addDropdown('ativo', 'Ativo:', false, array('S' => 'Sim', 'N' => 'Não'));

        //forma_cielo
        $this->_addRadio('forma_cielo', '* Cielo: ', true, array('S' => 'Sim', 'N' => 'Não'));

        //forma_paypal
        $this->_addRadio('forma_paypal', '* PayPal: ', true, array('S' => 'Sim', 'N' => 'Não'));

        //forma_transferencia
        $this->_addRadio('forma_transferencia', '* Transferência: ', true, array('S' => 'Sim', 'N' => 'Não'));

        //forma_ipag
        $this->_addRadio('forma_ipag', '* iPag: ', true, array('S' => 'Sim', 'N' => 'Não'));

        $this->_addDropdown('parcelas', '* Número de parcelas:', true, array(
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5',
            6 => '6',
            7 => '7',
            8 => '8',
            9 => '9',
            10 => '10',
            11 => '11',
            12 => '12'
        ));

        $this->_addRadio('visualizar_trabalhos', '* Visualizar trabalhos: ', true, array('S' => 'Sim', 'N' => 'Não'));
        
        //mensagem_cadastro
        $this->genericTextArea('mensagem_cadastro_novo', '* Mensagem de cadastro p/ novos usuários: (%LOGIN%, %NOME_INSCRITO%)', true, false, false, 0, 900000);

        //mensagem_cadastro
        $this->genericTextArea('mensagem_cadastro_antigo', '* Mensagem de cadastro p/ usuários antigos: (%LOGIN%, %NOME_INSCRITO%)', true, false, false, 0, 900000);

        //mensagem_pagamento
        $this->genericTextArea('mensagem_pagamento', '* Mensagem de confirmação de pagamento: (%LOGIN%, %NOME_INSCRITO%)', true, false, false, 0, 900000);

        //mensagem_transferencia
        $this->genericTextArea('mensagem_transferencia', '* Mensagem para transferência bancária: (%LOGIN%, %NOME_INSCRITO%)', true, false, false, 0, 900000);

        //mensagem_trabalho
        $this->genericTextArea('mensagem_trabalho', '* Mensagem de aprovação do trabalho: (%LOGIN%, %NOME_INSCRITO%)', true, false, false, 0, 900000);

        //observacoes
        $this->genericTextArea('observacoes', '* Descrição:', true, true, false, 0, 900000);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

    public function setCidadesByEstado($estado){
        //buscar cidade
        $serviceCidade = $this->serviceLocator->get('Cidade');
        $cidades = $serviceCidade->getRecords($estado, 'estado');
        $cidades = $serviceCidade->prepareForDropDown($cidades, array('id', 'nome'));
        //Setando valores
        $cidades = $this->get('cidade')->setAttribute('options', $cidades);
        
        return $cidades;      
    }

    public function setData($data){
        if(isset($data['estado'])){
            $this->setCidadesByEstado($data['estado']);
        }

        if(isset($data['cidade']) && !isset($data['estado'])){
            //find estado by cidade
            $cidade = $this->serviceLocator->get('Cidade')->getRecord($data['cidade']);
            $data['estado'] = $cidade->estado;
            $this->setCidadesByEstado($data['estado']);
        }

        $data->data_inicio = parent::converterData($data->data_inicio);
        $data->data_fim = parent::converterData($data->data_fim);

        $data->mensagem_cadastro_novo = html_entity_decode($data->mensagem_cadastro_novo);
        $data->mensagem_cadastro_antigo = html_entity_decode($data->mensagem_cadastro_antigo);
        $data->mensagem_pagamento = html_entity_decode($data->mensagem_pagamento);
        $data->mensagem_transferencia = html_entity_decode($data->mensagem_transferencia);
        $data->mensagem_trabalho = html_entity_decode($data->mensagem_trabalho);
        $data->observacoes = html_entity_decode($data->observacoes);


        if(isset($data->site_evento) && !empty($data->site_evento)){
            if(!strstr($data->site_evento, "http:\\\\")){
                if(strstr($data->site_evento, "www.")){
                    $data->site_evento = "http:\\\\".$data->site_evento;
                }else{
                    $data->site_evento = "http:\\\\www.".$data->site_evento;
                }   
            }
        }

        parent::setData($data);
    }

 }