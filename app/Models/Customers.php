<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customers extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;
    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id', 'firstname', 'lastname', 'id_users', 'id_customersType', 'id_company', 'priority', 'updated_at', 'nextActivity'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        
    ];

    public function CustomersCompany(){
        return $this->belongsTo(Company::class, 'id_company');
    }

    public function CustomersType(){
        return $this->belongsTo(CustomersType::class, 'id_customersType');
    }

    public function ToDo(){
        return $this->hasMany(ToDo::class, 'id_customers');
    }

}
