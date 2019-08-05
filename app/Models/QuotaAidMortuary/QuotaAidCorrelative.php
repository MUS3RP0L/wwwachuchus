<?php

namespace Muserpol\Models\QuotaAidMortuary;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotaAidCorrelative extends Model
{
    use  SoftDeletes;
    public function wf_state()
    {
        return $this->belongsTo('Muserpol\Models\Workflow\WorkflowState', 'wf_state_id');
    }
    public function user()
    {
        return $this->belongsTo('Muserpol\User');
    }
    public function quota_aid_mortuary()
    {
        return $this->belongsTo('Muserpol\Models\QuotaAidMortuary\QuotaAidMortuary');
    }
}
