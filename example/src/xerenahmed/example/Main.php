<?php

declare(strict_types=1);

namespace xerenahmed\example;

use Illuminate\Database\Capsule\Manager;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use Ramsey\Uuid\Uuid;
use SOFe\AwaitGenerator\Await;
use Webmozart\PathUtil\Path;
use xerenahmed\database\ExecutorManager;
use xerenahmed\example\executors\CustomExecutor;
use xerenahmed\example\global\MyGlobalExecutor;
use xerenahmed\example\models\PlayerModel;
use function bin2hex;
use function json_encode;
use function random_bytes;

class Main extends PluginBase{
	public const CONN_NAME = "example-plugin";

	private ExecutorManager $executorManager;

	public function onEnable(): void{
		$capsule = self::newCapsule(Server::getInstance()->getDataPath());
		ExecutorManager::registerCapsule(self::CONN_NAME, $capsule);

		$this->executorManager = ExecutorManager::create();
		$this->executorManager->register(MyGlobalExecutor::getInstance());
		$this->executorManager->register(CustomExecutor::getInstance());

		Server::getInstance()->getLogger()->info("Creating tables...");
		Await::f2c(function(): \Generator{
			yield from CustomExecutor::getInstance()->createTables();
			Server::getInstance()->getLogger()->info("Creating a player");

			$uuid = Uuid::uuid4()->toString();
			yield from MyGlobalExecutor::getInstance()->create(PlayerModel::class, [
				"uuid" => $uuid,
				"username" => bin2hex(random_bytes(8)),
				"money" => 500
			]);

			$player = yield from MyGlobalExecutor::getInstance()->first(PlayerModel::where('uuid', $uuid));
			Server::getInstance()->getLogger()->info("Player created => " . json_encode($player));
		});
	}

	public function onDisable(): void{
		$this->executorManager->quit();
	}

	public static function newCapsule(string $dataPath): Manager{
		return ExecutorManager::newCapsule(self::CONN_NAME, [
			"driver" => "sqlite",
			"database" => Path::join($dataPath, "database.sqlite"),
			"prefix" => "",
		]);
	}
}
