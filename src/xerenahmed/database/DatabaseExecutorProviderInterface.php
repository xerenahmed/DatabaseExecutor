<?php

declare(strict_types=1);

namespace xerenahmed\database;

use GuzzleHttp\Promise\Promise;
use pocketmine\snooze\SleeperNotifier;

interface DatabaseExecutorProviderInterface{
	public function createThread(HandlerQueue $handlerQueue, SleeperNotifier $notifier): DatabaseExecutorThread;

	public function waitAll(): void;

	public function stop(): void;

	public function createPromise(mixed ...$values): Promise;

	public function createAsync(mixed ...$values): \Generator;
}
