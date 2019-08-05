<?php

namespace Muserpol\Models\Workflow;

use Illuminate\Database\Eloquent\Model;
use Muserpol\User;
use Muserpol\Models\RecordType;

class WorkflowRecord extends Model
{
    protected $table = "wf_records";
    public $guarded = [];

    public function recordable()
    {
        return $this->morphTo();
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function wf_state()
    {
        return $this->belongsTo(WorkflowState::class);
    }
    public function record_type()
    {
        return $this->belongsTo(RecordType::class);
    }
}
