<?php

namespace Jexactyl\Http\Controllers\Auth;

use Jexactyl\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Jexactyl\Facades\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LoginController extends AbstractLoginController
{
    private ViewFactory $view;

    public function __construct(ViewFactory $view)
    {
        parent::__construct();
        $this->view = $view;
    }

    public function index(): View
    {
        return $this->view->make('templates/auth.core');
    }

    public function login(Request $request): JsonResponse
    {
        $userIp = $request->getClientIp();

        //
        // ─── WHITELIST BYPASS ───────────────────────────────────────────────
        //
        $whitelistFile = base_path('whitelist_ip.txt');
        if (file_exists($whitelistFile)) {
            $whitelisted = array_map('trim', file(
                $whitelistFile,
                FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
            ));
            if (in_array($userIp, $whitelisted, true)) {
                return $this->attemptLogin($request);
            }
        }
        //
        // ─── END WHITELIST BYPASS ───────────────────────────────────────────

        //
        // ─── PROXYCHECK.IO LOOKUP ───────────────────────────────────────────
        //
        $proxyKey = env('PROXYCHECK_KEY');
        $response = Http::get("https://proxycheck.io/v2/{$userIp}?key={$proxyKey}&vpn=1");
        $data = $response->json();

        if (($data['status'] ?? '') !== 'ok') {
            return response()->json(['error' => 'Failed to verify IP status. Please try again later.'], 403);
        }

        $info    = $data[$userIp] ?? [];
        $isProxy = (($info['proxy'] ?? 'no') === 'yes');
        $type    = strtolower($info['type'] ?? '');
        $isVpn   = in_array($type, ['vpn', 'openvpn'], true);

        if ($isProxy || $isVpn) {
            return response()->json(['error' => 'VPN or Proxy detected. Please disable it to proceed.'], 403);
        }
        //
        // ─── END PROXYCHECK.IO LOOKUP ────────────────────────────────────────

        return $this->attemptLogin($request); // Removed $userIp argument
    }

    // Updated method signature to match parent (only Request $request)
    protected function attemptLogin(Request $request): JsonResponse
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->sendLockoutResponse($request);
        }

        try {
            $username = $request->input('user');
            /** @var User $user */
            $user = User::query()
                ->where($this->getField($username), $username)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->sendFailedLoginResponse($request);
        }

        if (! password_verify($request->input('password'), $user->password)) {
            $this->sendFailedLoginResponse($request, $user);
        }

        if (! $user->use_totp) {
            return $this->sendLoginResponse($user, $request);
        }

        Activity::event('auth:checkpoint')
            ->withRequestMetadata()
            ->subject($user)
            ->log();

        $request->session()->put('auth_confirmation_token', [
            'user_id'     => $user->id,
            'token_value' => $token = Str::random(64),
            'expires_at'  => CarbonImmutable::now()->addMinutes(5),
        ]);

        return new JsonResponse([
            'data' => [
                'complete'           => false,
                'confirmation_token' => $token,
            ],
        ]);
    }
}