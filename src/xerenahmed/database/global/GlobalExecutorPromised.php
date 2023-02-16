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

use AnourValar\EloquentSerialize\Service;
use GuzzleHttp\Promise\Promise;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use xerenahmed\database\DatabaseExecutorProviderInterface;

/**
 * @mixin DatabaseExecutorProviderInterface
 */
abstract class GlobalExecutorPromised implements DatabaseExecutorProviderInterface {

	public function get(Builder $builder): Promise{
		return $this->runBuilderMethod($builder, "get");
	}

	public function first(Builder $builder): Promise{
		return $this->runBuilderMethod($builder, "first");
	}

	/**
	 * @param array<string, mixed> $values
	 */
	public function update(Builder $builder, array $values): Promise{
		return $this->runBuilderMethod($builder, "update", [$values]);
	}

	public function delete(Builder $builder): Promise{
		return $this->runBuilderMethod($builder, "delete");
	}

	public function exists(Builder $builder): Promise{
		return $this->runBuilderMethod($builder, "exists");
	}

	/**
	 * @param array<string, mixed> $values
	 */
	public function insert(Builder $builder, array $values): Promise{
		return $this->runBuilderMethod($builder, "insert", [$values]);
	}

	public function refresh(Model $model): Promise{
		return $this->modelOperation($model, 'refresh');
	}

	public function save(Model $model): Promise{
		return $this->modelOperation($model, 'save');
	}

	public function updateModel(Model $model, array $values): Promise{
		return $this->modelOperation($model, 'update', [$values]);
	}

	public function modelOperation(Model $model, string $operation, array $values = []): Promise{
		$model->setConnection(null);
		return $this->createPromise("model-operation", $model, $operation, $values);
	}

	/**
	 * @param array<string, mixed> $attributes
	 */
	public function create(string $modelClass, array $attributes): Promise{
		return $this->createPromise("create", $modelClass, [$attributes]);
	}

	public function raw(string $method, string $rawQuery): Promise{
		return $this->createPromise("raw", $method, $rawQuery);
	}

	public function runBuilderMethod(Builder $builder, string $method, mixed ...$values): Promise{
		return $this->createPromise($method, (new Service())->serialize($builder), ...$values);
	}
}
