<?php

namespace Evento\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;


use Evento\Form\Evento as eventoForm;
use Evento\Form\QuantidadeInscricoes as quantidadeInscricoesForm;
use Evento\Form\ValorEvento as valorEventoForm;
use Evento\Form\CodigoPromocional as promocaoForm;
use Evento\Form\Mensagem as mensagemForm;
use Evento\Form\ConfiguracoesInscricao as formConfiguracoes;

use Evento\Form\OpcaoAlternativa as formAlternativa;
use Evento\Form\Opcao as formOpcao;
use Evento\Form\PromocaoAssociados as formPromocaoAssociados;
use Evento\Form\CategoriaTrabalho as formCategoria;
use Evento\Form\Transmissao as formTransmissao;

class EventoController extends BaseController
{

    public function indexAction()
    {   
        $usuario = $this->getServiceLocator()->get('session')->read();
        $params = array();
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $params['empresa'] = $usuario['empresa'];
        }

        //pesquisar eventos
        $serviceEvento = $this->getServiceLocator()->get('Evento');
        $eventos = $serviceEvento->getRecordsFromArray($params, 'data_inicio DESC, id DESC');

        //paginação
        $paginator = new Paginator(new ArrayAdapter($eventos->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);

        return new ViewModel(array(
                    'eventos'     => $paginator,
                    'usuario'     => $usuario
                ));
    }

    public function novoAction()
    { 
        $formEvento = new eventoForm('formEvento', $this->getServiceLocator());
        //caso venha um post salvar
        if($this->getRequest()->isPost()){
            //salvar e enviar para  edit
            $dados = $this->getRequest()->getPost();
            $files = $this->getRequest()->getfiles()->toArray();
            $serviceEvento = $this->getServiceLocator()->get('Evento');

            //validar form
            $formEvento->setData($dados);
            if($formEvento->isValid()){
                //upload de imagem
                if(empty($files['banner_evento']['name'])){
                    $this->flashMessenger()->addErrorMessage('Por favor insira uma imagem!');
                    return $this->redirect()->toRoute('eventoNovo');
                }
                $dir = 'public/arquivos/eventos';

                $dados = $formEvento->getData();
                $nomeArquivo = $serviceEvento->getNextInsertId('tb_evento');
                $dados['banner_evento'] = $this->uploadImagem($files['banner_evento'], $dir, $dados, $nomeArquivo->Auto_increment);

                //certificado 1
                if(!empty($files['certificado_1']['name'])){
                    $dir = 'public/arquivos/eventos/certificados';
                    $extensao = $this->getExtensao($files['certificado_1']['name']);
                    if($extensao != 'jpg'){
                        $this->flashMessenger()->addWarningMessage('Favor inserir uma imagem .jpg para o certificado 1!');
                        return $this->redirect()->toRoute('eventoNovo');
                    }
                    $dados['certificado_1'] = $this->uploadImagem($files['certificado_1'], $dir, $dados, $nomeArquivo->Auto_increment.'_1');
                }

                //certificado 2
                if(!empty($files['certificado_2']['name'])){
                    $dir = 'public/arquivos/eventos/certificados';
                    $extensao = $this->getExtensao($files['certificado_2']['name']);
                    if($extensao != 'jpg'){
                        $this->flashMessenger()->addWarningMessage('Favor inserir uma imagem .jpg para o certificado 2!');
                        return $this->redirect()->toRoute('eventoNovo');
                    }
                    $dados['certificado_2'] = $this->uploadImagem($files['certificado_2'], $dir, $dados, $nomeArquivo->Auto_increment.'_2');
                }

                //certificado 3
                if(!empty($files['certificado_3']['name'])){
                    $dir = 'public/arquivos/eventos/certificados';
                    $extensao = $this->getExtensao($files['certificado_3']['name']);
                    if($extensao != 'jpg'){
                        $this->flashMessenger()->addWarningMessage('Favor inserir uma imagem .jpg para o certificado 3!');
                        return $this->redirect()->toRoute('eventoNovo');
                    }
                    $dados['certificado_3'] = $this->uploadImagem($files['certificado_3'], $dir, $dados, $nomeArquivo->Auto_increment.'_3');
                }

                $dados['mensagem_cadastro_novo'] = htmlspecialchars($dados['mensagem_cadastro_novo']);
                $dados['mensagem_cadastro_antigo'] = htmlspecialchars($dados['mensagem_cadastro_antigo']);
                $dados['mensagem_pagamento'] = htmlspecialchars($dados['mensagem_pagamento']);
                $dados['mensagem_transferencia'] = htmlspecialchars($dados['mensagem_transferencia']);
                $dados['observacoes'] = htmlspecialchars($dados['observacoes']);

                //salvar evento
                $result = $serviceEvento->insert($dados);
                if($result){
                    //criar campos obrigatórios para o evento
                    $camposEvento = $this->getServiceLocator()->get('InscricaoCampos')->fetchAll()->toArray();
                    $camposReplicados = $this->getServiceLocator()
                                                ->get('InscricaoCamposEvento')
                                                ->camposDefaultByEvento($result, $camposEvento);

                    //mensagem de erro
                    if(!$camposReplicados){
                         $this->flashMessenger()->addErrorMessage('Falha ao replicar campos do evento, por favor contate o administrador!');
                         return $this->redirect()->toRoute('eventoAlterar', array('id' => $result)); 
                    }
                    //sucesso criar mensagem e redir para edit
                    $this->flashMessenger()->addSuccessMessage('Evento inserido com sucesso!');                
                    return $this->redirect()->toRoute('eventoAlterar', array('id' => $result));
                }else{
                    //falha, exibir mensagem
                    $this->flashMessenger()->addErrorMessage('Falha ao inserir evento!'); 
                }
            }

        }

        return new ViewModel(array('formEvento' => $formEvento));
    }

    public function alterarAction(){
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        $serviceEvento = $this->getServiceLocator()->get('Evento');

        //Pesquisar EVENTO
        $idEvento = $this->params()->fromRoute('id');
        $evento = $serviceEvento->getEventoById($idEvento, $empresa);
        
        //instanciar form de evento
        $formEvento = new eventoForm('formEvento', $this->getServiceLocator());
        $formEvento->setData($evento);

        //instanciar form de QUANTIDADE DE INSCRIÇÕES formQuantidadeInscricoes
        $formQuantidadeInscricoes = new quantidadeInscricoesForm('formQuantidadeInscricoes', $this->getServiceLocator());
        $serviceQuantidadeInscricoes = $this->getServiceLocator()->get('QuantidadeCategoria');
        $idQuantidadeInscricao = $this->params()->fromRoute('quantidadeinscricao');

        if($idQuantidadeInscricao){
            $quantidadeInscricao = $serviceQuantidadeInscricoes->getRecord($idQuantidadeInscricao);

            //decode html
            $quantidadeInscricao->descricao_categoria = html_entity_decode($quantidadeInscricao->descricao_categoria);
            $formQuantidadeInscricoes->setData($quantidadeInscricao);
        }

        $quantidadeInscricoes = $serviceQuantidadeInscricoes->getQuantidadeInscricoesCategoriaByEvento($idEvento)->toArray();

        //instancviar form de VALOR DO EVENTO
        $formValor = new valorEventoForm('formValor', $this->getServiceLocator(), $evento->id);
        $serviceValor = $this->getServiceLocator()->get('ValorEvento');
        $idValor = $this->params()->fromRoute('valor');
        if($idValor){
            $valor = $serviceValor->getRecord($idValor);
            $formValor->setData($valor);
        }
        //Pesquisar todos os valores do evento
        $valores = $serviceValor->getValoresByCadEvento($evento->id);
        
        //instanciar form de CÒDIGO DE DESCONTO
        $formPromocao = new promocaoForm('formPromocao');
        $servicePromocao = $this->getServiceLocator()->get('PromocaoEvento');
        $idPromocao = $this->params()->fromRoute('promocao');
        if($idPromocao){
            $promocao = $servicePromocao->getRecord($idPromocao);
            $formPromocao->setData($promocao);
        }else{
            //Caso seja inserção de promoção gerar código no bcrypt
            $bcrypt = new bcrypt();
            //$bcrypt->verify($dados['senha_atual'], $usuario['senha']
            $codigo_promocao = $bcrypt->create(date('Y').'t6478*lkim@e'.date('s').'e!ve*&n#asdiv549tos'.'&*');
            $codigo_promocao = array('codigo_promocional' => $codigo_promocao);
            $formPromocao->setData($codigo_promocao);
            unset($codigo_promocao);
        }
        //pesquisar códigos de desconto
        $promocoes = $servicePromocao->getRecords($idEvento, 'evento');

        //PROMOÇÃO DE ASSOCIADOS
        $formPromocaoAssociados = new formPromocaoAssociados('formAssociados', $this->getServiceLocator(), $evento);
        $servicePromocaoAssociado = $this->getServiceLocator()->get('PromocaoAssociado');
        $idPromocaoAssociado = $this->params()->fromRoute('promocaoassociado');
        if($idPromocaoAssociado){
            $promocaoAssociado = $servicePromocaoAssociado->getRecord($idPromocaoAssociado);
            $formPromocaoAssociados->setData($promocaoAssociado);
        }

        //INSTANCIAR OPÇÕES DO EVENTO
        $formAlternativa = new formAlternativa('formAlternativa', $this->getServiceLocator(), $evento->id);
        $serviceOpcoesAlternativa = $this->getServiceLocator()->get('EventoOpcaoAlternativa');
        $idAlternativa = $this->params()->fromRoute('alternativa');
        if($idAlternativa){
            $alternativa = $serviceOpcoesAlternativa->getRecord($idAlternativa);
            $alternativa->descricao = html_entity_decode($alternativa->descricao);
            $formAlternativa->setData($alternativa);
        }

        //POST
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();

            $files = $this->getRequest()->getfiles()->toArray();

            //caso venha evento
            if(isset($dados->sigla)){
                $files = $this->getRequest()->getfiles()->toArray();
                $formEvento->setData($dados);
            
                if($formEvento->isValid()){
                    $dados = $formEvento->getData();
                    //caso tenha imagem
                    if(!empty($files['banner_evento']['name'])){
                        //deletar imagem existente
                        if(file_exists($evento['banner_evento'])){
                            unlink($evento['banner_evento']);
                        }

                        //Incluir nova imagem
                        $dir = 'public/arquivos/eventos';
                        $dados['banner_evento'] = $this->uploadImagem($files['banner_evento'], $dir, $dados, $evento['id']);
                        
                    }else{
                        unset($dados['banner_evento']);
                    }
                    //certificado 1
                    if(!empty($files['certificado_1']['name'])){
                        if(file_exists($evento['certificado_1'])){
                            unlink($evento['certificado_1']);
                        }
                        $dir = 'public/arquivos/eventos/certificados';
                        $extensao = $this->getExtensao($files['certificado_1']['name']);
                        if($extensao != 'jpg'){
                            $this->flashMessenger()->addWarningMessage('Favor inserir uma imagem .jpg para o certificado 1!');
                            return $this->redirect()->toRoute('eventoAlterar', array('id' => $evento->id));
                        }
                        $dados['certificado_1'] = $this->uploadImagem($files['certificado_1'], $dir, $dados, $evento['id'].'_1');

                    }else{
                        unset($dados['certificado_1']);
                    }

                    //certificado 2
                    if(!empty($files['certificado_2']['name'])){
                        if(file_exists($evento['certificado_2'])){
                            unlink($evento['certificado_2']);
                        }
                        $dir = 'public/arquivos/eventos/certificados';
                        $extensao = $this->getExtensao($files['certificado_2']['name']);
                        if($extensao != 'jpg'){
                            $this->flashMessenger()->addWarningMessage('Favor inserir uma imagem .jpg para o certificado 2!');
                            return $this->redirect()->toRoute('eventoAlterar', array('id' => $evento->id));
                        }
                        $dados['certificado_2'] = $this->uploadImagem($files['certificado_2'], $dir, $dados, $evento['id'].'_2');

                    }else{
                        unset($dados['certificado_2']);
                    }

                    //certificado 3
                    if(!empty($files['certificado_3']['name'])){
                        if(file_exists($evento['certificado_3'])){
                            unlink($evento['certificado_3']);
                        }
                        $dir = 'public/arquivos/eventos/certificados';
                        $extensao = $this->getExtensao($files['certificado_3']['name']);
                        if($extensao != 'jpg'){
                            $this->flashMessenger()->addWarningMessage('Favor inserir uma imagem .jpg para o certificado 3!');
                            return $this->redirect()->toRoute('eventoAlterar', array('id' => $evento->id));
                        }
                        $dados['certificado_3'] = $this->uploadImagem($files['certificado_3'], $dir, $dados, $evento['id'].'_3');

                    }else{
                        unset($dados['certificado_3']);
                    }

                    $dados['mensagem_cadastro_novo'] = htmlspecialchars($dados['mensagem_cadastro_novo']);
                    $dados['mensagem_cadastro_antigo'] = htmlspecialchars($dados['mensagem_cadastro_antigo']);
                    $dados['mensagem_pagamento'] = htmlspecialchars($dados['mensagem_pagamento']);
                    $dados['mensagem_transferencia'] = htmlspecialchars($dados['mensagem_transferencia']);
                    $dados['observacoes'] = htmlspecialchars($dados['observacoes']);
                    //alterar evento
                    $serviceEvento->update($dados, array('id' => $evento->id));
                    $this->flashMessenger()->addSuccessMessage('Evento alterado com sucesso!');
                    return $this->redirect()->toRoute('eventoAlterar', array('id' => $evento->id));
                    
                }
            }else{
                //caso seja inscrições por categoria
                if(isset($dados->quantidade_maxima_inscritos)){
                    $formQuantidadeInscricoes->setData($dados);
                    if($formQuantidadeInscricoes->isValid()){
                        $dados = $formQuantidadeInscricoes->getData();
                        $dados['evento'] = $idEvento;

                        //html encode
                        $dados['descricao_categoria'] = htmlspecialchars($dados['descricao_categoria']);

                        //alterar
                        if($idQuantidadeInscricao){
                            if($serviceQuantidadeInscricoes->update($dados, array('id' => $idQuantidadeInscricao))){
                                $this->flashMessenger()->addSuccessMessage('Quantidade de inscrições por categoria alterada com sucesso!');
                            }else{
                                $this->flashMessenger()->addErrorMessage('Erro ao alterar quantidade de inscrições por categoria de usuário!');
                            }
                        }else{
                            //inserir
                            $idQuantidadeInscricao = $serviceQuantidadeInscricoes->insert($dados);
                            if($idQuantidadeInscricao){
                                $this->flashMessenger()->addSuccessMessage('Quantidade de inscrições por categoria vinculada com sucesso!');
                            }else{
                                $this->flashMessenger()->addErrorMessage('Erro ao vincular quantidade de inscrições por categoria de usuário!');
                            }
                        }
                        return $this->redirect()->toRoute('eventoAlterar', array('id' => $evento->id));
                    }
                }else{
                    //caso seja valor da inscrição
                    if(isset($dados->valor_inscricao)){
                        $formValor->setData($dados);
                        if($formValor->isValid()){
                            $dados = $formValor->getData();
                            $dados['evento'] = $idEvento;

                            //Validar as datas, pous não podem estar em conflito
                            if(!$this->validarValor($dados)){
                                $this->flashMessenger()->addErrorMessage('Existem períodos em conflito para a mesma categoria!');
                                return $this->redirect()->toRoute('eventoAlterar', array('id' => $evento->id));
                            }
                            //inserir
                            $idValor = $serviceValor->insert($dados);
                            if($idValor){
                                $this->flashMessenger()->addSuccessMessage('Valor da inscrição inserido com sucesso!');
                            }else{
                                $this->flashMessenger()->addErrorMessage('Erro ao inserir valor da inscrição!');
                            }
                            return $this->redirect()->toRoute('eventoAlterar', array('id' => $evento->id));
                        }
                    }else{
                        //caso seja código promocional
                        if(isset($dados->codigo_promocional)){
                            $formPromocao->setData($dados);
                            if($formPromocao->isValid()){
                                $dados = $formPromocao->getData();
                                $dados['evento'] = $idEvento;

                                //alterar
                                if($idPromocao){
                                    if($servicePromocao->update($dados, array('id' => $idPromocao))){
                                        $this->flashMessenger()->addSuccessMessage('Código promocional alterado com sucesso!');
                                    }else{
                                        $this->flashMessenger()->addErrorMessage('Erro ao alterar código promocional!');
                                    }
                                }else{
                                    //inserir
                                    $idPromocao = $servicePromocao->insert($dados);
                                    if($idPromocao){
                                        $this->flashMessenger()->addSuccessMessage('Código promocional inserido com sucesso!');
                                    }else{
                                        $this->flashMessenger()->addErrorMessage('Erro ao inserir código promocional!');
                                    }
                                }
                                return $this->redirect()->toRoute('eventoAlterar', array('id' => $evento->id));
                            }
                        }else{
                            if(isset($dados['status_associado'])){
                                $formPromocaoAssociados->setData($dados);
                                if($formPromocaoAssociados->isValid()){
                                    $dados = $formPromocaoAssociados->getData();
                                    if($idPromocaoAssociado){
                                        //alterar
                                        if($servicePromocaoAssociado->update($dados, array('id' => $idPromocaoAssociado))){
                                            $this->flashMessenger()->addSuccessMessage('Promoção do associado alterada com sucesso!');
                                        }else{
                                            $this->flashMessenger()->addErrorMessage('Erro ao alterar promoção de associados!');
                                        }
                                    }else{
                                        //inserir
                                        $categoriasAssociados = $this->serviceLocator->get('CategoriaAssociado')->getRecordsFromArray(array('empresa' => $evento->empresa, 'ativo' => 'S'), 'nome')->toArray();
                                        $idPromocaoAssociado = $servicePromocaoAssociado->inserir($dados, $quantidadeInscricoes, $categoriasAssociados, $idEvento);
                                        if($idPromocaoAssociado){
                                            $this->flashMessenger()->addSuccessMessage('Promoção de associados inserida com sucesso!');
                                            $this->flashMessenger()->addWarningMessage('Atenção: A promoção só será aplicada caso exista uma anuidade ativa na data de inscrição!');
                                        }else{
                                            $this->flashMessenger()->addErrorMessage('Erro ao inserir promoção de associados!');
                                        }
                                    }
                                    return $this->redirect()->toRoute('eventoAlterar', array('id' => $evento->id));
                                }
                            }else{
                                //caso venha opções e alternativas de um evento
                                if(isset($dados->evento_opcao)){
                                    $dados['descricao'] = htmlspecialchars($dados['descricao']);
                                    $formAlternativa->setData($dados);
                                    if($formAlternativa->isValid()){
                                        //verificar se é inserção ou edição
                                        if($idAlternativa){
                                            //ALTERAR
                                            $serviceOpcoesAlternativa->update($formAlternativa->getData(), array('id' => $idAlternativa));
                                            $this->flashMessenger()->addSuccessMessage('Alternativa alterada com sucesso!');
                                            return $this->redirect()->toRoute('eventoAlterar', array('id' => $evento->id));
                                        }else{
                                            //INSERIR
                                            $serviceOpcoesAlternativa->insert($formAlternativa->getData());
                                            $this->flashMessenger()->addSuccessMessage('Alternativa inserida com sucesso!');
                                            return $this->redirect()->toRoute('eventoAlterar', array('id' => $evento->id));
                                        }
                                    }
                                }   
                            }
                        }
                    }
                }
            }
            
        }

        //pesquisar dados para listagem de alternativas
        $opcoesAlternativas = $serviceOpcoesAlternativa->getQuestoesAlternativas($evento->id);

        //pesquisar dados para listagem de promoções de associados
        $promocoesAssociados = $servicePromocaoAssociado->getPromocoesByEvento($evento->id);
        return new ViewModel(array(
                                'formEvento'               => $formEvento,
                                'evento'                   => $evento,
                                'formQuantidadeInscricoes' => $formQuantidadeInscricoes,
                                'quantidadeInscricoes'     => $quantidadeInscricoes,
                                'formValor'                => $formValor,
                                'valores'                  => $valores,
                                'formPromocao'             => $formPromocao,
                                'promocoes'                => $promocoes,
                                'formAlternativa'          => $formAlternativa,
                                'opcoesAlternativas'       => $opcoesAlternativas,
                                'empresa'                  => $empresa,
                                'formPromocaoAssociados'   => $formPromocaoAssociados,
                                'promocoesAssociados'      => $promocoesAssociados
                            ));
    }

    public function adicionaropcaoAction(){
        $formOpcao = new formOpcao('formOpcao');
        $idEvento = $this->params()->fromRoute('evento');
        $serviceOpcao = $this->getServiceLocator()->get('EventoOpcao');
        
        $idOpcao = $this->params()->fromRoute('opcao');
        $opcao = false;
        if($idOpcao){
            $opcao = $serviceOpcao->getRecord($idOpcao);
            $formOpcao->setData($opcao);
        }
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formOpcao->setData($dados);
            if($formOpcao->isValid()){
                $dados = $formOpcao->getData();
                if($idOpcao){
                    $serviceOpcao->update($dados, array('id' => $idOpcao));
                    $this->flashMessenger()->addSuccessMessage('Opção alterada com sucesso!');
                    $this->redirect()->toRoute('eventoAlterar', array('id' => $idEvento));
                }else{
                    $dados['evento'] = $idEvento;
                    $serviceOpcao->insert($dados);
                    $this->flashMessenger()->addSuccessMessage('Opção inserida com sucesso!');
                    $this->redirect()->toRoute('eventoAlterar', array('id' => $idEvento));
                }
            }
        }

        //pesquisar opções do evento
        $opcoes = $serviceOpcao->getRecords($idEvento, 'evento');

        return new ViewModel(array(
                'formOpcao'     =>  $formOpcao,
                'idEvento'      =>  $idEvento,
                'opcoes'        =>  $opcoes
            ));
    }

    public function deletaropcaoAction(){
        $serviceOpcao = $this->getServiceLocator()->get('EventoOpcao');
        $serviceOpcao->delete(array('id' => $this->params()->fromRoute('id')));
        $this->flashMessenger()->addSuccessMessage('Opção excluída com sucesso!');
        return $this->redirect()->toRoute('opcaoAtividadeNovo', array('evento' => $this->params()->fromRoute('evento')));
    }

    public function deletarquantidadeinscricoesAction(){
        $serviceQuantidadeInscricoes = $this->getServiceLocator()->get('QuantidadeCategoria');
        $serviceQuantidadeInscricoes->delete(array('id' => $this->params()->fromRoute('id')));
        $this->flashMessenger()->addSuccessMessage('Quantidade de inscrição por categoria excluída com sucesso! (obs:. Não impacta em inscrições já realizadas)');
        return $this->redirect()->toRoute('eventoAlterar', array('id' => $this->params()->fromRoute('evento')));
    }

    public function deletarvalorinscricaoAction(){
        $serviceValor = $this->getServiceLocator()->get('ValorEvento');
        $serviceValor->delete(array('id' => $this->params()->fromRoute('id')));
        $this->flashMessenger()->addSuccessMessage('Valor do evento excluído com sucesso!');
        return $this->redirect()->toRoute('eventoAlterar', array('id' => $this->params()->fromRoute('evento')));
    }

    public function deletarpromocaoAction(){
        $servicePromocao = $this->getServiceLocator()->get('PromocaoEvento');
        $servicePromocao->delete(array('id' => $this->params()->fromRoute('id')));
        $this->flashMessenger()->addSuccessMessage('Promoção excluída com sucesso!');
        return $this->redirect()->toRoute('eventoAlterar', array('id' => $this->params()->fromRoute('evento')));
    }

    public function deletarpromocaoassociadoAction(){
        $servicePromocao = $this->getServiceLocator()->get('PromocaoAssociado');
        $servicePromocao->delete(array('id' => $this->params()->fromRoute('id')));
        $this->flashMessenger()->addSuccessMessage('Promoção de associado excluída com sucesso!');
        return $this->redirect()->toRoute('eventoAlterar', array('id' => $this->params()->fromRoute('evento')));
    }

    public function deletaralternativaAction(){
        $serviceAlternativa = $this->getServiceLocator()->get('EventoOpcaoAlternativa');
        $serviceAlternativa->delete(array('id' => $this->params()->fromRoute('id')));
        $this->flashMessenger()->addSuccessMessage('Alternativa excluída com sucesso!');
        return $this->redirect()->toRoute('eventoAlterar', array('id' => $this->params()->fromRoute('evento')));
    }

    public function camposobrigatoriosAction(){
        //pesquisar evento
        $idEvento = $this->params()->fromRoute('id');
        $evento = $this->getServiceLocator()->get('Evento')->getRecord($idEvento);
        $serviceCamposInscricao = $this->getServiceLocator()->get('InscricaoCamposEvento');

        //caso for um post salvar ALTERAÇÕES
        if($this->getRequest()->isPost()){
            $dadosFormulario = $this->getRequest()->getPost();
            $dadosFormulario = $this->prepareParaInserir($dadosFormulario);
            foreach ($dadosFormulario as $index => $alterar) {
                $res = $serviceCamposInscricao->update(array('label' => $alterar['label'], 
                                                      'aparecer' => $alterar['aparecer'], 
                                                      'obrigatorio' => $alterar['obrigatorio']
                                                    ), 
                                                array('id' => $alterar['id']));

                if($res != 1 && $res != 0){
                    $this->flashMessenger()->addErrorMessage('Erro ao alterar configuração do campo '.$index.'!');
                    return $this->redirect()->toRoute('camposObrigatorios', array('id' => $idEvento));
                }
            }
            $this->flashMessenger()->addSuccessMessage('Campos configurados com sucesso!');
            return $this->redirect()->toRoute('camposObrigatorios', array('id' => $idEvento));
        }

        $camposInscricao = $serviceCamposInscricao->getCamposByEvento($idEvento)->toArray();

        //instanciar form dos campos obrigatórios
        $formConfiguracoesInscricao = new formConfiguracoes('formConfiguracoes', $this->getServiceLocator(), $camposInscricao);

        //popular o form de configurações
        

        return new ViewModel(array(
                                'campos' => $formConfiguracoesInscricao,
                                'evento' => $evento
                                ));
    }

    private function prepareParaInserir($camposFormulario){
        $camposTratado = array();
        foreach ($camposFormulario as $indice => $campo) {
            $nomes = explode('1', $indice);
            $camposTratado[$nomes[0]][$nomes[1]] = $camposFormulario[$indice];
        }
        return $camposTratado;
    }

    public function mensagemparticipanteAction(){
        $idEvento = $this->params()->fromRoute('id');
        $serviceMensagens = $this->getServiceLocator()->get('MensagensEvento');
        //instanciar form de mensagem
        $formMensagem = new mensagemForm('formMensagem', $this->getServiceLocator(), $idEvento);

        $erros = array();
        $email = false;
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $dados['evento'] = $idEvento;
            if($dados['evento_cliente_categoria'] == 'T'){
              unset($dados['evento_cliente_categoria']);
            }
            $formMensagem->setData($dados);
            if($formMensagem->isValid()){
                if($serviceMensagens->insert($dados->toArray())){
                    $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
                    $searchParams = array('evento' => $idEvento);
                    if(!empty($dados->evento_cliente_categoria)){
                        //envia apenas para a categoria selecionada
                        $searchParams['cliente_categoria'] = $dados->evento_cliente_categoria;
                    }

                    if(!empty($dados->pago)){
                        //envia apenas para pagos ou não pagos
                        $searchParams['pago'] = $dados->pago;
                    }
                    
                    $inscricoes = $serviceInscricao->getInscricaoMensagens($searchParams);
                    $email = array();
                    $email['contatos'] = array();
                    $email['assunto'] = $dados['assunto'];
                    $email['mensagem'] = html_entity_decode($dados['mensagem']);
                    $email['requests'] = array();
                    $count = 0;
                    $numRequests = 1;

                    foreach ($inscricoes as $inscricao) {
                        if(!empty($inscricao->email)){
                            if($this->validarEmail($inscricao->email)){
                                $count++;
                                $email['contatos'][] = strtolower(trim($inscricao->email));
                                if($count == 50){
                                    $count = 0;
                                    if($numRequests == 1){
                                        $email['requests'][] = array('min' => 0, 'max' => 50);
                                    }else{
                                        $email['requests'][] = array('min' => ($numRequests-1)*50, 'max' => $numRequests*50);
                                    }
                                    $numRequests++;
                                }
                            }else{
                                $erros[$inscricao->cpf] = $inscricao->email;
                            }
                        }else{
                           $erros[$inscricao->cpf] = $inscricao->email;
                        }
                    }
                    
                    if($count > 0){
                        if($numRequests == 1){
                            $email['requests'][] = array('min' => 0, 'max' => $count);
                        }else{
                            $email['requests'][] = array('min' => ($numRequests-1)*50, 'max' => ($numRequests-1)*50+$count);
                        }
                    }
                }
            }
        }

        //pesquisar mensagens enviadas
        $mensagens = $serviceMensagens->getMensagensByEvento($idEvento);

        return new ViewModel(array(
                        'idEvento'         => $idEvento,
                        'mensagens'        => $mensagens,
                        'formMensagem'     => $formMensagem,
                        'listaEmail'       => $email,
                        'erros'            => $erros
                    ));
    }

    public function enviaremailAction(){
        $dadosPost = $this->getRequest()->getPost();
        $dados = $dadosPost['dados'];
        $range = $dados['requests'][$dadosPost['posicao']];
        $destinatarios = array();
        for ($i=$range['min']; $i < $range['max']; $i++) { 
            $destinatarios[] = $dados['contatos'][$i];
        }
        
        $mailer = $this->getServiceLocator()->get('mailer');
        $mailer->mailUserCco($destinatarios, $dados['assunto'], html_entity_decode($dados['mensagem']));
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('status' => true));
        return $view;
    }

    public function replicareventoAction(){
        $idEvento = $this->params()->fromRoute('idEvento');
        $idNovoEvento = $this->getServiceLocator()->get('Evento')->replicarEvento($idEvento);
        if($idNovoEvento){
            $this->flashMessenger()->addSuccessMessage('Evento replicado com sucesso!');
            return $this->redirect()->toRoute('eventoAlterar', array('id' => $idNovoEvento));
        }else{
            $this->flashMessenger()->addErrorMessage('Aconteceu algum erro, por favor tente novamente!');
            return $this->redirect()->toRoute('eventoAlterar', array('id' => $idEvento));
        }

        return new ViewModel();
    }
    
    public function categoriastrabalhoAction(){
        $usuario = $this->getServiceLocator()->get('session')->read();
        $idEvento = $this->params()->fromRoute('idEvento');
        if(!empty($usuario['empresa'])){
            $this->layout('layout/empresa');
            
            //verificar se evento é da empresa
            $evento = $this->getServiceLocator()->get('Evento')->getRecord($idEvento);
            if($evento && $evento['empresa'] != $usuario['empresa']){
                $this->flashMessenger()->addWarningMessage('Evento não encontrado!');
                return $this->redirect()->toRoute('evento');
            }

        }

        $categorias = $this->getServiceLocator()->get('InscricaoTrabalhoCategoria')->getRecords($idEvento, 'evento', array('*'), 'categoria');
        
        return new ViewModel(array(
            'categorias' => $categorias,
            'idEvento'   => $idEvento
        ));
    }

    public function novacategoriatrabalhoAction(){
        $usuario = $this->getServiceLocator()->get('session')->read();
        $idEvento = $this->params()->fromRoute('idEvento');
        if(!empty($usuario['empresa'])){
            $this->layout('layout/empresa');

            //verificar se evento é da empresa
            $evento = $this->getServiceLocator()->get('Evento')->getRecord($idEvento);
            if($evento && $evento['empresa'] != $usuario['empresa']){
                $this->flashMessenger()->addWarningMessage('Evento não encontrado!');
                return $this->redirect()->toRoute('evento');
            }
        }

        $formCategoria = new formCategoria('frmCategoria');

        if($this->getRequest()->isPost()){
            $formCategoria->setData($this->getRequest()->getPost());
            if($formCategoria->isValid()){
                $dados = $formCategoria->getData();
                $dados['evento'] = $idEvento;
                $this->getServiceLocator()->get('InscricaoTrabalhoCategoria')->insert($dados);
                $this->flashMessenger()->addSuccessMessage('Categoria inserida com sucesso!');
                return $this->redirect()->toRoute('categoriasTrabalho', array('idEvento' => $idEvento));
            }
        }

        return new ViewModel(array(
            'formCategoria' => $formCategoria,
            'idEvento'      => $idEvento
        ));

    }

    public function deletarcategoriatrabalhoAction(){
        $usuario = $this->getServiceLocator()->get('session')->read();
        $categoria = $this->getServiceLocator()->get('InscricaoTrabalhoCategoria')->getRecord($this->params()->fromRoute('idCategoria'));
        if(!empty($usuario['empresa'])){
            $this->layout('layout/empresa');

            //verificar se evento é da empresa
            $evento = $this->getServiceLocator()->get('Evento')->getRecord($categoria->evento);
            if($evento && $evento['empresa'] != $usuario['empresa']){
                $this->flashMessenger()->addWarningMessage('Evento não encontrado!');
                return $this->redirect()->toRoute('evento');
            }
        }

        $res = $this->getServiceLocator()->get('InscricaoTrabalhoCategoria')->delete(array('id' => $categoria['id']));
        if($res){
            $this->flashMessenger()->addSuccessMessage('Categoria excluída com sucesso!');
        }else{
            $this->flashMessenger()->addErrorMessage('Erro ao excluir a categoria, por favor tente novamente!');
        }
        return $this->redirect()->toRoute('categoriasTrabalho', array('idEvento' => $categoria['evento']));
    }

    public function cadastrartransmissaoAction(){
        $formTransmissao = new formTransmissao('frmTransmissao');
        $evento = $this->getServiceLocator()->get('Evento')->getRecord($this->params()->fromRoute('evento'));

        //verificar se é empresa
        $usuario = $this->getServiceLocator()->get('session')->read();
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            if($evento->empresa != $usuario['empresa']){
                $this->flashMessenger()->addWarningMessage('Evento não encontrado!');
                return $this->redirect()->toRoute('evento');
            }
        }

        $idTransmissao = $this->params()->fromRoute('transmissao');
        if($idTransmissao){
            $transmissao = $this->getServiceLocator()->get('EventoTransmissao')->getRecord($idTransmissao);
            if($transmissao->evento != $evento->id){
                $this->flashMessenger()->addWarningMessage('Transmissão não encontrada!');
                return $this->redirect()->toRoute('cadastrarTransmissao', array('evento' => $evento->id));
            }
            $formTransmissao->setData($transmissao);
        }
        ///se veio post salvar ou alterar
        if($this->getRequest()->isPost()){
            $formTransmissao->setData($this->getRequest()->getPost());
            if($formTransmissao->isValid()){
                //alterar
                if($idTransmissao){
                    $this->getServiceLocator()->get('EventoTransmissao')->update($formTransmissao->getData(), array('id' => $idTransmissao));
                    $this->flashMessenger()->addSuccessMessage('Transmissão alterada com sucesso!');
                    return $this->redirect()->toRoute('cadastrarTransmissao', array('evento' => $evento->id));
                }else{
                    //salvar
                    $dados = $formTransmissao->getData();
                    $dados['evento'] = $evento->id;
                    $this->getServiceLocator()->get('EventoTransmissao')->insert($dados);
                    $this->flashMessenger()->addSuccessMessage('Transmissão cadastrada com sucesso!');
                    return $this->redirect()->toRoute('cadastrarTransmissao', array('evento' => $evento->id));
                }
            }
        }

        //pesquisar transmissões do evento
        $transmissoes = $this->getServiceLocator()->get('EventoTransmissao')->getRecords($evento->id, 'evento', array('*'), 'inicio ASC, sala');
        return new ViewModel(array(
            'formTransmissao'   =>  $formTransmissao,
            'transmissoes'      =>  $transmissoes,
            'evento'            =>  $evento
        ));
    }

    public function deletartransmissaoAction(){
        //validar se evento e transmissão são da empresa 
        $usuario = $this->getServiceLocator()->get('session')->read();
        if($usuario['id_usuario_tipo'] == 3){
            $evento = $this->getServiceLocator()->get('Evento')->getRecord($this->params()->fromRoute('evento'));
            $transmissao = $this->getServiceLocator()->get('EventoTransmissao')->getRecord($this->params()->fromRoute('transmissao'));

            if($usuario['empresa'] != $evento->empresa || $evento->id != $transmissao->evento){
                $this->flashMessenger()->addWarningMessage('Evento ou transmissão não encontrados!');
                return $this->redirect()->toRoute('evento');
            }
        }

        //deletar a transmissão
        $this->getServiceLocator()->get('EventoTransmissao')->delete(array('id' => $this->params()->fromRoute('transmissao')));
        $this->flashMessenger()->addSuccessMessage('Transmissão excluída com sucesso!');
        return $this->redirect()->toRoute('cadastrarTransmissao', array('evento' => $this->params()->fromRoute('evento')));
    }

    public function uploadImagem($arquivo, $caminho, $dados, $idEvento){
        if(!file_exists($caminho)){
            mkdir($caminho);
        }
        
        if(!empty($arquivo['tmp_name'])){
            $extensao = $this->getExtensao($arquivo['name']);
            if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/'.$idEvento.'.'.$extensao)){
                return $caminho.'/'.$idEvento.'.'.$extensao;
            }
        }
        

        return false;
    }

    private function validarValor($dados){
        $serviceValor = $this->getServiceLocator()->get('ValorEvento');
        $valores = $serviceValor->getValoresPeriodo($dados);

        if($valores->count() > 0){
            return false;
        }else{
            return true;
        }
    }

}

