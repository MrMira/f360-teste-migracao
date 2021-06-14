<?php

require __DIR__ . "/../../vendor/autoload.php";

use F360\Migracao\Pipeline\FileReader;

//
// Verificamos se a coleção existe.
//
$database_client = new MongoDB\Client();

$collection_names =  $database_client->f360_migracao->listCollectionNames();
foreach ($collection_names as $collection) {
    if ($collection === 'empresas_socios') {
        echo ("Parece que a coleção já existe. Apague a mesma para que ela possa ser recriada!");
        return;
    }
}

//
// Inserção dos dados no banco
//
$collection = $database_client->f360_migracao->empresas_socios;

$path_full = './pipeline/extract/K3241.K032001K.CNPJ.D01120.L00001';

$file_reader = new FileReader($path_full);

$register_quantity = $file_reader->get_register_quantity();
var_dump("Qtd. de registros.: {$register_quantity}");

$file_reader->open();

$block_size = 10000 / 100; // Quantidade de registros por bloco.
$block_quant = 100; // Quantidade de blocos a ser lido.

$offset = 0;
for ($block_step = 0; $block_step < $block_quant; $block_step++) {
    $register_block = $file_reader->parse(1 * $block_step + $offset, $block_size);

    $updateResult = $collection->insertMany(
        $register_block
    );
}