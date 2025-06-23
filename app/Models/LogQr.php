<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogQr extends Model
{
    use HasFactory;

    protected $table = 'log_qr';

    protected $fillable = [
        'user_id',
        'nama',
        'kantor',
        'lokasi_scan',
        'keterangan',
        'waktu_scan',
    ];

    public $timestamps = true;
}
