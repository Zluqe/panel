<?php

namespace Jexactyl\Http\Controllers\Api\Client;

use Jexactyl\Models\User;
use Jexactyl\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Jexactyl\Facades\Activity;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Jexactyl\Notifications\VerifyEmail;
use Jexactyl\Exceptions\DisplayException;
use Jexactyl\Services\Users\UserUpdateService;
use Jexactyl\Transformers\Api\Client\AccountTransformer;
use Jexactyl\Http\Requests\Api\Client\Account\UpdateEmailRequest;
use Jexactyl\Http\Requests\Api\Client\Account\UpdatePasswordRequest;
use Jexactyl\Http\Requests\Api\Client\Account\UpdateUsernameRequest;

class AccountController extends ClientApiController
{
    /**
     * AccountController constructor.
     */
    public function __construct(private AuthManager $manager, private UserUpdateService $updateService)
    {
        parent::__construct();
    }

    public function index(Request $request): array
    {
        return $this->fractal->item($request->user())
            ->transformWith($this->getTransformer(AccountTransformer::class))
            ->toArray();
    }

    /**
     * Update the authenticated user's email address.
     */
    public function updateEmail(UpdateEmailRequest $request): JsonResponse
    {
        $original = $request->user()->email;
        $this->updateService->handle($request->user(), $request->validated());

        if ($original !== $request->input('email')) {
            Activity::event('user:account.email-changed')
                ->property(['old' => $original, 'new' => $request->input('email')])
                ->log();
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Update the authenticated user's password. All existing sessions will be logged
     * out immediately.
     *
     * @throws \Throwable
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $this->updateService->handle($request->user(), $request->validated());

        $guard = $this->manager->guard();
        $guard->setUser($user);

        if (method_exists($guard, 'logoutOtherDevices')) {
            $guard->logoutOtherDevices($request->input('password'));
        }

        Activity::event('user:account.password-changed')->log();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Update the authenticated user's username.
     *
     * @throws \Jexactyl\Exceptions\Model\DataValidationException
     * @throws \Jexactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function updateUsername(UpdateUsernameRequest $request): JsonResponse
    {
        return response()->json([
            'error' => 'Username change is disabled.',
        ], 403);
    }

    public function verify(Request $request): JsonResponse
    {
        $token = $this->genStr();
        $name = $this->settings->get('settings::app:name', 'Jexactyl');
        DB::table('verification_tokens')->insert(['user' => $request->user()->id, 'token' => $token]);
        $request->user()->notify(new VerifyEmail($request->user(), $name, $token));

        return new JsonResponse(['success' => true, 'data' => []]);
    }

    /**
     * Redeem a coupon for the authenticated user.
     *
     * This method checks the coupon code, verifies it hasn’t expired or been used up,
     * and ensures that the current user (based on their username) hasn't already redeemed it.
     *
     * @throws DisplayException
     */
    public function coupon(Request $request)
    {
        $code = $request->input('code');
        $coupon = Coupon::query()->where('code', $code)->first();
        if (!$coupon) {
            throw new DisplayException('Invalid coupon code specified.');
        }
        if ($coupon->getAttribute('expired')) {
            throw new DisplayException('This coupon has expired.');
        }
        if ($coupon->getAttribute('uses') < 1) {
            throw new DisplayException('This coupon has no uses left.');
        }

        $username = $request->user()->username;

        // Check if this user already redeemed the coupon.
        $exists = DB::table('coupon_redemptions')
            ->where('coupon_id', $coupon->id)
            ->where('username', $username)
            ->exists();
        if ($exists) {
            throw new DisplayException('You have already redeemed this coupon.');
        }

        // Insert a new coupon redemption record.
        DB::table('coupon_redemptions')->insert([
            'coupon_id'  => $coupon->id,
            'username'   => $username,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Decrement the coupon's available uses.
        $coupon->uses = $coupon->uses - 1;
        $coupon->save();

        // Credit the user's store balance.
        $balance = $request->user()->store_balance;
        $request->user()->update(['store_balance' => $balance + $coupon->cr_amount]);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    private function genStr(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $pieces = [];
        $max = mb_strlen($chars, '8bit') - 1;
        for ($i = 0; $i < 32; ++$i) {
            $pieces[] = $chars[mt_rand(0, $max)];
        }

        return implode('', $pieces);
    }
}
