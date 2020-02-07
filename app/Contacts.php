<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{
    //
    public function sections()
    {
        return $this->belongsTo('App\Sections');
    }
}
