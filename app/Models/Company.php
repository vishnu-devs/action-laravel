<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name','email'];

    public function users() {
        return $this->hasMany(User::class);
    }

    public function products() {
        return $this->hasMany(Product::class);
    }
}

