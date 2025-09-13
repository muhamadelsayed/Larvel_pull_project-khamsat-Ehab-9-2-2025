<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TruckImage extends Model {
    protected $fillable = ['truck_id', 'path'];
}