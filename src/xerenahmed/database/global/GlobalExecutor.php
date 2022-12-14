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

namespace xerenahmed\database\global;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use xerenahmed\database\DatabaseExecutorProviderInterface;
use function get_class;

/**
 * @mixin DatabaseExecutorProviderInterface
 */
abstract class GlobalExecutor implements DatabaseExecutorProviderInterface{
	public function get(Builder $builder): \Generator{
		$modelClass = get_class($builder->getModel());

		$collection = yield from $this->runBuilderMethod($builder, "get");
		return $collection->map(function(\stdClass $values) use ($modelClass){
			/** @var Model $model */
			$model = new $modelClass();

			return $model->fill((array) $values);
		});
	}

	public function first(Builder $builder): \Generator{
		$modelClass = get_class($builder->getModel());

		$values = yield from $this->runBuilderMethod($builder, "first");
		if($values === null){
			return null;
		}

		/** @var Model $model */
		$model = new $modelClass();
		return $model->fill((array) $values);
	}

	/**
	 * @param array<string, mixed> $values
	 */
	public function update(Builder $builder, array $values): \Generator{
		return yield from $this->runBuilderMethod($builder, "update", [$values]);
	}

	public function delete(Builder $builder): \Generator{
		return yield from $this->runBuilderMethod($builder, "delete");
	}

	/**
	 * @param array<string, mixed> $values
	 */
	public function insert(Builder $builder, array $values): \Generator{
		return yield from $this->runBuilderMethod($builder, "insert", [$values]);
	}

	public function save(Model $model): \Generator{
		$model->setConnection(null);
		return yield from $this->createAsync("save", $model);
	}

	/**
	 * @param array<string, mixed> $attributes
	 */
	public function create(string $modelClass, array $attributes): \Generator{
		return yield from $this->createAsync("create", $modelClass, [$attributes]);
	}

	public function raw(string $method, string $rawQuery): \Generator{
		return yield from $this->createAsync("raw", $method, $rawQuery);
	}

	public function runBuilderMethod(Builder $builder, string $method, mixed ...$values): \Generator{
		$baseQuery = $builder->toBase();
		$baseQuery->connection = null; // @phpstan-ignore-line

		return yield from $this->createAsync($method, $baseQuery, ...$values);
	}
}
