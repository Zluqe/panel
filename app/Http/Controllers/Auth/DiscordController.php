<?php

namespace Jexactyl\Http\Controllers\Auth;

use Jexactyl\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Jexactyl\Exceptions\DisplayException;
use Jexactyl\Http\Controllers\Controller;
use Jexactyl\Services\Users\UserCreationService;
use Jexactyl\Contracts\Repository\SettingsRepositoryInterface;

class DiscordController extends Controller
{
    private SettingsRepositoryInterface $settings;
    private UserCreationService $creationService;

    public function __construct(
        UserCreationService $creationService,
        SettingsRepositoryInterface $settings
    ) {
        $this->creationService = $creationService;
        $this->settings        = $settings;
    }

    public function index(): JsonResponse
    {
        return new JsonResponse([
            'https://discord.com/api/oauth2/authorize?' .
            'client_id='    . $this->settings->get('jexactyl::discord:id') .
            '&redirect_uri=' . route('auth.discord.callback') .
            '&response_type=code&scope=identify%20email%20guilds%20guilds.join&prompt=none',
        ], 200, [], null, false);
    }

    public function callback(Request $request)
    {
        $userIp = $request->getClientIp();

        //
        // ─── ENHANCED VPN/PROXY DETECTION ───────────────────────────────────
        //
        $proxyKey = env('PROXYCHECK_KEY');
        if (!empty($proxyKey)) {
            $url = "https://proxycheck.io/v2/{$userIp}?key={$proxyKey}&vpn=1&asn=1&risk=1";
            $response = Http::get($url);
            $data = $response->json();

            // Validate API response status :cite[5]:cite[9]
            if (($data['status'] ?? '') !== 'ok') {
                throw new DisplayException('Failed to verify IP status. Please try again later.');
            }

            $info = $data[$userIp] ?? [];
            $isProxy = ($info['proxy'] ?? 'no') === 'yes';
            $type = strtolower($info['type'] ?? '');
            $isVpn = in_array($type, ['vpn', 'openvpn', 'tor', 'hosting'], true);
            $isHosting = ($info['is_hosting'] ?? false) === true;
            $highRisk = ($info['risk'] ?? 0) > 85; // Risk threshold 85/100 :cite[5]
            
            if ($isProxy || $isVpn || $isHosting || $highRisk) {
                $reason = match(true) {
                    $isProxy => 'Proxy',
                    $isVpn => 'VPN',
                    $isHosting => 'Hosting Service',
                    $highRisk => 'High-Risk IP',
                    default => 'Suspicious Activity'
                };
                throw new DisplayException("{$reason} detected. Please disable it to proceed.");
            }
        }
        //
        // ─── END DETECTION ─────────────────────────────────────────────────

        return $this->proceedWithDiscordLogin($request);
    }

    private function proceedWithDiscordLogin(Request $request)
    {
        $userIp = $request->getClientIp();
        
        // Exchange code for token
        $tokenResp = Http::asForm()->post('https://discord.com/api/oauth2/token', [
            'client_id'     => $this->settings->get('jexactyl::discord:id'),
            'client_secret' => $this->settings->get('jexactyl::discord:secret'),
            'grant_type'    => 'authorization_code',
            'code'          => $request->input('code'),
            'redirect_uri'  => route('auth.discord.callback'),
        ]);

        if (! $tokenResp->ok()) {
            throw new DisplayException('Failed to authenticate with Discord');
        }
        $req = json_decode($tokenResp->body());

        if (preg_match('(email|guilds|identify|guilds\.join)', $req->scope) !== 1) {
            throw new DisplayException('Insufficient OAuth scopes granted');
        }

        // Fetch user info
        $discord = json_decode(
            Http::withHeaders(['Authorization' => 'Bearer ' . $req->access_token])
                ->asForm()
                ->get('https://discord.com/api/users/@me')
                ->body()
        );

        // Email whitelist
        $allowedDomains = [
            'gmail.com', 'outlook.com', 'yahoo.com',
            'icloud.com', 'hotmail.com', 'proton.me',
        ];
        $emailDomain = substr(strrchr($discord->email, "@"), 1);
        if (! in_array($emailDomain, $allowedDomains, true)) {
            throw new DisplayException('Your email provider is not supported. Please contact support.');
        }

        // Add to guild
        Http::withHeaders([
            'Authorization' => 'Bot ' . env('DISCORD_TOKEN'),
        ])->put(
            'https://discord.com/api/v10/guilds/' . env('DISCORD_GUILD_ID') . '/members/' . $discord->id,
            ['access_token' => $req->access_token]
        );

        // Existing user?
        if (User::where('discord_id', $discord->id)->exists()) {
            $user = User::where('discord_id', $discord->id)->first();
            Auth::loginUsingId($user->id, true);
            return redirect('/');
        }

        // New user creation
        $approved = $this->settings->get('jexactyl::discord:enabled') === 'true'
                 && $this->settings->get('jexactyl::approvals:enabled') !== 'true';

        $data = [
            'approved'       => $approved,
            'email'          => $discord->email,
            'username'       => $discord->id,
            'discord_id'     => $discord->id,
            'name_first'     => $discord->username,
            'name_last'      => $discord->discriminator,
            'password'       => $this->genString(),
            'ip'             => $userIp,
            'store_cpu'      => $this->settings->get('jexactyl::registration:cpu', 0),
            'store_memory'   => $this->settings->get('jexactyl::registration:memory', 0),
            'store_disk'     => $this->settings->get('jexactyl::registration:disk', 0),
            'store_slots'    => $this->settings->get('jexactyl::registration:slot', 0),
            'store_ports'    => $this->settings->get('jexactyl::registration:port', 0),
            'store_backups'  => $this->settings->get('jexactyl::registration:backup', 0),
            'store_databases'=> $this->settings->get('jexactyl::registration:database', 0),
        ];

        try {
            $this->creationService->handle($data);
        } catch (\Exception $e) {
            throw new DisplayException('Failed to create user account');
        }

        $user = User::where('username', $discord->id)->first();
        Auth::loginUsingId($user->id, true);
        return redirect('/');
    }

    private function genString(): string
    {
        $chars = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($chars), 0, 16);
    }
}
