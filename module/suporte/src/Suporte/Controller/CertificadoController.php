<?php



namespace Suporte\Controller;



use Application\Controller\BaseController;

use Zend\View\Model\ViewModel;



use Zend\Paginator\Paginator;

use Zend\Paginator\Adapter\ArrayAdapter;

use Zend\Session\Container;



use Suporte\Form\Certificado as formCertificado;

use Suporte\Form\ConfigurarCertificado as formConfigurar;



class CertificadoController extends BaseController

{



    public function indexAction()

    {   

        $imagem = false;

        $this->layout('layout/login');

        $siglaEvento = $this->params()->fromRoute('siglaEvento');

        $evento = $this->getServiceLocator()->get('Evento')->getRecord($siglaEvento, 'sigla');

        $formCertificado = new formCertificado('frmCertificado');

        $numeroCertificado = $this->params()->fromRoute('numeroCertificado');

        if(empty($numeroCertificado)){

            $numeroCertificado = 1;

        }
        if($this->getRequest()->isPost()){

            $formCertificado->setData($this->getRequest()->getPost());
            if($formCertificado->isValid()){

                $dados = $formCertificado->getData();

                $inscricao = $this->getServiceLocator()->get('Inscricao')
                    ->getInscricoesPagas(array('cpf' => $dados['cpf'], 'evento' => $evento->id))
                    ->current();
                
                if($inscricao){

                    if(empty($inscricao['certificado_'.$numeroCertificado]) || !file_exists($inscricao['certificado_'.$numeroCertificado])){

                        $this->flashMessenger()->addWarningMessage('Certificado ainda não está disponível!');

                        return $this->redirect()->toRoute('buscarCertificado', array('siglaEvento' => $siglaEvento));

                    }
                    $imagem = $this->gerarCertificado($inscricao, $numeroCertificado);

                }else{

                    $this->flashMessenger()->addWarningMessage('CPF não encontrado!');

                    return $this->redirect()->toRoute('buscarCertificado', array('siglaEvento' => $siglaEvento));

                }

            }

        }

        return new ViewModel(array(

            'formCertificado'   => $formCertificado,

            'evento'            => $evento,

            'imagem'            => $imagem

        ));

    }



    private function gerarCertificado($evento, $numeroCertificado){

        //Carregar imagem

        $rImg = ImageCreateFromJPEG($evento['certificado_'.$numeroCertificado]);

        $font = "public/arial.ttf";

        //pesquisar campos
        $campos = $this->getServiceLocator()->get('EventoCertificado')->getRecordsFromArray(array('evento' => $evento->evento, 'certificado' => $numeroCertificado));

        foreach ($campos as $campo) {
            $texto = mb_strtoupper($evento[strtolower($campo['nome_campo'])]);

            if(!empty($campo['maximo_caracteres']) && $campo['maximo_caracteres'] > 0){

                $texto = substr($texto, 0, $campo['maximo_caracteres']);

            }

            $rgb = $this->hex2rgb($campo['cor']);

            $cor = imagecolorallocate($rImg, $rgb[0], $rgb[1], $rgb[2]);

            

            //$center = ((imagesx($im)/2)-(strlen($text)/2));

            if($campo['centralizar'] == 'S'){
                $bbox = imagettfbbox($campo['tamanho_fonte'], 0, $font, $texto);
                $textWidth = abs($bbox[2] - $bbox[0]);
                $campo['posicao_x'] = (imagesx($rImg) - $textWidth) / 2;
            }

            imagettftext($rImg, $campo['tamanho_fonte'], 0, $campo['posicao_x'], $campo['posicao_y'], $cor, $font, $texto);

        }

        //Header e output

        //header('Content-type: image/jpeg');

        imagejpeg($rImg,"public/certificados/imagem".$evento->id_cliente.".jpg",100);

        imagedestroy($rImg);

        //echo "window.open('http://eventostime.com.br/public/certificados/imagem'.$evento->id_cliente.'.jpg', 'Certificado');";

        //file_put_contents("certificado.jpg", fopen('http://eventos.local/public/certificados/imagem'.$evento->id_cliente.'.jpg', 'r'));



        //return "/certificados/imagem".$evento->id_cliente.".jpg";

        //header("Content-disposition: attachment; filename=imagem".$evento->id_cliente.".jpg");

        //header('Content-Description: File Transfer');

        //readfile("imagem.jpg");

        header('Content-Type: image/jpeg');

        header('Content-Disposition: attachment; filename=imagem'.$evento->id_cliente.'.jpg');

        header('Pragma: no-cache');

        readfile('public/certificados/imagem'.$evento->id_cliente.'.jpg');



    }



    private function hex2rgb($hex){

        $hex = ltrim($hex, '#');



        if(strlen($hex) == 3)

            return [

                hexdec($hex[0].$hex[0]),

                hexdec($hex[1].$hex[1]),

                hexdec($hex[2].$hex[2]),

            ];

        else

            return [

                hexdec($hex[0].$hex[1]),

                hexdec($hex[2].$hex[3]),

                hexdec($hex[4].$hex[5]),

            ];

    }



    public function configurarcertificadoAction(){

        $idEvento = $this->params()->fromRoute('evento');

        $serviceConfigurar = $this->getServiceLocator()->get('EventoCertificado');

        $evento = $this->getServiceLocator()->get('Evento')->getRecord($idEvento);

        $formConfigurar = new formConfigurar('frmConfigurar');

        

        $numeroCertificado = $this->params()->fromRoute('numerocertificado');

        $idCertificado = $this->params()->fromRoute('certificado');

        if($idCertificado){

            $certificado = $serviceConfigurar->getRecord($idCertificado);

            $formConfigurar->setData($certificado);

        }



        if($this->getRequest()->isPost()){

            $formConfigurar->setData($this->getRequest()->getPost());

            if($formConfigurar->isValid()){

                $dados = $formConfigurar->getData();

                $dados['evento'] = $idEvento;

                $dados['certificado'] = $numeroCertificado;

                if($idCertificado){

                    $serviceConfigurar->update($dados, array('id' => $idCertificado));

                    $this->flashMessenger()->addSuccessMessage('Configuração alterada com sucesso!');

                }else{

                    $serviceConfigurar->insert($dados);

                    $this->flashMessenger()->addSuccessMessage('Configuração salva com sucesso!');

                }

                return $this->redirect()->toRoute('configurarCertificado', array('evento' => $idEvento, 'numerocertificado' => $numeroCertificado));

            }

        }



        $configuracoes = $serviceConfigurar->getRecordsFromArray(array('evento' => $idEvento, 'certificado' => $numeroCertificado));



        return new ViewModel(array(

            'formConfigurar'    =>  $formConfigurar,

            'configuracoes'     =>  $configuracoes,

            'evento'            =>  $evento,

            'numeroCertificado' =>  $numeroCertificado

        ));

    }



    public function deletarconfiguracaoAction(){

        if($this->getServiceLocator()->get('EventoCertificado')->delete(array('id' => $this->params()->fromRoute('id')))){

            $this->flashMessenger()->addSuccessMessage('Configuração excluída com sucesso!');

        }else{

            $this->flashMessenger()->addErrorMessage('Ocorreu algum erro, por favor tente novamente!');

        }

        return $this->redirect()->toRoute('configurarCertificado', array('evento' => $this->params()->fromRoute('evento')));   

    }



}



