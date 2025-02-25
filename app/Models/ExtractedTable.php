<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtractedTable extends Model
{
    use HasFactory;

    protected $table = 'extracted_tables'; // Sesuai dengan nama tabel di migration

    protected $fillable = ['nama', 'konten', 'isi'];
}
