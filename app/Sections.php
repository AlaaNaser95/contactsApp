<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sections extends Model
{
    //
    public function contacts()
    {
        return $this->hasMany('App\Contacts');
    }
}
