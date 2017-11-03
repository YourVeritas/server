<?php

class DateBase
{ 
	private static $db = null;
	

	public static function getInstance()
	{     
		if(is_null(self::$db)) 
		{
			self::$db = new PDO('mysql:host='.DB_HOST.'; dbname='.DB_DB , DB_USER, DB_PASSWORD);
		} 
		return self::$db;
	}	
	
	private function __construct(){}
	private function __clone() {}
	private function __wakeup() {}
	

	function __destruct()
	{
		mysqli_close(self::$db);
	}
}
