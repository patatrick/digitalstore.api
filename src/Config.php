<?php
namespace App;
class Config
{
	public string $hash_semilla;
	public string $ip_master;
	public array $cors;
	public array $token;
	public array $api;
	public array $databases;
	function __construct()
	{
		$xml = simplexml_load_file(__DIR__."/../web.config");
		$config = json_decode(json_encode($xml), true);
		$this->hash_semilla = $config['hash_semilla'];
		$this->token = $config['token'];
		$this->api = $config['api'];
		$this->cors = $config['cors'];
		$this->databases = $config['databases'];
		$this->ip_master = $config['ip_master'];
	}
}
// $randomBytes = random_bytes(32);
// $tokenKey = bin2hex($randomBytes);