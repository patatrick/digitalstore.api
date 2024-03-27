<?php
namespace App\Services;
use App\Services\MySqlService as Mysql;
use App\Models\Observacion;
use \PDO;

class ObservacionService
{
	/** @return Observacion[] . Retorna todas las observaciones de la tienda */
	public function GetAll(int $id_tienda) : array
	{
		try
		{
			$db = MySql::Connect();
			$q = "SELECT * FROM observaciones WHERE id_tienda = ?";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $id_tienda, PDO::PARAM_STR);
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_CLASS, Observacion::class);
			$db = null;
			return $data;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "ObservacionService " . $e->getMessage()." in line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	public function Insert(Observacion $observacion, int $id_tienda) : int
	{
		try
		{
			$db = MySql::Connect();
			$q = "INSERT INTO empleados (:descripcion, :ci_empleado, :ci_ingresa, :id_tienda)";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":descripcion", $observacion->descripcion, PDO::PARAM_STR);
			$stmt->bindParam(":ci_empleado", $observacion->ci_empleado, PDO::PARAM_INT);
			$stmt->bindParam(":ci_ingresa", $observacion->ci_ingresa, PDO::PARAM_STR);
			$stmt->bindParam(":id_tienda", $observacion->id_tienda, PDO::PARAM_INT);
			$stmt->execute();
			$exito = $stmt->rowCount() ? true : false;
			$db = null;
			return $exito;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "EmpleadoService " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	public function Delete(string $ci, int $id_tienda) : bool
	{
		try
		{
			$db = MySql::Connect();
			$q = "DELETE FROM observaciones WHERE id = :id";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":id", $ci, PDO::PARAM_INT);
			$stmt->execute();
			$exito = $stmt->rowCount() ? true : false;
			$db = null;
			return $exito;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "EmpleadoService " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
}