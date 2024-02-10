<?php
namespace App\Services;
use App\Services\MySqlService as Mysql;
use App\Models\VentaDTO;
use \PDO;

class VentaService
{
	/**
	 * Inserta una venta y su detalle en la base de datos.
	 *
	 * @param VentaDTO $ventaDTO Objeto VentaDTO que representa la venta a insertar.
	 * @return bool Devuelve true si la inserción fue exitosa, false en caso contrario.
	 */
	public function Insertar($ventaDTO) : bool
	{
		try
		{
			$db = MySql::Connect();
			$db->beginTransaction();

			$q = "INSERT INTO ventas(total, nro_redbank, id_vendedor) VALUES(?, ?, ?)";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $ventaDTO->total , PDO::PARAM_INT);
			$stmt->bindParam(2, $ventaDTO->nro_redbank, PDO::PARAM_INT);
			$stmt->bindParam(3, $ventaDTO->id_vendedor, PDO::PARAM_INT);
			$stmt->execute();
			$id_venta = (int) $db->lastInsertId();
			if ($id_venta) {
				foreach ($ventaDTO->detalles as $detalle) {
					$q = "INSERT INTO detalles(id_venta, id_inventario, cant, precio, mayorista) VALUES(?, ?, ?, ?, ?)";
					$stmt2 = $db->prepare($q);
					$stmt2->bindParam(1, $id_venta, PDO::PARAM_INT);
					$stmt2->bindParam(2, $detalle->id_inventario, PDO::PARAM_INT);
					$stmt2->bindParam(3, $detalle->cant, PDO::PARAM_INT);
					$stmt2->bindParam(4, $detalle->precio, PDO::PARAM_INT);
					$stmt2->bindParam(5, $detalle->mayorista, PDO::PARAM_BOOL);
					$stmt2->execute();
				}
			}
			$db->commit();
		}
		catch (\PDOException $e) {
			$db->rollBack();
			$db = null;
			echo "VentaService " . $e->getMessage()." in line ".$e->getLine();
			http_response_code(422);
			die();
		}
		$db = null;
		return true;
	}
}