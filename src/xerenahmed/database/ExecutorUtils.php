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

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use RedHelper\IconHelper;

class ExecutorUtils{
	public static function notEnoughMoney(Player $player): \Closure{
		return function() use ($player){
			if(!$player->isOnline()){
				return;
			}

			$player->sendMessage(
				IconHelper::ERROR . TextFormat::RED .
				' Ücret alınırken bir hata oluştu. Yeterli paraya sahip misin? Eğer cevap evet ise lütfen daha sonra tekrar deneyiniz.'
			);
		};
	}

	public static function anErrorOccurred(Player $player): \Closure{
		return function() use ($player): void{
			if(!$player->isOnline()){
				return;
			}

			$player->sendMessage(
				IconHelper::ERROR . TextFormat::RED .
				' Bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.'
			);
		};
	}
}
