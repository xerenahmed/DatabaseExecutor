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

use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use pocketmine\Server;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\thread\Thread;
use poggit\virion\devirion\DEVirion;
use function array_shift;
use function array_slice;
use function count;
use function get_class;
use function implode;
use function in_array;
use function intval;
use function is_array;
use function is_string;
use function serialize;
use function sprintf;
use function unserialize;

abstract class DatabaseExecutorThread extends Thread{
	protected string $dataPath;
	protected \Threaded $executor;
	protected \ThreadedLogger $logger;
	protected string $logPrefix = "DatabaseExecutor";
	protected string $name;

	public function __construct(
		public HandlerQueue    $queue,
		public SleeperNotifier $notifier
	){
		$this->executor = new \Threaded();
		$server = Server::getInstance();
		$this->dataPath = $server->getDataPath();
		$this->logger = $server->getLogger();
		$this->name = parent::getThreadName();

		$classLoaders = [$server->getLoader()];
		$deVirion = $server
			->getPluginManager()
			->getPlugin('DEVirion');
		if($deVirion instanceof DEVirion){
			$classLoaders[] = $deVirion->getVirionClassLoader();
		}
		$this->setClassLoaders($classLoaders);
	}

	/**
	 * @throws \Exception
	 */
	public function onRun(): void{
		\GlobalLogger::set($this->logger);
		\GlobalLogger::get()->info($this->getName() . ' started');
		$connection = $this->createConnection();

		$resolver = new ConnectionResolver();
		$resolver->addConnection('default', $connection);
		$resolver->setDefaultConnection('default');
		Model::setConnectionResolver($resolver);

		$connection->enableQueryLog();
		while(true){
			$value = $this->queue->fetch();
			if($value === null){
				break;
			}

			$data = unserialize($value);
			if(!is_array($data) || count($data) < 1){
				throw new \InvalidArgumentException('Invalid data');
			}

			$id = array_shift($data);
			$error = null;
			$result = null;
			$logCountsOld = count($connection->getQueryLog());
			try{
				$result = $this->handle($connection, $data);
			}catch(\Exception $e){
				$error = $e;
			}

			$logCountsNew = count($connection->getQueryLog());

			if($logCountsNew > $logCountsOld){
				foreach(array_slice($connection->getQueryLog(), $logCountsOld) as $index => $query){
					$this->log(intval($logCountsOld + $index), $query);
				}
			}

			if($error !== null){
				$this->logError($id, $error);
			}

			$this->executor[] = serialize([$id, $error?->getMessage(), $result]);
			$this->notifier->wakeupSleeper();
		}

		$connection->disconnect();
		\GlobalLogger::get()->info($this->getName() . ' ended');
	}

	abstract  public function createConnection(): Connection;

	/**
	 * @param mixed[] $data
	 */
	abstract public function handle(Connection $connection, array $data): mixed;

	/**
	 * @param \Closure[] $handlers
	 */
	public function fetchResults(array &$handlers): void{
		if($this->executor->count() < 1){
			return;
		}

		while(true){
			$raw = $this->executor->shift();
			if(!is_string($raw)){
				break;
			}

			$result = unserialize($raw);
			if(!is_array($result) || count($result) !== 3){
				throw new \RuntimeException('Invalid result');
			}

			$id = array_shift($result);
			$error = array_shift($result);
			$values = array_shift($result);

			if($handlers[$id] !== null){
				$handler = $handlers[$id];
				if(!empty($error)){
					$handler(false, $error);
				}else{
					$handler(true, $values);
				}
				unset($handlers[$id]);
			}
		}
	}

	public function getName(): string{
		return $this->name;
	}

	public function setName(string $name): void{
		$this->name = $name;
	}

	public function getThreadName() : string{
		return $this->name;
	}

	protected function logError(int $id, \Exception $error): void{
		$errorLogMessage = "[{$this->logPrefix}] [Error] [$id] {$error->getMessage()}";
		$isErrorImportant = $error instanceof QueryException && !in_array($error->getCode(), ["23000", "22003"], true); // duplicate entry, out of range value
		\GlobalLogger::get()->{$isErrorImportant ? 'error' : 'debug'}($errorLogMessage);
	}

	/**
	 * @param array{query: string, bindings: string[], time: string} $logData
	 */
	protected function log(
		int    $index,
		array  $logData
	): void{
		\GlobalLogger::get()->debug(
			sprintf(
				"[{$this->logPrefix}] [Execution] [$index] %s [%s] [%s ms]",
				$logData['query'],
				implode(', ', $logData['bindings']),
				$logData['time']
			)
		);
	}

	public function quit(): void{
		$this->queue->kill();
		parent::quit();
	}
}