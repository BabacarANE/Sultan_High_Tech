<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailOrder extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'delivery_note_path', 'invoice_path'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

