<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoidRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'requested_by',
        'requested_at',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];
}
