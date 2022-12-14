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

namespace xerenahmed\database;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\Utils;
use pocketmine\Server;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\utils\SingletonTrait;
use SOFe\AwaitGenerator\Await;
use function is_string;
use function usleep;

trait DatabaseExecutorProvider{
	use SingletonTrait;

	protected int $idCounter = 0;
	protected DatabaseExecutorThread $handlerTask;
	/** @var \Closure[] */
	protected array $handlers = [];
	protected HandlerQueue $queue;
	protected bool $isRunning = false;

	public function __construct(){
		$this->queue = new HandlerQueue();
		$notifier = new SleeperNotifier();
		$this->handlerTask = $this->createThread($this->queue, $notifier);

		Server::getInstance()
			->getTickSleeper()
			->addNotifier($notifier, function(): void{
				$this->readResults();
			});
		$this->handlerTask->start();
		$this->isRunning = true;
	}

	protected function publish(\Closure $handler): int{
		if(!$this->isRunning){
			throw new \RuntimeException('Executor is not running');
		}

		$id = $this->idCounter++;
		$this->handlers[$id] = $handler;
		return $id;
	}

	public function createPromise(mixed ...$values): Promise{
		$promise = new Promise();
		$id = $this->publish(function(bool $status, $value) use ($promise): void{
			if($status){
				$promise->resolve($value);
			}else{
				$promise->reject($value);
			}
		});
		$this->queue->schedule($id, ...$values);
		return $promise;
	}

	public function createAsync(mixed ...$values): \Generator{
		return Await::promise(function($resolve, $reject) use ($values): void{
			$id = $this->publish(function(bool $status, $value) use ($resolve, $reject): void{
				if($status){
					$resolve($value);
				}else{
					$reject(is_string($value) ? new \Exception($value) : $value);
				}
			});
			$this->queue->schedule($id, ...$values);
		});
	}

	public function readResults(): void{
		$this->handlerTask->fetchResults($this->handlers);
		Utils::queue()->run();
	}

	public function waitAll(): void{
		while(!empty($this->handlers)){
			$this->readResults();
			usleep(1000);
		}
	}

	public function stop(): void{
		$this->handlerTask->quit();
		$this->isRunning = false;
	}
}
