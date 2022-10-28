<?php

declare(strict_types=1);

namespace xerenahmed\database;

use pocketmine\Server;
use xerenahmed\database\global\GlobalExecutor;
use xerenahmed\database\global\GlobalExecutorPromised;
use function get_class;

class ExecutorManager{
	/** @var DatabaseExecutorProvider[] */
	public array $executors = [];

	public static function create() : ExecutorManager{
		return new ExecutorManager();
	}

	public function register(DatabaseExecutorProviderInterface $executor) : void{
		$this->executors[] = $executor;
	}

	public function quit() : void{
		foreach($this->executors as $executor){
			Server::getInstance()->getLogger()->debug("Waiting executor " . get_class($executor));
			$executor->waitAll();
			Server::getInstance()->getLogger()->debug("Shutting down executor " . get_class($executor));
			$executor->stop();
		}
	}
}
