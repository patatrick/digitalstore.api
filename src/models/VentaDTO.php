<?php
namespace App\Models;
class VentaDTO
{
	public int $id = 0;
	public string | null $ingreso = null;
	public int $total = 0;
	public int | null $nro_redbank = null;
	public string $ci_vendedor = "";
    public string $sku_caja = "";
    /** @param Detalle[] $detalles */
	public array $detalles = [];
}