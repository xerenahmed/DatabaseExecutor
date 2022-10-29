# DatabaseExecutor
DatabaseExecutor is a virion library for executing SQL queries asynchronously with **Laravel** **models** and **builders**. It's working for 3 months on a production server. Tested with MySQL and Sqlite.

```php
// For example
$query = PlayerModel::username('xerenahmed');
$playerModel = GlobalExecutor::getInstance()->first($query);
$playerModel->locale = 'en';
GlobalExecutor::getInstance()->save($playerModel);
```

[![DigitalOcean Referral Badge](https://web-platforms.sfo2.digitaloceanspaces.com/WWW/Badge%203.svg)](https://www.digitalocean.com/?refcode=68d7bc7aff41&utm_campaign=Referral_Invite&utm_medium=Referral_Program&utm_source=badge)

# Quick Start

## Installation

### Install Dependencies
DatabaseExecutor requires PHP 8 or later. It also requires the following packages:

```bash
composer require illuminate/database:^9.37.0 guzzlehttp/promises:^1.5 
```

Make sure you have the [await-generator](https://github.com/SOF3/await-generator) virion. 

### Install DatabaseExecutor
Download or clone repository and put it in your virions folder.

## Usage
There is a example plugin for using DatabaseExecutor. You can find it in the `example` folder.

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

In here we will create a base executor instance that will be used to execute queries.
Also you use this instance to connect to your database and load your classes.

```php
use Illuminate\Database\Capsule\Manager;

abstract class MyExecutorThread extends DatabaseExecutorThread{

	/**
	 * @throws \Exception
	 */
	public function createCapsule(): Manager{
	    // https://github.com/illuminate/database#usage-instructions
	    return ExecutorManager::newCapsule("plugin-name", [
			"driver" => "sqlite",
			"database" => Path::join($dataPath, "database.sqlite"),
			"prefix" => "",
		]);
	}

	public function registerClassLoaders(): void{
		parent::registerClassLoaders();
		
		// Register your vendor autoloader here
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
    $executor->stop();
});
```

Use ExecutorManager to manage & quit all executors

```php
class Manager extends PluginBase{
	public static ExecutorManager $executorManager;

	public function onEnable(): void{
		self::$executorManager = ExecutorManager::create();
		self::$executorManager->register(MoneyExecutor::getInstance());
	}

	protected function onDisable(): void{
		self::$executorManager->quit();
	}
}
```

## Use models in main thread
You should set connection resolver in main thread to use models in main thread. And specify the connection name in your model.

```php
$capsule =  ExecutorManager::newCapsule("plugin-name", [
    "driver" => "sqlite",
    "database" => Path::join($dataPath, "database.sqlite"),
    "prefix" => "",
]);
// Register in connection resolver, so you can use models in main thread
ExecutorManager::registerCapsule(self::CONN_NAME, $capsule);
```

## Global Executor

Global Executor is a singleton executor that can be used to execute queries without needing to create an executor
instance.
Useful for executing simple queries. You should implement your own 'global' executor if you want to use it. Because it's must be unique per plugin.

### Implement
```php
use xerenahmed\database\global\GlobalExecutor;
use xerenahmed\database\DatabaseExecutorProvider;

class MyGlobalExecutor extends GlobalExecutor{
	use DatabaseExecutorProvider;

	public function createThread(HandlerQueue $handlerQueue, SleeperNotifier $notifier): DatabaseExecutorThread{
		return new MyGlobalExecutorThread($handlerQueue, $notifier);
	}
}

use Illuminate\Database\Capsule\Manager;
use xerenahmed\database\global\GlobalExecutorThread;

class MyGlobalExecutorThread extends GlobalExecutorThread{
	public function createCapsule(): Manager{
		return Main::newCapsule($this->dataPath);
	}
}
```

### Usage after implementing
```php
Await::f2c(function(){
    $result = yield from MyGlobalExecutor::getInstance()->first(PlayerModel::username('xerenahmed'));
    echo "Found " . $result->username;
});
// or
MyGlobalExecutorPromised::getInstance()->first(PlayerModel::username('xerenahmed'))
    ->then(function(PlayerModel $player){
        echo $player->money;
    });
```

## Advanced Usage

Transactions etc.

```php
class AdvancedExecutor implements DatabaseExecutorProviderInterface{
	use DatabaseExecutorProvider;

	public function createThread(HandlerQueue $handlerQueue, SleeperNotifier $notifier): DatabaseExecutorThread{
		return new AdvancedExecutorThread($handlerQueue, $notifier);
	}
	
	public function create(string $founderUsername, string $name, string $description, int $createCost): \Generator{
		return $this->createAsync(Action::CREATE, strtolower($founderUsername), $name, $description, $createCost);
	}

	public function getTeamMemberModels(array $playerUsernames): \Generator{
		return $this->createAsync(Action::GET_TEAM_MEMBER_MODELS, array_map('strtolower', $playerUsernames));
	}
}

class AdvancedExecutorThread extends MyExecutorThread{
    public function handle(Connection $connection, array $data): mixed{
        $action = array_shift($data);

        return match ($action) {
            Action::CREATE => $this->create($connection, ...$data),
            Action::GET_TEAM_MEMBER_MODELS => $this->getTeamMemberModels(...$data),
            default => throw new \InvalidArgumentException("Invalid action $action")    
        };  
    }
    
    public function create(Connection $connection, string $founderUsername, string $name, string $description, int $teamCreateCost): TeamModel{
        $connection->beginTransaction();
        try{
            PlayerModel::player($founderUsername)->update([
                "money" => $connection->raw("money - {$teamCreateCost}"),
            ]);
	    	
            $team = new TeamModel();
            // Set params
            $team->save();  
		    
            TeamMemberModel::create([
                "username" => strtolower($founderUsername),
                "team" => $team->id,
                "statue" => 2,
                "created_at" => Carbon::now(),
            ]);

            $connection->commit();
        }catch(\Exception $e){
            $connection->rollBack();
            throw $e;
        }

        return $team;
    }

    public function getTeamMemberModels(array $playerUsernames): Collection{
        return TeamMemberModel::with([
            "team.members",
            "mission.mission"
        ])->whereIn("username", $playerUsernames)->get();
    }
}
```

## Need Help?

Join Pocketmine-MP discord and ask it on #plugin-dev
https://discord.gg/Uj59kg4UAS

[![Discord Banner 2](https://discordapp.com/api/guilds/373199722573201408/widget.png?style=banner2)](https://discord.gg/Uj59kg4UAS)
