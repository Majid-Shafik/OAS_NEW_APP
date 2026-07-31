<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Province
 * 
 * @property int $ID
 * @property string|null $NAME
 * @property string|null $ENG_NAME
 *
 * @package App\Models
 */
class Province extends Model
{
	protected $table = 'province';
	protected $primaryKey = 'ID';
	public $timestamps = false;

	protected $fillable = [
		'NAME',
		'ENG_NAME'
	];
}
