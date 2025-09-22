<?php

namespace Evento\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Evento\Form\PlanilhaCielo as formUpload;
use Evento\Form\RelatorioEmpresa as formEventos;

class RelatoriosController extends BaseController
{

    public function relatorioinscricoesAction()
    {   
        //Pegar id da inscrição na sessão
        $idEvento = $this->params()->fromRoute('evento');

        //pesquisar inscrições
        $usuario = $this->getServiceLocator()->get('session')->read();
        if($usuario['id_usuario_tipo'] == 3){
            //validar se evento é desta empresa
            $evento = $this->getServiceLocator()->get('Evento')->getRecordFromArray(array(
                'id' => $idEvento, 
                'empresa' => $usuario['empresa']
            ));
            if(!$evento){
                $this->flashMessenger()->addWarningMessage('Evento não encontrado!');
                return $this->redirect()->toRoute('relatoriosEmpresa');
            }

        }


        $inscricoes  = $this->getServiceLocator()->get('Inscricao')->getInscricoesExcel($idEvento);

        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Time Sistemas");
        $objPHPExcel->getProperties()->setTitle("Relatório de avaliações");
        $objPHPExcel->getProperties()->setDescription("Relatório gerado pelo sistema de avaliações.");
        $objPHPExcel->getActiveSheet()->setTitle('Simple');

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel = $this->escreverCabecalho($objPHPExcel);

        $linhas = 1;
        $formHelper = new formUpload('formConversao');
        
        foreach ($inscricoes as $inscricao) {
            $linhas++;
            $objPHPExcel = $this->escreverLinha($objPHPExcel, $linhas, $inscricao, $formHelper);
        }
        

        //gerar e redir p download
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $diretorio = 'public/relatorios';
        if(!file_exists($diretorio)){
            mkdir($diretorio);
        }

        $fileName = $diretorio.'/inscricoes.xlsx';
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



    private function escreverCabecalho($objPHPExcel){
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Inscriçao');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'CPF');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'CNPJ');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Nome completo');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Nome no certificado');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Nome no cracha');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Estado civil');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Nacionalidade');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Sexo');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'RG');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'Data de nascimento');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Email');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'Empresa');
        $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'Endereço comercial');
        $objPHPExcel->getActiveSheet()->SetCellValue('O1', 'CEP');
        $objPHPExcel->getActiveSheet()->SetCellValue('P1', 'Cidade');
        $objPHPExcel->getActiveSheet()->SetCellValue('Q1', 'Bairro');
        $objPHPExcel->getActiveSheet()->SetCellValue('R1', 'Rua');
        $objPHPExcel->getActiveSheet()->SetCellValue('S1', 'Número');
        $objPHPExcel->getActiveSheet()->SetCellValue('T1', 'Complemento');
        $objPHPExcel->getActiveSheet()->SetCellValue('U1', 'Profissão');
        $objPHPExcel->getActiveSheet()->SetCellValue('V1', 'Cargo');
        $objPHPExcel->getActiveSheet()->SetCellValue('W1', 'Telefone');
        $objPHPExcel->getActiveSheet()->SetCellValue('X1', 'Celular');
        $objPHPExcel->getActiveSheet()->SetCellValue('Y1', 'Status do pagamento');
        $objPHPExcel->getActiveSheet()->SetCellValue('Z1', 'Categoria');
        $objPHPExcel->getActiveSheet()->SetCellValue('AA1', 'Valor bruto da inscrição');
        $objPHPExcel->getActiveSheet()->SetCellValue('AB1', 'Valor liquido da inscrição');
        $objPHPExcel->getActiveSheet()->SetCellValue('AC1', '% Desconto');
        $objPHPExcel->getActiveSheet()->SetCellValue('AD1', 'Código promocional');
        $objPHPExcel->getActiveSheet()->SetCellValue('AE1', 'Conselho');
        $objPHPExcel->getActiveSheet()->SetCellValue('AF1', 'Outro conselho');
        $objPHPExcel->getActiveSheet()->SetCellValue('AG1', 'Número no conselho');
        $objPHPExcel->getActiveSheet()->SetCellValue('AH1', 'Especialidade');
        $objPHPExcel->getActiveSheet()->SetCellValue('AI1', 'Status do pagamento');
        $objPHPExcel->getActiveSheet()->SetCellValue('AJ1', 'Data de pagamento');
        $objPHPExcel->getActiveSheet()->SetCellValue('AK1', 'Forma de pagamento');
        $objPHPExcel->getActiveSheet()->SetCellValue('AL1', 'Data/hora da inscrição');
        return $objPHPExcel;

    }

    private function escreverLinha($objPHPExcel, $numero_linha, $dados, $form){
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$numero_linha, $dados['id']);
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$numero_linha, $dados['cpf']);
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$numero_linha, $dados['cnpj']);
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$numero_linha, $dados['nome_completo']);
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$numero_linha, $dados['nome_certificado']);
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$numero_linha, $dados['nome_certificado']);
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$numero_linha, $dados['nome_civil']);
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$numero_linha, $dados['nome_nacionalidade']);
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$numero_linha, $dados['sexo']);
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$numero_linha, $dados['rg']);
        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$numero_linha, $form->converterData($dados['data_nascimento']));
        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$numero_linha, $dados['email']);
        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$numero_linha, $dados['nome_empresa']);
        $objPHPExcel->getActiveSheet()->SetCellValue('N'.$numero_linha, $dados['endereco_comercial']);
        $objPHPExcel->getActiveSheet()->SetCellValue('O'.$numero_linha, $dados['cep']);
        $objPHPExcel->getActiveSheet()->SetCellValue('P'.$numero_linha, $dados['nome_cidade'].' - '.$dados['uf']);
        $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$numero_linha, $dados['bairro']);
        $objPHPExcel->getActiveSheet()->SetCellValue('R'.$numero_linha, $dados['nm_rua']);
        $objPHPExcel->getActiveSheet()->SetCellValue('S'.$numero_linha, $dados['numero']);
        $objPHPExcel->getActiveSheet()->SetCellValue('T'.$numero_linha, $dados['complemento']);
        $objPHPExcel->getActiveSheet()->SetCellValue('U'.$numero_linha, $dados['profissao']);
        $objPHPExcel->getActiveSheet()->SetCellValue('V'.$numero_linha, $dados['cargo']);
        $objPHPExcel->getActiveSheet()->SetCellValue('W'.$numero_linha, $dados['telefone']);
        $objPHPExcel->getActiveSheet()->SetCellValue('X'.$numero_linha, $dados['celular']);
        $objPHPExcel->getActiveSheet()->SetCellValue('Y'.$numero_linha, $dados['status_pagamento']);
        $objPHPExcel->getActiveSheet()->SetCellValue('Z'.$numero_linha, $dados['nome_categoria']);
        $objPHPExcel->getActiveSheet()->SetCellValue('AA'.$numero_linha, $dados['valor_bruto']);
        $objPHPExcel->getActiveSheet()->SetCellValue('AB'.$numero_linha, $dados['valor_total']);
        $objPHPExcel->getActiveSheet()->SetCellValue('AC'.$numero_linha, $dados['porcentagem_desconto']);
        $objPHPExcel->getActiveSheet()->SetCellValue('AD'.$numero_linha, $dados['codigo_desconto']);
        $objPHPExcel->getActiveSheet()->SetCellValue('AE'.$numero_linha, $dados['conselho']);
        $objPHPExcel->getActiveSheet()->SetCellValue('AF'.$numero_linha, $dados['outro_conselho']);
        $objPHPExcel->getActiveSheet()->SetCellValue('AG'.$numero_linha, $dados['numero_conselho']);
        $objPHPExcel->getActiveSheet()->SetCellValue('AH'.$numero_linha, $dados['especialidade']);
        $objPHPExcel->getActiveSheet()->SetCellValue('AI'.$numero_linha, $dados['status_pagamento']);
        //tratar data de pagamento
        $data = $dados['data_hora_pagamento2'];
        if(!empty($dados['data_hora_pagamento'])){
          $data = $dados['data_hora_pagamento'];
        }
        $data = explode(' ', $data);
        $objPHPExcel->getActiveSheet()->SetCellValue('AJ'.$numero_linha, $this->converterData($data[0]));
        $objPHPExcel->getActiveSheet()->SetCellValue('AK'.$numero_linha, $dados['nome_forma_pagamento']);
        $objPHPExcel->getActiveSheet()->SetCellValue('AL'.$numero_linha, $form->converterData($dados['data_hora_inscricao']));
        return $objPHPExcel;
    }

    public function relatoriosempresaAction(){
        $this->layout('layout/empresa');
        $usuario = $this->getServiceLocator()->get('session')->read();
        $formEvento = new formEventos('frmEventos', $this->getServiceLocator(), $usuario['empresa']);

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            return $this->redirect()->toRoute('relatorioInscricoes', array('evento' => $dados['evento']));
        }
        return new ViewModel(array(
                'formEvento' => $formEvento
            ));

    }

}
?>