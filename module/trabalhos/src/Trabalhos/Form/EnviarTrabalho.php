<?php

 namespace Trabalhos\Form;
 
 use Application\Form\Base as BaseForm; 
use Application\Validator\Cpf;
 
 class EnviarTrabalho extends BaseForm {
     
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
        
        $serviceCategoria = $this->serviceLocator->get('InscricaoTrabalhoCategoria');
        $categorias = $serviceCategoria->getRecords($idEvento, 'evento', array('id', 'categoria'), 'categoria')->toArray();
        $categorias = $serviceCategoria->prepareForDropDown($categorias, array('id', 'categoria'));
        $this->_addDropdown('categoria', '* Categoria:', true, $categorias);

        $this->genericTextInput('titulo', '* Título:', true);


        $this->genericTextInput('afiliacao', '* Afiliação:', true);

        $this->genericTextInput('descritor1', '* Descritor:', true);

        $this->genericTextInput('descritor2', '* Descritor:', true);

        $this->genericTextInput('descritor3', 'Descritor:', false);

        $this->genericTextInput('apoio_financeiro', 'Apoio financeiro:', false);

        $this->genericTextArea('resumo', '* Resumo (Sem lista de autores e afiliações): ', true, false, false, 0, 900000, 'width: 100%;');

        $this->genericTextInput("nome1", '* Nome do coautor:', true);
        $this->textInputCpf("cpf1", 'CPF do coautor: ', false, 'CPF');
        for ($i=2; $i <= 5; $i++) { 
            $this->genericTextInput("nome$i", 'Nome do coautor:', false);
            $this->textInputCpf("cpf$i", 'CPF do coautor: ', false, 'CPF');
        }

        $this->addPDF('comite_etica', '* Cópia do parecer (CEP, PROSPERO ou TCLE): ', true);

        $this->addPDF('stricto_sensu', 'Stricto sensu - Comprovante de matrícula atualizado (últimos 3 meses): ');

        $this->addPDF('mestrado_doutorado', 'Mestrado ou Doutorado - Certificado ou ata de defesa de mestrado ou doutorado, ou diploma de mestrado/doutorado: ');

        $this->genericTextInput('link_video', 'Link vídeo(Apenas para a categoria novos talentos):', false, false, false, 'width: 100%');

        $this->_addRadio('pode_postar', 'Os vídeos enviados para o processo seletivo poderão ser postados em nossas redes sociais? ', false, array('S' => 'Sim', 'N' => 'Não'));

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

        for ($i=1; $i <= 5; $i++) { 
            $this->getInputFilter()->get("cpf$i")->getValidatorChain()->addValidator(new Cpf());
        }
    }

    public function setData($data){
        $data['resumo'] = html_entity_decode($data['resumo']);
        parent::setData($data);
    }

 }
