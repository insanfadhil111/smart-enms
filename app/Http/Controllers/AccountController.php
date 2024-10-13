<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    /**
     * Menampilkan halaman manajemen pengguna.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $users = User::all();
        return view('pages.account', compact('users'));
    }

    /**
     * Menampilkan form untuk menambahkan akun baru.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('pages.create-account'); // Ensure this view exists
    }

    /**
     * Menyimpan akun baru ke database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi data termasuk gambar
        $request->validate([
            'username' => 'required|unique:users,username',
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'level' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi gambar opsional
        ]);

        // Proses upload gambar
        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = $request->file('image')->store('img/users', 'public'); // Store image in public directory
        } else {
            $imageName = 'default-avatar.png'; // Set default image if none is uploaded
        }

        // Simpan akun baru
        User::create([
            'username' => $request->username,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'level' => $request->level,
            'image' => $imageName, // Simpan nama gambar
        ]);

        // Redirect dengan pesan sukses
        return redirect()->route('account.index')->with('success', 'Akun baru berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit untuk akun yang dipilih.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('pages.edit-account', compact('user')); // Ensure this view exists
    }

    /**
     * Memperbarui data akun di database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validasi data termasuk gambar
        $request->validate([
            'username' => 'required|unique:users,username,' . $id,
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'level' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi gambar opsional
        ]);

        $user = User::findOrFail($id);
        
        // Update fields only if they're filled
        if ($request->filled('username')) {
            $user->username = $request->input('username');
        }
        if ($request->filled('firstname')) {
            $user->firstname = $request->input('firstname');
        }
        if ($request->filled('lastname')) {
            $user->lastname = $request->input('lastname');
        }
        if ($request->filled('email')) {
            $user->email = $request->input('email');
        }
        if ($request->filled('level')) {
            $user->level = $request->input('level');
        }
        if ($request->hasFile('image')) {
            // Handle the image upload
            $imagePath = $request->file('image')->store('img/users', 'public');
            // Hapus gambar lama jika bukan gambar default
            if ($user->image !== 'default-avatar.png') {
                Storage::disk('public')->delete($user->image);
            }
            $user->image = $imagePath; // Update to the new image name
        }
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        // Redirect dengan pesan sukses
        return redirect()->route('account.index')->with('success', 'Akun berhasil diperbarui!');
    }

    /**
     * Menghapus akun dari database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Hapus gambar jika bukan default
        if ($user->image !== 'default-avatar.png') {
            Storage::disk('public')->delete($user->image);
        }

        $user->delete();

        // Redirect dengan pesan sukses
        return redirect()->route('account.index')->with('success', 'Akun berhasil dihapus!');
    }
}
