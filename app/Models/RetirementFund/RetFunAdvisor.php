<?php

namespace Muserpol\Models\RetirementFund;

use Illuminate\Database\Eloquent\Model;
use Muserpol\Helpers\Util;

class RetFunAdvisor extends Model
{
    public function city_identity_card()
    {
        return $this->belongsTo('Muserpol\Models\City','city_identity_card_id','id');
    }
    
    public function kinship()
    {
        return $this->belongsTo('Muserpol\Models\Kinship');
    }

    public function ret_fun_beneficiaries()
    {
        return $this->belongsToMany('Muserpol\Models\RetirementFund\RetFunBeneficiary','ret_fun_advisor_beneficiary','ret_fun_advisor_id','ret_fun_beneficiary_id');
    }

    /**
     * Methods
     */
    public function fullName()
    {
        $name = $this->first_name . ' ' . $this->second_name . ' ' . $this->last_name . ' ' . $this->mothers_last_name . ' ' . $this->applicant_surname_husband;
        return Util::removeSpaces($name);
    }
}
