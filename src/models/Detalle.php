<?php
namespace App\Models;
class Detalle
{
	public int $id = 0;
	public int $id_venta = 0;
	public int $id_inventario = 0;
	public string $nombre = "";
	public int $cant = 0;
	public int $precio = 0;
	public int $precio_un = 0;
	public bool $mayorista = false;
}
