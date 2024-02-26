<?php
namespace App\Models;
class UsuarioDTO 
{
    public Usuario $Usuario;

    /** @var TiendaDTO[] */
    public array $Tienda = [];
}