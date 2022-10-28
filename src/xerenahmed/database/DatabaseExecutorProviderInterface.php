<?php

declare(strict_types=1);

namespace xerenahmed\database;

use pocketmine\snooze\SleeperNotifier;

interface DatabaseExecutorProviderInterface{
	public function createThread(HandlerQueue $handlerQueue, SleeperNotifier $notifier) : DatabaseExecutorThread;
}
