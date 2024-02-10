<?php
namespace App\Services;
use App\Models\InventarioDTO;
use App\Models\TipoProducto;
use App\Services\MySqlService as Mysql;
use \PDO;

class InventarioService
{
	public function Traer(int $id_tienda, int $id_usuario) : array
	{
		try
		{
				$db = MySql::Connect();
				$q = "  SELECT * FROM view_inventario WHERE id_tienda = ? AND id_usuario = ?";

				$stmt = $db->prepare($q);
				$stmt->bindParam(1, $id_tienda, PDO::PARAM_INT);
				$stmt->bindParam(2, $id_usuario, PDO::PARAM_INT);
				$stmt->execute();
				$inventario = $stmt->fetchAll(PDO::FETCH_CLASS, InventarioDTO::class);


				$q = "SELECT * FROM tipo_productos ORDER BY nombre ASC";
				$stmt3 = $db->prepare($q);
				$stmt3->execute();
				$tipoProductos = $stmt3->fetchAll(PDO::FETCH_CLASS, TipoProducto::class);

				$data = [
					"inventario" => $inventario,
					"tipoProductos" => $tipoProductos
				];
				
				$db = null;
				return $data;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "InventarioService " . $e->getMessage()." in line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
}