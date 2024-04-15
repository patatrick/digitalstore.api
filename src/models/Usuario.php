<?php
namespace App\Models;
class Usuario 
{
	public int $id = 0;
	public string $ci = "";
	public ?string $cod = null;
	public string $nombre = "";
	public ?string $psw = null;
	public string $direccion = "";
	public string $id_comuna = "";
	public string $telefono = "";
}