<?php
namespace App;
class Config
{
	public string $hash_semilla;
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
		$this->databases = $config['databases'];
	}
}
// $randomBytes = random_bytes(32);
// $tokenKey = bin2hex($randomBytes);