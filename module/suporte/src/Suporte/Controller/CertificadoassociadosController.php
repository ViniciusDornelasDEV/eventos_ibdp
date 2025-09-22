<?php
namespace Suporte\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;
use Suporte\Form\Certificado as formCertificado;
use Suporte\Form\ConfigurarCertificadoAssociados as formConfigurar;

class CertificadoassociadosController extends BaseController
{
    public function downloadAction()
    {   
        $usuario = $this->getServiceLocator()->get('session')->read();
        if($usuario['id_usuario_tipo'] == 2){
            $associado = $this->getServiceLocator()->get('Associado')->getAssociadoByClienteId($usuario['cliente']);
        }else{
            $associado = $this->getServiceLocator()->get('Associado')->getAssociadoByClienteId(false, $this->params()->fromRoute('idCategoria'));
        }

        if(empty($associado['certificado']) || strtotime(date('Y-m-d')) > strtotime($associado['validade_certificado'])){
            die('CERTIFICADO INDISPONÍVEL!');
        }

        return $this->gerarCertificado($associado);
    }

    private function gerarCertificado($associado){
        //Carregar imagem
        $rImg = ImageCreateFromJPEG($associado['certificado']);
        $font = "public/arial.ttf";
        //pesquisar campos
        $campos = $this->getServiceLocator()->get('AssociadoCertificado')->getRecordsFromArray(array('categoria' => $associado['categoria_associado']));
        foreach ($campos as $campo) {
            $texto = mb_strtoupper($associado[strtolower($campo['nome_campo'])]);
            if(!empty($campo['maximo_caracteres']) && $campo['maximo_caracteres'] > 0){
                $texto = substr($texto, 0, $campo['maximo_caracteres']);
            }
            $rgb = $this->hex2rgb($campo['cor']);
            $cor = imagecolorallocate($rImg, $rgb[0], $rgb[1], $rgb[2]);
            //$center = ((imagesx($im)/2)-(strlen($text)/2));
            if($campo['centralizar'] == 'S'){
                $fontwidth = imagefontwidth($campo['tamanho_fonte']);
                $campo['posicao_x'] = (imagesx($rImg)/2) - ($fontwidth*(strlen($evento[strtolower($campo['nome_campo'])])/2));
                $width = imagesx($rImg);
                $bbox = imagettfbbox($campo['tamanho_fonte'], 0, $font, $texto);
                $bbox["width"] = max($bbox[0],$bbox[2],$bbox[4],$bbox[6]) - min($bbox[0],$bbox[2],$bbox[4],$bbox[6]); 
                $campo['posicao_x'] = $width/2-$bbox["width"]/2;
                //imagettftext($rImg, $campo['tamanho_fonte'], 0, , $campo['posicao_y'], $cor, $font, utf8_decode(strtoupper($evento[$campo['nome_campo']])));
            }
            imagettftext($rImg, $campo['tamanho_fonte'], 0, $campo['posicao_x'], $campo['posicao_y'], $cor, $font, $texto);
        }
        //Header e output
        imagejpeg($rImg,"public/certificados/imagemassociado".$associado->id_associado.".jpg",100);
        imagedestroy($rImg);
        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename=imagemassociado'.$associado->id_associado.'.jpg');
        header('Pragma: no-cache');
        readfile('public/certificados/imagemassociado'.$associado->id_associado.'.jpg');
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
        $idCategoria = $this->params()->fromRoute('idCategoria');
        $serviceConfigurar = $this->getServiceLocator()->get('AssociadoCertificado');
        $categoria = $this->getServiceLocator()->get('CategoriaAssociado')->getRecord($idCategoria);
        $formConfigurar = new formConfigurar('frmConfigurar');
        $idCertificado = $this->params()->fromRoute('certificado');

        if($idCertificado){
            $certificado = $serviceConfigurar->getRecord($idCertificado);
            $formConfigurar->setData($certificado);
        }
        if($this->getRequest()->isPost()){
            $formConfigurar->setData($this->getRequest()->getPost());
            if($formConfigurar->isValid()){
                $dados = $formConfigurar->getData();
                $dados['categoria'] = $idCategoria;
                if($idCertificado){
                    $serviceConfigurar->update($dados, array('id' => $idCertificado));
                    $this->flashMessenger()->addSuccessMessage('Configuração alterada com sucesso!');
                }else{
                    $serviceConfigurar->insert($dados);
                    $this->flashMessenger()->addSuccessMessage('Configuração salva com sucesso!');
                }
                return $this->redirect()->toRoute('configurarCertificadoAssociado', array('idCategoria' => $idCategoria));
            }
        }

        $configuracoes = $serviceConfigurar->getRecordsFromArray(array('categoria' => $idCategoria));

        return new ViewModel(array(
            'formConfigurar'    =>  $formConfigurar,
            'configuracoes'     =>  $configuracoes,
            'categoria'         =>  $categoria,
            'numeroCertificado' =>  $numeroCertificado
        ));
    }

    public function deletarconfiguracaoAction(){
        if($this->getServiceLocator()->get('AssociadoCertificado')->delete(array('id' => $this->params()->fromRoute('id')))){
            $this->flashMessenger()->addSuccessMessage('Configuração excluída com sucesso!');
        }else{
            $this->flashMessenger()->addErrorMessage('Ocorreu algum erro, por favor tente novamente!');
        }
        return $this->redirect()->toRoute('configurarCertificadoAssociado', array('idCategoria' => $this->params()->fromRoute('idCategoria')));   
    }
}
