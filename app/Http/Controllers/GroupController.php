<?php

namespace App\Http\Controllers;

use App\Models\Group; // Pastikan Anda memiliki model Group
use Illuminate\Http\Request;

class GroupController extends Controller
{
    // Menampilkan daftar grup
    public function index()
    {
        $groups = Group::all();
        return view('groups.index', compact('groups'));
    }

    // Menampilkan form untuk menambahkan grup baru
    public function create()
    {
        return view('groups.create');
    }

    // Menyimpan grup baru ke dalam database
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Membuat grup baru
        Group::create($request->all());

        // Redirect ke daftar grup setelah berhasil
        return redirect()->route('groups.index')->with('success', 'Group added successfully!');
    }
}
