<?php

declare(strict_types=1);

namespace xerenahmed\database\global;

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
		$modelClass = get_class($builder->getModel());

		// @phpstan-ignore-next-line
		return $this->runBuilderMethod($builder, "get")->then(function(Collection $collection) use ($modelClass){
			return $collection->map(function(\stdClass $values) use ($modelClass){
				/** @var Model $model */
				$model = new $modelClass();

				return $model->fill((array) $values);
			});
		});
	}

	public function first(Builder $builder): Promise{
		$modelClass = get_class($builder->getModel());

		// @phpstan-ignore-next-line
		return $this->runBuilderMethod($builder, "first")->then(function(?\stdClass $values) use ($modelClass){
			if($values === null){
				return null;
			}

			/** @var Model $model */
			$model = new $modelClass();
			return $model->fill((array) $values);
		});
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
		$baseQuery = $builder->toBase();
		$baseQuery->connection = null; // @phpstan-ignore-line

		return $this->createPromise($method, $baseQuery, ...$values);
	}
}
