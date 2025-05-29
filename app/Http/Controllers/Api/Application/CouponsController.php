<?php

namespace Jexactyl\Http\Controllers\Api\Application;

use Carbon\Carbon;
use Jexactyl\Models\Coupon;
use Jexactyl\Models\ApiKey;
use Jexactyl\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Jexactyl\Http\Requests\Api\Application\Coupons\StoreCouponRequest;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CouponsController extends Controller
{
    public function store(StoreCouponRequest $request): array
    {
        // Get token from authenticated user
        $token = $request->user()->currentAccessToken();
        
        // Handle API key retrieval
        if ($token instanceof ApiKey) {
            $apiKey = $token;
        } else {
            // Fallback to request attribute
            $apiKey = $request->attributes->get('api_key');
            
            // Final fallback to database lookup
            if (!$apiKey) {
                $apiKey = ApiKey::find($token->tokenable_id);
            }
        }

        // Verify API key exists
        if (!$apiKey) {
            throw new AccessDeniedHttpException(
                'Could not identify valid API key for this request'
            );
        }

        // Validate permissions
        if ($apiKey->r_coupons < 2) {
            throw new AccessDeniedHttpException(
                'Your API key does not have permission to create coupons. ' . 
                'Required permission level: 2 or higher'
            );
        }
        
        // Check for duplicate coupon code
        if (Coupon::where('code', $request->input('code'))->exists()) {
            throw new ConflictHttpException('Coupon code already exists');
        }

        // Handle expiration time
        $expires = null;
        if ($request->filled('expires')) {
            try {
                $expires = Carbon::now()->addHours($request->input('expires'));
            } catch (\InvalidArgumentException $e) {
                throw new HttpException(422, 'Invalid expiration hours');
            }
        }

        // Create coupon
        try {
            $coupon = Coupon::create([
                'code' => $request->input('code'),
                'uses' => $request->input('uses'),
                'cr_amount' => $request->input('credits'),
                'expires' => $expires,
                'created_at' => Carbon::now(),
            ]);
            
            return [
                'success' => true,
                'data' => $coupon->toArray(),
                'message' => 'Successfully created coupon code',
            ];
        } catch (\Exception $e) {
            throw new HttpException(500, 'Failed to create coupon: ' . $e->getMessage());
        }
    }
}
