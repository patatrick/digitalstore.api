<?php
namespace App\Services;
use \PDO;
use App\Config;
class MySqlService
{
	public static function Connect()
	{
        $config = new Config();
        $dsn = $config->databases["digitalpost"]["dsn"];
        $user = $config->databases["digitalpost"]["user"];
        $psw = $config->databases["digitalpost"]["psw"];
        $dbConnection = new PDO($dsn, $user, $psw);
		$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $dbConnection;
	}
}