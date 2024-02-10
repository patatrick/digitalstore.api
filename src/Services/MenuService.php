<?php
namespace App\Services;
use App\Services\MySqlService as Mysql;
use App\Models\Menu;
use \PDO;
class MenuService
{
	public function Traer(int $id_usuario) : array
	{
		try
		{
			$db = MySql::Connect();
			$q = "SELECT * FROM menu m INNER JOIN usuarios_menus um ON m.id = um.id_menu WHERE um.id_usuarios = ?";
			$stmt = $db->prepare($q);
			$stmt->bindParam(1, $id_usuario, PDO::PARAM_INT);
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