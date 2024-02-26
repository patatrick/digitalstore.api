<?php
namespace App\Models;
class VentaDTO
{
	public int $id = 0;
	public string | null $ingreso = null;
	public int $total = 0;
	public int | null $nro_redbank = null;
	public int $id_vendedor = 0;
    public int $id_caja = 0;
	public array $detalles = [new Detalle()];
}