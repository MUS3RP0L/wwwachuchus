<?php

namespace Muserpol\Models;

use Illuminate\Database\Eloquent\Model;

class Hierarchy extends Model
{
	protected $fillable = [
		'code',
		'name'
	];

    public function degrees()
    {
        return $this->hasMany(Degree::class);
    }
}
