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

namespace xerenahmed\database;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\Utils;
use pocketmine\Server;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\utils\SingletonTrait;
use SOFe\AwaitGenerator\Await;
use function is_string;
use function usleep;

interface DatabaseExecutorProviderInterface{
	public function createThread(HandlerQueue $handlerQueue, SleeperNotifier $notifier): DatabaseExecutorThread;
}
