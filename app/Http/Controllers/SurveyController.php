<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aplikasi;
use App\Models\KategoriKuesioner;
use App\Models\Jawaban;
use App\Models\Kuesioner;
use App\Models\Responden;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Rules\TurnstileRule;

class SurveyController extends Controller
{
    /* ===================== STEP 1 ===================== */

    public function index(string $uid, $step = 1)
    {
        $aplikasi = Aplikasi::with('opd')->where('id_encrypt', $uid)->first();
        if ($resp = $this->guardIfClosed($aplikasi)) return $resp;

        // âœ… Clear session jika sudah committed (user mulai survey baru)
        if (session('survey_committed') === true) {
            $this->clearSurveySession();
        }

        $kategori = KategoriKuesioner::with(['kuesioner' => function ($q) {
            $q->orderBy('urutan');
        }])->get();

        // âœ… Selalu clear di step 1
        if ($step == 1) {
            $this->clearSurveySession();
            $respondenData = [];
        } else {
            $respondenData = session('responden_data', []);
        }

        return view('survey.survey', [
            'step'          => $step,
            'aplikasi'      => $aplikasi,
            'kategori'      => $kategori,
            'respondenData' => $respondenData,
        ]);
    }

    public function storeStep1(Request $request, $uid)
    {
        $aplikasi = Aplikasi::where('id_encrypt', $uid)->firstOrFail();

        if ($request->aplikasi_id !== $uid) {
            abort(400, 'Aplikasi tidak cocok dengan URL.');
        }

        $this->validateStep1($request);
        if ($resp = $this->guardIfClosed($aplikasi)) return $resp;

        $nama = strip_tags(trim($request->nama));
        $nama = preg_replace('/[^A-Za-z\s\'\.\-]/', '', $nama);

        if (strlen($nama) < 2 || strlen($nama) > 100) {
            return back()->withErrors(['nama' => 'Nama tidak valid. Hanya huruf, spasi, titik, tanda petik, dan tanda hubung yang diperbolehkan.']);
        }

        $noHp = preg_replace('/[^0-9]/', '', $request->no_hp);

        if (strlen($noHp) < 10 || strlen($noHp) > 13 || !preg_match('/^08/', $noHp)) {
            return back()->withErrors(['no_hp' => 'Nomor HP tidak valid']);
        }

        $this->clearSurveySession();
        $sessionId = session()->getId();

        $qIds = Kuesioner::orderBy('urutan')->pluck('id')->toArray();
        $surveySid = uniqid('survey_', true);

        session([
            'responden_data' => [
                'aplikasi_id'      => $aplikasi->id,
                'aplikasi_encrypt' => $request->aplikasi_id,
                'nama'             => $nama,
                'usia'             => (int)$request->usia,
                'no_hp'            => $noHp,
                'jenis_kelamin'    => $request->jenis_kelamin,
                'created_at'       => now()->toISOString(),
                'ip_address'       => $request->ip(),
                'user_agent'       => $request->userAgent(),
                'session_id'       => $sessionId,
            ],
            'survey_answers'    => [],
            'survey_started_at' => now()->toISOString(),
            'survey_step'       => 2,
            'csrf_token'        => csrf_token(),
            'q_ids'             => $qIds,
            'q_total'           => count($qIds),
            'q_pos'             => 0,
            'survey_sid'        => $surveySid,
            'survey_committed'  => false,
            'survey_active'     => true
        ]);

        return redirect()->route('survey.question', ['uid' => $uid]);
    }

    /* ===================== STEP 2 ===================== */

    public function showQuestion(string $uid)
    {
        // âœ… Cek survey_committed dulu
        if (session('survey_committed') === true) {
            $this->clearSurveySession();
            Log::info('User accessing question after survey committed', [
                'aplikasi_uid' => $uid,
                'session_id'   => session()->getId(),
            ]);
            return redirect()->route('survey', ['uid' => $uid])
                ->with('info', 'Survey Anda sudah selesai. Silakan mulai survey baru.');
        }

        if (!session('survey_active')) {
            Log::warning('Unauthorized access to question page', [
                'uid' => $uid,
                'ip' => request()->ip(),
            ]);

            return redirect()->route('survey', ['uid' => $uid])
                ->with('error', 'Sesi survey tidak valid. Silakan mulai dari awal.');
        }

        $aplikasi = Aplikasi::where('id_encrypt', $uid)->firstOrFail();
        if ($resp = $this->guardIfClosed($aplikasi)) return $resp;

        if (!session('responden_data')) {
            Log::warning('Missing responden data', [
                'uid' => $uid,
                'ip' => request()->ip()
            ]);

            return redirect()->route('survey', ['uid' => $uid])
                ->with('error', 'Session berakhir. Silakan mulai ulang survey.');
        }

        $kid = $this->currentQuestionId();

        if (!$kid) {
            Log::error('Invalid question ID', [
                'uid' => $uid,
                'q_pos' => $this->qPos(),
                'q_total' => $this->qTotal()
            ]);

            return redirect()->route('survey', ['uid' => $uid])
                ->with('error', 'Terjadi kesalahan. Silakan mulai ulang survey.');
        }

        $kuesioner = Kuesioner::select(['id', 'pertanyaan', 'tipe', 'is_mandatory', 'kategori_id', 'persepsi', 'gambar', 'skala_type', 'skala_labels'])
            ->findOrFail($kid);

        $index          = $this->qPos() + 1;
        $totalQuestions = $this->qTotal();
        $jawabanSebelumnya = $this->getSessionAnswer($kuesioner->id);

        // âœ… PERUBAHAN KRITIS: Gunakan getSkalaLabelsForDisplay() untuk inversi otomatis
        $skalaLabels = $kuesioner->getSkalaLabelsForDisplay();

        if (request()->ajax()) {
            return response()->json([
                'html' => view('survey.partials.step2', compact(
                    'aplikasi',
                    'kuesioner',
                    'index',
                    'totalQuestions',
                    'jawabanSebelumnya',
                    'skalaLabels'
                ))->render()
            ]);
        }

        return view('survey.survey', [
            'step'              => 2,
            'aplikasi'          => $aplikasi,
            'kuesioner'         => $kuesioner,
            'index'             => $index,
            'totalQuestions'    => $totalQuestions,
            'jawabanSebelumnya' => $jawabanSebelumnya,
            'skalaLabels'       => $skalaLabels,
        ]);
    }

    public function saveCurrentAnswer(Request $request, string $uid)
    {
        try {
            if (!session('survey_active')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi survey tidak valid'
                ], 403);
            }

            if (session('survey_committed') === true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Survey sudah selesai'
                ], 410);
            }

            $aplikasi = Aplikasi::where('id_encrypt', $uid)->firstOrFail();
            if ($resp = $this->guardIfClosed($aplikasi, true)) return $resp;

            if (!session('responden_data')) {
                return response()->json(['success' => false, 'message' => 'Session expired'], 403);
            }

            $kid = $this->currentQuestionId();
            if (!$kid) return response()->json(['success' => false, 'message' => 'Invalid question'], 404);

            $kuesioner = Kuesioner::select(['id', 'pertanyaan', 'tipe', 'is_mandatory', 'kategori_id', 'persepsi', 'gambar'])
                ->findOrFail($kid);

            if ($request->has('jawaban')) {
                $jawaban = $request->input('jawaban');

                if ($kuesioner->tipe === 'free_text') {
                    if (!$kuesioner->is_mandatory || ($kuesioner->is_mandatory && !empty(trim($jawaban)))) {
                        $answerData = $this->prepareAnswerDataForSession($request, $aplikasi, $kuesioner);
                        $this->saveToSession($answerData, $kuesioner->id);
                        return response()->json(['success' => true, 'message' => 'Saved to session']);
                    }
                }

                if ($this->isValidAnswerInput($request, $kuesioner)) {
                    $answerData = $this->prepareAnswerDataForSession($request, $aplikasi, $kuesioner);
                    $this->saveToSession($answerData, $kuesioner->id);
                    return response()->json(['success' => true, 'message' => 'Saved to session']);
                }
            }

            $this->removeFromSession($kuesioner->id);
            return response()->json(['success' => true, 'message' => 'Empty answer handled']);
        } catch (\Exception $e) {
            Log::error('Auto-save error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Save failed'], 500);
        }
    }

    public function storeAnswer(Request $request, string $uid)
    {
        try {
            if (!session('survey_active')) {
                Log::warning('storeAnswer called without active survey', [
                    'uid' => $uid,
                    'ip' => $request->ip()
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sesi survey tidak valid',
                        'redirect' => route('survey', ['uid' => $uid])
                    ], 403);
                }

                return redirect()->route('survey', ['uid' => $uid])
                    ->with('error', 'Sesi survey tidak valid. Silakan mulai dari awal.');
            }

            // âœ… Guard: Cek committed
            if (session('survey_committed') === true) {
                Log::warning('Attempt to submit committed survey', [
                    'aplikasi_uid' => $uid,
                    'survey_sid' => session('survey_sid'),
                    'ip' => $request->ip()
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'redirect' => route('survey.finish', ['uid' => $uid])
                    ]);
                }
                return redirect()->route('survey.finish', ['uid' => $uid]);
            }

            $aplikasi = Aplikasi::where('id_encrypt', $uid)->firstOrFail();
            if ($resp = $this->guardIfClosed($aplikasi)) return $resp;

            $respondenData = session('responden_data');
            if (!$respondenData) {
                if ($request->ajax()) {
                    return response()->json([
                        'success'  => false,
                        'message'  => 'Session berakhir. Silakan mulai ulang survey.',
                        'redirect' => route('survey', ['uid' => $uid])
                    ], 403);
                }
                abort(403, 'Session responden hilang. Mulai ulang survei.');
            }

            $currentPos = $this->qPos();
            $totalQuestions = $this->qTotal();
            $isLast = ($currentPos + 1) >= $totalQuestions;

            $kuesioner = Kuesioner::findOrFail($request->kuesioner_id);
            $isRequired = (bool) $kuesioner->is_mandatory;

            $jawabanTerisi = false;
            if ($request->filled('jawaban')) {
                $jawaban = $request->input('jawaban');
                if ($kuesioner->tipe === 'radio') {
                    $jawabanTerisi = is_numeric($jawaban) && (int)$jawaban >= 1 && (int)$jawaban <= 5;
                } else {
                    $jawabanTerisi = strlen(trim($jawaban)) > 0;
                }
            }

            if ($isRequired && !$jawabanTerisi) {
                return $request->ajax()
                    ? response()->json(['success' => false, 'errors' => ['jawaban' => 'Pertanyaan ini wajib diisi.']], 422)
                    : back()->withErrors(['jawaban' => 'Pertanyaan ini wajib diisi.']);
            }

            // if ($isLast) {
            //     $rules = ['captcha' => 'required|captcha'];
            //     $messages = [
            //         'captcha.required' => 'Mohon selesaikan verifikasi.',
            //         'captcha.captcha'  => 'Captcha tidak valid. Silakan coba lagi.',
            //     ];

            //     $v = Validator::make($request->all(), $rules, $messages);
            //     if ($v->fails()) {
            //         return $request->ajax()
            //             ? response()->json(['success' => false, 'errors' => $v->errors()], 422)
            //             : back()->withErrors($v->errors());
            //     }
            // }
            if ($isLast) {
                $rules = ['cf-turnstile-response' => ['required', new TurnstileRule()]];
                $messages = [
                    'cf-turnstile-response.required' => 'Mohon selesaikan verifikasi keamanan.',
                ];

                $v = Validator::make($request->all(), $rules, $messages);
                if ($v->fails()) {
                    return $request->ajax()
                        ? response()->json(['success' => false, 'errors' => $v->errors()], 422)
                        : back()->withErrors($v->errors());
                }
            }

            if ($jawabanTerisi && $this->isValidAnswerInput($request, $kuesioner)) {
                $answerData = $this->prepareAnswerDataForSession($request, $aplikasi, $kuesioner);
                $this->saveToSession($answerData, $kuesioner->id);
            } else {
                if (!$isRequired) {
                    $this->removeFromSession($kuesioner->id);
                }
            }

            // âœ… Jika terakhir â†’ commit & finish
            if ($isLast) {
                $this->commitAllDataToDatabase($aplikasi->id);

                if ($request->ajax()) {
                    return response()->json(['success' => true, 'redirect' => route('survey.finish', ['uid' => $uid])]);
                }
                return redirect()->route('survey.finish', ['uid' => $uid]);
            }

            // âœ… Kalau belum terakhir â†’ next question
            return $this->goNext($request, $aplikasi, $uid, $currentPos);
        } catch (\Exception $e) {
            Log::error("StoreAnswer error: " . $e->getMessage());
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => 'Terjadi kesalahan. Silakan coba lagi.'], 500)
                : back()->withErrors(['error' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }

    public function finish(string $uid)
    {
        $aplikasi = Aplikasi::where('id_encrypt', $uid)->first();

        // âœ… Guard: Harus committed
        if (session('survey_committed') !== true) {
            Log::warning('Unauthorized access to finish page - not committed', [
                'uid' => $uid,
                'ip' => request()->ip(),
                'session_id' => session()->getId(),
            ]);

            return redirect()->route('survey', ['uid' => $uid])
                ->with('error', 'Anda harus menyelesaikan survey terlebih dahulu.');
        }

        // âœ… Set flag active = false
        session(['survey_active' => false]);

        // âœ… Clear survey data (tapi keep survey_committed untuk prevent duplicate)
        session()->forget([
            'survey_answers',
            'survey_started_at',
            'survey_step',
            'q_pos',
            'responden_data'
        ]);

        // âœ… STAY di halaman finish - return view, BUKAN redirect
        return view('survey.survey', [
            'step'     => 3,
            'aplikasi' => $aplikasi
        ]);
    }

    public function closed(string $uid)
    {
        $aplikasi = Aplikasi::with('opd')->where('id_encrypt', $uid)->first();

        // ✅ FIX: Redirect ke survey jika status = open
        if ($aplikasi && $aplikasi->status === 'open') {
            return redirect()->route('survey', ['uid' => $uid])
                ->with('info', 'Survey masih aktif. Silakan isi survey.');
        }

        return view('survey.partials.closed', ['aplikasi' => $aplikasi]);
    }

    /* ===================== UTIL ===================== */

    private function clearSurveySession()
    {
        session()->forget([
            'responden_data',
            'survey_answers',
            'survey_started_at',
            'survey_step',
            'q_ids',
            'q_total',
            'q_pos',
            'survey_sid',
            'survey_active',
            'survey_committed'
        ]);
    }

    private function validateStep1(Request $request)
    {
        $request->validate([
            'aplikasi_id'   => 'required|exists:aplikasi,id_encrypt',
            'nama' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[A-Za-z\s\'\.\-]+$/u'],
            'usia'          => 'required|integer|min:17|max:99',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'no_hp'         => ['required', 'string', 'regex:/^08[0-9]{8,11}$/']
        ], [
            'nama.regex' => 'Nama hanya boleh mengandung huruf, spasi, titik, tanda petik, dan tanda hubung.',
            'usia.min'   => 'Usia tidak valid.',
            'usia.max'   => 'Usia tidak valid.',
            'no_hp.regex' => 'Format nomor HP tidak valid. Harus dimulai dengan 08 dan 10-13 digit.'
        ]);
    }

    private function getSessionAnswer($kuesionerId)
    {
        $sessionAnswers = session('survey_answers', []);
        if (isset($sessionAnswers[$kuesionerId]) && $this->isValidAnswer($sessionAnswers[$kuesionerId])) {
            $ans = $sessionAnswers[$kuesionerId];
            $ans['source'] = 'session';
            return $ans;
        }
        return null;
    }

    private function isValidAnswer($answer)
    {
        if (!$answer || !isset($answer['tipe']) || !isset($answer['kuesioner_id'])) {
            return false;
        }

        if ($answer['tipe'] === 'radio') {
            return isset($answer['skor']) &&
                is_numeric($answer['skor']) &&
                (int)$answer['skor'] >= 1 &&
                (int)$answer['skor'] <= 5;
        }

        if ($answer['tipe'] === 'free_text') {
            return isset($answer['isi_teks']) && is_string($answer['isi_teks']);
        }

        return false;
    }

    private function isValidAnswerInput($request, $kuesioner)
    {
        $jawaban = $request->input('jawaban');

        if ($kuesioner->tipe === 'radio') {
            return is_numeric($jawaban) && (int)$jawaban >= 1 && (int)$jawaban <= 5;
        }

        if ($kuesioner->tipe === 'free_text') {
            return $jawaban === null || $jawaban === '' || (is_string($jawaban) && strlen(trim($jawaban)) > 0);
        }

        return false;
    }

    private function prepareAnswerDataForSession($request, $aplikasi, $kuesioner)
    {
        $jawaban = $request->input('jawaban');

        $isiTeks = null;
        if ($kuesioner->tipe === 'free_text' && $jawaban !== null) {
            $isiTeks = strip_tags(trim((string)$jawaban));
            $isiTeks = htmlspecialchars($isiTeks, ENT_QUOTES, 'UTF-8');
            $isiTeks = substr($isiTeks, 0, 1000);
        }

        return [
            'kuesioner_id' => $kuesioner->id,
            'skor'         => $kuesioner->tipe === 'radio' && $jawaban !== null
                ? (int)$jawaban
                : null,
            'isi_teks'     => $isiTeks,
            'tipe'         => $kuesioner->tipe,
            'timestamp'    => now()->toISOString(),
        ];
    }

    private function saveToSession($answerData, $kuesionerId)
    {
        $sessionAnswers = session('survey_answers', []);
        $sessionAnswers[$kuesionerId] = $answerData;
        session(['survey_answers' => $sessionAnswers]);
    }

    private function removeFromSession($kuesionerId)
    {
        $sessionAnswers = session('survey_answers', []);
        if (isset($sessionAnswers[$kuesionerId])) {
            unset($sessionAnswers[$kuesionerId]);
            session(['survey_answers' => $sessionAnswers]);
        }
    }

    private function goNext(Request $request, Aplikasi $aplikasi, string $uid, ?int $currentPos = null)
    {
        try {
            $currentPos = $this->qPos();
            $total      = $this->qTotal();

            if ($currentPos >= $total - 1) {
                if ($request->ajax()) {
                    return response()->json([
                        'success'  => true,
                        'redirect' => route('survey.finish', ['uid' => $uid])
                    ]);
                }
                return redirect()->route('survey.finish', ['uid' => $uid]);
            }

            $newPos = $currentPos + 1;
            $this->setQPos($newPos);

            if ($this->qPos() !== $newPos) {
                $this->setQPos($newPos);
            }

            $nextId    = $this->currentQuestionId();
            $nextIndex = $this->qPos() + 1;

            $kuesionerSelanjutnya = $nextId
                ? Kuesioner::select([
                    'id',
                    'pertanyaan',
                    'tipe',
                    'is_mandatory',
                    'kategori_id',
                    'persepsi',
                    'gambar',
                    'skala_type',
                    'skala_labels'
                ])->find($nextId)
                : null;

            if (!$kuesionerSelanjutnya) {
                if ($request->ajax()) {
                    return response()->json([
                        'success'  => true,
                        'redirect' => route('survey.finish', ['uid' => $uid])
                    ]);
                }
                return redirect()->route('survey.finish', ['uid' => $uid]);
            }

            if ($request->ajax()) {
                $jawabanSebelumnya = $this->getSessionAnswer($kuesionerSelanjutnya->id);

                // âœ… PERUBAHAN KRITIS: Gunakan getSkalaLabelsForDisplay() untuk inversi otomatis
                $skalaLabels = $kuesionerSelanjutnya->getSkalaLabelsForDisplay();

                $html = view('survey.partials.step2', [
                    'aplikasi'          => $aplikasi,
                    'kuesioner'         => $kuesionerSelanjutnya,
                    'index'             => $nextIndex,
                    'totalQuestions'    => $total,
                    'jawabanSebelumnya' => $jawabanSebelumnya,
                    'skalaLabels'       => $skalaLabels,
                ])->render();

                return response()->json([
                    'success' => true,
                    'html' => $html
                ]);
            }
            return redirect()->route('survey.question', ['uid' => $uid]);
        } catch (\Exception $e) {
            Log::error('goNext error: ' . $e->getMessage());

            return $request->ajax()
                ? response()->json(['success' => false, 'message' => 'Terjadi kesalahan navigasi.'], 500)
                : back()->withErrors(['error' => 'Terjadi kesalahan navigasi.']);
        }
    }

    private function commitAllDataToDatabase($aplikasiDbId): void
    {
        if (session('survey_committed') === true) {
            Log::warning('Duplicate submission attempt blocked', [
                'aplikasi_id' => $aplikasiDbId,
                'survey_sid' => session('survey_sid'),
                'session_id' => session()->getId()
            ]);
            return; // Stop, jangan commit lagi
        }

        $respondenData  = session('responden_data');
        $sessionAnswers = session('survey_answers', []);

        if (!$respondenData) {
            throw new \Exception('Data responden tidak ditemukan dalam session');
        }

        if (
            empty($respondenData['nama']) ||
            strlen($respondenData['nama']) < 2 ||
            strlen($respondenData['nama']) > 100
        ) {
            throw new \Exception('Data nama tidak valid');
        }

        if (
            empty($respondenData['no_hp']) ||
            strlen($respondenData['no_hp']) < 10 ||
            strlen($respondenData['no_hp']) > 13 ||
            !preg_match('/^08/', $respondenData['no_hp'])
        ) {
            throw new \Exception('Data nomor HP tidak valid');
        }

        if (!in_array($respondenData['jenis_kelamin'], ['Laki-laki', 'Perempuan'])) {
            throw new \Exception('Data jenis kelamin tidak valid');
        }

        if (
            !is_int($respondenData['usia']) ||
            $respondenData['usia'] < 17 ||
            $respondenData['usia'] > 99
        ) {
            throw new \Exception('Data usia tidak valid');
        }

        try {
            DB::beginTransaction();

            $responden = Responden::create([
                'aplikasi_id'   => $respondenData['aplikasi_id'],
                'nama'          => htmlspecialchars($respondenData['nama'], ENT_QUOTES, 'UTF-8'),
                'usia'          => $respondenData['usia'],
                'no_hp'         => preg_replace('/[^0-9]/', '', $respondenData['no_hp']),
                'jenis_kelamin' => $respondenData['jenis_kelamin'],
                'ip_address'    => $respondenData['ip_address'] ?? request()->ip(),
                'user_agent'    => substr($respondenData['user_agent'] ?? request()->userAgent() ?? '', 0, 500),
                'session_id'    => $respondenData['session_id'] ?? session()->getId(),
            ]);

            $validAnswersCount = 0;
            $skippedAnswers    = 0;

            foreach ($sessionAnswers as $kuesionerId => $ans) {
                if (!$this->isValidAnswer($ans)) {
                    $skippedAnswers++;
                    continue;
                }
                if (!Kuesioner::where('id', $ans['kuesioner_id'])->exists()) {
                    $skippedAnswers++;
                    continue;
                }

                $isiTeks = null;
                if (isset($ans['isi_teks']) && $ans['isi_teks']) {
                    $isiTeks = htmlspecialchars($ans['isi_teks'], ENT_QUOTES, 'UTF-8');
                    $isiTeks = substr($isiTeks, 0, 1000);
                }

                Jawaban::create([
                    'aplikasi_id'  => $aplikasiDbId,
                    'responden_id' => $responden->id,
                    'kuesioner_id' => $ans['kuesioner_id'],
                    'skor'         => isset($ans['skor']) ? (int)$ans['skor'] : null,
                    'isi_teks'     => $isiTeks,
                ]);
                $validAnswersCount++;
            }

            // âœ… Set committed flag SEBELUM commit transaction
            session(['survey_committed' => true]);

            DB::commit();

            Log::info("Survey commit berhasil", [
                'responden_id'    => $responden->id,
                'aplikasi_id'     => $aplikasiDbId,
                'survey_sid'      => session('survey_sid'),
                'answers_saved'   => $validAnswersCount,
                'answers_skipped' => $skippedAnswers,
                'session_id'      => $respondenData['session_id'] ?? session()->getId()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            session(['survey_committed' => false]);

            Log::error("Commit survey gagal: " . $e->getMessage(), [
                'aplikasi_id' => $aplikasiDbId,
                'survey_sid' => session('survey_sid'),
                'session_id' => session()->getId(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
            ]);
            throw new \Exception('Gagal menyimpan data survey: ' . $e->getMessage());
        }
    }

    private function guardIfClosed($aplikasi, bool $expectJson = false)
    {
        if (!$aplikasi || $aplikasi->status === 'closed') {
            if ($expectJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Survey ditutup. Aksi tidak dapat dilakukan.'
                ], 423);
            }
            return view('survey.partials.closed', ['aplikasi' => $aplikasi]);
        }
        return null;
    }

    private function qList(): array
    {
        return session('q_ids', []);
    }

    private function qTotal(): int
    {
        return (int) session('q_total', 0);
    }

    private function qPos(): int
    {
        return (int) session('q_pos', 0);
    }

    private function setQPos(int $p): void
    {
        $total = $this->qTotal();
        $p = max(0, min($p, max(0, $total - 1)));
        session(['q_pos' => $p]);
    }

    private function currentQuestionId(): ?int
    {
        $list = $this->qList();
        $pos  = $this->qPos();
        return $list[$pos] ?? null;
    }

    public function prevQuestion(Request $request, string $uid)
    {
        try {
            // âœ… CRITICAL: Check multiple conditions for survey completion
            $isCommitted = session('survey_committed') === true;
            $isNotActive = !session('survey_active');
            $noRespondent = !session('responden_data');
            $noQuestions = !session('q_ids') || !session('q_total');

            // If survey is completed/committed OR any critical session data is missing
            if ($isCommitted || $isNotActive || $noRespondent || $noQuestions) {
                Log::warning('Attempt to navigate back - survey session invalid', [
                    'uid' => $uid,
                    'ip' => $request->ip(),
                    'session_id' => session()->getId(),
                    'survey_committed' => session('survey_committed'),
                    'survey_active' => session('survey_active'),
                    'has_responden' => session('responden_data') ? 'yes' : 'no',
                    'has_questions' => session('q_ids') ? 'yes' : 'no'
                ]);

                return response()->json([
                    'success' => false,
                    'completed' => true,
                    'message' => 'Survey Anda telah selesai. Silakan refresh halaman untuk memulai survey baru.'
                ], 410);
            }

            $aplikasi = Aplikasi::where('id_encrypt', $uid)->firstOrFail();

            if (!session('responden_data')) {
                return response()->json([
                    'success' => false,
                    'completed' => true,
                    'message' => 'Session expired. Silakan refresh halaman.'
                ], 410);
            }

            $this->setQPos($this->qPos() - 1);

            $kid = $this->currentQuestionId();
            if (!$kid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid question'
                ], 404);
            }

            $kuesioner = Kuesioner::select(['id', 'pertanyaan', 'tipe', 'is_mandatory', 'kategori_id', 'persepsi', 'gambar', 'skala_type', 'skala_labels'])
                ->findOrFail($kid);

            $index          = $this->qPos() + 1;
            $totalQuestions = $this->qTotal();
            $jawabanSebelumnya = $this->getSessionAnswer($kuesioner->id);

            // âœ… PERUBAHAN KRITIS: Gunakan getSkalaLabelsForDisplay() untuk inversi otomatis
            $skalaLabels = $kuesioner->getSkalaLabelsForDisplay();

            $html = view('survey.partials.step2', compact('aplikasi', 'kuesioner', 'index', 'totalQuestions', 'jawabanSebelumnya', 'skalaLabels'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            Log::error('prevQuestion error: ' . $e->getMessage(), [
                'uid' => $uid,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }
}
