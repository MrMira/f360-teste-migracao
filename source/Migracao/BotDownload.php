<?php

require __DIR__ . "/../../vendor/autoload.php";

use F360\Migracao\Pipeline\FileDownloaderFromURL;


$file_name = 'F.K03200$Z.D10510.MUNICCSV.zip';
$file_name = isset($argv[1]) ? $argv[1] : $file_name;

$url_base = "http://200.152.38.155/CNPJ/" ;
$path_save = "./pipeline/download/";

$file_downloader = new FileDownloaderFromURL(
    $url_base, $file_name, $path_save
);


$result = $file_downloader->download_if_not_exists($file_name);

if($result) {
    echo "Arquivo baixado com sucesso!";
} else {
    echo "Ops! Ocorreu um erro ao baixar o arquivo. Talvez ele já exista?";
}