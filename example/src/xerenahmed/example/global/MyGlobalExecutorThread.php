<?php

declare(strict_types=1);

namespace xerenahmed\example\global;

use Illuminate\Database\Capsule\Manager;
use Webmozart\PathUtil\Path;
use xerenahmed\database\global\GlobalExecutorThread;
use xerenahmed\example\Main;

class MyGlobalExecutorThread extends GlobalExecutorThread{
	public function createCapsule(): Manager{
		return Main::newCapsule($this->dataPath);
	}

	public function registerClassLoaders(): void{
		parent::registerClassLoaders();

		require_once Path::join($this->dataPath, "vendor/autoload.php");
	}
}
