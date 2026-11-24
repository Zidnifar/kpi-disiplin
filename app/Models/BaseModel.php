<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Dasar semua model: kunci utama berupa UUID (CHAR 36), tanpa created_at/updated_at.
 * Catatan: $guarded kosong memudahkan tahap awal; sempitkan dengan $fillable bila perlu.
 */
abstract class BaseModel extends Model
{
    use HasUuids;

    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
