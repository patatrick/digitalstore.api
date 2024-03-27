<?php
namespace App\Models;
class Caja
{
	public int $id = 0;
	public string $sku_caja = "";
	public string $cod_jefe_tienda = "";
	public int $id_tienda = 0;
	public string $apertura = "";
	public string | null $cierre = null;
}
