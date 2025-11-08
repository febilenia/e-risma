<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\OPD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class AdminOPDController extends Controller
{
    /**
     * Manual authentication check - HANYA SUPERADMIN
     */
    private function checkAuth()
    {
        if (!session('logged_in') || !session('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = session('user');
        if ($user->role !== 'superadmin') {
            return redirect('/dashboard')->with('error', 'Akses ditolak. Hanya superadmin yang dapat mengakses halaman ini.');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }
        
        // ✅ FIX: Pastikan semua OPD diambil dan di-sort
        $opdList = OPD::orderBy('nama_opd', 'asc')->get();

        return view('data_master.admin_opd.master_admin_opd', compact('opdList'));
    }

    public function data(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        if ($request->ajax()) {
            $data = User::with('opd')
                ->where('role', 'admin_opd')
                ->latest()
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_opd', function ($row) {
                    return $row->opd ? $row->opd->nama_opd : '-';
                })
                ->addColumn('password_status', function ($row) {
                    if ($row->needsPasswordChange()) {
                        return '<span class="badge bg-danger">Perlu Diganti</span>';
                    }
                    $daysLeft = $row->daysUntilPasswordExpiry();
                    if ($daysLeft <= 30) {
                        return '<span class="badge bg-warning">Segera Kadaluarsa (' . $daysLeft . ' hari)</span>';
                    }
                    return '<span class="badge bg-success">Aktif (' . $daysLeft . ' hari lagi)</span>';
                })
                ->addColumn('last_password_change', function ($row) {
                    return $row->password_changed_at 
                        ? $row->password_changed_at->format('d/m/Y H:i') 
                        : 'Belum pernah';
                })
                ->addColumn('aksi', function ($row) {
                    return '
                        <button class="btn btn-sm btn-warning btn-edit me-1" 
                            data-id="' . $row->id . '" 
                            title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-primary btn-reset-password me-1" 
                            data-id="' . $row->id . '" 
                            data-name="' . $row->name . '"
                            title="Reset Password">
                            <i class="fas fa-key"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete" 
                            data-id="' . $row->id . '" 
                            data-name="' . $row->name . '" 
                            title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['password_status', 'aksi'])
                ->make(true);
        }

        return $this->index();
    }

    public function store(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username|regex:/^[a-z0-9_]+$/',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/',
            ],
            'opd_id' => 'required|exists:opd,id',
        ], [
            'username.regex' => 'Username hanya boleh huruf kecil, angka, dan underscore.',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan simbol.',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'admin_opd',
            'opd_id' => $request->opd_id,
            'password_changed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Admin OPD berhasil ditambahkan!'
        ]);
    }

    public function edit($id)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id . '|regex:/^[a-z0-9_]+$/',
            'opd_id' => 'required|exists:opd,id',
        ], [
            'username.regex' => 'Username hanya boleh huruf kecil, angka, dan underscore.',
        ]);

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'opd_id' => $request->opd_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Admin OPD berhasil diperbarui!'
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $request->validate([
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/',
            ],
        ], [
            'new_password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan simbol.',
        ]);

        $user = User::findOrFail($id);
        $user->updatePassword($request->new_password);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset!'
        ]);
    }

    public function destroy($id)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $user = User::findOrFail($id);

        // Cek apakah bukan superadmin
        if ($user->role === 'superadmin') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus superadmin!'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin OPD berhasil dihapus!'
        ]);
    }
}