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

		$object = $data[1];
		/** @var Builder $object */
		$object->connection = $connection;

		$values = $data[2] ?? [];
		return $object->{$action}(...$values);
	}
}
