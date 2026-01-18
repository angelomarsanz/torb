<?php

/**
 * User Model
 *
 * User Model manages User operation.
 *
 * @category   User
 * @package    vRent
 * @author     Techvillage Dev Team
 * @copyright  2020 Techvillage
 * @license
 * @version    2.7
 * @link       http://techvill.net
 * @since      Version 1.3
 * @deprecated None
 */

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\UserDetails;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = ['profile_src'];

    public function users_verification()
    {
        return $this->hasOne('App\\Models\\UsersVerification', 'user_id', 'id');
    }

    public function payouts()
    {
        return $this->hasMany('App\\Models\\Payouts', 'user_id', 'id');
    }

    public function accounts()
    {
        return $this->hasMany('App\\Models\\Accounts', 'user_id', 'id');
    }

    public function getWallet()
    {
        return $this->hasOne('App\\Models\\Wallet', 'user_id', 'id');
    }

    public function activity()
    {
        return $this->hasMany('App\\Models\\Activity', 'user_id', 'id');
    }

    public function withdrawal()
    {
        return $this->hasMany('App\\Models\\Withdrawal', 'user_id', 'id');
    }

    public function properties()
    {
        return $this->hasMany('App\\Models\\Properties', 'host_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany('App\\Models\\Reviews', 'sender_id', 'id');
    }

    public function getProfileSrcAttribute()
    {
        if ($this->attributes['profile_image'] == '') {
            $src = asset('images/default-profile.png');
        } else {
            $src = url('images/profile/'.$this->attributes['id'].'/'.$this->attributes['profile_image']);
        }

        return $src;
    }

    public function details_key_value()
    {
        $details = UserDetails::where('user_id', $this->attributes['id'])->pluck('value', 'field');
        return $details;
    }

    public function getAccountSinceAttribute()
    {
        $since = date('F Y', strtotime($this->created_at));

        return $since;
    }
}