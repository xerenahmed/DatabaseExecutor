<?php

declare(strict_types=1);

namespace xerenahmed\example\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use xerenahmed\example\Main;

/**
 * @mixin Builder
 */
class PlayerModel extends Model{
	protected $table = "players";
	protected $primaryKey = "uuid";
	public $incrementing = false;
	public $timestamps = true;
	protected $connection = Main::CONN_NAME;

	protected $fillable = [
		"uuid",
		"username",
		"money",
	];
}
