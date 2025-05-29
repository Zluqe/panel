<?php

namespace Jexactyl\Http\Requests\Api\Application\Coupons;

use Jexactyl\Http\Requests\Api\Application\ApplicationApiRequest;

class StoreCouponRequest extends ApplicationApiRequest
{
    public function rules(): array
    {
        return [
            'code' => 'required|string|min:4|max:32',
            'uses' => 'required|integer|min:1',
            'credits' => 'required|integer|min:1',
            'expires' => 'nullable|integer|min:1',
        ];
    }
}
