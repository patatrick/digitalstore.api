<?php
namespace App\Services;
use App\Services\MySqlService as Mysql;
use App\Models\Producto;
use \PDO;

class ProductoService
{
	public function Traer(int $id_usuario, string $sku, int $id_tienda) : Producto | bool
	{
		try
		{
			$db = MySql::Connect();
			$q = "SELECT * FROM productos_tienda pt INNER JOIN usuarios_tienda ut ON pt.id_tienda = ut.id_tienda
				WHERE pt.id_tienda = ? AND ut.id_usuario = ? AND pt.sku = ?";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $id_tienda, PDO::PARAM_INT);
			$stmt->bindParam(2, $id_usuario, PDO::PARAM_INT);
			$stmt->bindParam(3, $sku, PDO::PARAM_STR);
			$stmt->execute();
			$producto = $stmt->fetchObject(Producto::class);

			if (!$producto) {
				$q = "SELECT * FROM productos WHERE sku = ?";
				$stmt2 = $db->prepare($q);
				$stmt2->bindParam(1, $sku, PDO::PARAM_STR);
				$stmt2->execute();
				$producto = $stmt2->fetchObject(Producto::class);
			}

			$db = null;
			return $producto;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "ProductoService " . $e->getMessage()." in line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	public function TraerProductoInventario(string $sku, int $id_tienda) : Producto | bool
	{
		try
		{
			$db = MySql::Connect();
			$q = "	SELECT DISTINCT
                        id, id_producto, id_tienda, sku, cantidad, vendedor, vencimiento, ingreso,
                        precio, id_tipo_producto, nombre_producto, precio_mayor
					FROM(
						SELECT I.*, P.nombre nombre_producto, P.id id_producto
						FROM inventario I
						INNER JOIN productos P ON I.sku = P.sku
						WHERE I.id_tienda = :id_tienda
						AND P.sku
						UNION
						SELECT I.*, P.nombre nombre_producto, P.id id_producto FROM inventario I
						INNER JOIN productos_tienda P ON I.sku = P.sku AND P.id_tienda = :id_tienda
						WHERE I.id_tienda = :id_tienda
					) AS SUBQUERY
					WHERE sku = :sku ORDER BY vencimiento ASC LIMIT 1";

			$stmt = $db->prepare($q);
			$stmt->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
			$stmt->bindParam(":sku", $sku, PDO::PARAM_STR);
			$stmt->execute();
			$producto = $stmt->fetchObject(Producto::class);
			$db = null;
			return $producto;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "ProductoService " . $e->getMessage()." in line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	public function ExisteSkuInterno(int $id_tienda, string $sku) : bool
	{
		try
		{
			$db = MySql::Connect();
			$q = "SELECT sku FROM productos_tienda WHERE id_tienda = ? AND sku = ?";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $id_tienda, PDO::PARAM_INT);
			$stmt->bindParam(2, $sku, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_OBJ) ? true : false;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "ProductoService " . $e->getMessage()." in line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	public function Insertar($producto, $inventario) : int
	{
		try
		{
			$db = MySql::Connect();
			$db->beginTransaction();
			$q = "  CALL sp_sku_interno_insertar(?, ?, ?, ?);";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $producto->id, PDO::PARAM_INT);
			$stmt->bindParam(2, $inventario->id_tienda, PDO::PARAM_INT);
			$stmt->bindParam(3, $producto->sku, PDO::PARAM_STR);
			$stmt->bindParam(4, $producto->nombre, PDO::PARAM_STR);
			$stmt->execute();
			$db->lastInsertId();

			$q = "  INSERT INTO inventario(id_tienda, sku, cantidad, vendedor, vencimiento, precio, id_tipo_producto, precio_mayor)
					VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
			$stmt2 = $db->prepare($q);
			$stmt2->bindParam(1, $inventario->id_tienda, PDO::PARAM_INT);
			$stmt2->bindParam(2, $inventario->sku, PDO::PARAM_STR);
			$stmt2->bindParam(3, $inventario->cantidad, PDO::PARAM_INT);
			$stmt2->bindParam(4, $inventario->vendedor, PDO::PARAM_STR);
			$stmt2->bindParam(5, $inventario->vencimiento, PDO::PARAM_STR);
			$stmt2->bindParam(6, $inventario->precio, PDO::PARAM_INT);
			$stmt2->bindParam(7, $inventario->id_tipo_producto, PDO::PARAM_INT);
			$stmt2->bindParam(8, $inventario->precio_mayor, PDO::PARAM_INT);
			$stmt2->execute();
			$idInsertado = (int) $db->lastInsertId();
			$db->commit();
		}
		catch (\PDOException $e) {
			$db->rollBack();
			echo "ProductoService " . $e->getMessage()." in line ".$e->getLine();
			http_response_code(422);
			$db = null;
			die();
		}
		$db = null;
		return $idInsertado;
	}
	public function Actualizar($producto, $inventario) : bool
	{
		try
		{
			$db = MySql::Connect();
			$q = "	SELECT * FROM usuarios_tienda WHERE id_usuario = ? AND id_tienda = ?";
			$stmt3 = $db->prepare($q);
			$stmt3->bindParam(1, $inventario->vendedor, PDO::PARAM_INT);
			$stmt3->bindParam(2, $inventario->id_tienda, PDO::PARAM_INT);
			$stmt3->execute();
			$usuarioTienda = $stmt3->fetch(PDO::FETCH_OBJ);
			if (!$usuarioTienda) {
				$db = null;
				return false;
			}

			$db->beginTransaction();
			$q = "  CALL sp_sku_interno_insertar(?, ?, ?, ?, ?);";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $producto->id, PDO::PARAM_INT);
			$stmt->bindParam(2, $inventario->vendedor, PDO::PARAM_INT);
			$stmt->bindParam(3, $inventario->id_tienda, PDO::PARAM_INT);
			$stmt->bindParam(4, $producto->sku, PDO::PARAM_STR);
			$stmt->bindParam(5, $producto->nombre, PDO::PARAM_STR);
			$stmt->execute();


			$q = "  UPDATE inventario SET
					cantidad = ?,
					vendedor = ?,
					vencimiento = ?,
					precio = ?,
					precio_mayor = ?,
					id_tipo_producto = ?
					WHERE id = ?";
			$stmt2 = $db->prepare($q);
			$stmt2->bindParam(1, $inventario->cantidad, PDO::PARAM_INT);
			$stmt2->bindParam(2, $inventario->vendedor, PDO::PARAM_INT);
			$stmt2->bindParam(3, $inventario->vencimiento, PDO::PARAM_STR);
			$stmt2->bindParam(4, $inventario->precio, PDO::PARAM_INT);
			$stmt2->bindParam(5, $inventario->precio_mayor, PDO::PARAM_INT);
			$stmt2->bindParam(6, $inventario->id_tipo_producto, PDO::PARAM_INT);
			$stmt2->bindParam(7, $inventario->id, PDO::PARAM_INT);
			$stmt2->execute();
			$db->commit();
			// $exito = $stmt2->rowCount() != 0 ? true : false;
			$exito = true;
		}
		catch (\PDOException $e) {
			$db->rollBack();
			echo "ProductoService " . $e->getMessage()." in line ".$e->getLine();
			http_response_code(422);
			$db = null;
			die();
		}
		$db = null;
		return $exito;
	}
	public function Eliminar(int $id_inventario, int $id_tienda, int $id_usuario) : bool
	{
		try
		{
			$db = MySql::Connect();
			$q = "	SELECT * FROM usuarios_tienda WHERE id_usuario = ? AND id_tienda = ?";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $id_usuario, PDO::PARAM_INT);
			$stmt->bindParam(2, $id_tienda, PDO::PARAM_INT);
			$stmt->execute();
			$usuarioTienda = $stmt->fetch(PDO::FETCH_OBJ);
			if (!$usuarioTienda) {
				$db = null;
				return false;
			}

			$q = "  DELETE FROM inventario WHERE id = ?";
			$stmt2 = $db->prepare($q);
			$stmt2->bindParam(1, $id_inventario, PDO::PARAM_INT);
			$stmt2->execute();
			return $stmt2->rowCount() != 0 ? true : false;
		}
		catch (\PDOException $e) {
			echo "No se puede eliminar porque tienes detalles de ventas asociados al producto";
			http_response_code(422);
			$db = null;
			die();
		}
	}
}