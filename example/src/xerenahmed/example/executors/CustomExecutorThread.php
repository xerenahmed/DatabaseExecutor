<?php

declare(strict_types=1);

namespace xerenahmed\example\executors;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use xerenahmed\database\DatabaseExecutorProvider;
use xerenahmed\example\MyDatabaseHandler;
use function array_shift;

class CustomExecutorThread extends MyDatabaseHandler{
	use DatabaseExecutorProvider;

	public function handle(Connection $connection, array $data) : mixed{
		$action = array_shift($data);
		if($action === "createTables"){
			$connection->getSchemaBuilder()->create("players", function(Blueprint $table){
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
