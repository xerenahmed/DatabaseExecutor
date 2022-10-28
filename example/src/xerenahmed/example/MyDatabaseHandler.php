<?php

declare(strict_types=1);

namespace xerenahmed\example;

use Illuminate\Database\Capsule\Manager;
use Webmozart\PathUtil\Path;
use xerenahmed\database\DatabaseExecutorThread;

abstract class MyDatabaseHandler extends DatabaseExecutorThread{

	public function createCapsule(): Manager{
		return Main::newCapsule($this->dataPath);
	}

	public function registerClassLoaders(): void{
		parent::registerClassLoaders();

		require_once Path::join($this->dataPath, "vendor/autoload.php");
	}
}
