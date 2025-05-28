<?php

namespace App\Http\Controllers\Admin;

use App\Models\Server;
use App\Models\User;
use App\Repositories\Daemon\DaemonServerRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pterodactyl\Exceptions\Http\Connection\DaemonConnectionException;

class ServerSubuserController extends Controller
{
    protected $daemonServerRepository;

    public function __construct(DaemonServerRepository $daemonServerRepository)
    {
        $this->daemonServerRepository = $daemonServerRepository;
    }

    /**
     * Add a subuser to a server.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Server  $server
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Server $server)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->input('email'))->firstOrFail();

        try {
            // Add subuser on Panel
            $server->subusers()->create([
                'user_id' => $user->id,
                'permissions' => $request->input('permissions', ['*']),
            ]);

            // Sync with Daemon
            $this->daemonServerRepository->setServer($server)->revokeUserJTI($user->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Subuser added successfully',
                'user_id' => $user->id
            ]);
        } catch (DaemonConnectionException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync with daemon: ' . $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a subuser from a server.
     *
     * @param  \App\Models\Server  $server
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Server $server, User $user)
    {
        try {
            // Remove subuser from Panel
            $server->subusers()->where('user_id', $user->id)->delete();

            // Sync with Daemon
            $this->daemonServerRepository->setServer($server)->revokeUserJTI($user->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Subuser removed successfully'
            ]);
        } catch (DaemonConnectionException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync with daemon: ' . $exception->getMessage()
            ], 500);
        }
    }
}
