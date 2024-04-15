<?php
namespace App\Services;
use App\Models\UsuarioTienda;
use App\Services\MySqlService as Mysql;
use App\Models\Usuario;
use \PDO;

class AdministradorService
{
	/** @return Usuario[] */
	public function GetAll(int $id_tienda) : array
	{
		try
		{
			$db = MySql::Connect();
			$q = "	SELECT U.*, NULL AS psw FROM usuarios U
					INNER JOIN usuarios_tienda UT ON U.id = UT.id_usuario
					WHERE UT.id_tienda = :id_tienda AND UT.estado = 1
			 ";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_CLASS, Usuario::class);
			$db = null;
			return $data;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "AdministradorService " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}

	public function Insert(Usuario $usuario, UsuarioTienda $usuarioTienda) : int
	{
		$db = MySql::Connect();
		$db->beginTransaction();
		$id_inserted = 0;
		try
		{
			$q =	"INSERT INTO usuarios(id, ci, cod, nombre, psw, direccion, id_comuna, telefono)
					VALUES(:id, :ci, :cod, :nombre, :psw, :direccion, :id_comuna, :telefono);";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":id", $usuario->id, PDO::PARAM_INT);
			$stmt->bindParam(":ci", $usuario->ci, PDO::PARAM_STR);
			$stmt->bindParam(":cod", $usuario->cod, PDO::PARAM_STR);
			$stmt->bindParam(":nombre", $usuario->nombre, PDO::PARAM_STR);
			$stmt->bindParam(":psw", $usuario->psw, PDO::PARAM_STR);
			$stmt->bindParam(":direccion", $usuario->direccion, PDO::PARAM_STR);
			$stmt->bindParam(":id_comuna", $usuario->id_comuna, PDO::PARAM_STR);
			$stmt->bindParam(":telefono", $usuario->telefono, PDO::PARAM_STR);
			$stmt->execute();

			$q =	"INSERT INTO usuarios_tienda(id_usuario, id_tienda, id_rol, estado)
					VALUES(:id_usuario, :id_tienda, :id_rol, :estado)";
			$stmt1 = $db->prepare($q);
			$stmt1->bindParam(":id_usuario", $usuarioTienda->id_usuario, PDO::PARAM_INT);
			$stmt1->bindParam(":id_tienda", $usuarioTienda->id_tienda, PDO::PARAM_INT);
			$stmt1->bindParam(":id_rol", $usuarioTienda->id_rol, PDO::PARAM_STR);
			$stmt1->bindParam(":estado", $usuarioTienda->estado, PDO::PARAM_INT);
			$stmt1->execute();
			$db->commit();
			$id_inserted = $db->lastInsertId();
		}
		catch (\PDOException $e) {
			$db->rollBack();
			echo "AdministradorService " . $e->getMessage()." on line ".$e->getLine();
		}
		$db = null;
		return $id_inserted;
	}

	public function Update(Usuario $usuario, string $id_rol, int $id_tienda) : bool
	{
		$db = MySql::Connect();
		$db->beginTransaction();
		$rowsAffected = false;
		try
		{
			$db = MySql::Connect();
			$q =	"UPDATE usuarios SET
						ci = :ci,
						cod = :cod,
						nombre = :nombre,
						psw = :psw,
						direccion = :direccion,
						id_comuna = :id_comuna,
						telefono = :telefono
					WHERE id = :id";

			$stmt = $db->prepare($q);
			$stmt->bindParam(":id", $usuario->id, PDO::PARAM_INT);
			$stmt->bindParam(":ci", $usuario->ci, PDO::PARAM_STR);
			$stmt->bindParam(":cod", $usuario->cod, PDO::PARAM_STR);
			$stmt->bindParam(":nombre", $usuario->nombre, PDO::PARAM_STR);
			$stmt->bindParam(":psw", $usuario->psw, PDO::PARAM_STR);
			$stmt->bindParam(":direccion", $usuario->direccion, PDO::PARAM_STR);
			$stmt->bindParam(":id_comuna", $usuario->id_comuna, PDO::PARAM_STR);
			$stmt->bindParam(":telefono", $usuario->telefono, PDO::PARAM_STR);
			$stmt->execute();
			$rowsAffected = ($stmt->rowCount() > 0) ? true : false;

			$q =	"UPDATE usuarios_tienda SET id_rol = :id_rol
					WHERE id_usuario = :id_usuario AND id_tienda = :id_tienda";
			$stmt1 = $db->prepare($q);
			$stmt1->bindParam(":id_usuario", $usuario->id, PDO::PARAM_INT);
			$stmt1->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
			$stmt1->bindParam(":id_rol", $id_rol, PDO::PARAM_STR);
			$stmt1->execute();
			$rowsAffected = (!$rowsAffected == false || $stmt1->rowCount() > 0) ? true : false;
			$db->commit();
		}
		catch (\PDOException $e) {
			$db->rollBack();
			echo "AdministradorService " . $e->getMessage()." on line ".$e->getLine();
		}
		$db = null;
		return $rowsAffected;
	}
	public function Delete(int $id_usuario, int $id_tienda) : bool
	{
		try
		{
			$db = MySql::Connect();
			$stmt = $db->prepare("UPDATE usuarios_tienda SET estado = 0 WHERE id_usuario = :id_usuario AND id_tienda = :id_tienda");
			$stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
			$stmt->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
			$stmt->execute();
			$rowsAffected = ($stmt->rowCount() > 0) ? true : false;
			$db = null;
			return $rowsAffected;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "AdministradorService " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
}