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

use function array_merge;
use function serialize;
use function strval;

class HandlerQueue extends \Threaded{
	public bool $isKilled = false;
	public \Threaded $queue;

	public function __construct(){
		$this->queue = new \Threaded();
	}

	public function schedule(int $id, mixed ...$data): void{
		if($this->isKilled()){
			throw new \InvalidArgumentException(
				'You cannot schedule a request on an invalidated queue.'
			);
		}
		$this->synchronized(function() use ($id, $data): void{
			$this->queue[] = serialize(array_merge([$id], $data));
			$this->notifyOne();
		});
	}

	public function isKilled(): bool{
		return $this->isKilled;
	}

	public function fetch(): ?string{
		$value = $this->synchronized(function(): ?string{
			while($this->queue->count() === 0 && !$this->isKilled()){
				$this->wait();
			}

			$value = $this->queue->shift();
			return $value !== null ? strval($value) : null;
		});

		return $value !== null ? strval($value) : null;
	}

	public function kill(): void{
		$this->synchronized(function(): void{
			$this->isKilled = true;
			$this->notify();
		});
	}
}
