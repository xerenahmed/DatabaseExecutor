<?php

declare(strict_types=1);

namespace xerenahmed\example;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;
use Webmozart\PathUtil\Path;
use xerenahmed\database\DatabaseExecutorThread;

abstract class MyDatabaseHandler extends DatabaseExecutorThread{
	public function createConnection() : Connection{
		return self::createConnectionInternal($this->dataPath);
	}

	public static function createConnectionInternal(string $dataPath) : Connection{
		$capsule = new Manager();
		$capsule->addConnection([
			"driver" => "sqlite",
			"database" => Path::join($dataPath, "database.sqlite"),
			"prefix" => "",
		]);
		return $capsule->getConnection();
	}
}
