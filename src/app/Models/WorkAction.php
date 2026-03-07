<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkAction extends Model
{
    use HasFactory;

    protected $table = 'work_actions';

    protected $fillable = ['name'];
}
