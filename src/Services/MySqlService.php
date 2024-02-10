<?php
namespace App\Services;
use \PDO;
use const App\DB_HOST;
use const App\DB_NAME;
use const App\DB_PORT;
use const App\DB_USER;
use const App\DB_PSW;
class MySqlService
{
	public static function Connect()
	{
		$dbConnection = new PDO("mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME, DB_USER, DB_PSW);
		$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $dbConnection;
	}
}