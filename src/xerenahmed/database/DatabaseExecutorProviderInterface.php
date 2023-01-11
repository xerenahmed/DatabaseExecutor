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

namespace xerenahmed\database;

use GuzzleHttp\Promise\Promise;
use pocketmine\snooze\SleeperNotifier;

interface DatabaseExecutorProviderInterface{
	public function createThread(HandlerQueue $handlerQueue, SleeperNotifier $notifier): DatabaseExecutorThread;

	public function waitAll(): void;

	public function stop(): void;

	public function createPromise(mixed ...$values): Promise;

	public function createAsync(mixed ...$values): \Generator;
}
