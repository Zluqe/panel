<?php

namespace Jexactyl\Http\Controllers\Base;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Jexactyl\Http\Controllers\Controller;
use Illuminate\View\Factory as ViewFactory;
use Jexactyl\Contracts\Repository\ServerRepositoryInterface;
use GuzzleHttp\Client;

class IndexController extends Controller
{
    /**
     * IndexController constructor.
     */
    public function __construct(
        protected ServerRepositoryInterface $repository,
        protected ViewFactory $view
    ) {
    }

    /**
     * Returns listing of user's servers (dashboard).
     */
    public function index(Request $request): View
    {
        $shouldShowPopup = false;

        $discordId = Auth::user()->discord_id ?? null;
        if (!$discordId) {
            $shouldShowPopup = true;
        } else {
            $isMember = $this->checkDiscordMembership($discordId);
            if (!$isMember) {
                $shouldShowPopup = true;
            }
        }

        return $this->view->make('templates/base.core', [
            'shouldShowPopup' => $shouldShowPopup,
        ]);
    }


    private function checkDiscordMembership($discordId): bool
    {
        $client = new Client();

        try {
            $response = $client->request('GET', sprintf(
                'https://discord.com/api/v10/guilds/%s/members/%s',
                env('DISCORD_GUILD_ID'),
                $discordId
            ), [
                'headers' => [
                    'Authorization' => 'Bot ' . env('DISCORD_TOKEN'),
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return false;
        }
    }
}