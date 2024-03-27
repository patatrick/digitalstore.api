<?php
namespace App\Services;
use App\Models\Caja;
use App\Services\MySqlService as Mysql;
use App\Models\VentaDTO;
use \PDO;

class VentaService
{
	/**
	 * Inserta una venta y su detalle en la base de datos.
	 * @param VentaDTO $ventaDTO Objeto VentaDTO que representa la venta a insertar.
	 * @return bool Devuelve true si la inserción fue exitosa, false en caso contrario.
	 */
	public function Insertar($ventaDTO, int $nro_caja) : bool
	{
		try
		{
			$db = MySql::Connect();
			$db->beginTransaction();
			$q = "INSERT INTO ventas(total, nro_redbank, ci_vendedor, sku_caja) VALUES(?, ?, ?, ?)";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $ventaDTO->total , PDO::PARAM_INT);
			$stmt->bindParam(2, $ventaDTO->nro_redbank, PDO::PARAM_INT);
			$stmt->bindParam(3, $ventaDTO->ci_vendedor, PDO::PARAM_STR);
			$stmt->bindParam(4, $ventaDTO->sku_caja, PDO::PARAM_STR);
			$stmt->execute();
			$id_venta = (int) $db->lastInsertId();
			if ($id_venta) {
				foreach ($ventaDTO->detalles as $detalle) {
					$q = "INSERT INTO detalles(id_venta, id_inventario, cant, precio, mayorista, nro_caja) VALUES(?, ?, ?, ?, ?, ?)";
					$stmt2 = $db->prepare($q);
					$stmt2->bindParam(1, $id_venta, PDO::PARAM_INT);
					$stmt2->bindParam(2, $detalle->id_inventario, PDO::PARAM_INT);
					$stmt2->bindParam(3, $detalle->cant, PDO::PARAM_INT);
					$stmt2->bindParam(4, $detalle->precio, PDO::PARAM_INT);
					$stmt2->bindParam(5, $detalle->mayorista, PDO::PARAM_BOOL);
					$stmt2->bindParam(6, $nro_caja, PDO::PARAM_INT);
					$stmt2->execute();
				}
			}
			$db->commit();
		}
		catch (\PDOException $e) {
			$db->rollBack();
			$db = null;
			echo "VentaService " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
		$db = null;
		return true;
	}
	public function OpenCaja(Caja $caja) : int
	{
		try
		{
			$db = MySql::Connect();
			$q = "INSERT INTO cajas(sku_caja, cod_jefe_tienda, id_tienda) VALUES(:sku_caja, :cod_jefe_tienda, :id_tienda)";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":sku_caja", $caja->sku_caja, PDO::PARAM_STR);
			$stmt->bindParam(":cod_jefe_tienda", $caja->cod_jefe_tienda, PDO::PARAM_STR);
			$stmt->bindParam(":id_tienda", $caja->id_tienda, PDO::PARAM_INT);
			$stmt->execute();
			$id_inserted = $db->lastInsertId();
			$db = null;
			return $id_inserted;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "VentaService " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	public function CerrarCaja(int $id_caja) : bool
	{
		try
		{
			$db = MySql::Connect();
			$q = "UPDATE cajas SET cierre = NOW() WHERE id = ?";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $id_caja, PDO::PARAM_INT);
			$stmt->execute();
			$rowCount = $stmt->rowCount() > 0 ? true : false;
			$db = null;
			return $rowCount;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "VentaService " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
}