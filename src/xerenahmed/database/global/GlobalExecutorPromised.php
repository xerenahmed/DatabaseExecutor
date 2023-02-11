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
use Illuminate\Support\Collection;
use xerenahmed\database\DatabaseExecutorProviderInterface;
use function get_class;

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

	public function save(Model $model): Promise{
		$model->setConnection(null);
		return $this->createPromise("save", $model);
	}

	/**
	 * @param array<string, mixed> $attributes
	 */
	public function create(string $modelClass, array $attributes): Promise{
		return $this->createPromise("create", $modelClass, [$attributes]);
	}

	public function runBuilderMethod(Builder $builder, string $method, mixed ...$values): Promise{
		return $this->createPromise($method, (new Service)->serialize($builder), ...$values);
	}
}
