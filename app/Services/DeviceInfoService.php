<?php

namespace App\Services;

use Illuminate\Http\Request;

class DeviceInfoService
{
    /**
     * Parse User-Agent string into human-readable device, OS, and browser summary.
     */
    public static function parse(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Perangkat Tidak Dikenal';
        }

        $ua = $userAgent;
        $device = '💻 Komputer / Laptop';
        $os = 'Unknown OS';
        $browser = 'Unknown Browser';

        $isMobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $ua);

        if (preg_match('/iPhone/i', $ua)) {
            $device = '📱 iPhone';
            if (preg_match('/OS (\d+[_.]\d+)/i', $ua, $matches)) {
                $os = 'iOS ' . str_replace('_', '.', $matches[1]);
            } else {
                $os = 'iOS';
            }
        } elseif (preg_match('/iPad/i', $ua)) {
            $device = '📱 iPad';
            if (preg_match('/OS (\d+[_.]\d+)/i', $ua, $matches)) {
                $os = 'iPadOS ' . str_replace('_', '.', $matches[1]);
            } else {
                $os = 'iPadOS';
            }
        } elseif (preg_match('/Android/i', $ua)) {
            $os = 'Android';
            if (preg_match('/Android\s+([0-9\.]+)/i', $ua, $matches)) {
                $os = 'Android ' . $matches[1];
            }
            if (preg_match('/;\s*([^;]+?)\s*Build/i', $ua, $matches)) {
                $model = trim($matches[1]);
                $device = "📱 HP ({$model})";
            } else {
                $device = '📱 HP Android';
            }
        } elseif (preg_match('/Windows NT 10.0/i', $ua)) {
            $device = '💻 Komputer / Laptop';
            $os = 'Windows 10/11';
        } elseif (preg_match('/Windows NT 6.3/i', $ua)) {
            $device = '💻 Komputer / Laptop';
            $os = 'Windows 8.1';
        } elseif (preg_match('/Windows NT 6.1/i', $ua)) {
            $device = '💻 Komputer / Laptop';
            $os = 'Windows 7';
        } elseif (preg_match('/Windows NT/i', $ua)) {
            $device = '💻 Komputer / Laptop';
            $os = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS X/i', $ua)) {
            $device = '💻 Mac / MacBook';
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $ua)) {
            $device = '💻 Linux Device';
            $os = 'Linux';
        }

        // Browser Detection
        if (preg_match('/Edg\/([0-9\.]+)/i', $ua, $matches)) {
            $browser = 'Edge ' . explode('.', $matches[1])[0];
        } elseif (preg_match('/Chrome\/([0-9\.]+)/i', $ua, $matches)) {
            $browser = ($isMobile ? 'Chrome Mobile ' : 'Chrome ') . explode('.', $matches[1])[0];
        } elseif (preg_match('/Safari\/([0-9\.]+)/i', $ua, $matches) && !preg_match('/Chrome/i', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/Firefox\/([0-9\.]+)/i', $ua, $matches)) {
            $browser = 'Firefox';
        }

        return "{$device} • {$os} • {$browser}";
    }

    /**
     * Get accurate client IP address (supporting reverse proxy like Vercel/Cloudflare).
     */
    public static function getClientIp(Request $request): string
    {
        $forwarded = $request->header('x-forwarded-for');
        if ($forwarded) {
            $ips = explode(',', $forwarded);
            return trim($ips[0]);
        }
        return $request->ip() ?? 'Unknown IP';
    }
}
