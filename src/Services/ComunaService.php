<?php
namespace App\Services;
use App\Config;
use App\Models\Comuna;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
class ComunaService
{
	private readonly Config $config;
	public function __construct() {
		$this->config = new Config();
	}

	/** @return Comuna[] */
	public function GetAll() : array
	{
		try
		{
			$client = new Client();
			$responseGuzzle = $client->get($this->config->api['digital_gob_dpa']);
			if ($responseGuzzle->getStatusCode() != 200) {
				throw new \Exception($responseGuzzle->getBody()->getContents());
			}
			$objResponse = json_decode($responseGuzzle->getBody()->getContents());
			$client = null;

			return $objResponse;
		}
		catch (RequestException $e) {
			$client = null;
			echo "ComunaService " . $e->getMessage()." on line ".$e->getLine();
			http_response_code(500);
			die();
		}
	}
}