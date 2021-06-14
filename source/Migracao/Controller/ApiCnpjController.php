<?php

namespace F360\Migracao\Controller;

use F360\Migracao\Pipeline\FileReader;

class ApiCnpjController
{
    /**
     * Página inicial da API.
     */
    public static function index($request, $response, array $args)
    {
        $payload = json_encode([
            'NAME' => 'F360 - API CNPJ',
            'VERSION' => '1.0.0',
            'STATUS' => 'WORKING'
        ]);

        $response->getBody()->write($payload);
       
        $response = $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        return $response;
    }

    /**
     * Dado um número de CNPJ, retorna os dados coletados.
     */
    public static function givenCnpjGetData($request, $response, array $args)
    {
        $numero = $args['numero'];

        $database_client = new \MongoDB\Client();
        $collection = $database_client->f360_migracao->empresas_socios;

        $cursor = $collection->find([
            "LAYOUT PRINCIPAL.CNPJ" => $numero
        ]);

        
        $blocks = [];
        foreach($cursor as $document) {
            $blocks[] = $document;
        }

       $payload = json_encode($blocks);

       $response->getBody()->write($payload);
       
       $response = $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        return $response;
    }

    /**
     * Página de teste da aplicação.
     */
    public static function test($request, $response, array $args)
    {
        $path_full = './pipeline/extract/K3241.K032001K.CNPJ.D01120.L00001';

        $file_reader = new FileReader($path_full);

        $register_quantity = $file_reader->get_register_quantity();
        var_dump("Qtd. de registros.: {$register_quantity}");
        
        $file_reader->open();
        
        $block_size = 5; // Quantidade de registros por bloco.
        $block_quant = 2; // Quantidade de blocos a ser lido.
        
        $offset = 0;
        
        for($pos = 0; $pos < $block_quant; $pos++) {
            $register_block = $file_reader->parse(1 * $pos + $offset, $block_size);
            var_dump($register_block);
        }
        
        return $response;
    }
}