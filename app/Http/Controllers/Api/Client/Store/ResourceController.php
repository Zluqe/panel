<?php

namespace Jexactyl\Http\Controllers\Api\Client\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Jexactyl\Exceptions\DisplayException;
use Jexactyl\Services\Store\ResourcePurchaseService;
use Jexactyl\Transformers\Api\Client\Store\CostTransformer;
use Jexactyl\Transformers\Api\Client\Store\UserTransformer;
use Jexactyl\Http\Controllers\Api\Client\ClientApiController;
use Jexactyl\Http\Requests\Api\Client\Store\PurchaseResourceRequest;

class ResourceController extends ClientApiController
{
    private int $maxCredits = 10000; // Define the maximum credit limit
    private int $maxDailyCredits = 360; // Define the daily credit earning limit

    /**
     * ResourceController constructor.
     */
    public function __construct(private ResourcePurchaseService $purchaseService)
    {
        parent::__construct();
    }

    /**
     * Get the resources for the authenticated user.
     *
     * @throws DisplayException
     */
    public function user(Request $request)
    {
        return $this->fractal->item($request->user())
            ->transformWith($this->getTransformer(UserTransformer::class))
            ->toArray();
    }

    /**
     * Get the cost of resources.
     *
     * @throws DisplayException
     */
    public function costs(Request $request)
    {
        $data = [];
        $prefix = 'jexactyl::store:cost:';
        $types = ['cpu', 'memory', 'disk', 'slot', 'port', 'backup', 'database'];

        foreach ($types as $type) {
            array_push($data, $this->settings->get($prefix . $type, 0));
        }

        return $this->fractal->item($data)
            ->transformWith($this->getTransformer(CostTransformer::class))
            ->toArray();
    }

    /**
     * Allows a user to earn credits via passive earning.
     *
     * @throws DisplayException
     */
    public function earn(Request $request)
    {
        $amount = $this->settings->get('jexactyl::earn:amount', 0);

        if ($this->settings->get('jexactyl::earn:enabled') != 'true') {
            throw new DisplayException('Credit earning is currently disabled.');
        }

        $user = $request->user();
        $currentDate = now();

        // Reset daily credits if the reset date has passed
        if ($user->daily_reset_at === null || $currentDate->greaterThan($user->daily_reset_at)) {
            $user->update([
                'daily_credits_earned' => 0,
                'daily_reset_at' => $currentDate->endOfDay(),
            ]);
        }

        $newDailyTotal = $user->daily_credits_earned + $amount;

        if ($newDailyTotal > $this->maxDailyCredits) {
            throw new DisplayException("You can only earn up to {$this->maxDailyCredits} credits per day.");
        }

        $newBalance = $user->store_balance + $amount;

        if ($newBalance > $this->maxCredits) {
            throw new DisplayException("You cannot have more than {$this->maxCredits} credits.");
        }

        // Update the user's daily credits and store balance
        $user->update([
            'store_balance' => $newBalance,
            'daily_credits_earned' => $newDailyTotal,
        ]);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Allows users to purchase resources via the store.
     *
     * @throws DisplayException
     */
    public function purchase(PurchaseResourceRequest $request): JsonResponse
    {
        $user = $request->user();
        $newBalance = $user->store_balance - $request->input('amount');

        if ($newBalance > $this->maxCredits) {
            throw new DisplayException("You cannot have more than {$this->maxCredits} credits.");
        }

        $this->purchaseService->handle($request);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}