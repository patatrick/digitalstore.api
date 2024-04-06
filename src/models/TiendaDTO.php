<?php
namespace App\Models;
class TiendaDTO
{
    public int $id;
	public string $nombre;
	public string $giro;
	public string $id_rol;
    public ?int $id_caja;
	public string $ip;
}