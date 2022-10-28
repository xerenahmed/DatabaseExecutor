<?php

declare(strict_types=1);

namespace xerenahmed\database\global;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use xerenahmed\database\DatabaseExecutorProvider;
use xerenahmed\database\DatabaseExecutorProviderInterface;
use function get_class;

/**
 * @mixin DatabaseExecutorProvider
 */
abstract class GlobalExecutor implements DatabaseExecutorProviderInterface{
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
