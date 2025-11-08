<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Generate nonce untuk CSP
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);

        $response = $next($request);

        // Deteksi konteks aplikasi
        [$isSurveyPublic, $isAdminArea] = $this->detectContext($request);

        // ===== Content Security Policy =====
        $csp = $this->buildCSP($nonce, $isSurveyPublic);
        $response->headers->set('Content-Security-Policy', $csp);

        // ===== X-Content-Type-Options =====
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // ===== X-Frame-Options =====
        if ($isSurveyPublic) {
            $response->headers->remove('X-Frame-Options');
        } else {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        // ===== X-XSS-Protection =====
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // ===== Strict-Transport-Security (HSTS) =====
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // ===== Referrer-Policy =====
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ===== Permissions-Policy =====
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()'
        );

        // ===== Hapus Header Sensitif =====
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }

    private function buildCSP(string $nonce, bool $isSurveyPublic): string
    {
        $csp = [
            // Default: hanya dari self
            "default-src 'self'",

            "script-src 'self' 'nonce-{$nonce}' " .
                "https://code.jquery.com " .
                "https://cdn.jsdelivr.net " .
                "https://cdnjs.cloudflare.com " .
                "https://cdn.datatables.net " .
                "https://challenges.cloudflare.com " .
                "https://static.cloudflareinsights.com",

            "style-src 'self' 'nonce-{$nonce}' " .
                "https://fonts.googleapis.com " .
                "https://cdn.jsdelivr.net " .
                "https://cdnjs.cloudflare.com " .
                "https://cdn.datatables.net",

            // Font: self + Google Fonts + CDN + data URIs
            "font-src 'self' " .
                "https://fonts.gstatic.com " .
                "https://cdnjs.cloudflare.com " .
                "data:",

            // Image: self + data URIs + https + blob
            "img-src 'self' data: https: blob:",

            // Connect: self + API endpoints + CDN
            "connect-src 'self' " .
                "https://digisatis.my.id " .
                "https://cdn.jsdelivr.net " .
                "https://cdnjs.cloudflare.com " .
                "https://cdn.datatables.net " .
                "https://challenges.cloudflare.com",

            // Frame: self + Cloudflare Turnstile
            "frame-src 'self' https://challenges.cloudflare.com https://digisatis.my.id",

            // Object: none
            "object-src 'none'",

            // Base URI: self
            "base-uri 'self'",

            // Form Action: self
            "form-action 'self'",

            // Media: self
            "media-src 'self'",
        ];

        // Frame Ancestors: berbeda untuk public survey vs admin
        if ($isSurveyPublic) {
            $csp[] = "frame-ancestors 'self' https://gresikkab.go.id https://*.gresikkab.go.id";
        } else {
            $csp[] = "frame-ancestors 'self'";
        }

        return implode('; ', $csp) . ';';
    }

    private function detectContext(Request $request): array
    {
        $path = trim($request->path(), '/');

        // Jika root path
        if ($path === '') {
            return [true, false]; // Survey public
        }

        $firstSegment = explode('/', $path)[0];

        // Daftar prefix untuk admin area
        $adminPrefixes = [
            'dashboard',
            'login',
            'logout',
            'captcha-refresh',
            'admin',
            'responden',
        ];

        $isAdminArea = in_array($firstSegment, $adminPrefixes, true);
        $isSurveyPublic = !$isAdminArea;

        return [$isSurveyPublic, $isAdminArea];
    }
}
