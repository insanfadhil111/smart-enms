<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SidenavController extends Controller
{
    protected $sidenavItems = [];

    public function __construct()
    {
        // Contoh data awal
        $this->sidenavItems = [
            'Electricity' => [
                ['name' => 'Electrical Energy', 'url' => '/energy', 'icon' => 'fa-solid fa-plug-circle-bolt'],
                ['name' => 'Renewable Energy', 'url' => '/nre', 'icon' => 'fa-solid fa-leaf'],
            ],
        ];
    }

    public function addGroup(Request $request)
    {
        // Validasi input
        $request->validate([
            'group_name' => 'required|string',
            'item_name' => 'required|string',
            'item_url' => 'required|string',
            'item_icon' => 'required|string',
        ]);

        // Tambahkan grup baru
        $this->sidenavItems[$request->group_name][] = [
            'name' => $request->item_name,
            'url' => $request->item_url,
            'icon' => $request->item_icon,
        ];

        // Simpan dalam session atau database sesuai kebutuhan
        session(['sidenavItems' => $this->sidenavItems]);

        return redirect()->back()->with('success', 'Group and item added successfully!');
    }
}
