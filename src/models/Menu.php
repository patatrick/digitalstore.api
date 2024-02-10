<?php
namespace App\Models;
class Menu
{
	public ?int $id = null;
	public ?string $nombre = null;
	public ?int $orden = null;
	public ?string $url = null;
	public ?bool $estado = null;
}