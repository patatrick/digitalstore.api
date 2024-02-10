<?php
namespace App\Traits;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use const App\TK_KEY;
use const App\TK_EXP;
trait TokenTrait
{
	public function GenerateJWT($playLoad) : string
	{
		try
		{
			$playLoad["exp"] = time() + TK_EXP;
			return JWT::encode($playLoad, TK_KEY, "HS256");
		}
		catch (\Throwable $th) {
			echo "className " . $th->getMessage()." in line ".$th->getLine();
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
			return JWT::decode($token, new Key(TK_KEY, 'HS256'));
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
	public function getRolId(Request $request) : int
	{
		$token = trim(str_replace("Bearer ", "", $request->getHeaderLine('Authorization')));
		$playload = $this->DecodeJWT($token);
		return $playload->id_rol;
	}
}
