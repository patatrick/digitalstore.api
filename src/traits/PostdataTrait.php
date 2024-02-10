<?php
namespace App\Traits;
trait PostdataTrait
{
	public function parseToClass(array | null $postData, $myClass)
	{
		try
		{
			if (!is_array($postData) || count($postData) == 0 || !class_exists($myClass)) {
				return false;
			}
			$obj = new $myClass();
			foreach ($postData as $key => $value) {
				$obj->{$key} = $value;
			}
			return $obj;
		}
		catch (\Throwable $th) {
			return false;
		}
	}	
}
