<?php
use App\Controllers\InformeController;
use App\Controllers\InventarioController;
use App\Controllers\VentaController;
require __DIR__ . "/vendor/autoload.php";
require_once __DIR__."./src/Constantes.php";

ini_set('display_errors', 1);
error_reporting(-1);
date_default_timezone_set('America/Santiago');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

use Slim\Factory\AppFactory;
use App\Middlewares\AuthMiddleware;
use Slim\Routing\RouteCollectorProxy;

use App\Controllers\LoginController;
use App\Controllers\MenuController;
use App\Controllers\ProductoController;

$app = AppFactory::create();
$app->setBasePath("/tienda.biotecnochile.api");
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();

// Routes

$app->post('/login', [LoginController::class, "Index"]);

$app->get('/menu', [MenuController::class, "Index"])->add(new AuthMiddleware);

$app->group('/producto', function (RouteCollectorProxy $group)
{
    $group->get('/tienda/{id_tienda}/sku/{sku}', [ProductoController::class, "Index"]);
    $group->get('/inventario/tienda/{id_tienda}/sku/{sku}', [ProductoController::class, "TraerProductoInventario"]);
    $group->get('/tienda/{id_tienda}/generar-sku-interno', [ProductoController::class, "TraerSku"]);
    $group->post('/', [ProductoController::class, "Insertar"]);
    $group->put('/', [ProductoController::class, "Actualizar"]);
    $group->delete('/tienda/{id_tienda}/inventario/{id_inventario}', [ProductoController::class, "Eliminar"]);
})
->add(new AuthMiddleware);

$app->group('/inventario', function (RouteCollectorProxy $group)
{
    $group->get('/tienda/{id_tienda}', [InventarioController::class, "Index"]);
})
->add(new AuthMiddleware);

$app->group('/venta', function (RouteCollectorProxy $group)
{
    $group->post('', [VentaController::class, "Insertar"]);
})
->add(new AuthMiddleware);

$app->group('/informe', function (RouteCollectorProxy $group)
{
    $group->get('/venta/tienda/{id_tienda}/tipo/{tipo}', [InformeController::class, "ObtenerVentas"]);
})
->add(new AuthMiddleware);



$app->run();