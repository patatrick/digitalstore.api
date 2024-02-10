<?php
namespace App\Services;
use App\Models\Tienda;
use App\Models\Usuario;
use App\Models\UsuarioDTO;
use App\Services\MySqlService As MySql;
use \PDO;
class LoginService
{
	public function Index(string $ci, string $psw) : UsuarioDTO | null
	{
		try
		{
			$db = MySql::Connect();
			$q = " SELECT id, ci, nombre, direccion, id_comuna, telefono, id_rol
				   FROM usuarios WHERE ci = ? AND psw = ?";
			$stmt1 = $db->prepare($q);
			$stmt1->bindParam(1, $ci, PDO::PARAM_STR);
			$stmt1->bindParam(2, $psw, PDO::PARAM_STR);
			$stmt1->execute();
			$dataUser = $stmt1->fetchObject(Usuario::class);
			if (!$dataUser) {
				return null;
			}

			$q = "	SELECT t.* FROM usuarios_tienda ut
					INNER JOIN tienda t ON ut.id_tienda = t.id
					WHERE id_usuarios = ?";
			$stmt2 = $db->prepare($q);
			$stmt2->bindParam(1, $dataUser->id, PDO::PARAM_INT);
			$stmt2->execute();
			$dataTienda = $stmt2->fetchAll(PDO::FETCH_CLASS, Tienda::class);
			
			$usuarioDTO = new UsuarioDTO();
			$usuarioDTO->Usuario = $dataUser;
			$usuarioDTO->Tienda = $dataTienda;
			$db = null;
			return $usuarioDTO;
		}
		catch (\PDOException $e) {
			$db = null;
			echo $e->getMessage()." in line ".$e->getLine();
			return null;
		}
	}
}