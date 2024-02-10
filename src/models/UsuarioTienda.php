<?php
namespace App\Models;
class UsuarioTienda
{
    public int $id_usuarios;
    public int $id_tienda;

    /** @var Tienda[] */
    public array $tienda = [];
}