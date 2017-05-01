<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\GASModel;
use App\SluggableID;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, CreditableTrait, GASModel, SluggableID;

    public $incrementing = false;
    protected $table = 'users';
    protected $fillable = ['firstname', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    public function gas()
    {
        return $this->belongsTo('App\Gas');
    }

    public function roles($target = null)
    {
        return $this->belongsToMany('App\Role')->orderBy('name', 'asc')->withPivot('id');
    }

    public function notifications()
    {
        return $this->belongsToMany('App\Notification')->withPivot('done')->where('notification_user.done', '=', false)->orderBy('start_date', 'desc');
    }

    public function allnotifications()
    {
        return $this->belongsToMany('App\Notification')->orderBy('start_date', 'desc');
    }

    public function contacts()
    {
        return $this->morphMany('App\Contact', 'target');
    }

    public function deposit()
    {
        return $this->belongsTo('App\Movement');
    }

    public function fee()
    {
        return $this->belongsTo('App\Movement');
    }

    public function getSlugID()
    {
        return str_slug($this->printableName());
    }

    public function printableName()
    {
        return $this->lastname.' '.$this->firstname;
    }

    public function addRole($role, $assigned)
    {
        $test = $this->roles()->where('roles.id', $role->id)->first();
        if ($test == null) {
            $this->roles()->attach($role->id);
            $test = $this->roles()->where('roles.id', $role->id)->first();
        }

        if ($assigned)
            $test->attachApplication($assigned);
    }

    public function removeRole($role, $assigned)
    {
        $test = $this->roles()->where('roles.id', $role->id)->first();
        if ($test == null)
            return;

        if ($assigned)
            $test->detachApplication($assigned);
        else
            $this->roles()->detach($role->id);
    }
}
