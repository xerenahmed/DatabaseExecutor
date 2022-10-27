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

namespace xerenahmed\database\global;

use GuzzleHttp\Promise\Promise;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use pocketmine\snooze\SleeperNotifier;
use xerenahmed\database\DatabaseExecutorProvider;
use xerenahmed\database\DatabaseExecutorProviderInterface;
use xerenahmed\database\HandlerQueue;
use function get_class;

class GlobalExecutorPromised implements DatabaseExecutorProviderInterface {
	use DatabaseExecutorProvider;

	public function createThread(HandlerQueue $handlerQueue, SleeperNotifier $notifier): GlobalExecutorThread{
		$thread = new GlobalExecutorThread($handlerQueue, $notifier);
		$thread->setName("PromisedGlobalExecutor");
		return $thread;
	}

	public function get(Builder $builder): Promise{
		$modelClass = get_class($builder->getModel());

		return $this->runBuilderMethod($builder, "get")->then(function($collection) use ($modelClass){
			return $collection->map(function(\stdClass $model) use ($modelClass){
				return (new $modelClass())->fill((array) $model);
			});
		});
	}

	public function first(Builder $builder): Promise{
		$modelClass = get_class($builder->getModel());

		return $this->runBuilderMethod($builder, "first")->then(function($values) use ($modelClass){
			if($values === null){
				return null;
			}

			return (new $modelClass())->fill((array) $values);
		});
	}

	public function update(Builder $builder, array $values): Promise{
		return $this->runBuilderMethod($builder, "update", [$values]);
	}

	public function delete(Builder $builder): Promise{
		return $this->runBuilderMethod($builder, "delete");
	}

	public function insert(Builder $builder, array $values): Promise{
		return $this->runBuilderMethod($builder, "insert", [$values]);
	}

	public function save(Model $model): Promise{
		$model->setConnection(null);
		return $this->createPromise("save", $model);
	}

	public function create(string $modelClass, array $attributes): Promise{
		return $this->createPromise("create", $modelClass, [$attributes]);
	}

	public function runBuilderMethod(Builder $builder, string $method, mixed ...$values): Promise{
		$baseQuery = $builder->toBase();
		$baseQuery->connection = null;

		return $this->createPromise($method, $baseQuery, ...$values);
	}
}
