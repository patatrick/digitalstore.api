<?php
namespace App\Services;
use App\Services\MySqlService as Mysql;
use App\Models\Usuario;
use \PDO;

class UsuarioService
{
	public function GetAll(int $id_tienda) : array
	{
		try
		{
			$db = MySql::Connect();
			$q = "SELECT U.id, U.ci, U.nombre, U.direccion, U.id_comuna, U.telefono, UT.id_rol
			FROM usuarios U INNER JOIN usuarios_tienda UT ON U.id = UT.id_usuario
			WHERE UT.id_tienda = ? AND (UT.id_rol = 'J' OR UT.id_rol = 'C')";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $id_tienda, PDO::PARAM_INT);
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_CLASS, Usuario::class);
			
			$db = null;
			return $data;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "Usuario " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	public function GetOneByTienda(int $id_tienda, string $id_cod_usuario) : Usuario | bool
	{
		try
		{
			$db = MySql::Connect();
			$q = "	SELECT U.id, U.ci, U.nombre, U.direccion, U.id_comuna, U.telefono
					FROM usuarios U INNER JOIN usuarios_tienda UT ON U.id = UT.id_usuario
					WHERE UT.id_tienda = :id_tienda AND (UT.id_rol = 'J' OR UT.id_rol = 'C')
					AND (U.ci = :id_cod_usuario OR U.cod = :id_cod_usuario)";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
			$stmt->bindParam(":id_cod_usuario", $id_cod_usuario, PDO::PARAM_STR);
			$stmt->execute();
			$data = $stmt->fetchObject(Usuario::class);
			$db = null;
			return $data;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "Usuario " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	/** Retorna el id del usuario encontrado o false sino lo encuentra.*/
	public function GetOne(string $ci) : Usuario | bool
	{
		try
		{
			$db = MySql::Connect();
			$q = "	SELECT * FROM usuarios WHERE ci = :ci";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":ci", $ci, PDO::PARAM_STR);
			$stmt->execute();
			$data = $stmt->fetchObject(Usuario::class);
			$db = null;
			return $data ? $data : false;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "Usuario " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	/** Retorna Id insertado */
	public function Insert(Usuario $usuario, int $id_tienda, string $id_rol) : int
	{
		$db = MySql::Connect();
		$db->beginTransaction();
		try
		{
			$userExiste = $this->GetOne($usuario->ci);
			if ($userExiste === false) {
				$q = "  INSERT INTO usuarios(ci, cod, nombre, psw, direccion, id_comuna, telefono)
						VALUES(:ci, :cod, :nombre, :psw, :direccion, :id_comuna, :telefono)";
				$stmt1 = $db->prepare($q);
				$stmt1->bindParam(":ci", $usuario->ci, PDO::PARAM_STR);
				$stmt1->bindParam(":cod", $usuario->cod, PDO::PARAM_STR);
				$stmt1->bindParam(":nombre", $usuario->nombre, PDO::PARAM_STR);
				$stmt1->bindParam(":psw", $usuario->psw, PDO::PARAM_STR);
				$stmt1->bindParam(":direccion", $usuario->direccion, PDO::PARAM_STR);
				$stmt1->bindParam(":id_comuna", $usuario->id_comuna, PDO::PARAM_INT);
				$stmt1->bindParam(":telefono", $usuario->telefono, PDO::PARAM_STR);
				$stmt1->execute();
				$id_usuario = $db->lastInsertId("id_usuario");
			}
            else {
                $id_usuario = $userExiste->id;
            }
			$q = "  INSERT INTO usuarios_tienda(id_usuario, id_tienda, id_rol)
					VALUES(:id_usuario, :id_tienda, :id_rol);";
			$stmt2 = $db->prepare($q);
			$stmt2->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
			$stmt2->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
			$stmt2->bindParam(":id_rol", $id_rol, PDO::PARAM_STR);
			$stmt2->execute();
			
			$db->commit();
		}
		catch (\PDOException $e) {
			$db->rollBack();
			echo "Usuario " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
		}
		$db = null;
		return $id_usuario;
	}
	/** Retorna true si se logra actualizar el registro */
	public function UpdateRol(int $id_usuario, int $id_tienda, string $id_rol) : bool
	{
		try
		{
            $db = MySql::Connect();
			$q = "  UPDATE usuarios_tienda SET
					id_rol = :id_rol
					WHERE id = :id_usuario AND id_tienda = :id_tienda
			";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
			$stmt->bindParam(":id_rol", $id_rol, PDO::PARAM_STR);
			$stmt->execute();
			$db->commit();
			$exito = ($stmt->rowCount()) ? true : false;
            $db = null;
            return $exito;
		}
		catch (\PDOException $e) {
            $db = null;
			echo "Usuario " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
            die();
		}
	}
	/** Retorna true si se logra actualizar el registro */
	public function Update(Usuario $usuario) : bool
	{
		try
		{
            $db = MySql::Connect();
			$q = "  UPDATE usuarios SET
					nombre = :nombre,
					psw = :psw,
					direccion = :direccion,
					id_comuna = :id_comuna,
					telefono = :telefono
					WHERE id = :id
			";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":id", $usuario->id, PDO::PARAM_INT);
			$stmt->bindParam(":nombre", $usuario->nombre, PDO::PARAM_STR);
			$stmt->bindParam(":psw", $usuario->psw, PDO::PARAM_STR);
			$stmt->bindParam(":direccion", $usuario->direccion, PDO::PARAM_STR);
			$stmt->bindParam(":id_comuna", $usuario->id_comuna, PDO::PARAM_INT);
			$stmt->bindParam(":telefono", $usuario->telefono, PDO::PARAM_STR);
			$stmt->execute();
            $exito = ($stmt->rowCount()) ? true : false;
            $db = null;
            return $exito;
		}
		catch (\PDOException $e) {
            $db = null;
			echo "Usuario " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
            die();
		}
	}
	/** Retorna true si se logra borrar el registro */
	public function Delete(int $id_usuario, int $id_tienda, bool $estado) : bool
	{
		try
		{
			$db = MySql::Connect();
			$q = "  UPDATE usuarios_tienda SET estado = :estado
					WHERE id_usuario = :id_usuario AND id_tienda = :id_tienda
			";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
			$stmt->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
			$stmt->bindParam(":estado", $estado, PDO::PARAM_BOOL);
			$stmt->execute();
			$exito = $stmt->rowCount() ? true : false;
			$db = null;
			return $exito;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "Usuario " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			DIE();
		}
	}
}