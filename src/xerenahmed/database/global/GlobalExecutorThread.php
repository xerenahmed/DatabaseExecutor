<?php

declare(strict_types=1);

namespace xerenahmed\database\global;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use xerenahmed\database\DatabaseExecutorThread;

abstract class GlobalExecutorThread extends DatabaseExecutorThread{

	public function handle(Connection $connection, array $data): mixed{
		[$method, $object] = $data;

		if ($method === "create") {
			return $object::create(...$data[2]);
		}

		/** @var Builder $object */
		$object->connection = $connection;

		$values = $data[2] ?? [];
		return $object->{$method}(...$values);
	}
}
