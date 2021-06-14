<?php

//
// Carregamento do 'autoload' da aplicação.
//
require __DIR__ . './vendor/autoload.php';

//
// Importação de classes.
//
use F360\Migracao\Controller\TesteController;
use F360\Migracao\Controller\ApiCnpjController;


//
// Configuração do microframework de rotas.
//
$app = \Slim\Factory\AppFactory::create();
$app->setBasePath('/f360-teste-migracao');
$app->addErrorMiddleware(true, true, true);


//
// Conjunto de Rotas da aplicação
//
$app->get('/', [
    ApiCnpjController::class, 'index'
]);

$app->get('/cnpj/{numero}', [
    ApiCnpjController::class, 'givenCnpjGetData'
]);

$app->get('/teste', [
    ApiCnpjController::class, 'test'
]);


//
// Realização 'dispatch' das rotas.
//
$app->run();