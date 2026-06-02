<?php

use Illuminate\Database\Eloquent\Model;

class Manage extends Model{
    protected $table ='users';
    protected $fillable = [
        'id',
        'name',
        'email',
        'phone'
    ];
}