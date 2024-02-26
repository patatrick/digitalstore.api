<?php
namespace App\Models;
class Proveedor
{
	public int $id;
	public string $rut;
	public string $nombre;
	public string $descripcion;
	public string $telefono;
	public int $id_tienda;
}