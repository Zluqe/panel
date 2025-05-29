<?php

namespace Jexactyl\Http\Requests\Api\Application\Coupons;

use Jexactyl\Http\Requests\Api\Application\ApplicationApiRequest;

class StoreCouponRequest extends ApplicationApiRequest
{
    // Add this resource property to resolve the initialization error
    protected string $resource = 'coupon';
    protected int $permission = 2; // 2 = Create permission

    public function rules(): array
    {
        return [
            'code' => 'required|string|min:4|max:32',
            'uses' => 'required|integer|min:1',
            'credits' => 'required|integer|min:1',
            'expires' => 'nullable|integer|min:1',
        ];
    }

    // Add this method to override authorization
    public function authorize(): bool
    {
        return true;
    }
}
