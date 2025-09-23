<?php

 namespace Cliente\Form;
 
 use Application\Form\Base as BaseForm; 
 use Application\Validator\Cpf;
 use Application\Validator\Cnpj;
 
 class PessoaFisica extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $campos)
    {
        
        $this->setServiceLocator($serviceLocator);
        
        parent::__construct($name);

        //cpf
        if($campos['cpf']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['cpf']['obrigatorio']);
            $this->textInputCpf('cpf', $atributos['prefixo'].$campos['cpf']['label_campo'], $atributos['obrigatorio'], 'CPF');
            
        }  

        //nome_completo
        if($campos['nome_completo']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['nome_completo']['obrigatorio']);
            $this->genericTextInput('nome_completo', $atributos['prefixo'].$campos['nome_completo']['label_campo'], $atributos['obrigatorio'], 'Nome completo');
        }
        
        //nome_certificado
        if($campos['nome_certificado']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['nome_certificado']['obrigatorio']);
            $this->genericTextInput('nome_certificado', $atributos['prefixo'].$campos['nome_certificado']['label_campo'], $atributos['obrigatorio'], 'Nome no certificado');
        }

        //nome_cracha
        if($campos['nome_cracha']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['nome_cracha']['obrigatorio']);
            $this->genericTextInput('nome_cracha', $atributos['prefixo'].$campos['nome_cracha']['label_campo'], $atributos['obrigatorio'], 'Nome no crachá');                
        }
                
        //estado_civil
        $serviceEstadoCivil = $this->getServiceLocator()->get('EstadoCivil');
        if($campos['estado_civil']['aparecer'] == 'S'){
            $estadoCivil = $serviceEstadoCivil->fetchAll(array('id', 'nome'))->toArray();
            $estadoCivil = $serviceEstadoCivil->prepareForDropDown($estadoCivil, array('id', 'nome'));

            $atributos = $this->booleanByAtivo($campos['estado_civil']['obrigatorio']);
            $this->_addDropdown('estado_civil', $atributos['prefixo'].$campos['estado_civil']['label_campo'], $atributos['obrigatorio'], $estadoCivil);
        }

        //nacionalidade
        if($campos['nacionalidade']['aparecer'] == 'S'){
            $nacionalidade = $this->getServiceLocator()->get('Nacionalidade')->fetchAll(array('id', 'nome'))->toArray();
            $nacionalidade = $serviceEstadoCivil->prepareForDropDown($nacionalidade, array('id', 'nome'));
            
            $atributos = $this->booleanByAtivo($campos['nacionalidade']['obrigatorio']);
            $this->_addDropdown('nacionalidade', $atributos['prefixo'].$campos['nacionalidade']['label_campo'], $atributos['obrigatorio'], $nacionalidade);
        }

        //sexo
        if($campos['sexo']['aparecer'] == 'S'){
            $sexo = array('' => '--', 'M' => 'Masculino', 'F' => 'Feminino');
            
            $atributos = $this->booleanByAtivo($campos['sexo']['obrigatorio']);
            $this->_addDropdown('sexo', $atributos['prefixo'].$campos['sexo']['label_campo'], $atributos['obrigatorio'], $sexo);
        }


        //rg
        if($campos['rg']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['rg']['obrigatorio']);
            $this->genericTextInput('rg', $atributos['prefixo'].$campos['rg']['label_campo'], $atributos['obrigatorio']);
        }





        //conselho
        if($campos['conselho']['aparecer'] == 'S'){
            $conselhos = array('' => '-- Selecione --', 'CRM' => 'CRM', 'COREN' => 'COREN', 'CRN' => 'CRN', 'Outro' => 'Outro (Explicar no campo abaixo)');
            $atributos = $this->booleanByAtivo($campos['conselho']['obrigatorio']);
            $this->_addDropdown('conselho', $atributos['prefixo'].$campos['conselho']['label_campo'], $atributos['obrigatorio'], $conselhos, 'outroConselho(this.value);');
        }

        //outro_conselho
        if($campos['outro_conselho']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['outro_conselho']['obrigatorio']);
            $this->genericTextInput('outro_conselho', $atributos['prefixo'].$campos['outro_conselho']['label_campo'], $atributos['obrigatorio']);
        }

        //numero_conselho
        if($campos['numero_conselho']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['numero_conselho']['obrigatorio']);
            $this->genericTextInput('numero_conselho', $atributos['prefixo'].$campos['numero_conselho']['label_campo'], $atributos['obrigatorio']);
        }

        //especialidade
        if($campos['especialidade']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['especialidade']['obrigatorio']);
            $this->genericTextInput('especialidade', $atributos['prefixo'].$campos['especialidade']['label_campo'], $atributos['obrigatorio']);
        }


        











        //data_nascimento
        if($campos['data_nascimento']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['data_nascimento']['obrigatorio']);
            $this->genericTextInput('data_nascimento', $atributos['prefixo'].$campos['data_nascimento']['label_campo'], $atributos['obrigatorio']);
        }

        //como_ficou_sabendo
        if($campos['como_ficou_sabendo']['aparecer'] == 'S'){
            $serviceSabendo = $this->getServiceLocator()->get('ComoFicouSabendo');
            $comoFicouSabendo = $serviceSabendo->fetchAll(array('id', 'nome'))->toArray();
            $comoFicouSabendo = $serviceSabendo->prepareForDropDown($comoFicouSabendo, array('id', 'nome'));
            
            $atributos = $this->booleanByAtivo($campos['como_ficou_sabendo']['obrigatorio']);
            $this->_addDropdown('como_ficou_sabendo', $atributos['prefixo'].$campos['como_ficou_sabendo']['label_campo'], $atributos['obrigatorio'], $comoFicouSabendo);
        }









        //sabendo_aberta
        if($campos['sabendo_aberta']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['sabendo_aberta']['obrigatorio']);
            $this->genericTextInput('sabendo_aberta', $atributos['prefixo'].$campos['sabendo_aberta']['label_campo'], $atributos['obrigatorio']);

            $serviceSabendo = $this->getServiceLocator()->get('ComoFicouSabendo');
            $comoFicouSabendo = $serviceSabendo->fetchAll(array('id', 'nome'))->toArray();
            $opcoes = $serviceSabendo->prepareForDropDown($comoFicouSabendo, array('id', 'nome'), false, array());
            
            $opcoes = array(
                'label' => $atributos['prefixo'].$campos['sabendo_aberta']['label_campo'],
                'value_options' => $opcoes,
            );


            $this->add(array(
                'name' => 'sabendo_aberta',
                'type' => 'Select',
                'attributes' => array(
                    'required' => $atributos['obrigatorio'],
                    'id' => 'sabendo_aberta',
                    'class'=> 'form-control',
                    'style' => 'max-width: 216px;height:30%;',
                    'multiple'  => 'multiple'
                ),
                'options' => $opcoes,
            )); 
           
            /*
             * As we're using input filter, we need to make sure all fields get added to it 
             * Or else getData will not find them 
             */
            $this->inputFilterArray['sabendo_aberta'] = array(
                'name' => 'sabendo_aberta',
                'required' => $atributos['obrigatorio'],
                'filters' => array(
                    array('name' => 'StripTags'),
                )
            );
        }

        //email
        if($campos['email']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['email']['obrigatorio']);
            $this->addEmailElement('email', $atributos['prefixo'].$campos['email']['label_campo'], $atributos['obrigatorio'], 'Email');
        }


        //nome_empresa
        if($campos['nome_empresa']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['nome_empresa']['obrigatorio']);
            $this->genericTextInput('nome_empresa', $atributos['prefixo'].$campos['nome_empresa']['label_campo'], $atributos['obrigatorio'], 'Empresa');
        }

        //endereco_comercial
        if($campos['endereco_comercial']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['endereco_comercial']['obrigatorio']);
            $this->genericTextInput('endereco_comercial', $atributos['prefixo'].$campos['endereco_comercial']['label_campo'], $atributos['obrigatorio'], 'Endereço comercial');        
        }

        //pais
        if($campos['pais']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['pais']['obrigatorio']);
            
            $this->add(array(
                'name' => 'pais',
                'attributes' => array(
                    'type' => 'text',
                    'required' => $atributos['obrigatorio'],
                    'class' => 'form-control',
                    'id'    => 'pais',
                    'value' =>  'Brasil'
                ),
                'options' => array(
                    'label' => $atributos['prefixo'].$campos['pais']['label_campo'],
                ),
            ));

            if($atributos['obrigatorio']) {
                $this->setMinMaxLenght('pais', $atributos['obrigatorio'], 1, 100);
            } else {
                $this->allowEmpty('pais');
            }
        }

        //cep
        if($campos['cep']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['cep']['obrigatorio']);
            $this->genericTextInput('cep', $atributos['prefixo'].$campos['cep']['label_campo'], $atributos['obrigatorio']);
        }
        
        //uf
        if($campos['cidade']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['cidade']['obrigatorio']);

            $estados = $this->getServiceLocator()->get('Estado')->fetchAll(array('id', 'uf'))->toArray();
            $estados = $serviceEstadoCivil->prepareForDropDown($estados, array('id', 'uf'));
            
            $this->_addDropdown('estado', $atributos['prefixo'].'Estado:', $atributos['obrigatorio'], $estados, 'CarregaCidade(this.value, "'.$atributos['obrigatorio'].'");');
            
            //cidade
            $cidades = $this->getServiceLocator()->get('Cidade')->fetchAll(array('id', 'nome'))->toArray();
            $cidades = $serviceEstadoCivil->prepareForDropDown($cidades, array('id', 'nome'));
            $this->_addDropdown('cidade', $atributos['prefixo'].$campos['cidade']['label_campo'], $atributos['obrigatorio'], $cidades);
        }
        
        //bairro
        if($campos['bairro']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['bairro']['obrigatorio']);
            $this->genericTextInput('bairro', $atributos['prefixo'].$campos['bairro']['label_campo'], $atributos['obrigatorio'], 'Bairro');
        }
        
        //nm_rua
        if($campos['nm_rua']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['nm_rua']['obrigatorio']);
            $this->genericTextInput('nm_rua', $atributos['prefixo'].$campos['nm_rua']['label_campo'], $atributos['obrigatorio'], 'Rua');
        }

        //numero
        if($campos['numero']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['numero']['obrigatorio']);
            $this->genericTextInput('numero', $atributos['prefixo'].$campos['numero']['label_campo'], $atributos['obrigatorio'], 'Numero');
        }

        //endereco
        if($campos['endereco']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['endereco']['obrigatorio']);
            $this->genericTextInput('endereco', $atributos['prefixo'].$campos['endereco']['label_campo'], $atributos['obrigatorio'], 'Endereco');
        }

        //complemento
        if($campos['complemento']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['complemento']['obrigatorio']);
            $this->genericTextInput('complemento', $atributos['prefixo'].$campos['complemento']['label_campo'], $atributos['obrigatorio'], 'Complemento');
        }

        //profissao
        if($campos['profissao']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['profissao']['obrigatorio']);
            $this->genericTextInput('profissao', $atributos['prefixo'].$campos['profissao']['label_campo'], $atributos['obrigatorio'], 'Profissão');
        }

        //cargo
        if($campos['cargo']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['cargo']['obrigatorio']);
            $this->genericTextInput('cargo', $atributos['prefixo'].$campos['cargo']['label_campo'], $atributos['obrigatorio'], 'Cargo');
        }

        //telefone
        if($campos['telefone']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['telefone']['obrigatorio']);
            $this->genericTextInput('telefone', $atributos['prefixo'].$campos['telefone']['label_campo'], $atributos['obrigatorio'], 'Telefone');
        }

        //celular
        if($campos['celular']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['celular']['obrigatorio']);
            $this->genericTextInput('celular', $atributos['prefixo'].$campos['celular']['label_campo'], $atributos['obrigatorio'], 'Celular');
        }
        
        //código promocional
        if($campos['codigo']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['codigo']['obrigatorio']);
            $this->genericTextInput('codigo', $atributos['prefixo'].$campos['codigo']['label_campo'], $atributos['obrigatorio'], 'Código promocional');
        }
        
        //observacoes
        if($campos['observacoes']['aparecer'] == 'S'){
            $atributos = $this->booleanByAtivo($campos['observacoes']['obrigatorio']);
            $this->genericTextInput('observacoes', $atributos['prefixo'].$campos['observacoes']['label_campo'], $atributos['obrigatorio']);
        }

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

        if($campos['cpf']['aparecer'] == 'S'){
            $this->getInputFilter()->get('cpf')->getValidatorChain()->addValidator(new Cpf());
        }
        
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
            $data['estado'] = $cidade['estado'];
            $this->setCidadesByEstado($data['estado']);
        }

        if(isset($data['data_nascimento']) && !empty($data['data_nascimento'])){
            $data['data_nascimento'] = parent::converterData($data['data_nascimento']);
        }
        
        parent::setData($data);
    }
 }
