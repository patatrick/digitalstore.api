<?php
namespace App\Models;
class InventarioDTO
{
	public ?int $id = null;
    public ?int $id_producto = null;
	public ?int $id_tienda = null;
	public ?string $sku = null;
	public ?int $cantidad = null;
	public ?int $vendedor = null;
	public ?string $vencimiento = null;
	public ?string $ingreso = null;
	public ?int $precio = null;
	public ?int $id_tipo_producto = null;
    public ?string $nombre_producto = null;
    public ?int $precio_mayor = null;
}