<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoidLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'action',
        'performed_by',
        'performed_at',
        'note',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'performed_at' => 'datetime',
    ];

    // Allow action attribute to be cast to string explicitly (defensive)
    protected $attributes = [
        'action' => null,
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
