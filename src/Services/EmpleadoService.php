<?php
namespace App\Services;
use App\Services\MySqlService as Mysql;
use App\Models\Empleado;
use \PDO;

class EmpleadoService
{
	/** Retorna a un empleado de una tienda */
	public function GetOne(string $ci_cod, int $id_tienda, int $estado = 1) : Empleado | bool
	{
		try
		{
			$db = MySql::Connect();
			$q = "SELECT * FROM empleados WHERE id_tienda = :id_tienda AND ( cod = :ci_cod OR ci = :ci_cod ) AND estado = :estado";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":ci_cod", $ci_cod, PDO::PARAM_STR);
			$stmt->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
			$stmt->bindParam(":estado", $estado, PDO::PARAM_INT);
			$stmt->execute();
			$data = $stmt->fetchObject(Empleado::class);
			
			$db = null;
			return $data;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "EmpleadoService " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	/** Retorna a todos los empleados de una tienda */
	public function GetAll(int $id_tienda) : array
	{
		try
		{
			$db = MySql::Connect();
			$q = "SELECT * FROM empleados WHERE id_tienda = ? AND estado = 1";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $id_tienda, PDO::PARAM_INT);
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_CLASS, Empleado::class);
			$db = null;
			return $data;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "EmpleadoService " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	/** Inserta un nuevo empleado a una tienda */
	public function Insert(Empleado $empleado, int $id_tienda) : bool
	{
		try
		{
			$db = MySql::Connect();
			$empleadoExiste = $this->GetOne($empleado->ci, $id_tienda, 0);
			if ($empleadoExiste) {
				$q = "UPDATE empleados SET estado = 1 WHERE ci = :ci AND id_tienda = :id_tienda";
				$stmt = $db->prepare($q);
				$stmt->bindParam(":ci", $empleado->ci, PDO::PARAM_STR);
				$stmt->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
				$stmt->execute();
				$exito = $stmt->rowCount() ? true : false;
				$db = null;
			}
			else {
				$q = "INSERT INTO empleados(ci, id_tienda, id_rol, cod, nombre, direccion, id_comuna, telefono, estado) VALUES(:ci, :id_tienda, :id_rol, :cod, :nombre, :direccion, :id_comuna, :telefono, :estado)";
				$stmt = $db->prepare($q);
				$stmt->bindParam(":ci", $empleado->ci, PDO::PARAM_STR);
				$stmt->bindParam(":id_tienda", $empleado->id_tienda, PDO::PARAM_INT);
				$stmt->bindParam(":id_rol", $empleado->id_rol, PDO::PARAM_STR);
				$stmt->bindParam(":cod", $empleado->cod, PDO::PARAM_STR);
				$stmt->bindParam(":nombre", $empleado->nombre, PDO::PARAM_STR);
				$stmt->bindParam(":direccion", $empleado->direccion, PDO::PARAM_STR);
				$stmt->bindParam(":id_comuna", $empleado->id_comuna, PDO::PARAM_STR);
				$stmt->bindParam(":telefono", $empleado->telefono, PDO::PARAM_STR);
				$stmt->bindParam(":estado", $empleado->estado, PDO::PARAM_INT);
				$stmt->execute();
				$exito = $stmt->rowCount() ? true : false;
			}
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
	/** Actualiza a un empleado de una tienda */
	public function Update(Empleado $empleado, int $id_tienda) : bool
	{
		try
		{
			$db = MySql::Connect();
			$q = "	UPDATE empleados SET 
					id_rol = :id_rol, 
					nombre = :nombre, 
					direccion = :direccion, 
					id_comuna = :id_comuna, 
					telefono = :telefono,
					cod = :cod
					WHERE id_tienda = :id_tienda AND ci = :ci
				";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":ci", $empleado->ci, PDO::PARAM_STR);
			$stmt->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
			$stmt->bindParam(":id_rol", $empleado->id_rol, PDO::PARAM_STR);
			$stmt->bindParam(":cod", $empleado->cod, PDO::PARAM_STR);
			$stmt->bindParam(":nombre", $empleado->nombre, PDO::PARAM_STR);
			$stmt->bindParam(":direccion", $empleado->direccion, PDO::PARAM_STR);
			$stmt->bindParam(":id_comuna", $empleado->id_comuna, PDO::PARAM_STR);
			$stmt->bindParam(":telefono", $empleado->telefono, PDO::PARAM_STR);
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
			$q = "DELETE FROM empleados WHERE ci = :ci AND id_tienda = :id_tienda";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":ci", $ci, PDO::PARAM_STR);
			$stmt->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
			$stmt->execute();
			$exito = $stmt->rowCount() ? true : false;
			return $exito;
		}
		catch (\PDOException $e) {
			try
			{
				$q = "UPDATE empleados SET estado = 0 WHERE ci = :ci AND id_tienda = :id_tienda";
				$stmt = $db->prepare($q);
				$stmt->bindParam(":ci", $ci, PDO::PARAM_STR);
				$stmt->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
				$stmt->execute();
				$exito = $stmt->rowCount() ? true : false;
				$db = null;
				return $exito;
			}
			catch (\PDOException $e2) {
				$db = null;
				echo "EmpleadoService " . $e2->getMessage()." on line ".$e2->getLine();
				http_response_code(422);
				die();
			}
		}
	}
}