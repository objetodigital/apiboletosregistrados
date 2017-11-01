<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OBRSDK\HttpClient;

/**
 * Description of RemessasCliente
 *
 * @author Antonio
 */
class RemessasCliente extends Nucleo\Instancia {

    /**
     * 
     * @param \OBRSDK\Entidades\Remessas $remessas
     * @return \OBRSDK\Entidades\Remessas[]
     */
    public function gerarRemessa(\OBRSDK\Entidades\Remessas $remessas) {
        $remessas_dados = $remessas->getAtributes();
        $boletos = [];
        foreach ($remessas_dados['boletos'] as $boleto) {
            $boletos[] = [
                "boleto_id" => $boleto['boleto_id']
            ];
        }
        // remove dados anterior
        unset($remessas_dados['boletos']);
        // seta com dados atualizado
        $remessas_dados['boletos'] = $boletos;

        $resposta = $this->apiCliente->addAuthorization()
                ->postJson('remessas', $remessas_dados)
                ->getRespostaArray();

        $remessasResponse = [];
        // preenche com objeto de resposta
        foreach ($resposta['remessas'] as $remessa) {
            $remessaEntidade = new \OBRSDK\Entidades\Remessas();
            $remessaEntidade->setAtributos($remessa);
            $remessasResponse[] = $remessaEntidade;
        }

        return $remessasResponse;
    }

    /**
     * @param string $remessa_id
     * @return \OBRSDK\Entidades\Remessas
     */
    public function getRemessaPorId($remessa_id) {
        $resposta = $this->apiCliente->addAuthorization()
                        ->get('remessas/' . $remessa_id)->getRespostaArray();

        $remessa_resposta = new \OBRSDK\Entidades\Remessas();
        $remessa_resposta->setAtributos($resposta);

        return $remessa_resposta;
    }

}
