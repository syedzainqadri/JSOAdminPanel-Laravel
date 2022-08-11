<?php

namespace App\Models;

use App\Models\Setting;
use App\Models\UserPlan;
use App\Models\Transaction;
use Modules\Ad\Entities\Ad;
use Illuminate\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\PushNotification\Entities\UserDeviceToken;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, MustVerifyEmail;

    protected $guarded = [];

    protected $appends = ['image_url'];
    protected $guard = 'user';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function ($customer) {
            $setting = Setting::first();
            $customer->userPlan()->create([
                'ad_limit'  =>  $setting->free_ad_limit,
                'featured_limit'  =>  $setting->free_featured_ad_limit,
                'subscription_type' => $setting->subscription_type,
            ]);
        });
    }

    public function getImageUrlAttribute()
    {
        if (is_null($this->image)) {
            return asset('backend/image/default-user.png');
        }

        return asset($this->image);
    }

    /**
     *  HasMany
     * @return HasMany|Collection|Customer
     */
    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    /**
     * User Pricing Plan
     *
     * @return HasOne
     *
     */
    public function userPlan(): HasOne
    {
        return $this->hasOne(UserPlan::class);
    }

    /**
     * User Transactions
     *
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function reviews()
    {
        return $this->hasMany(User::class, 'user_id');
    }

    public function socialMedia()
    {
        return $this->hasMany(SocialMedia::class, 'user_id');
    }

    public function deviceToken()
    {
        return $this->hasMany(UserDeviceToken::class, 'user_id', 'id');
    }
}
