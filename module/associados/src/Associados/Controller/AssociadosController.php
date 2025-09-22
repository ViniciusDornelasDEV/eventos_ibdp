<?php

namespace Associados\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Associados\Form\Associado as formAssociado;
use Associados\Form\PesquisarAssociado as formPesquisa;
use Associados\Form\ImportarAssociados as formUpload;
use Associados\Form\ArquivosAssociados as formArquivosAssociado;

class AssociadosController extends BaseController
{

    public function indexAction()
    {   
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }
        //pesquisar empresas
        $serviceAssociado = $this->getServiceLocator()->get('Associado');
        
        $formPesquisa = new formPesquisa('formPesquisa', $this->getServiceLocator(), $empresa);

        $container = new Container();
        if(!isset($container->dados)){
            $container->dados = array();
        }
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados['limpar'])){
                $container->dados = array();
                $this->redirect()->toRoute('listaAssociados');
            }else{
                $formPesquisa->setData($dados);
                if($formPesquisa->isValid()){
                    $container->dados = $formPesquisa->getData();
                }
                
            }
        }

        if($empresa){
            $container->dados['empresa'] = $empresa;
        }
        $formPesquisa->setData($container->dados);
        $associados = $serviceAssociado->getAssociados($container->dados);

        //paginação
        $paginator = new Paginator(new ArrayAdapter($associados->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);

        return new ViewModel(array(
                    'associados'     => $paginator,
                    'formPesquisa'   => $formPesquisa,
                    'empresa'        => $empresa
                ));
    }

    public function novoAction()
    { 
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }
        $formAssociado = new formAssociado('formAssociado', $this->getServiceLocator(), true, $empresa);
        
        if($this->getRequest()->isPost()){
            $formAssociado->setData($this->getRequest()->getPost());
            if($formAssociado->isValid()){
                $serviceAssociado = $this->getServiceLocator()->get('Associado');
                $dados = $formAssociado->getData();

                if($empresa){
                    $dados['empresa'] = $empresa;
                }
                $result = $serviceAssociado->inserirAssociado($dados);
                if($result['status']){
                    $this->flashMessenger()->addSuccessMessage($result['msg']);
                    return $this->redirect()->toRoute('associadosAlterar', array('id' => $result['status'])); 
                }else{
                    $this->flashMessenger()->addErrorMessage($result['msg']);
                    return $this->redirect()->toRoute('associadosNovo');
                }
            }
        }

        return new ViewModel(array('formAssociado' => $formAssociado));
    }

    public function alterarAction(){
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        $formAssociado = new formAssociado('formAssociado', $this->getServiceLocator(), true, $empresa);
        $serviceAssociado = $this->getServiceLocator()->get('Associado');
        $idAssociado = $this->params()->fromRoute('id');
        $associado = $serviceAssociado->getAssociado($idAssociado);
        
        $formAssociado->setData($associado);

        //arquivos do associado
        $serviceArquivos = $this->getServiceLocator()->get('AssociadoArquivoIndividual');
        $formArquivo = new formArquivosAssociado('frmArquivo');

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados['descricao'])){
                $formArquivo->setData($dados);
                if($formArquivo->isValid()){
                    //upload da imagem e salvar em banco
                    $dados = $formArquivo->getData();
                    $files = $this->getRequest()->getfiles()->toArray();
                    $idArquivo = $serviceArquivos->getNextInsertId('tb_associado_arquivo_individual');
                    $dados = $this->uploadArquivoAssociado($files, $idAssociado, $idArquivo->Auto_increment, $dados);
                    $dados['associado'] = $idAssociado;
                    $serviceArquivos->insert($dados);
                    $this->flashMessenger()->addSuccessMessage('Arquivo vinculado com sucesso!');
                    return $this->redirect()->toRoute('associadosAlterar', array('id' => $idAssociado)); 
                }

            }else{
                $formAssociado->setData($dados);
                if($formAssociado->isValid()){
                    $dados = $formAssociado->getData();
                    if($empresa){
                        $dados['empresa'] = $empresa;
                    }
                    $result = $this->getServiceLocator()->get('Associado')->atualizarAssociado($dados, $associado);
                    if($result){
                        $this->flashMessenger()->addSuccessMessage('Associado alterado com sucesso!');
                    }else{
                        $this->flashMessenger()->addErrorMessage('Ocorreu algum erro ao alterar o associado, por favor tente novamente!');
                    }
                    return $this->redirect()->toRoute('associadosAlterar', array('id' => $idAssociado)); 
                }
            }
        }

        $arquivos = $serviceArquivos->getRecords($idAssociado, 'associado', array('*'), 'descricao');

        return new ViewModel(array(
            'formAssociado' => $formAssociado,
            'formArquivo'   => $formArquivo,
            'arquivos'      => $arquivos,
            'idAssociado'   => $idAssociado,
            'associado'     => $associado
        ));
    }

    public function downloadarquivoindividualAction(){
        $serviceArquivos = $this->getServiceLocator()->get('AssociadoArquivoIndividual');
        $arquivo = $serviceArquivos->getRecord($this->params()->fromRoute('idArquivo'));
        $fileName = $arquivo->arquivo;
        if(!is_file($fileName)) {
            //Não foi possivel encontrar o arquivo
        }

        //se cliente, verificar se o arquivo é realmente dele!
        $usuario = $this->getServiceLocator()->get('session')->read();
        if($usuario['id_usuario_tipo'] == 2){
            $associado = $this->getServiceLocator()->get('Associado')->getAssociados(array('cliente' => $usuario['cliente']))->current();
            if($associado['id'] != $arquivo['associado']){
                $this->flashMessenger()->addWarningMessage('Arquivo nã encontrado!');
                return $this->redirect()->toRoute('arquivosCliente');
            }
        }

        $fileContents = file_get_contents($fileName);

        $response = $this->getResponse();
        $response->setContent($fileContents);

        $headers = $response->getHeaders();
        $headers->clearHeaders()
            ->addHeaderLine('Content-Type', 'whatever your content type is')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->addHeaderLine('Content-Length', strlen($fileContents));
        return $this->response;
    }

    public function deletararquivoAction(){
        $serviceArquivos = $this->getServiceLocator()->get('AssociadoArquivoIndividual');
        $idArquivo = $this->params()->fromRoute('idArquivo');
        $idAssociado = $this->params()->fromRoute('idAssociado');
        $arquivo = $serviceArquivos->getRecord($idArquivo);
        if(!$arquivo){
            $this->flashMessenger()->addWarningMessage('Arquivo não encontrado!');
            return $this->redirect()->toRoute('associadosAlterar', array('id' => $idAssociado));
        }
        unlink($arquivo['arquivo']);
        $serviceArquivos->delete(array('id' => $idArquivo));
        $this->flashMessenger()->addSuccessMessage('Arquivo excluído com sucesso!');
        return $this->redirect()->toRoute('associadosAlterar', array('id' => $idAssociado));
    }


    public function carregarcategoriaassociadoAction(){   
        $params = $this->getRequest()->getPost();
        //instanciar form
        $formAssociado = new formAssociado('formAssociado', $this->getServiceLocator(), $params->required);

        $categoria = $formAssociado->setCategoriasByEmpresa($params->empresa, $params->todos);
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('categorias' => $categoria));
        return $view;
    }

    public function importarassociadosAction(){
        
        $formUpload = new formUpload('formUpload', $this->getServiceLocator());
        $numeroAssociados = 0;
        $serviceAssociado = $this->getServiceLocator()->get('Associado');
        if($this->getRequest()->isPost()){
            $files = $this->getRequest()->getfiles()->toArray();
            $arquivo = $this->uploadImagem($files);
            
            $objReader = new \PHPExcel_Reader_Excel5();
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($arquivo);
            $objPHPExcel->setActiveSheetIndex(0);

            $dados = $this->getRequest()->getPost();
            $serviceAssociado = $this->getServiceLocator()->get('Associado');
            $numeroAssociados = $serviceAssociado->importarAssociados($objPHPExcel, $dados);
            

        }
        return new ViewModel(array(
                'formUpload'        => $formUpload,
                'numeroAssociados'  => $numeroAssociados,
                'msg'               => $serviceAssociado->msg
            ));
    }

    public function exportarassociadosAction(){
        //verificar se é empresa
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $empresa = $usuario['empresa'];
        }

        //pesquisar associados
        $associados = $this->getServiceLocator()->get('Associado')->exportarAssociados($empresa);

        $preparedArray = array();
        $idAssociado = false;
        
        foreach ($associados as $associado) {
            if($idAssociado != $associado['id_associado']){
                $idAssociado = $associado['id_associado'];
                $preparedArray[$idAssociado] = $associado;
                $preparedArray[$idAssociado]['anuidades'] = array();
            }

            //se data_vencimento < ano_cadastro PAGO
            $data = explode('-', $associado['data_vencimento']);
            if(strtotime($data[0]) < strtotime($associado['ano_cadastro'])){
              $preparedArray[$idAssociado]['anuidades'][] = array(
                  'descricao_anuidade'      =>  $associado['descricao_anuidade'],
                  'id_pagamento'            =>  1,
                  'nome_forma'              =>  'Não se aplica',
                  'data_pagamento'          =>  '',
                  'data_baixa'              =>  '',
                  'observacoes_comprovante' =>  '',
                  'valor'                   =>  '0'
              );
            }else{
              $preparedArray[$idAssociado]['anuidades'][] = array(
                  'descricao_anuidade'      =>  $associado['descricao_anuidade'],
                  'id_pagamento'            =>  $associado['id_pagamento'],
                  'nome_forma'              =>  $associado['nome_forma'],
                  'data_pagamento'          =>  $associado['data_pagamento'],
                  'data_baixa'              =>  $associado['data_baixa'],
                  'observacoes_comprovante' =>  $associado['observacoes_comprovante'],
                  'valor'                   =>  $associado['valor_pagamento']
              );
            }
        }
        
        //gerar relatório
        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Time Sistemas");
        $objPHPExcel->getProperties()->setTitle("Associados");
        $objPHPExcel->getProperties()->setDescription("Relatório gerado pelo sistema de eventos.");
        $objPHPExcel->getActiveSheet()->setTitle('Associados');
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Empresa');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Nome completo');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Nome no certificado');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Nome no crachá');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'CPF');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'RG');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Telefone');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Celular');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Email');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Data de nascimento');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'CEP');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Cidade');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'Estado');
        $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'Bairro');
        $objPHPExcel->getActiveSheet()->SetCellValue('O1', 'Rua');
        $objPHPExcel->getActiveSheet()->SetCellValue('P1', 'Número');
        $objPHPExcel->getActiveSheet()->SetCellValue('Q1', 'Complemento');
        $objPHPExcel->getActiveSheet()->SetCellValue('R1', 'Estado civil');
        $objPHPExcel->getActiveSheet()->SetCellValue('S1', 'Nacionalidade');
        $objPHPExcel->getActiveSheet()->SetCellValue('T1', 'Sexo');
        $objPHPExcel->getActiveSheet()->SetCellValue('U1', 'Conselho');
        $objPHPExcel->getActiveSheet()->SetCellValue('V1', 'Nº do conselho');
        $objPHPExcel->getActiveSheet()->SetCellValue('W1', 'Especialidade');
        $objPHPExcel->getActiveSheet()->SetCellValue('X1', 'Profissão');
        $objPHPExcel->getActiveSheet()->SetCellValue('Y1', 'Cargo');
        $objPHPExcel->getActiveSheet()->SetCellValue('Z1', 'Categoria');
        $objPHPExcel->getActiveSheet()->SetCellValue('AA1', 'Status');
        $numColuna = 26;
        $associado = $preparedArray[key($preparedArray)];

        foreach ($associado['anuidades'] as $value) {
            $numColuna++;    
            $objPHPExcel->getActiveSheet()->SetCellValue(parent::getExcelLetter($numColuna).'1', 'Anuidade');
            $numColuna++;    
            $objPHPExcel->getActiveSheet()->SetCellValue(parent::getExcelLetter($numColuna).'1', 'Status');
            $numColuna++;    
            $objPHPExcel->getActiveSheet()->SetCellValue(parent::getExcelLetter($numColuna).'1', 'Forma de pagamento');
            $numColuna++;    
            $objPHPExcel->getActiveSheet()->SetCellValue(parent::getExcelLetter($numColuna).'1', 'Data de pagamento');
            $numColuna++;
            $objPHPExcel->getActiveSheet()->SetCellValue(parent::getExcelLetter($numColuna).'1', 'Data de baixa');
            $numColuna++;    
            $objPHPExcel->getActiveSheet()->SetCellValue(parent::getExcelLetter($numColuna).'1', 'Valor da anuidade');
            $numColuna++;    
            $objPHPExcel->getActiveSheet()->SetCellValue(parent::getExcelLetter($numColuna).'1', 'Observacoes');
        }

        $numLinha = 1;
        foreach ($preparedArray as $associado) {
            $numLinha++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$numLinha, $associado['nome_fantasia']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$numLinha, $associado['nome_completo']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$numLinha, $associado['nome_certificado']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$numLinha, $associado['nome_cracha']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$numLinha, $associado['cpf'].' '.$associado['cnpj']);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$numLinha, $associado['rg']);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$numLinha, $associado['telefone']);
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$numLinha, $associado['celular']);
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$numLinha, $associado['email']);
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$numLinha, $associado['data_nascimento']);
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$numLinha, $associado['cep']);
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$numLinha, $associado['nome_cidade']);
            $objPHPExcel->getActiveSheet()->SetCellValue('M'.$numLinha, $associado['nome_estado']);
            $objPHPExcel->getActiveSheet()->SetCellValue('N'.$numLinha, $associado['bairro']);
            $objPHPExcel->getActiveSheet()->SetCellValue('O'.$numLinha, $associado['nm_rua']);
            $objPHPExcel->getActiveSheet()->SetCellValue('P'.$numLinha, $associado['numero']);
            $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$numLinha, $associado['complemento']);
            $objPHPExcel->getActiveSheet()->SetCellValue('R'.$numLinha, $associado['nome_estado_civil']);
            $objPHPExcel->getActiveSheet()->SetCellValue('S'.$numLinha, $associado['nome_nacionalidade']);
            $objPHPExcel->getActiveSheet()->SetCellValue('T'.$numLinha, $associado['sexo']);
            $objPHPExcel->getActiveSheet()->SetCellValue('U'.$numLinha, $associado['conselho']);
            $objPHPExcel->getActiveSheet()->SetCellValue('V'.$numLinha, $associado['numero_conselho']);
            $objPHPExcel->getActiveSheet()->SetCellValue('W'.$numLinha, $associado['especialidade']);
            $objPHPExcel->getActiveSheet()->SetCellValue('X'.$numLinha, $associado['profissao']);
            $objPHPExcel->getActiveSheet()->SetCellValue('Y'.$numLinha, $associado['cargo']);
            $objPHPExcel->getActiveSheet()->SetCellValue('Z'.$numLinha, $associado['nome_categoria']);
            $objPHPExcel->getActiveSheet()->SetCellValue('AA'.$numLinha, $associado['status_associado']);

            $numColuna = 26;
            foreach ($associado['anuidades'] as $anuidade) {
                $numColuna++;    
                $objPHPExcel->getActiveSheet()->SetCellValue(parent::getExcelLetter($numColuna).$numLinha, $anuidade['descricao_anuidade']);
                $numColuna++;  
                $status = 'Inadimplente';
                if(!empty($anuidade['id_pagamento'])){
                    $status = 'Adimplente';
                }  
                $objPHPExcel->getActiveSheet()->SetCellValue(parent::getExcelLetter($numColuna).$numLinha, $status);
                $numColuna++;
                //FORMA DE PAGAMENTO
                $objPHPExcel->getActiveSheet()->SetCellValue(parent::getExcelLetter($numColuna).$numLinha, $anuidade['nome_forma']);
                $numColuna++;
                //DATA DE PAGAMENTO
                $data = explode(' ', $anuidade['data_pagamento']);
                $objPHPExcel->getActiveSheet()->SetCellValue(parent::getExcelLetter($numColuna).$numLinha, $this->converterData($data[0]));
                $numColuna++;
                $objPHPExcel->getActiveSheet()->SetCellValue(parent::getExcelLetter($numColuna).$numLinha, $this->converterData($anuidade['data_baixa']));
                $numColuna++;
                //VALOR PAGO
                $objPHPExcel->getActiveSheet()->SetCellValue(parent::getExcelLetter($numColuna).$numLinha, $this->exibirMonetario($anuidade['valor']));
                $numColuna++;
                $objPHPExcel->getActiveSheet()->SetCellValue(parent::getExcelLetter($numColuna).$numLinha, $anuidade['observacoes_comprovante']);
            }
        }

        //gerar e redir p download
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $diretorio = 'public/relatorios';
        if(!file_exists($diretorio)){
            mkdir($diretorio);
        }

        $fileName = $diretorio.'/associados.xlsx';
        $objWriter->save($fileName);
        
        //enviar para download
        $fileContents = file_get_contents($fileName);

        $response = $this->getResponse();
        $response->setContent($fileContents);

        $headers = $response->getHeaders();
        $headers->clearHeaders()
            ->addHeaderLine('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->addHeaderLine('Content-Length', strlen($fileContents));
        return $this->response;


    }

    public function uploadImagem($arquivos){
        $caminho = 'public/arquivos';

        foreach ($arquivos as $nomeArquivo => $arquivo) {
            if(!empty($arquivo['tmp_name'])){
                $extensao = $this->getExtensao($arquivo['name']);
                if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/associados.'.$extensao)){
                    $dados[$nomeArquivo] = $caminho.'/associados.'.$extensao;
                }
            }
        }

        return $caminho.'/associados.'.$extensao;
    }

    public function uploadArquivoAssociado($arquivos, $idAssociado, $idArquivo, $dados){
        $caminho = 'public/arquivos/associados';
        if(!file_exists($caminho)){
            mkdir($caminho);
        }

        $caminho .= '/'.$idAssociado;
        if(!file_exists($caminho)){
            mkdir($caminho);
        }        

        foreach ($arquivos as $nomeArquivo => $arquivo) {
            if(!empty($arquivo['tmp_name'])){
                $extensao = $this->getExtensao($arquivo['name']);
                if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/'.$idArquivo.'.'.$extensao)){
                    $dados[$nomeArquivo] = $caminho.'/'.$idArquivo.'.'.$extensao;
                }
            }
        }

        return $dados;
    }



}

