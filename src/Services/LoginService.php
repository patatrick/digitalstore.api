<?php
namespace App\Services;
use App\Models\TiendaDTO;
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
			$q = "  SELECT id, ci, cod, nombre, direccion, id_comuna, telefono
				FROM usuarios WHERE ci = ? AND psw = ?";
			$stmt1 = $db->prepare($q);
			$stmt1->bindParam(1, $ci, PDO::PARAM_STR);
			$stmt1->bindParam(2, $psw, PDO::PARAM_STR);
			$stmt1->execute();
			$dataUser = $stmt1->fetchObject(Usuario::class);
			if (!$dataUser) {
				return null;
			}

			$q = "  SELECT t.id, t.nombre, ut.id_rol, t.ip, t.cant_cajas FROM usuarios_tienda ut
					INNER JOIN tienda t ON ut.id_tienda = t.id
					WHERE ut.id_usuario = ?
					AND ( ut.id_rol = 'A' OR ut.id_rol = 'J' )
					ORDER BY ut.id_rol";
			$stmt2 = $db->prepare($q);
			$stmt2->bindParam(1, $dataUser->id, PDO::PARAM_INT);
			$stmt2->execute();
			$dataTienda = $stmt2->fetchAll(PDO::FETCH_CLASS, TiendaDTO::class);
			
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