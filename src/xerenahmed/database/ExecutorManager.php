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

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use pocketmine\Server;
use function get_class;

class ExecutorManager{
	/** @var DatabaseExecutorProviderInterface[] */
	public array $executors = [];

	public static function create(): ExecutorManager{
		return new ExecutorManager();
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

	public static ?ConnectionResolver $resolver = null;

	public static function registerCapsule(string $name, Manager $capsule): void{
		if(self::$resolver === null){
			self::$resolver = new ConnectionResolver();
			Model::setConnectionResolver(self::$resolver);
		}

		if(self::$resolver->hasConnection($name)){
			throw new \InvalidArgumentException("Connection $name already exists");
		}

		self::$resolver->addConnection($name, $capsule->getConnection());
	}

	/**
	 * @param array<string, mixed> $options
	 */
	public static function newCapsule(string $name, array $options): Manager{
		$capsule = new Manager();
		$capsule->addConnection($options, $name);
		$capsule->getDatabaseManager()->setDefaultConnection($name);
		return $capsule;
	}
}
