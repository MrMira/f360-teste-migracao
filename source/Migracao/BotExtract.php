<?php

require __DIR__ . "/../../vendor/autoload.php";

use F360\Migracao\Pipeline\FileExtractor;


$file_name = 'DADOS_ABERTOS_CNPJ_01.zip';
$file_name = isset($argv[1]) ? $argv[1] : $file_name;

$path_file = './pipeline/download';
$path_extract = './pipeline/extract';

$file_extractor = new FileExtractor(
     $path_file, $file_name, $path_extract
);


$result = $file_extractor->extract();

if($result) {
     echo "Arquivo extraído com sucesso!";
} else {
     echo "Ops! Ocorreu um erro ao extrair o arquivo.";
}
