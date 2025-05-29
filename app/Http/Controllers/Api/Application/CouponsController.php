<?php

namespace Jexactyl\Http\Controllers\Api\Application;

use Carbon\Carbon;
use Jexactyl\Models\Coupon;
use Jexactyl\Http\Controllers\Controller;
use Jexactyl\Http\Requests\Api\Application\Coupons\StoreCouponRequest;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CouponsController extends Controller
{
    public function store(StoreCouponRequest $request): array
    {
        if (Coupon::where('code', $request->input('code'))->exists()) {
            throw new ConflictHttpException('Coupon code already exists');
        }

        $expires = $request->input('expires')
            ? Carbon::now()->addHours($request->input('expires'))
            : null;

        $coupon = Coupon::create([
            'code' => $request->input('code'),
            'uses' => $request->input('uses'),
            'cr_amount' => $request->input('credits'),
            'expires' => $expires,
        ]);

        return $coupon->toArray();
    }
}
