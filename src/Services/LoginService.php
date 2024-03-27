<?php
namespace App\Services;
use App\Config;
use App\Models\Empleado;
use App\Models\TiendaDTO;
use App\Models\Usuario;
use App\Models\UsuarioDTO;
use App\Services\MySqlService As MySql;
use \PDO;
class LoginService
{
    private readonly Config $_config;
    public function __construct() {
        $this->_config = new Config();
    }
	public function Index(string $ci, string $psw) : UsuarioDTO | null
	{
		try
		{
			$db = MySql::Connect();
			$q = "SELECT id, ci, cod, nombre, direccion, id_comuna, telefono FROM usuarios WHERE ci = ? AND psw = ?";
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
			echo "LoginService " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	public function LoginVenta(int $id_tienda, int $nro_caja, string $ip, string $sku_jefeTienda) : object | null
	{
		try
		{
			$db = MySql::Connect();
			$q = "SELECT t.id, t.nombre, t.cant_cajas FROM tienda t
            INNER JOIN empleados e ON e.id_tienda = t.id
            WHERE t.id = :id_tienda AND e.cod = :sku AND (t.ip = :ip OR :ip = :ip_master) AND :nro_caja <= t.cant_cajas";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
			$stmt->bindParam(":sku", $sku_jefeTienda, PDO::PARAM_STR);
			$stmt->bindParam(":ip", $ip, PDO::PARAM_STR);
			$stmt->bindParam(":ip_master", $this->_config->ip_master, PDO::PARAM_STR);
			$stmt->bindParam(":nro_caja", $nro_caja, PDO::PARAM_INT);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_OBJ);
			$db = null;
			return $data ? $data : null;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "LoginService " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
	public function LoginCajero(string $sku, int $id_tienda, string $ip) : Empleado | null
	{
		try
		{
			$db = MySql::Connect();
			$q = "SELECT e.* FROM empleados e INNER JOIN tienda t ON e.id_tienda = t.id
				  WHERE e.cod = :cod AND (t.ip = :ip OR :ip = :ip_master) AND t.id = :id_tienda AND e.estado = 1";
			$stmt = $db->prepare($q);
			$stmt->bindParam(":cod", $sku, PDO::PARAM_STR);
			$stmt->bindParam(":ip", $ip, PDO::PARAM_STR);
            $stmt->bindParam(":ip_master", $this->_config->ip_master, PDO::PARAM_STR);
			$stmt->bindParam(":id_tienda", $id_tienda, PDO::PARAM_INT);
			$stmt->execute();
			$data = $stmt->fetchObject(Empleado::class);
			$db = null;
			return $data ? $data : null;
		}
		catch (\PDOException $e) {
			$db = null;
			echo "LoginService " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
}