<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modules extends Model
{
    public $timestamps = true;

    protected $fillable = [
		'code','name', 'short_name', 'icon', 'index'
  ];
  
  public function subModules()
  {
      return $this->hasMany(SubModules::class);
  }
}