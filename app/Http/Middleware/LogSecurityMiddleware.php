<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogSecurityMiddleware
{
    private array $suspiciousPatterns = [
        '/union\s+select/i',
        '/\bexec\b/i',
        '/\bxp_cmdshell\b/i',
        '/\bselect\b.*\bfrom\b/i',
        '/\binsert\b.*\binto\b/i',
        '/\bupdate\b.*\bset\b/i',
        '/\bdelete\b.*\bfrom\b/i',
        '/\bdrop\b.*\btable\b/i',
        '/<script/i',
        '/javascript:/i',
        '/onerror=/i',
        '/onclick=/i',
    ];

    public function handle(Request $request, Closure $next)
    {
        // Логируем подозрительные запросы
        if ($this->isSuspicious($request)) {
            Log::warning('Suspicious request detected', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'input' => $request->except(['password', 'password_confirmation']),
            ]);
        }

        // Логируем все неудачные попытки авторизации
        if ($request->is('api/auth/login') && $request->method() === 'POST') {
            Log::info('Login attempt', [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
            ]);
        }

        return $next($request);
    }

    private function isSuspicious(Request $request): bool
    {
        $input = json_encode($request->all());

        foreach ($this->suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }
}
