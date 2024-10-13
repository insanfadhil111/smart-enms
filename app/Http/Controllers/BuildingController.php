<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BuildingController extends Controller
{
    // Menampilkan daftar gedung
    public function index()
    {
        $buildings = Building::all();
        return view('pages.building', compact('buildings'));
    }

    // Menyimpan gedung baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'path' => 'nullable|string',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = $request->file('image')->store('images/buildings', 'public');
        }

        Building::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imageName,
            'path' => $request->path,
        ]);

        return redirect()->back()->with('success', 'Building added successfully!');
    }

    // Show edit form
    public function edit($id)
    {
        $building = Building::findOrFail($id);
        return view('pages.edit_building', compact('building'));
    }

    // Update building
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'path' => 'nullable|string|max:255',
        ]);

        $building = Building::findOrFail($id);
        
        // Update fields only if they're filled
        if ($request->filled('name')) {
            $building->name = $request->input('name');
        }
        if ($request->filled('description')) {
            $building->description = $request->input('description');
        }
        if ($request->hasFile('image')) {
            // Handle the image upload
            $imagePath = $request->file('image')->store('buildings', 'public');
            $building->image = $imagePath;
        }
        if ($request->filled('path')) {
            $building->path = $request->input('path');
        }

        $building->save();

        return redirect()->route('building.index')->with('success', 'Building updated successfully!');
    }

    // Delete building
    public function destroy($id)
    {
        $building = Building::findOrFail($id);
        $building->delete();

        return redirect()->route('building.index')->with('success', 'Building deleted successfully!');
    }

    // //New Building Auto
    // public function dashboard($building)
    // {
    //     // Anda bisa menggunakan $building untuk query data gedung spesifik jika diperlukan
    //     return view('dashboard.empty', compact('building'));
    // }

    // //Dashboard Gedung Baru
    // public function showDashboard($name)
    // {
    //     // Ambil gedung berdasarkan nama (pastikan sudah ada logika untuk menemukan gedung)
    //     $building = Building::where('name', str_replace('-', ' ', $name))->first();

    //     // Jika gedung tidak ditemukan, Anda bisa mengarahkan pengguna ke halaman 404 atau halaman lain.
    //     if (!$building) {
    //         return abort(404);
    //     }

    //     return view('dashboard.new', compact('building'));
    // }

    // public function show($buildingId)
    // {
    //     // Mendapatkan building berdasarkan ID
    //     $building = Building::findOrFail($buildingId);
        
    //     // Mengatur item sidenav untuk ditampilkan
    //     $sidenavItems = [
    //         'Main' => [
    //             [
    //                 'name' => 'Dashboard',
    //                 'url' => route('dashboard.show', $building->id),
    //                 'icon' => 'fa fa-home text-primary text-sm opacity-10',
    //             ],
    //             // Tambahkan item lain sesuai kebutuhan
    //         ],
    //         // Tambahkan grup lain jika diperlukan
    //     ];

    //     return view('layouts.navbars.auth.new-sidenav', compact('sidenavItems', 'building'));
    // }

    // public function createDashboard(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'path' => 'required|string|unique:buildings,path',
    //         'image' => 'nullable|image|max:2048',
    //     ]);

    //     $building = new Building();
    //     $building->name = $request->name;
    //     $building->description = $request->description;
    //     $building->path = $request->path;

    //     if ($request->hasFile('image')) {
    //         $building->image = $request->file('image')->store('buildings', 'public');
    //     }

    //     $building->save();

    //     return redirect()->route('buildings.newDashboard', $building->id);
    // }

    // public function newDashboard($buildingId)
    // {
    //     $building = Building::findOrFail($buildingId);
    //     return view('buildings.new-dashboard', compact('building'));
    // }

    //Penyesuaian gambar new-sidenav

}
