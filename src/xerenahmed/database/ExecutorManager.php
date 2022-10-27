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
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Eren Ahmet Akyol <ahmederen123@gmail.com>, 2022
 *
 * @author RedMC Team
 * @link https://www.redmc.me/
 */

declare(strict_types=1);

namespace xerenahmed\database;

use pocketmine\Server;
use xerenahmed\database\global\GlobalExecutor;
use xerenahmed\database\global\GlobalExecutorPromised;
use function get_class;

class ExecutorManager{
	/** @var DatabaseExecutorProvider[] */
	public array $executors = [];

	public static function create(): ExecutorManager{
		return new ExecutorManager();
	}

	public static function createWithGlobal(): ExecutorManager{
		$executorManager = new ExecutorManager();
		$executorManager->register(GlobalExecutor::getInstance());
		$executorManager->register(GlobalExecutorPromised::getInstance());
		return $executorManager;
	}

	public function register(DatabaseExecutorProviderInterface $executor): void{
		$this->executors[] = $executor;
	}

	public function quit(): void{
		foreach($this->executors as $executor){
			Server::getInstance()->getLogger()->debug("Waiting executor " . get_class($executor));
			$executor->waitAll();
			Server::getInstance()->getLogger()->debug("Shutting down executor " . get_class($executor));
			$executor->stop();
		}
	}
}
