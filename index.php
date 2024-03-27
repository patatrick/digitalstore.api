<?php
const PRODUCTION = true;

if (PRODUCTION === false) {
	ini_set('display_errors', 1);
	error_reporting(-1);
}

date_default_timezone_set('America/Santiago');
require __DIR__ . "/vendor/autoload.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

use App\Middlewares\AuthMiddleware;
use App\Middlewares\CustomCorsMiddleware;
use App\Middlewares\AdministradorMiddleware;
use App\Middlewares\AdministradorOrJefeMiddleware;

use App\Controllers\LoginController;
use App\Controllers\MenuController;
use App\Controllers\ProductoController;
use App\Controllers\InformeController;
use App\Controllers\InventarioController;
use App\Controllers\ProveedorController;
use App\Controllers\VentaController;
use App\Controllers\UsuarioController;
use App\Controllers\EmpleadoController;

$app = AppFactory::create();
if (PRODUCTION === false) {
	$app->setBasePath("/tienda.biotecnochile.api");
	$app->addErrorMiddleware(true, true, true);
}
else {
	$app->addErrorMiddleware(false, false, false);
}

$app->addBodyParsingMiddleware();
$app->add(new CustomCorsMiddleware());


// Routes
// $app->get('/hello', [HelloController::class, "Index"]);
$app->post('/login', [LoginController::class, "Index"]);
$app->get('/menu/tienda/{id_tienda}', [MenuController::class, "Index"])->add(new AuthMiddleware);
$app->group('/producto', function (RouteCollectorProxy $group)
{
	$group->get('/tienda/{id_tienda}/sku/{sku}', [ProductoController::class, "Index"]);
	$group->get('/inventario/tienda/{id_tienda}/sku/{sku}', [ProductoController::class, "TraerProductoInventario"]);
	$group->get('/tienda/{id_tienda}/generar-sku-interno', [ProductoController::class, "TraerSku"]);
	$group->post('/tienda/{id_tienda}', [ProductoController::class, "Insertar"]);
	$group->put('/tienda/{id_tienda}', [ProductoController::class, "Actualizar"]);
	$group->delete('/tienda/{id_tienda}/inventario/{id_inventario}', [ProductoController::class, "Eliminar"]);
})->add(new AuthMiddleware);

$app->group('/inventario', function (RouteCollectorProxy $group)
{
	$group->get('/tienda/{id_tienda}', [InventarioController::class, "Index"]);
})->add(new AuthMiddleware);

$app->group('/venta', function (RouteCollectorProxy $group)
{
    $group->post('/login', [LoginController::class, "LoginVenta"]);
    $group->post('/logout', [LoginController::class, "CloseCaja"]);
    $group->post('/login/cajero/tienda/{id_tienda}', [LoginController::class, "LoginCajero"]);
    $group->get('/tienda/{id_tienda}', [MenuController::class, "MenuVentas"])->add(new AuthMiddleware);
	$group->post('/tienda/{id_tienda}', [VentaController::class, "Insertar"])->add(new AuthMiddleware);
});

$app->group('/informe', function (RouteCollectorProxy $group)
{
	$group->get('/venta/tienda/{id_tienda}/tipo/{tipo}', [InformeController::class, "ObtenerVentas"]);
})->add(new AuthMiddleware);

$app->group('/usuario', function (RouteCollectorProxy $group)
{
	$group->get('/tienda/{id_tienda}', [UsuarioController::class, "GetAll"]);
	$group->get('{id_usuario}/tienda/{id_tienda}', [UsuarioController::class, "GetOneByTienda"]);
	$group->post('/rol/{id_rol}/tienda/{id_tienda}', [UsuarioController::class, "Insert"])->add(new AdministradorOrJefeMiddleware);
	$group->put('/tienda/{id_tienda}', [UsuarioController::class, "Update"])->add(new AdministradorOrJefeMiddleware);
	$group->put('/{id_usuario}/rol/{id_rol}/tienda/{id_tienda}', [UsuarioController::class, "UpdateRol"])->add(new AdministradorOrJefeMiddleware);
	$group->delete('/{id_usuario}/estado/{id_estado}/tienda/{id_tienda}', [UsuarioController::class, "Delete"])->add(new AdministradorOrJefeMiddleware);
})->add(new AuthMiddleware);

$app->get('/empleado/{cod}/tienda/{id_tienda}', [EmpleadoController::class, "GetOne"]);
$app->group('/empleado', function (RouteCollectorProxy $group)
{
	$group->get('/tienda/{id_tienda}', [EmpleadoController::class, "GetAll"])->add(new AdministradorOrJefeMiddleware);
	$group->get('/comuna/tienda/{id_tienda}', [EmpleadoController::class, "GetAllAndCommune"])->add(new AdministradorOrJefeMiddleware);
	$group->post('/tienda/{id_tienda}', [EmpleadoController::class, "Insert"])->add(new AdministradorOrJefeMiddleware);
	$group->put('/new_cod/{new_cod}/tienda/{id_tienda}', [EmpleadoController::class, "Update"])->add(new AdministradorOrJefeMiddleware);
	$group->delete('/{ci}/tienda/{id_tienda}', [EmpleadoController::class, "Delete"])->add(new AdministradorOrJefeMiddleware);
})->add(new AuthMiddleware);

$app->group('/observacion', function (RouteCollectorProxy $group)
{
	$group->get('/{cod}/tienda/{id_tienda}', [EmpleadoController::class, "GetOne"]);
	$group->get('/tienda/{id_tienda}', [EmpleadoController::class, "GetAll"])->add(new AdministradorOrJefeMiddleware);
	$group->get('/comuna/tienda/{id_tienda}', [EmpleadoController::class, "GetAllAndCommune"])->add(new AdministradorOrJefeMiddleware);
	$group->post('/tienda/{id_tienda}', [EmpleadoController::class, "Insert"])->add(new AdministradorOrJefeMiddleware);
	$group->put('/new_cod/{new_cod}/tienda/{id_tienda}', [EmpleadoController::class, "Update"])->add(new AdministradorOrJefeMiddleware);
	$group->delete('/{ci}/tienda/{id_tienda}', [EmpleadoController::class, "Delete"])->add(new AdministradorOrJefeMiddleware);
})->add(new AuthMiddleware);

// Sólo administradores
$app->group('/proveedor', function (RouteCollectorProxy $group)
{
	$group->get('/tienda/{id_tienda}', [ProveedorController::class, "GetAll"]);
	$group->post('/tienda/{id_tienda}', [ProveedorController::class, "Insert"]);
	$group->put('/tienda/{id_tienda}', [ProveedorController::class, "Update"]);
	$group->delete('/{id_proveedor}/tienda/{id_tienda}', [ProveedorController::class, "Delete"]);
})
->add(new AuthMiddleware)->add(new AdministradorMiddleware);

$app->run();