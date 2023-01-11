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

namespace xerenahmed\example\executors;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use xerenahmed\example\MyDatabaseHandler;
use function array_shift;

class CustomExecutorThread extends MyDatabaseHandler{

	public function handle(Connection $connection, array $data): mixed{
		$action = array_shift($data);
		if($action === "createTables"){
			$builder = $connection->getSchemaBuilder();
			if ($builder->hasTable("players")){
				return null;
			}
			$builder->create("players", function(Blueprint $table){
				$table->uuid();
				$table->string("username", 16);
				$table->integer("money");
				$table->primary("uuid");
				$table->timestamps();
			});
		}

		return null;
	}
}
