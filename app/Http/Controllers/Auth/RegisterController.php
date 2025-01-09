<?php

namespace Jexactyl\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Jexactyl\Exceptions\DisplayException;
use Jexactyl\Http\Requests\Auth\RegisterRequest;
use Jexactyl\Services\Users\UserCreationService;
use Jexactyl\Exceptions\Model\DataValidationException;
use Jexactyl\Contracts\Repository\SettingsRepositoryInterface;
use Jexactyl\Models\User;

class RegisterController extends AbstractLoginController
{
    /**
     * RegisterController constructor.
     */
    public function __construct(private UserCreationService $creationService, private SettingsRepositoryInterface $settings)
    {
        parent::__construct();
    }

    /**
     * Handle a register request to the application.
     *
     * @throws DataValidationException|DisplayException
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $approved = false;
        $verified = false;
        $prefix = 'jexactyl::registration:';

        if ($this->settings->get($prefix . 'enabled') != 'true') {
            throw new DisplayException('Unable to register user.');
        }

        if (!$this->settings->get($prefix . 'verification')) {
            $verified = true;
        }

        if ($this->settings->get('jexactyl::approvals:enabled') != 'true') {
            $approved = true;
        }

        // Check if an account already exists for this IP
        $ipAddress = $request->getClientIp();
        if (User::query()->where('ip_address', $ipAddress)->exists()) {
            throw new DisplayException('An account already exists with this IP address.');
        }

        // Proceed with user creation
        $this->creationService->handle([
            'email' => $request->input('email'),
            'username' => $request->input('user'),
            'name_first' => 'Jexactyl',
            'name_last' => 'User',
            'password' => $request->input('password'),
            'ip' => $ipAddress, // Pass the IP to the creation service
            'store_cpu' => $this->settings->get($prefix . 'cpu', 0),
            'store_memory' => $this->settings->get($prefix . 'memory', 0),
            'store_disk' => $this->settings->get($prefix . 'disk', 0),
            'store_slots' => $this->settings->get($prefix . 'slot', 0),
            'store_ports' => $this->settings->get($prefix . 'port', 0),
            'store_backups' => $this->settings->get($prefix . 'backup', 0),
            'store_databases' => $this->settings->get($prefix . 'database', 0),
            'approved' => $approved,
            'verified' => $verified,
        ]);

        return new JsonResponse([
            'data' => [
                'complete' => true,
                'intended' => $this->redirectPath(),
            ],
        ]);
    }
}
