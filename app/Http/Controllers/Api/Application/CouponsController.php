<?php

namespace Jexactyl\Http\Controllers\Api\Application;

use Carbon\Carbon;
use Jexactyl\Models\Coupon;
use Jexactyl\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Jexactyl\Http\Requests\Api\Application\Coupons\StoreCouponRequest;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CouponsController extends Controller
{
    public function store(StoreCouponRequest $request): array
    {
        $apiKey = $request->attributes->get('api_key');
        
        // Validate API key permissions
        if (!$apiKey->r_coupons || $apiKey->r_coupons < 2) {
            throw new AccessDeniedHttpException(
                'Your API key does not have permission to create coupons. ' . 
                'Required permission level: 2 or higher'
            );
        }
        
        // Check for duplicate coupon code
        if (Coupon::where('code', $request->input('code'))->exists()) {
            throw new ConflictHttpException('Coupon code already exists');
        }

        // Calculate expiration time if provided
        $expires = null;
        if ($request->input('expires')) {
            try {
                $expires = Carbon::now()->addHours($request->input('expires'));
            } catch (\Exception $e) {
                throw new HttpException(422, 'Invalid expiration time format');
            }
        }

        // Create the coupon
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
            ];
        } catch (\Exception $e) {
            throw new HttpException(500, 'Failed to create coupon: ' . $e->getMessage());
        }
    }
}
