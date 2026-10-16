<?php

namespace App\Models;

use App\Services\DeviceInfoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attempted_login',
        'user_name',
        'user_role',
        'user_email',
        'status',
        'failure_reason',
        'ip_address',
        'device',
        'user_agent',
    ];

    /**
     * Relationship to User (if user exists).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a login attempt safely without throwing exceptions to interrupt auth.
     */
    public static function record(
        Request $request,
        ?User $user = null,
        string $status = 'success',
        ?string $failureReason = null,
        ?string $attemptedLogin = null
    ): ?self {
        try {
            $ip = DeviceInfoService::getClientIp($request);
            $ua = $request->userAgent();
            $device = DeviceInfoService::parse($ua);

            return self::create([
                'user_id' => $user?->id,
                'attempted_login' => $attemptedLogin ?? $request->input('login_id'),
                'user_name' => $user?->name,
                'user_role' => $user?->role,
                'user_email' => $user?->email,
                'status' => $status,
                'failure_reason' => $failureReason,
                'ip_address' => $ip,
                'device' => $device,
                'user_agent' => $ua,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to write login log: ' . $e->getMessage());
            return null;
        }
    }
}
