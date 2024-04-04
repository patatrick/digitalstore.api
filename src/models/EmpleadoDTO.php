<?php
namespace App\Models;
class EmpleadoDTO
{
	public string $ci = "";
	public int $id_tienda = 0;
	public string $id_rol = "";
	public ?string $cod = "";
	public string $nombre = "";
	public string $direccion = "";
	public string $id_comuna = "0";
	public string $telefono = "";
	public int $estado = 1;
	public string $ingreso = "";
	public string $ip = "";
}
