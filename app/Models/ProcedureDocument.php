<?php

namespace Muserpol\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedureDocument extends Model
{
    public function procedure_requirements()
    {
        return $this->hasMany('Muserpol\Models\ProcedureRequirement');
    }

    public function scanned_documents()
    {
        return $this->hasMany('Muserpol\Models\ScannedDocument');
    }
}
