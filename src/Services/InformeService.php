<?php
namespace App\Services;
use App\Models\Detalle;
use App\Models\InformeDTO;
use App\Models\Venta;
use App\Services\MySqlService as Mysql;
use \PDO;

class InformeService
{
	public function Ventas(int $id_usuario, int $id_tienda, string $where) : InformeDTO | null
	{
		try
		{
			$db = MySql::Connect();
			$q = "	SELECT * FROM usuarios_tienda WHERE id_usuarios = ? AND id_tienda = ?";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $id_usuario, PDO::PARAM_INT);
			$stmt->bindParam(2, $id_tienda, PDO::PARAM_INT);
			$stmt->execute();
			$usuarioTienda = $stmt->fetch(PDO::FETCH_OBJ);
			if (!$usuarioTienda) {
				$db = null;
				return null;
			}

			$q = "	SELECT DISTINCT v.* FROM ventas v
					INNER JOIN detalles d ON v.id = d.id_venta
					INNER JOIN view_inventario i ON d.id_inventario = i.id
					INNER JOIN tienda t ON i.id_tienda = t.id
					$where
					AND t.id = ?
					ORDER BY id DESC
			";
			$stmt2 = $db->prepare($q);
			$stmt2->bindParam(1, $id_tienda, PDO::PARAM_INT);
			$stmt2->execute();

			$q = "	SELECT i.nombre_producto nombre, d.* FROM detalles d
					INNER JOIN ventas v ON d.id_venta = v.id
					INNER JOIN view_inventario i ON d.id_inventario = i.id
					INNER JOIN tienda t ON i.id_tienda = t.id
					$where
					AND t.id = ?
			";
			$stmt3 = $db->prepare($q);
			$stmt3->bindParam(1, $id_tienda, PDO::PARAM_INT);
			$stmt3->execute();

			$arrVenta = $stmt2->fetchAll(PDO::FETCH_CLASS, Venta::class);
			$arrDetalle = $stmt3->fetchAll(PDO::FETCH_CLASS, Detalle::class);

			$informeDTO = new InformeDTO();
			$informeDTO->venta = $arrVenta;
			$informeDTO->detalle = $arrDetalle;
			$db = null;
			return $informeDTO;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "InformeService " . $e->getMessage()." in line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
}