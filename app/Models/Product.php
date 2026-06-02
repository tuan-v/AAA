<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'sanpham';

    protected $fillable = [
        'ten',
        'so_luong',
        'mau_sac',
        'gia',
        'id_the_loai'
    ];
    public function theloai(){
        return $this->belongsTo(theloai::class, 'id_the_loai');
    }

    public $timestamps = false;
}
