<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PDFData extends Model
{
    use HasFactory;

    // Tambahkan kolom yang boleh diisi secara massal
    protected $table = 'pdf_data'; // Sesuaikan dengan nama tabel di database
    protected $fillable = ['filename', 'content'];
}

