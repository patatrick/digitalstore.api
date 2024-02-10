<?php
namespace App\Models;
class UsuarioDTO 
{
    public Usuario $Usuario;

    /** @var Tienda[] */
    public array $Tienda = [];
}