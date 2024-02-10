<?php
namespace App\Models;
class ProductoDTO
{
	public array $producto = [];
	public array $tipoProducto = [];
	
	public function __construct(array $producto, array $tipoProducto) {
		$this->producto = $producto;
		$this->tipoProducto = $tipoProducto;
	}
}