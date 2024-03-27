<?php
namespace App\Traits;
trait SkuTrait
{
	public function getTiendaIdBySku(string $sku) : int
	{
		if (!$sku) return 0;
		$sku = str_pad($sku, 13, '0', STR_PAD_LEFT);
		return !$sku ? 0 : (int) substr($sku, 0, strlen($sku) - 4);
	}
	public function getNroCajaBySku(string $sku) : int
	{
		if (!$sku) return 0;
		$sku = str_pad($sku, 13, '0', STR_PAD_LEFT);
		$nro_caja = substr($sku, strlen($sku) - 4, strlen($sku));
		return (int) substr($nro_caja, 0, strlen($nro_caja) - 1);
	}
	public function ValidateSKU(string $sku) : bool
	{
		try
		{
			$codigoSinDigito = substr($sku, 0, strlen($sku) - 1);	
			$digitos = str_split($codigoSinDigito);
			$sumaPares = $sumaImpares = 0;
			foreach ($digitos as $indice => $digito) {
				if (($indice % 2) == 0) {
					$sumaPares += $digito;
				} else {
					$sumaImpares += $digito;
				}
			}
			$sumaTotal = $sumaPares + $sumaImpares * 3;
			$digitoControl = (10 - ($sumaTotal % 10)) % 10;
			return  $codigoSinDigito . $digitoControl == $sku ? true : false;
		}
		catch (\Throwable $th) {
			return false;
		}
	}
}