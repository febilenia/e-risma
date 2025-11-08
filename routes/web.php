<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MasterOPDController;
use App\Http\Controllers\MasterAplikasiController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\RespondenController;
use App\Http\Controllers\KuesionerController;
use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriKuesionerController;


if (!function_exists('protectedRoute')) {
    function protectedRoute($callback)
    {
        return function (...$args) use ($callback) {
            $redirect = AuthController::redirectIfNotLoggedIn();
            if ($redirect) return $redirect;
            return $callback(...$args);
        };
    }
}

// ==========================
// AUTHENTIKASI
// ==========================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('captcha-refresh', function () {
    return response()->json(['captcha' => captcha_src('flat')]);
})->name('captcha.refresh');

// ==========================
// PROTECTED ROUTES - DENGAN HELPER FUNCTION
// ==========================

// Dashboard
Route::get('/dashboard', protectedRoute(function () {
    return app(DashboardController::class)->index();
}))->name('dashboard');

// Password Change
Route::get('/dashboard/ubah-password', protectedRoute(function () {
    return app(AuthController::class)->showChangePasswordForm();
}))->name('password.change');

Route::post('/dashboard/ubah-password', protectedRoute(function () {
    return app(AuthController::class)->changePassword(request());
}))->name('password.update');

// ==========================
// MASTER ADMIN OPD (Protected - Superadmin Only)
// ==========================
Route::prefix('dashboard')->group(function () {
    Route::get('/admin-opd', protectedRoute(function () {
        $opdList = \App\Models\OPD::all();
        return view('data_master.admin_opd.master_admin_opd', compact('opdList'));
    }))->name('admin-opd.index');

    Route::get('/admin-opd/data', protectedRoute(function () {
        return app(\App\Http\Controllers\AdminOPDController::class)->data(request());
    }))->name('admin-opd.data');

    Route::post('/admin-opd', protectedRoute(function () {
        return app(\App\Http\Controllers\AdminOPDController::class)->store(request());
    }))->name('admin-opd.store');

    Route::get('/admin-opd/{id}/edit', protectedRoute(function ($id) {
        return app(\App\Http\Controllers\AdminOPDController::class)->edit($id);
    }))->name('admin-opd.edit');

    Route::put('/admin-opd/{id}', protectedRoute(function ($id) {
        return app(\App\Http\Controllers\AdminOPDController::class)->update(request(), $id);
    }))->name('admin-opd.update');

    Route::post('/admin-opd/{id}/reset-password', protectedRoute(function ($id) {
        return app(\App\Http\Controllers\AdminOPDController::class)->resetPassword(request(), $id);
    }))->name('admin-opd.reset-password');

    Route::delete('/admin-opd/{id}', protectedRoute(function ($id) {
        return app(\App\Http\Controllers\AdminOPDController::class)->destroy($id);
    }))->name('admin-opd.destroy');
});

// ==========================
// DATA MASTER - OPD (Protected)
// ==========================
Route::get('/dashboard/opd', protectedRoute(function () {
    return app(MasterOPDController::class)->viewMaster();
}))->name('opd.master');

Route::get('/dashboard/opd/data', protectedRoute(function () {
    return app(MasterOPDController::class)->index(request());
}))->name('opd.index');

Route::get('/dashboard/opd/create', protectedRoute(function () {
    return app(MasterOPDController::class)->create();
}))->name('opd.create');

Route::post('/dashboard/opd/store', protectedRoute(function () {
    return app(MasterOPDController::class)->store(request());
}))->name('opd.store');

Route::get('/dashboard/opd/{id}/edit', protectedRoute(function ($id) {
    return app(MasterOPDController::class)->edit($id);
}))->name('opd.edit');

Route::post('/dashboard/opd/{id}/update', protectedRoute(function ($id) {
    return app(MasterOPDController::class)->update(request(), $id);
}))->name('opd.update');

Route::delete('/dashboard/opd/{id}', protectedRoute(function ($id) {
    return app(MasterOPDController::class)->destroy($id);
}))->name('opd.destroy');

// ==========================
// DATA MASTER - APLIKASI (Protected)
// ==========================
Route::prefix('/dashboard/aplikasi')->group(function () {
    Route::get('/', protectedRoute(function () {
        return app(MasterAplikasiController::class)->viewMaster();
    }))->name('aplikasi.master');

    Route::get('/data', protectedRoute(function () {
        return app(MasterAplikasiController::class)->index(request());
    }))->name('aplikasi.index');

    Route::post('/', protectedRoute(function () {
        return app(MasterAplikasiController::class)->store(request());
    }))->name('aplikasi.store');

    Route::get('/create', protectedRoute(function () {
        return app(MasterAplikasiController::class)->create();
    }))->name('aplikasi.create');

    Route::get('/{uid}/edit', protectedRoute(function ($uid) {
        return app(MasterAplikasiController::class)->edit($uid);
    }))->name('aplikasi.edit');

    Route::put('/{uid}', protectedRoute(function ($uid) {
        return app(MasterAplikasiController::class)->update(request(), $uid);
    }))->name('aplikasi.update');

    Route::delete('/{uid}', protectedRoute(function ($uid) {
        return app(MasterAplikasiController::class)->destroy($uid);
    }))->name('aplikasi.destroy');

    Route::get('/{uid}/responden', protectedRoute(function ($uid) {
        return app(MasterAplikasiController::class)->responden($uid);
    }))->name('aplikasi.responden');

    Route::get('/{uid}/responden-data', protectedRoute(function ($uid) {
        return app(MasterAplikasiController::class)->respondenData($uid);
    }))->name('aplikasi.responden.data');

    Route::post('/{uid}/toggle-status', protectedRoute(function ($uid) {
        return app(MasterAplikasiController::class)->toggleStatus($uid);
    }))->name('aplikasi.toggleStatus');

    Route::get('/{uid_aplikasi}/responden/{uid_responden}/jawaban', protectedRoute(function ($aplikasi, $responden) {
        return app(RespondenController::class)->jawabanResponden($aplikasi, $responden);
    }))->name('aplikasi.responden.jawaban');

    Route::delete('/{uid_aplikasi}/responden/{uid_responden}', protectedRoute(function ($aplikasi, $responden) {
        return app(RespondenController::class)->destroy($aplikasi, $responden);
    }))->name('aplikasi.responden.destroy');
});

// ==========================
// RESPONDEN (Protected)
// ==========================
Route::get('/admin/responden', protectedRoute(function () {
    return app(RespondenController::class)->index();
}))->name('responden.index');

Route::get('/responden/{id}/jawaban', protectedRoute(function ($id) {
    return app(RespondenController::class)->jawaban($id);
}))->name('responden.jawaban');

// ==========================
// KUESIONER (Protected)
// ==========================
Route::prefix('dashboard')->group(function () {
    Route::get('/kuesioner', protectedRoute(function () {
        return app(KuesionerController::class)->index();
    }))->name('kuesioner.index');

    Route::get('/kuesioner/data', protectedRoute(function () {
        return app(KuesionerController::class)->data();
    }))->name('kuesioner.data');

    Route::post('/kuesioner', protectedRoute(function () {
        return app(KuesionerController::class)->store(request());
    }))->name('kuesioner.store');

    Route::get('/kuesioner/{id}', protectedRoute(function ($id) {
        return app(KuesionerController::class)->edit($id);
    }))->name('kuesioner.edit');

    Route::put('/kuesioner/{id}', protectedRoute(function ($id) {
        return app(KuesionerController::class)->update(request(), $id);
    }))->name('kuesioner.update');

    Route::delete('/kuesioner/{id}', protectedRoute(function ($id) {
        return app(KuesionerController::class)->destroy($id);
    }))->name('kuesioner.destroy');

    Route::get('/kuesioner/check-urutan', protectedRoute(function () {
        return app(KuesionerController::class)->checkUrutan(request());
    }))->name('kuesioner.check-urutan');
});

// ==========================
// KATEGORI KUESIONER (Protected)
// ==========================
Route::prefix('dashboard')->group(function () {
    Route::get('/kategori-kuesioner', protectedRoute(function () {
        return app(KategoriKuesionerController::class)->index();
    }))->name('kategori-kuesioner.index');

    Route::get('/kategori-kuesioner/data', protectedRoute(function () {
        return app(KategoriKuesionerController::class)->getData(request());
    }))->name('kategori-kuesioner.data');

    Route::post('/kategori-kuesioner', protectedRoute(function () {
        return app(KategoriKuesionerController::class)->store(request());
    }))->name('kategori-kuesioner.store');

    Route::get('/kategori-kuesioner/{id}/edit', protectedRoute(function ($id) {
        return app(KategoriKuesionerController::class)->edit($id);
    }))->name('kategori-kuesioner.edit');

    Route::put('/kategori-kuesioner/{id}', protectedRoute(function ($id) {
        return app(KategoriKuesionerController::class)->update(request(), $id);
    }))->name('kategori-kuesioner.update');

    Route::delete('/kategori-kuesioner/{id}', protectedRoute(function ($id) {
        return app(KategoriKuesionerController::class)->destroy($id);
    }))->name('kategori-kuesioner.destroy');

    Route::get('/kategori-kuesioner/{id}/pertanyaan', protectedRoute(function ($id) {
        return app(KategoriKuesionerController::class)->getPertanyaan($id);
    }))->name('kategori-kuesioner.pertanyaan');
});

// ==========================
// ANALISIS (Protected)
// ==========================
Route::prefix('dashboard')->group(function () {
    Route::get('/analisis', protectedRoute(function () {
        return app(AnalisisController::class)->index();
    }))->name('analisis.index');

    Route::get('/analisis/data', protectedRoute(function () {
        return app(AnalisisController::class)->data(request());
    }))->name('analisis.data');

    Route::get('/analisis/summary', protectedRoute(function () {
        return app(DashboardController::class)->getAnalisisData(request());
    }))->name('analisis.summary');

    Route::get('/analisis/{uid}/detail', protectedRoute(function ($uid) {
        return app(AnalisisController::class)->detail($uid, request());
    }))->name('analisis.detail');

    Route::get('/analisis/export', protectedRoute(function () {
        return app(AnalisisController::class)->export(request());
    }))->name('analisis.export');

    Route::get('/analisis/{uid}/export', protectedRoute(function ($uid) {
        return app(AnalisisController::class)->exportDetail($uid, request());
    }))->name('analisis.export.detail');
});

// ==========================
// HALAMAN SURVEY (PUBLIC - No Authentication Required)
// ==========================
Route::pattern('uid', '^(?!dashboard$|login$|logout$|captcha-refresh$|admin$)[A-Za-z0-9]{8,}$');

Route::prefix('/{uid}')->group(function () {
    Route::get('/', [SurveyController::class, 'index'])->name('survey');
    Route::post('/submit', [SurveyController::class, 'storeStep1'])->name('survey.store.step1');
    Route::get('/survei', [SurveyController::class, 'showQuestion'])->name('survey.question');
    Route::post('/survei/answer',   [SurveyController::class, 'storeAnswer'])->name('survey.answer');
    Route::post('/survei/save',    [SurveyController::class, 'saveCurrentAnswer'])->name('survey.save.current');
    Route::post('/survei/prev',     [SurveyController::class, 'prevQuestion'])->name('survey.prev');

    Route::get('/finish', [SurveyController::class, 'finish'])->name('survey.finish');
    Route::get('/closed', [SurveyController::class, 'closed'])->name('survey.closed');
});
