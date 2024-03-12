<?php
namespace App\Services;
use App\Services\MySqlService as Mysql;
use App\Models\Proveedor;
use \PDO;

class ProveedorService
{
	/** @return Proveedor[] */
	public function GetAll(int $id_tienda) : array
	{
		try
		{
			$db = MySql::Connect();
			$q = "SELECT * FROM proveedores WHERE id_tienda = ?";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $id_tienda, PDO::PARAM_INT);
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_CLASS, Proveedor::class);
			
			$db = null;
			return $data;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "ProveedorService " . $e->getMessage()." in line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	public function Insert(Proveedor $proveedor) : int
	{
		try
		{
			$db = MySql::Connect();
			$q = "INSERT INTO proveedores(rut, nombre, descripcion, telefono, id_tienda) VALUES(?, ?, ?, ?, ?)";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $proveedor->rut, PDO::PARAM_STR);
			$stmt->bindParam(2, $proveedor->nombre, PDO::PARAM_STR);
			$stmt->bindParam(3, $proveedor->descripcion, PDO::PARAM_STR);
			$stmt->bindParam(4, $proveedor->telefono, PDO::PARAM_STR);
			$stmt->bindParam(5, $proveedor->id_tienda, PDO::PARAM_INT);
			$stmt->execute();
            $idInserted = $db->lastInsertId();
			$db = null;
			return $idInserted;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "ProveedorService " . $e->getMessage()." in line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	public function Update(Proveedor $proveedor) : bool
	{
		try
		{
			$db = MySql::Connect();
			$q = "  UPDATE proveedores SET
					nombre = :nombre,
					descripcion = :descripcion,
					telefono = :telefono
					WHERE id = :id
			";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":id", $proveedor->id, PDO::PARAM_INT);
			$stmt->bindParam(":nombre", $proveedor->nombre, PDO::PARAM_STR);
			$stmt->bindParam(":descripcion", $proveedor->descripcion, PDO::PARAM_STR);
			$stmt->bindParam(":telefono", $proveedor->telefono, PDO::PARAM_STR);
			$stmt->execute();
			$exito = $stmt->rowCount() ? true : false;
			$db = null;
			return $exito;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "ProveedorService " . $e->getMessage()." in line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	public function Delete(int $id_proveedor) : bool
	{
		try
		{
			$db = MySql::Connect();
			$q = "  DELETE FROM proveedores WHERE id = :id;";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":id", $id_proveedor, PDO::PARAM_INT);
			$stmt->execute();
			$exito = $stmt->rowCount() ? true : false;
			$db = null;
			return $exito;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "ProveedorService " . $e->getMessage()." in line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
}