<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Crypt;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Helper untuk generate encrypted survey URL
        if (!function_exists('generateSurveyUrl')) {
            function generateSurveyUrl($aplikasiId, $step = 'step-1') {
                $encryptedId = Crypt::encrypt($aplikasiId);
                
                switch($step) {
                    case 'step-1':
                        return route('survey', ['encryptedAplikasiId' => $encryptedId]);
                    case 'step-2':
                        return route('survey.question', ['encryptedAplikasiId' => $encryptedId, 'index' => 1]);
                    case 'finish':
                        return route('survey.finish', ['encryptedAplikasiId' => $encryptedId]);
                    default:
                        return route('survey', ['encryptedAplikasiId' => $encryptedId]);
                }
            }
        }

        // Helper untuk decrypt aplikasi ID
        if (!function_exists('decryptAplikasiId')) {
            function decryptAplikasiId($encryptedId) {
                try {
                    return Crypt::decrypt($encryptedId);
                } catch (\Exception $e) {
                    abort(404, 'Survey tidak ditemukan');
                }
            }
        }

        // Helper untuk generate survey URL dengan question index
        if (!function_exists('generateSurveyQuestionUrl')) {
            function generateSurveyQuestionUrl($aplikasiId, $questionIndex = 1) {
                $encryptedId = Crypt::encrypt($aplikasiId);
                return route('survey.question', [
                    'encryptedAplikasiId' => $encryptedId, 
                    'index' => $questionIndex
                ]);
            }
        }
    }
}