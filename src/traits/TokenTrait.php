<?php
namespace App\Traits;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Config;
trait TokenTrait
{
    private function Config() : Config
    {
        $config = new Config();
        return $config;
    }
	public function GenerateJWT($playLoad) : string
	{
		try
		{
			$playLoad["exp"] = time() + $this->Config()->token["exp"];
			return JWT::encode($playLoad, $this->Config()->token["key"], "HS256");
		}
		catch (\Throwable $th) {
			echo "TokenTrait " . $th->getMessage()." in line ".$th->getLine();
			http_response_code(500);
			die();
		}
	}
	public function DecodeJWT($token) : \stdClass|bool
	{
		try
		{
			if (!$token) {
				return false;
			}
			return JWT::decode($token, new Key($this->Config()->token["key"], 'HS256'));
		}
		catch (\Firebase\JWT\ExpiredException $e) {
			// Manejar la excepción de token expirado
			return false;
		}
		catch (\Exception $e) {
			return false;
			// echo "Error: " . $e->getMessage();
		}
	}
	public function UpdateJWT(Request $request) : string
	{
		$token = trim(str_replace("Bearer ", "", $request->getHeaderLine('Authorization')));
		return $this->GenerateJWT((array) $this->DecodeJWT($token));
	}
	public function getUserId(Request $request) : int
	{
		$token = trim(str_replace("Bearer ", "", $request->getHeaderLine('Authorization')));
		$playload = $this->DecodeJWT($token);
		return $playload->id_usuario;
	}
	/** @return \App\Models\TiendaDTO */
	public function getTienda(Request $request, int $id_tienda)
	{
		$token = trim(str_replace("Bearer ", "", $request->getHeaderLine('Authorization')));
		$playload = $this->DecodeJWT($token);
		$tienda = null;
		foreach ($playload->tienda as $value) {

			if ($value->id == $id_tienda) {
				$tienda = $value; 
			}
		}
		return $tienda;
	}
	public function getRol(Request $request, int $id_tienda) : string
	{
		$token = trim(str_replace("Bearer ", "", $request->getHeaderLine('Authorization')));
		$playload = $this->DecodeJWT($token);
		$tienda = null;
		foreach ($playload->tienda as $value) {
			if ($value->id == $id_tienda) {
				$tienda = $value; 
			}
		}
		return $tienda->id_rol;
	}
    public function GenerateEAN13(int $num) : string
	{
		try
		{
			$codigoSinDigito = $num;
			$codigoSinDigito = str_pad($codigoSinDigito, 12, '0', STR_PAD_LEFT);
			$digitos = str_split($codigoSinDigito);
			$sumaPares = $sumaImpares = 0;
			foreach ($digitos as $indice => $digito) {
				if (($indice % 2) == 0) {
					$sumaPares += $digito;
				} else {
					$sumaImpares += $digito;
				}
			}
			$sumaTotal = $sumaPares + $sumaImpares * 3;
			$digitoControl = (10 - ($sumaTotal % 10)) % 10;
			return  $codigoSinDigito . $digitoControl;
		}
		catch (\Throwable $th) {
			echo "UsuarioController " . $th->getMessage()." in line ".$th->getLine();
			http_response_code(500);
			die();
		}
	}
    private function GenerateEAN13Aleatorio() : string
	{
		try
		{
			$codigoSinDigito = rand(1, 1_000_000);
            $codigoSinDigito = str_pad($codigoSinDigito, 12, '0', STR_PAD_LEFT);
			$digitos = str_split($codigoSinDigito);
			$sumaPares = $sumaImpares = 0;
			foreach ($digitos as $indice => $digito) {
				if (($indice % 2) == 0) {
					$sumaPares += $digito;
				} else {
					$sumaImpares += $digito;
				}
			}
			$sumaTotal = $sumaPares + $sumaImpares * 3;
			$digitoControl = (10 - ($sumaTotal % 10)) % 10;
			return  $codigoSinDigito . $digitoControl;
		}
		catch (\Throwable $th) {
			return false;
		}
	}
}
