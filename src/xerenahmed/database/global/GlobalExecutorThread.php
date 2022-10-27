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

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use redmc\database\RedExecutorThread;

class GlobalExecutorThread extends RedExecutorThread{

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
