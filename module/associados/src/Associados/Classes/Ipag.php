<?php
namespace Associados\Classes;

class Ipag {
    /*private $baseUrl = 'https://sandbox.ipag.com.br';
    private $apiId = 'vinicius.s.dornelas@gmail.com';
    private $apiKey = '38A8-E8DA5F21-5CC18BA9-5022FC77-2F60';
    private $callbackUrlAssociado = 'https://7b0a-2804-15b4-101-713-dc78-49a5-bc24-a7db.ngrok-free.app/associado/pagamento/ipag/retorno';
    private $callbackUrlInscricao = 'https://7b0a-2804-15b4-101-713-dc78-49a5-bc24-a7db.ngrok-free.app/evento/pagamento/ipag/retorno';*/

    private $baseUrl = 'https://api.ipag.com.br';
    private $apiId = 'sbdof@sbdof.com.br';
    private $apiKey = 'BE76-01104433-A59548CE-014545F7-5AA3';
    private $callbackUrlAssociado = 'https://eventossbdof.com.br/associado/pagamento/ipag/retorno';
    private $callbackUrlInscricao = 'https://eventossbdof.com.br/evento/pagamento/ipag/retorno';
    private $serviceLocator;
    private $response;
    private $httpCode;

    public function __construct($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getHttpCode() 
    {
        return $this->httpCode;
    }
    public function realizarPagamentoAssociado($dadosPagamento, $associadoIpag)
    {
        $dadosPagamento['callback_url'] = $this->callbackUrlAssociado;
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->baseUrl.'/service/payment',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($dadosPagamento),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'x-api-version: 2',
            'Authorization: Basic '.base64_encode($this->apiId.':'.$this->apiKey)
        ),
        ));

        $this->response = json_decode(curl_exec($curl));
        $this->httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_errno($curl) == 0) {
            switch ($this->httpCode) {
                case 200:
                    return true;
                break;
                default:
                return false;
            }
        }
        curl_close($curl);
        return false;
    }

    public function realizarPagamentoInscricao($dadosPagamento, $inscricao)
    {
        $dadosPagamento['callback_url'] = $this->callbackUrlInscricao;
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->baseUrl.'/service/payment',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($dadosPagamento),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'x-api-version: 2',
            'Authorization: Basic '.base64_encode($this->apiId.':'.$this->apiKey)
        ),
        ));

        $this->response = json_decode(curl_exec($curl));
        $this->httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_errno($curl) == 0) {
            switch ($this->httpCode) {
                case 200:
                    return true;
                break;
                default:
                return $this->response;
            }
        }
        curl_close($curl);
        return false;
    }

    public function capturarRetornoInscricao($callback) 
    {
        $pago = $this->transacaoPaga($callback->attributes->status->code);
        $idInscricao = str_replace('i-', '', $callback->attributes->order_id);
        $inscricao = $this->serviceLocator->get('Inscricao')->getRecord($idInscricao);

        if ($pago['status'] == true) {
            $result = $this->serviceLocator->get('Inscricao')->pagarIpag($callback, $inscricao);
        }
        return $callback;
    }

    public function capturarRetornoAssociado($callback) {
        $pago = $this->transacaoPaga($callback->attributes->status->code);
        return $this->serviceLocator
            ->get('AssociadoIpag')
            ->atualizarPagamentoAssociado($callback, $pago);
    }

    public function getMensagemByCodigo($codigo) 
    {
        $mensagens = [
            1 => 'Iniciado',
            2 => 'Boleto Impresso',
            3 => 'Cancelado',
            4 => 'Em análise',
            5 => 'Pré-Autorizado',
            6 => 'Autorizado Valor Parcial',
            7 => 'Recusado',
            8 => 'Aprovado e Capturado',
            9 => 'Chargeback',
            10 => 'Em Disputa'
        ];
    
        return $mensagens[$codigo];
    }

    public function httpCodeMessage()
    {
        switch ($this->httpCode) {
            case 200:
                return 'Pagamento criado';
            break;
            case 401:
                return 'Erro de autenticação';
            case 403:
                return 'Não Autorizado. Sua conta não tem permissão para realizar essa ação';
            case 406:
                return 'Não aceito. Algum dado pode ser inválido verifique os dados enviados no body';
            default:
            return 'Erro ao realizar transação';
        }
    }



    public function transacaoPaga($codigoIpag)
    {
        if($codigoIpag == 8) {
            return array(
                'msg'    => 'Capturado',
                'status' => true
            );    
        }

        if($codigoIpag == 3 || $codigoIpag == 7) {
            return array(
                'msg'    => 'Cancelado ou recusado',
                'status' => false
            ); 
        }

        return array(
            'msg'    => 'Aguardando',
            'status' => false
        ); 
    }
}