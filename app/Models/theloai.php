<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class theloai extends Model{
    protected $table='theloai';
    // protected $fillable = [
    //     'ten_the_loai'
    // ];
    public function product(){
        return $this->hasMany(Product::class, 'id_the_loai');
    }
}