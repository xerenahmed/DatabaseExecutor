<?php

declare(strict_types=1);

namespace xerenahmed\example;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;
use xerenahmed\database\ExecutorManager;
use xerenahmed\example\executors\CustomExecutor;
use xerenahmed\example\global\MyGlobalExecutor;
use xerenahmed\example\models\PlayerModel;

class Main extends PluginBase {
	private ExecutorManager $executorManager;

	public function onEnable() : void {
		$this->executorManager = ExecutorManager::create();
		$this->executorManager->register(MyGlobalExecutor::getInstance());
		$this->executorManager->register(CustomExecutor::getInstance());

		Server::getInstance()->getLogger()->info("Creating tables...");
		Await::f2c(function() : \Generator{
			yield from CustomExecutor::getInstance()->createTables();
			Server::getInstance()->getLogger()->info("Creating a player");

			yield from MyGlobalExecutor::getInstance()->create(PlayerModel::class, [
				"uuid" => "",
				"username" => "xerenahmed",
				"money" => 500
			]);

			$player = yield from MyGlobalExecutor::getInstance()->first(PlayerModel::where('username', 'xerenahmed'));
			Server::getInstance()->getLogger()->info("Player created => " . json_encode($player));
		});
	}

	public function onDisable() : void{
		$this->executorManager->quit();
	}
}
