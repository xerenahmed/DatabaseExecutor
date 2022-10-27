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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use pocketmine\snooze\SleeperNotifier;
use xerenahmed\database\DatabaseExecutorProvider;
use xerenahmed\database\DatabaseExecutorProviderInterface;
use xerenahmed\database\HandlerQueue;
use function get_class;

class GlobalExecutor implements DatabaseExecutorProviderInterface{
	use DatabaseExecutorProvider;

	public function createThread(HandlerQueue $handlerQueue, SleeperNotifier $notifier): GlobalExecutorThread{
		return new GlobalExecutorThread($handlerQueue, $notifier);
	}

	public function get(Builder $builder): \Generator{
		$modelClass = get_class($builder->getModel());

		$collection = yield from $this->runBuilderMethod($builder, "get");
		return $collection->map(function(\stdClass $model) use($modelClass){
			return (new $modelClass())->fill((array) $model);
		});
	}

	public function first(Builder $builder): \Generator{
		$modelClass = get_class($builder->getModel());

		$values = yield from $this->runBuilderMethod($builder, "first");
		if($values === null){
			return null;
		}

		return (new $modelClass())->fill((array) $values);
	}

	public function update(Builder $builder, array $values): \Generator{
		return yield from $this->runBuilderMethod($builder, "update", [$values]);
	}

	public function delete(Builder $builder): \Generator{
		return yield from $this->runBuilderMethod($builder, "delete");
	}

	public function insert(Builder $builder, array $values): \Generator{
		return yield from $this->runBuilderMethod($builder, "insert", [$values]);
	}

	public function save(Model $model): \Generator{
		$model->setConnection(null);
		return yield from $this->createAsync("save", $model);
	}

	public function create(string $modelClass, array $attributes): \Generator{
		return yield from $this->createAsync("create", $modelClass, [$attributes]);
	}

	public function runBuilderMethod(Builder $builder, string $method, mixed ...$values): \Generator{
		$baseQuery = $builder->toBase();
		$baseQuery->connection = null;

		return yield from $this->createAsync($method, $baseQuery, ...$values);
	}
}
