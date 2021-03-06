<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OBRSDK\HttpClient\Nucleo;

/**
 * Description of ObjetoBoletosRegistradosCliente
 *
 * @author Antonio
 */
class HttpCliente implements \OBRSDK\HttpClient\Interfaces\ICoreCliente {

    /**
     * Base URL para acesso a API boletos registrados
     */
    static $BASE_URL = 'http://www.apiboletos.com.br/api.php/';

    /**
     * Versão da API que esse SDK trabalha
     */
    const API_VERSION = 'v1';

    /**
     * Interface de acesso
     * @var \GuzzleHttp\Client 
     */
    private $client;
    private static $instance = null;

    public function __construct() {
        if (self::$instance == null) {
            self::$instance = $this;
        }

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => self::$BASE_URL . self::API_VERSION . '/'
        ]);
    }

    private $headers = [];
    private $response;
    private $requestCalling = false;

    /**
     * 
     * @return \OBRSDK\HttpClient\Nucleo\HttpCliente
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new HttpCliente();
        }

        return self::$instance;
    }

    /**
     * Adiciona um header para a requisicao
     * 
     * @param string $nome
     * @param string $valor
     * @return \OBRSDK\HttpClient\Nucleo\HttpCliente
     */
    public function addHeader($nome, $valor) {
        $this->headers[$nome] = $valor;
        return $this;
    }

    /**
     * Envia um POST para API com JSON no body application/json
     * 
     * @param string $endpoint
     * @param array $body
     * @return \OBRSDK\HttpClient\Nucleo\HttpCliente
     */
    public function postJson($endpoint, array $body = null) {
        $this->post($endpoint, $body != null ? ['json' => $body] : null);
        return $this;
    }

    /**
     * Envia um POST para a API com parametros application/x-www-form-urlencoded
     * @param string $endpoint
     * @param array $param
     * @return \OBRSDK\HttpClient\Nucleo\HttpCliente
     */
    public function postParam($endpoint, array $param = null) {
        $this->post($endpoint, $param != null ? ['form_params' => $param] : null);
        return $this;
    }

    /**
     * Envia um GET para API
     * 
     * @param string $endpoint
     * @param array $queryString
     * @return \OBRSDK\HttpClient\Nucleo\HttpCliente
     */
    public function get($endpoint, array $queryString = null) {
        $this->request('GET', $endpoint, $queryString != null ? ['query' => $queryString] : null);
        return $this;
    }

    public function enviarArquivo($endpoint, $arquivo) {
        $this->upload($endpoint, $arquivo);
        return $this;
    }

    private function post($uri, array $post = null) {
        $this->request('POST', $uri, $post);
    }

    public function delete($endpoint) {
        $this->request('DELETE', $endpoint);
        return $this;
    }

    public function patchJson($endpoint, array $body = null) {
        $this->request('PATCH', $endpoint, $body != null ? ['json' => $body] : null);
        return $this;
    }

    public function putJson($endpoint, array $body = null) {
        $this->request('PUT', $endpoint, $body != null ? ['json' => $body] : null);
        return $this;
    }

    public function getResposta($assoc = false) {
        $this->requestCalling = false;
        $decoded = json_decode($this->response, $assoc);

        if ($decoded === false) {
            throw new \OBRSDK\Exceptions\RespostaException(json_encode([
                "mensagem" => "Não foi possivel fazer a leitura da resposta",
                "body" => $this->response
            ]));
        }

        return $decoded;
    }

    private function upload($uri, $arquivo) {
        if (!file_exists($arquivo)) {
            throw new \Exception("Arquivo {$arquivo} não encontrado para ser enviado");
        }

        $this->request('POST', $uri, ["__uploadfile__" => $arquivo]);
    }

    private function request($type, $uri, $data = null) {
        if ($this->requestCalling) {
            throw new \Exception("Não é possivel fazer uma requisicão sem obter a resposta da anterior");
        }

        $dataObjeto = new ApiData($data);
        $dataObjeto->addHeaders($this->headers);
        $this->headers = [];

        $requisicao = new ApiRequisicao($this->client, $type, $uri, $dataObjeto);
        $this->response = $requisicao->getRespostaConteudo();
        $this->requestCalling = true;
    }

    public static function setBaseUrl($url){
        self::$BASE_URL =$url;
    }

}
