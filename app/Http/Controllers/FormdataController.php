<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormdataController extends Controller
{
    /**
     * Menampilkan halaman form.
     */
    public function index()
    {
        return view('formdata.index');
    }

    /**
     * Menangani penyimpanan data dari form.
     */
    public function store(Request $request)
    {
       
    }
}
