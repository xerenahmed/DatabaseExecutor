<?php

declare(strict_types=1);

namespace xerenahmed\example\executors;

use pocketmine\snooze\SleeperNotifier;
use xerenahmed\database\DatabaseExecutorProvider;
use xerenahmed\database\DatabaseExecutorProviderInterface;
use xerenahmed\database\DatabaseExecutorThread;
use xerenahmed\database\HandlerQueue;

class CustomExecutor implements DatabaseExecutorProviderInterface{
	use DatabaseExecutorProvider;

	public function createThread(HandlerQueue $handlerQueue, SleeperNotifier $notifier) : DatabaseExecutorThread{
		return new CustomExecutorThread($handlerQueue, $notifier);
	}

	public function createTables() : \Generator{
		return $this->createAsync("createTables");
	}
}
