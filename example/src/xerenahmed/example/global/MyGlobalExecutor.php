<?php

declare(strict_types=1);

namespace xerenahmed\example\global;

use pocketmine\snooze\SleeperNotifier;
use xerenahmed\database\DatabaseExecutorProvider;
use xerenahmed\database\DatabaseExecutorThread;
use xerenahmed\database\global\GlobalExecutor;
use xerenahmed\database\HandlerQueue;

class MyGlobalExecutor extends GlobalExecutor{
	use DatabaseExecutorProvider;

	public function createThread(HandlerQueue $handlerQueue, SleeperNotifier $notifier) : DatabaseExecutorThread{
		return new MyGlobalExecutorThread($handlerQueue, $notifier);
	}
}
