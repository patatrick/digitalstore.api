<?php
namespace App\Models;
class Empleado
{
	public string $ci = "";
	public int $id_tienda;
	public string $id_rol;
	public ?string $cod;
	public string $nombre;
	public string $direccion;
	public string $id_comuna;
	public string $telefono;
	public int $estado = 1;
}
