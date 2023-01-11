<?php

/*
 * ______         _ __  ___ ____
 * | ___ \       | |  \/  /  __ \
 * | |_/ /___  __| | .  . | /  \/
 * |    // _ \/ _` | |\/| | |
 * | |\ \  __/ (_| | |  | | \__/\
 * \_| \_\___|\__,_\_|  |_/\____/
 *
 * Copyright (C) RedMC Network, Inc - All Rights Reserved
 *
 * You may use, distribute and modify this code under the
 * terms of the MIT license, which unfortunately won't be
 * written for another century.
 *
 * Written by xerenahmed <eren@redmc.me>, 2023
 *
 * @author RedMC Team
 * @link https://www.redmc.me/
 */

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
