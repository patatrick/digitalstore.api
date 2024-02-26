<?php
namespace App\Services;
use App\Services\MySqlService as Mysql;
use App\Models\Menu;
use \PDO;
class MenuService
{
	public function Traer(int $id_usuario, int $id_tienda) : array
	{
		try
		{
			$db = MySql::Connect();
			$q = "  SELECT m.*, CONVERT(m.estado, DECIMAL) estado
                    FROM menu m
                    INNER JOIN roles_menus ru ON m.id = ru.id_menu
                    INNER JOIN usuarios_tienda ut ON ru.id_rol = ut.id_rol
                    WHERE ut.id_usuario = ? AND ut.id_tienda = ? AND m.estado = 1";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $id_usuario, PDO::PARAM_INT);
			$stmt->bindParam(2, $id_tienda, PDO::PARAM_INT);
			$stmt->execute();
			$data = $stmt->fetchAll(PDO::FETCH_CLASS, Menu::class);
			$db = null;
			return $data;
		}
		catch (\PDOException $e) {
			$db = null;
			echo $e->getMessage()." in line ".$e->getLine();
			http_response_code(422);
			die();
		}
	}
}