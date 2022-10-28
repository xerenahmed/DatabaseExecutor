<?php

declare(strict_types=1);

namespace xerenahmed\example\global;

use Illuminate\Database\Connection;
use xerenahmed\database\global\GlobalExecutorThread;
use xerenahmed\example\MyDatabaseHandler;

class MyGlobalExecutorThread extends GlobalExecutorThread {
	public function createConnection() : Connection{
		return MyDatabaseHandler::createConnectionInternal($this->dataPath);
	}
}
