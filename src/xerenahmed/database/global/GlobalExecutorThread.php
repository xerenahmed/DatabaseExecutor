<?php

declare(strict_types=1);

namespace xerenahmed\database\global;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use xerenahmed\database\DatabaseExecutorThread;

abstract class GlobalExecutorThread extends DatabaseExecutorThread{

	// @internal API
	public function handle(Connection $connection, array $data): mixed{
		$action = $data[0];

		if ($action === "raw") {
			[, $method, $query] = $data;
			return $connection->{$method}($query);
		}
		if ($action === "create") {
			[, $object, $values] = $data;
			return $object::create(...$values);
		}

		/** @var Builder $object */
		$object->connection = $connection;

		$values = $data[2] ?? [];
		return $object->{$action}(...$values);
	}
}
