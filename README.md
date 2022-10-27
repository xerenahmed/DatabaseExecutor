# DatabaseExecutor
DatabaseExecutor is a virion library for executing SQL queries asynchronously with **Laravel** **models** and **builders**.

```php
$builder = PlayerModel::username('xerenahmed');
GlobalExecutor::getInstance()->first($builder);
```
[![DigitalOcean Referral Badge](https://web-platforms.sfo2.digitaloceanspaces.com/WWW/Badge%203.svg)](https://www.digitalocean.com/?refcode=68d7bc7aff41&utm_campaign=Referral_Invite&utm_medium=Referral_Program&utm_source=badge)

# Quick Start

## Installation

### Install Dependencies

```bash
composer require guzzlehttp/promises
composer require sof3/await-generator
composer require illuminate/database
```

### Install DatabaseExecutor

Download or clone repository and put it in your virions folder.

## Usage

### Define a model
For more info https://laravel.com/docs/9.x/eloquent#eloquent-model-conventions
```php
class PlayerModel extends Model{
	protected $table = 'players';

	protected $primaryKey = 'username';
	protected $keyType = "string";
	public $incrementing = false;

	protected $guarded = [];
	public $timestamps = true;

	public function scopeUsername(Builder $query, string $username): void{
		$query->where('username', strtolower($username));
	}
}
```

### Define your Base Executor Instance
In here we will create a base executor instance that will be used to 
Also you use this instance to connect to your database and load your classes. 
```php
<?php
abstract class MyExecutorThread extends DatabaseExecutorThread{

	/**
	 * @throws \Exception
	 */
	public function createConnection(): Connection{
            $capsule = new Capsule;
            
            $capsule->addConnection([
                'driver' => 'mysql',
                'host' => 'localhost',
                'database' => 'database',
                'username' => 'root',
                'password' => 'password',
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
            ]);
            
            return $capsule->getConnection();
	}

	public function registerClassLoaders(): void{
		parent::registerClassLoaders();
		
		// Register your vendor autoloaders here
		require_once $this->dataPath . '/plugins/vendor/autoload.php';
		
		// Register dotenv if you use it to load environment variables	
	}
}
```

### Define your Executor Instance
In here we will create an executor instance that will be used to execute queries.
```php

class MoneyExecutor implements DatabaseExecutorProviderInterface{
	use DatabaseExecutorProvider;

	public function createThread(HandlerQueue $handlerQueue, SleeperNotifier $notifier): DatabaseExecutorThread{
		return new MoneyExecutorThread($handlerQueue, $notifier);
	}

	public function add(string $username, float $money): Promise{
		return $this->createPromise(strtolower($username), $money);
	}
	
	public function addAsync(string $username, float $money): \Generator{
		return $this->createAsync(strtolower($username), $money);
	}
}

class MoneyExecutorThread extends MyExecutorThread{
	/**
	 * @param array{string, int} $data
	 */
	public function handle(Connection $connection, array $data): int{
		[$username, $money] = $data;
		return PlayerModel::username($username)->update([
			'money' => $connection->raw('money + ' . $money)
		]);
	}
}
```
### Use it
```php
$executor = MoneyExecutor::getInstance();
$executor->add('xerenahmed', 1000)->then(function(int $result){
    echo "Updated $result rows";
});
$executor->stop();
```

### Use with Await Generator
```php
$executor = MoneyExecutor::getInstance();
Await::f2c(function() use($executor){
    $result = yield from $executor->addAsync('xerenahmed', 1000);
    echo "Updated $result rows";
    $executor->quit();
});
```

Use ExecutorManager to manage & quit all executors
```php
class Manager extends PluginBase{
	public static ExecutorManager $executorManager;

	public function onEnable(): void{
		self::$executorManager = ExecutorManager::createWithGlobal();
		self::$executorManager->register(MoneyExecutor::getInstance());
	}

	protected function onDisable(): void{
		self::$executorManager->quit();
	}
}
```

## Global Executor
Global Executor is a singleton executor that can be used to execute queries without needing to create an executor instance.
Useful for executing simple queries.

```php
use xerenahmed\database\global\GlobalExecutor;
use xerenahmed\database\global\GlobalExecutorPromised;

Await::f2c(function(){
    $result = yield from GlobalExecutorPromised::first(PlayerModel::username('xerenahmed'));
    echo "Found " . $result->username;
});
// or
GlobalExecutorPromised::getInstance()->first(PlayerModel::username('xerenahmed'))
    ->then(function(PlayerModel $player){
        echo $player->money;
    });
```

## Need Help?
Join Pocketmine-MP discord and ask it on #plugin-dev 
https://discord.gg/Uj59kg4UAS

[![Discord Banner 2](https://discordapp.com/api/guilds/373199722573201408/widget.png?style=banner2)](https://discord.gg/Uj59kg4UAS)