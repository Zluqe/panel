<?php

namespace Jexactyl\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * @property string $code
 * @property int $uses
 * @property int $expires
 * @property int $cr_amount
 */
class Coupon extends Model
{
    public const RESOURCE_NAME = 'coupon';
    protected $table = 'coupons';

    protected $fillable = [
        'expired',
        'redeemed_by',
    ];

    protected $casts = [
        'redeemed_by' => 'array',
    ];

    public static array $validationRules = [
        'code' => 'required|string',
        'uses' => 'required|integer',
        'expires' => 'nullable|string',
        'expired' => 'nullable|boolean',
        'cr_amount' => 'required|integer',
    ];

    /**
     * Redeem this coupon for a given username.
     *
     * @param string $username
     * @throws \Exception if the coupon has already been redeemed by this user or if the redemption limit is reached.
     */
    public function redeem(string $username)
    {
        // Initialize the redeemed list if null.
        $redeemed = $this->redeemed_by ?? [];

        if (in_array($username, $redeemed)) {
            throw new \Exception("Coupon already redeemed by this user.");
        }

        if (count($redeemed) >= $this->uses) {
            throw new \Exception("Coupon redemption limit reached.");
        }

        $redeemed[] = $username;
        $this->redeemed_by = $redeemed;
        $this->save();
    }
}