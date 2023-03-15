<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estimate extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;
    protected $table = 'estimate';
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id', 'estimateNumber', 'estimateName', 'created_at', 'totalPriceHT', 'totalPriceTTC', 'tva', 'ended', 'estimatedCompletionDate', 'endDate', 'isBilled', 'id_customers', 'id_estimateStatus', 'users_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        
    ];

    public function Customers(){
        return $this->belongsTo(Customers::class, 'id_customers');
    }

    public function EstimateStatus(){
        return $this->belongsTo(EstimateStatus::class, 'id_estimateStatus');
    }

    public function EstimateItems(){
        return $this->belongsToMany(EstimateItems::class, 'addeditems', 'id', 'id_estimateitems');
    }

    public function Company(){
        return $this->hasOneThrough(Company::class, Customers::class, 'id', 'id', 'id_customers', 'id_company');
    }
  
    public function AddedItems(){
        return $this->hasMany(AddedItems::class, 'id');
    }

    public function CustomersInvoice(){
        return $this->hasMany(CustomersInvoice::class, 'id_estimate');
    }
}
