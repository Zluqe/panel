<?php

namespace Jexactyl\Http\Requests\Api\Application\Servers\Subuser;

use Jexactyl\Http\Requests\Api\Application\ApplicationApiRequest;

class StoreSubuserRequest extends ApplicationApiRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string',
        ];
    }
}
