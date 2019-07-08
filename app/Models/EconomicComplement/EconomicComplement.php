<?php

namespace Muserpol\Models\EconomicComplement;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Muserpol\Models\BaseWage;
use Muserpol\Models\ComplementaryFactor;
use Muserpol\Models\Workflow\WorkflowState;
use Muserpol\Helpers\Util;
use Log;
use Illuminate\Support\Facades\Crypt;
use Hashids\Hashids;
use DB;

class EconomicComplement extends Model
{
    // protected $table = 'economic_complements_1';
    protected $guarded = [];
    use SoftDeletes;

    public function user()
    {
        return $this->belongsTo('Muserpol\User');
    }
    public function affiliate()
    {
        return $this->belongsTo('Muserpol\Models\Affiliate');
    }
    public function eco_com_state()
    {
        return $this->belongsTo('Muserpol\Models\EconomicComplement\EcoComState');
    }
    public function eco_com_procedure()
    {
        return $this->belongsTo('Muserpol\Models\EconomicComplement\EcoComProcedure');
    }
    public function procedure_state()
    {
        return $this->belongsTo('Muserpol\Models\ProcedureState');
    }
    public function city()
    {
        return $this->belongsTo('Muserpol\Models\City');
    }
    public function category()
    {
        return $this->belongsTo('Muserpol\Models\Category');
    }
    public function degree()
    {
        return $this->belongsTo('Muserpol\Models\Degree');
    }
    public function base_wage()
    {
        return $this->belongsTo('Muserpol\Models\BaseWage');
    }
    public function complementary_factor()
    {
        return $this->belongsTo('Muserpol\Models\ComplementaryFactor');
    }
    public function wf_state()
    {
        return $this->belongsTo('Muserpol\Models\Workflow\WorkflowState', 'wf_current_state_id', 'id');
    }
    public function workflow()
    {
        return $this->belongsTo('Muserpol\Models\Workflow\Workflow');
    }
    public function discount_types()
    {
        return $this->belongsToMany('Muserpol\Models\DiscountType')->withPivot(['amount', 'date', 'message'])->withTimestamps();
    }
    public function encode()
    {
        $hashids = new Hashids('economic_complements', 10);
        return $hashids->encode($this->id);
    }
    public function decode($hash)
    {
        $hashids = new Hashids('economic_complements', 10);
        $id = $hashids->decode($hash);
        if ($id) {
            return $id[0];
        }
        return null;
    }
    public function getBasicInfoCode()
    {
        return array('hash' => $this->encode());
    }
    public function submitted_documents()
    {
        return $this->hasMany(EcoComSubmittedDocument::class);
    }
    public function eco_com_legal_guardian()
    {
        return $this->hasOne(EcoComLegalGuardian::class);
    }
    /**
     *!! TODO
     */
    public function eco_com_modality()
    {
        return $this->belongsTo('Muserpol\Models\EconomicComplement\EcoComModality');
    }
    public function eco_com_beneficiary()
    {
        return $this->hasOne('Muserpol\Models\EconomicComplement\EcoComBeneficiary');
    }
    public function tags()
    {
        return $this->morphToMany('Muserpol\Models\Tag', 'taggable')->withPivot(['user_id', 'date'])->withTimestamps();
    }
    public function wf_records()
    {
        return $this->morphMany('Muserpol\Models\Workflow\WorkflowRecord', 'recordable');
    }
    public function observations()
    {
        return $this->morphToMany('Muserpol\Models\ObservationType', 'observable')->whereNull('observables.deleted_at')->withPivot(['user_id', 'date', 'message', 'enabled', 'deleted_at'])->withTimestamps();
    }
    // public function procedure_modality()
    // {
    //     return $this->belongsTo('Muserpol\Models\ProcedureModality');
    // }
    public function getComplementaryFactor()
    {
        return intval($this->complementary_factor);
    }
    public function getTotalSemester()
    {
        return $this->difference * 6;
    }
    public function getOnlyTotalEcoCom()
    {
        return $this->total + $this->discount_types()->sum('amount');
    }
    public function qualify()
    {
        if ($this->eco_com_state->eco_com_state_type_id == 1 || $this->eco_com_state->eco_com_state_type_id == 6) {
            $eco_com_state = $this->eco_com_state;
            return response()->json([
                'status' => 'error',
                'msg' => 'Error',
                'errors' => ['No se puede realizar la amortización porque el trámite ' . $this->code . ' se encuentra en estado de ' . $eco_com_state->name],
            ], 422);
        }
        // // requalify
        // if ($economic_complement->total > 0 && ( $economic_complement->eco_com_state_id == 1 || $economic_complement->eco_com_state_id == 2 || $economic_complement->eco_com_state_id == 3 || $economic_complement->eco_com_state_id == 17 || $economic_complement->eco_com_state_id == 18 || $economic_complement->eco_com_state_id == 15 ) ) {
        //     $economic_complement->recalification_date = Carbon::now();
        //     $temp_eco_com = (array)json_decode($economic_complement);
        //     $old_eco_com = [];
        //     foreach ($temp_eco_com as $key => $value) {
        //         if ($key != 'old_eco_com') {
        //             $old_eco_com[$key] = $value;
        //         }
        //     }
        //     if (!$economic_complement->old_eco_com) {
        //         $economic_complement->old_eco_com=json_encode($old_eco_com);
        //     }
        //     $economic_complement->save();
        // }
        // // /requalify
        $eco_com_procedure = $this->eco_com_procedure;
        $eco_com_rent = EcoComRent::whereYear('year', '=', Carbon::parse($eco_com_procedure->year)->year)
            ->where('semester', '=', $eco_com_procedure->semester)
            ->get();
        $base_wage = BaseWage::whereYear('month_year', '=', Carbon::parse($eco_com_procedure->year)->year)->get();
        $complementary_factor = ComplementaryFactor::whereYear('year', '=', Carbon::parse($eco_com_procedure->year)->year)
            ->where('semester', '=', $eco_com_procedure->semester)
            ->get();
        if ($eco_com_rent->count() == 0) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Error',
                'errors' => ['Verifique que existan los promedio para la gestion ' . $eco_com_procedure->fullName()],
            ], 422);
        }
        if ($base_wage->count() == 0) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Error',
                'errors' => ['Verifique que si existen los sueldos para la gestion ' . $eco_com_procedure->fullName()],
            ], 422);
        }
        if ($complementary_factor->count() == 0) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Error',
                'errors' => ['Verifique los datos de los factores de complementación de la gestion ' . $eco_com_procedure->fullName()],
            ], 422);
        }
        $indicator = $eco_com_procedure->indicator;
        /**
         ** updating modality with components
         */
        //APS
        if ($this->affiliate->pension_entity->type == 'APS') {
            $component = 0;
            if ($this->aps_total_fsa > 0) {
                $component++;
            }
            if ($this->aps_total_cc  > 0) {
                $component++;
            }
            if ($this->aps_total_fs > 0) {
                $component++;
            }
            //vejez
            if ($this->isOldAge()) {
                if ($component == 1 && $this->total_rent >= $indicator) {
                    $this->eco_com_modality_id = 4;
                } elseif ($component == 1 && $this->total_rent < $indicator) {
                    $this->eco_com_modality_id = 6;
                } elseif ($component > 1 && $this->total_rent < $indicator) {
                    $this->eco_com_modality_id = 8;
                } else {
                    $this->eco_com_modality_id = 1;
                }
            }
            //Viudedad
            if ($this->isWidowhood()) {
                if ($component == 1 && $this->total_rent >= $indicator) {
                    $this->eco_com_modality_id = 5;
                } elseif ($component == 1 && $this->total_rent < $indicator) {
                    $this->eco_com_modality_id = 7;
                } elseif ($component > 1 && $this->total_rent < $indicator) {
                    $this->eco_com_modality_id = 9;
                } else {
                    $this->eco_com_modality_id = 2;
                }
            }
            //orfandad
            if ($this->isOrphanhood()) {
                if ($component == 1 && $this->total_rent >= $indicator) {
                    $this->eco_com_modality_id = 10;
                } elseif ($component == 1 && $this->total_rent < $indicator) {
                    $this->eco_com_modality_id = 11;
                } elseif ($component > 1 && $this->total_rent < $indicator) {
                    $this->eco_com_modality_id = 12;
                } else {
                    $this->eco_com_modality_id = 3;
                }
            }
        } else {
            //Senasir
            if ($this->isOldAge() && $this->total_rent < $indicator) {
                //vejez
                $this->eco_com_modality_id = 8;
            } elseif ($this->isWidowhood() && $this->total_rent < $indicator) {
                //Viudedad
                $this->eco_com_modality_id = 9;
            } elseif ($this->isOrphanhood() && $this->total_rent < $indicator) {
                //Orfandad
                $this->eco_com_modality_id = 12;
            } else {
                // $this->eco_com_modality_id = $this->eco_com_modality->procedure_modality_id;
            }
        }
        // $economic_complement->aps_disability = floatval(str_replace(',', '', $aps_disability));
        // $economic_complement->total_rent = $total_rent;
        $this->save();
        /**
         ** /updating modality with components
         */
        $this->total_rent_calc = $this->total_rent;

        /**
         ** averages
         ** actualizacion de las rentas netas
         */

        $total_rent = $this->total_rent;

        if ($this->eco_com_modality_id > 3 && $this->eco_com_modality_id < 10) { // no se esta tomando en cuenta a orfandad
            $eco_com_rent = EcoComRent::where('degree_id', '=', $this->degree_id)
                ->where('procedure_modality_id', '=', $this->eco_com_modality->procedure_modality_id)
                ->whereYear('year', '=', Carbon::parse($eco_com_procedure->year)->year)
                ->where('semester', '=', $eco_com_procedure->semester)
                ->first();
            // EXCEPTION WHEN TOTAL_RENT > AVERAGE IN MODALITIES 4 AND 5
            if ($this->total_rent > $eco_com_rent->average and ($this->eco_com_modality_id == 4 || $this->eco_com_modality_id == 5 || $this->eco_com_modality_id == 10)) { // se verifica si el total rent es mayor al promedio y que sea de un solo componente
                $total_rent = $this->total_rent;
                $this->total_rent_calc = $this->total_rent;
            } else {
                $total_rent = $eco_com_rent->average;
                $this->total_rent_calc = $eco_com_rent->average;
            }
        } else if ($this->eco_com_modality_id >= 10) { // solo orfandad
            $eco_com_rent = EcoComRent::where('degree_id', '=', $this->degree_id)
                //!! porque se toma en cuenta vejez?????
                ->where('procedure_modality_id', '=', 1)
                ->whereYear('year', '=', Carbon::parse($eco_com_procedure->year)->year)
                ->where('semester', '=', $eco_com_procedure->semester)
                ->first();
            if ($this->total_rent > $eco_com_rent->average and $this->eco_com_modality_id == 10) {
                $total_rent = $this->total_rent;
                $this->total_rent_calc = $this->total_rent;
            } else {
                $total_rent = $eco_com_rent->average;
                $this->total_rent_calc = $eco_com_rent->average;
            }
        }
        /**
         ** /averages
         */

        $base_wage = BaseWage::where('degree_id', $this->degree_id)->whereYear('month_year', '=', Carbon::parse($eco_com_procedure->year)->year)->first();

        //para el caso de las viudas 80%
        if ($this->isWidowhood()) {
            $base_wage_amount = $base_wage->amount * (80 / 100);
            $salary_reference = $base_wage_amount;
            $seniority = $this->category->percentage * $base_wage_amount;
        } else {
            $salary_reference = $base_wage->amount;
            $seniority = $this->category->percentage * $base_wage->amount;
        }

        $this->seniority = $seniority;
        $salary_quotable = $salary_reference + $seniority;
        $this->salary_quotable = $salary_quotable;
        $difference = $salary_quotable - $total_rent;
        $this->difference = $difference;
        $months_of_payment = 6;
        $total_amount_semester = $difference * $months_of_payment;
        $this->total_amount_semester = $total_amount_semester;
        // $economic_complement->sub_total_rent = floatval(str_replace(',', '', $sub_total_rent));
        // $economic_complement->reimbursement = floatval(str_replace(',', '', $reimbursement));
        // $economic_complement->dignity_pension = floatval(str_replace(',', '', $dignity_pension));
        $complementary_factor = ComplementaryFactor::where('hierarchy_id', '=', $base_wage->degree->hierarchy->id)
            ->whereYear('year', '=', Carbon::parse($eco_com_procedure->year)->year)
            ->where('semester', '=', $eco_com_procedure->semester)
            ->first();
        $this->complementary_factor_id = $complementary_factor->id;
        if ($this->isWidowhood()) {
            //viudedad
            $complementary_factor = $complementary_factor->widowhood;
        } else {
            //vejez
            $complementary_factor = $complementary_factor->old_age;
        }
        $this->complementary_factor = $complementary_factor;
        $total = $total_amount_semester * round(floatval($complementary_factor) / 100, 2);

        //RESTANDO PRESTAMOS, CONTABILIDAD Y REPOSICION AL TOTAL PORCONCEPTO DE DEUDA
        $total  = $total - $this->discount_types()->sum('amount');
        // }
        // if ($this->amount_loan > 0) {
        // }
        // if ($this->amount_accounting > 0) {
        //     $total  = $total - $this->amount_accounting;
        // }
        // if ($this->amount_replacement > 0) {
        //     $total  = $total - $this->amount_replacement;
        // }
        // if ($this->amount_credit > 0) {
        //     $total  = $total - $this->amount_credit;
        // }
        $this->total = $total;
        $this->base_wage_id = $base_wage->id;
        $this->salary_reference = $salary_reference;
        // if ($this->old_eco_com) {
        //     $old_total = json_decode($this->old_eco_com)->total;
        //     $this->total_repay =  floatval($total) - (floatval($old_total) + (floatval(json_decode($this->old_eco_com)->amount_loan) + floatval(json_decode($this->old_eco_com)->amount_replacement) + floatval(json_decode($this->old_eco_com)->amount_accounting) + floatval(json_decode($this->old_eco_com)->amount_credit)));
        //     $this->user_id = Auth::user()->id;
        //     $this->state = 'Edited';
        //     if (WorkflowState::where('role_id', '=', Util::getRol()->id)->first()) {
        //         $this->wf_current_state_id = WorkflowState::where('role_id', '=', Util::getRol()->id)->first()->id;
        //     } else {
        //         return redirect('economic_complement/' . $this->id)
        //             ->withErrors('Ocurrió un error verifique que los datos estén correctos.')
        //             ->withInput();
        //     }
        // }
        $this->save();
        if ($this->total_rent > $this->salary_quotable) {
            $this->eco_com_state_id = 12;
        } else {
            if ($this->eco_com_state_id == 12) {
                $this->eco_com_state_id = 16;
            }
        }
        $this->save();
        return response()->json([
            'status' => 'success',
        ], 200);
    }
    public function isOldAge()
    {
        return $this->eco_com_modality->procedure_modality_id == 29;
    }
    public function isWidowhood()
    {
        return $this->eco_com_modality->procedure_modality_id == 30;
    }
    public function isOrphanhood()
    {
        return $this->eco_com_modality->procedure_modality_id == 31;
    }
    public function hasLegalGuardian()
    {
        return $this->eco_com_legal_guardian;
    }
    public function hasDiscountTypes()
    {
        return $this->discount_types->count() > 0;
    }
    public function hasDiscountType($id)
    {
        return !!$this->discount_types()->where('discount_type_id', $id)->first();
    }
    public function eco_com_reception_type()
    {
        return $this->belongsTo(EcoComReceptionType::class);
    }
    public function hasObservationType($id)
    {
        return !!$this->observations()->where('observation_type_id', '=', $id)->first();
    }
    public function scopeHasEcoComState($query, ...$ids)
    {
        $collect = collect([]);
        foreach ($ids as $i) {
            $collect->push($i);
        }
        return $query->leftJoin('eco_com_states', 'economic_complements.eco_com_state_id', '=', 'eco_com_states.id')
            ->leftJoin('eco_com_state_types', 'eco_com_state_types.id', '=', 'eco_com_states.eco_com_state_type_id')
            ->whereIn('eco_com_state_types.id', $collect);
    }
    public function scopeNotHasEcoComState($query, ...$ids)
    {
        $collect = collect([]);
        foreach ($ids as $i) {
            $collect->push($i);
        }
        return $query->leftJoin('eco_com_states', 'economic_complements.eco_com_state_id', '=', 'eco_com_states.id')
            ->leftJoin('eco_com_state_types', 'eco_com_state_types.id', '=', 'eco_com_states.eco_com_state_type_id')
            ->whereNotIn('eco_com_state_types.id', $collect);
    }
    public function scopeEcoComProcedure($query, ...$ids)
    {
        $collect = collect([]);
        foreach ($ids as $i) {
            $collect->push($i);
        }
        return $query->whereIn('eco_com_procedure_id', $collect);
    }
    public function scopeWorkflow($query, ...$ids)
    {
        $collect = collect([]);
        foreach ($ids as $i) {
            $collect->push($i);
        }
        return $query->whereIn('workflow_id', $collect);
    }
    public function scopeWfState($query, ...$ids)
    {
        $collect = collect([]);
        foreach ($ids as $i) {
            $collect->push($i);
        }
        return $query->whereIn('wf_current_state_id', $collect);
    }
    public function scopeInboxState($query, ...$ids)
    {
        $collect = collect([]);
        foreach ($ids as $i) {
            $collect->push($i);
        }
        return $query->whereIn('inbox_state', $collect);
    }
    public function scopeCity($query)
    {
        return $query->leftJoin('cities as eco_com_city', 'eco_com_city.id', '=', 'economic_complements.city_id');
    }
    public function scopeBeneficiary($query)
    {
        return $query->leftJoin('eco_com_applicants as beneficiary', 'beneficiary.economic_complement_id', '=', 'economic_complements.id')
            ->leftJoin('cities as beneficiary_city', 'beneficiary_city.id', '=', 'beneficiary.city_identity_card_id');
    }
    public function scopeHasObservationTypeAndCorrect($query, ...$ids)
    {
        $collect = collect([]);
        foreach ($ids as $i) {
            $collect->push($i);
        }
        $model = 'economic_complements';
        $table = 'economic_complements';
        return $query->whereExists(function ($query) use ($model, $table, $collect) {
            $query->select(DB::raw(1))
                ->from('observables')
                ->where('observables.observable_id', '=', $table . '.id')
                ->where('observables.observable_type', '=', $model)
                ->where('observables.enabled', false)
                ->whereIn('observables.observation_type_id', $collect);
        });
        return $query->leftJoin('observables', function ($join) use ($model, $table, $collect) {
            $join->on('observables.observable_id', '=', $table . '.id')
                ->where('observables.observable_type', '=', $model)
                ->where('observables.enabled', false)
                ->whereIn('observables.observation_type_id', $collect);
        });
        return $query->leftJoin('observables', 'observables.observable_id', '=', $this->id)
            ->where('observables.observable_type', 'economic_complements')
            ->where('observables.enabled', true);
        return $this->observations()->whereIn('id', collect(func_get_args($ids)))->where('enabled', true)->get()->count();
        // return collect(func_get_args($ids))->includes($this->)
    }
    public function hasObservationTypeAndNotCorrect(...$ids)
    {
        return $this->observations()->whereIn('id', collect(func_get_args($ids)))->where('enabled', false)->get()->count();
        // return collect(func_get_args($ids))->includes($this->)
    }
    // public function hasObservationTypeAndCorrect(...$ids)
    // {
    //     return !! $this->observations()->whereIn('id', collect(func_get_args($ids)))->where('enabled', true)->get()->count();
    //     // return collect(func_get_args($ids))->includes($this->)
    // }
    public function scopeInfo($query)
    {
        return $query->leftJoin('cities as eco_com_city', 'eco_com_city.id', '=', 'economic_complements.city_id')
        ->leftJoin('degrees as eco_com_degree', 'economic_complements.degree_id', '=', 'eco_com_degree.id')
        ->leftJoin('categories as eco_com_category', 'economic_complements.category_id', '=', 'eco_com_category.id')->leftJoin('eco_com_modalities', 'economic_complements.eco_com_modality_id', '=', 'eco_com_modalities.id')
        ->leftJoin('procedure_modalities', 'eco_com_modalities.procedure_modality_id', '=', 'procedure_modalities.id')
        ->leftJoin('eco_com_reception_types', 'economic_complements.eco_com_reception_type_id', '=', 'eco_com_reception_types.id');
    }
    public function scopeOrder($query)
    {
        return $query->orderBy(DB::raw("regexp_replace(split_part(economic_complements.code, '/',3),'\D','','g')::integer"))
        ->orderBy(DB::raw("split_part(economic_complements.code, '/',2)"))
        ->orderBy(DB::raw("split_part(economic_complements.code, '/',1)::integer"));
    }
    public function scopeObservationType($query)
    {
        return $query->leftJoin('observables', 'economic_complements.id', 'observables.observable_id')->where('observables.observable_type', 'like','economic_complements')->leftJoin('observation_types', 'observables.observation_type_id', '=', 'observation_types.id');
    }
    public function getType()
    {
        $type = 'Derechohabiente';
        if ($this->isOldAge()) {
            $type = 'Titular';
        }
        return $type;
    }
    public function getTypeModality()
    {
        if ($this->isOldAge()) {
            $type = 'Vejez';
        }
        if ($this->isWidowhood()) {
            $type = 'Viuda(o)';
        }
        if ($this->isOrphanhood()) {
            $type = 'Huérfano Absoluto';
        }
        return $type;
    }
    public function notes()
    {
        return $this->morphMany('Muserpol\Models\Note', 'annotable');
    }
    public function procedure_records()
    {
        return $this->morphMany('Muserpol\Models\ProcedureRecord', 'recordable');
    }
    public static function basic_info_colums()
    {
        return "row_number() OVER () AS NRO, economic_complements.code as eco_com_code, beneficiary.identity_card as ci, beneficiary_city.first_shortened as ext, beneficiary.first_name as primer_nombre, beneficiary.second_name as segundo_nombre, beneficiary.last_name as apellido_paterno, beneficiary.mothers_last_name as apellido_materno, beneficiary.surname_husband as apellido_de_casado, beneficiary.birth_date as fecha_nac, eco_com_city.name as regional, eco_com_degree.name as grado, eco_com_category.name as categoria, procedure_modalities.name as tipo_de_prestacion, eco_com_reception_types.name";
    }
    // public static function basic_info_applicants()
    // {
    //     return "eco_com_applicants.identity_card as ci, city_applicant_identity_card.first_shortened as ext, eco_com_applicants.first_name as primer_nombre, eco_com_applicants.second_name as segundo_nombre, eco_com_applicants.last_name as apellido_paterno, eco_com_applicants.mothers_last_name as apellido_materno, eco_com_applicants.surname_husband as apellido_de_casado, eco_com_applicants.birth_date as fecha_nac";
    // }
    public static function basic_info_complements()
    {
        return "economic_complements.reception_date as fecha_de_recepcion, economic_complements.reimbursement as reintegro, economic_complements.dignity_pension as renta_dignidad, economic_complements.total_rent as renta_neto, economic_complements.total_rent_calc as neto, economic_complements.salary_reference as salario_referencial, economic_complements.seniority as antiguedad, economic_complements.difference as diferencia, economic_complements.complementary_factor as factor_complementario, economic_complements.total as total_complemento";
    }
    public static function basic_info_affiliates()
    {
        return "affiliates.id as nup, affiliates.identity_card, affiliate_city.first_shortened, affiliates.first_name as primer_nombre_causahabiente, affiliates.second_name as segundo_nombre_causahabiente, affiliates.last_name as ap_paterno_causahabiente, affiliates.mothers_last_name as ap_materno_causahabiente, affiliates.surname_husband as ape_casada_causahabiente, affiliates.birth_date as fecha_nacimiento, affiliates.nua as codigo_nua_cua";
    }
    public static function basic_info_legal_guardian()
    {
        return "eco_com_legal_guardians.first_name as primer_nombre_apoderado, eco_com_legal_guardians.second_name as segundo_nombre_apoderado, eco_com_legal_guardians.last_name as ap_paterno_apoderado, eco_com_legal_guardians.mothers_last_name as ap_materno_apoderado, eco_com_legal_guardians.surname_husband as ape_casada_apoderado, eco_com_legal_guardians.identity_card as ci_apoderado, city_legal_guardian_identity_card.first_shortened as ci_exp_apoderado, eco_com_legal_guardian_types.name as eco_com_legal_guardian_type_name";
    }
    public static function basic_info_user()
    {
        return "user_created.username as usuario_que_creo, user_current.username as usuario_actual";
    }

    public function scopeApplicantinfo($query)
    {
        return $query->leftJoin('eco_com_applicants', 'economic_complements.id', '=', 'eco_com_applicants.economic_complement_id')
            ->leftJoin('cities as city_applicant_identity_card', 'eco_com_applicants.city_identity_card_id', '=', 'city_applicant_identity_card.id');
    }
    public function scopeLegalguardianInfo($query)
    {
        return $query->leftJoin('eco_com_legal_guardians', 'economic_complements.id', '=', 'eco_com_legal_guardians.economic_complement_id')
            ->leftJoin('cities as city_legal_guardian_identity_card', 'eco_com_legal_guardians.city_identity_card_id', '=', 'city_legal_guardian_identity_card.id')
            ->leftJoin('eco_com_legal_guardian_types', 'eco_com_legal_guardians.eco_com_legal_guardian_type_id', '=', 'eco_com_legal_guardian_types.id');
    }
    public function scopeAffiliateinfo($query)
    {
        return $query->leftJoin('affiliates', 'economic_complements.affiliate_id', '=', 'affiliates.id')
            ->leftJoin('cities as affiliate_city', 'affiliates.city_identity_card_id', '=', 'affiliate_city.id')
            ->leftJoin('pension_entities', 'affiliates.pension_entity_id', '=', 'pension_entities.id');
    }
    public function scopeEcocomstates($query)
    {
        return $query->leftJoin('eco_com_states', 'economic_complements.eco_com_state_id', '=', 'eco_com_states.id')
            ->leftJoin('eco_com_state_types', 'eco_com_states.eco_com_state_type_id', '=', 'eco_com_state_types.id');
    }
    public function scopeWfstates($query)
    {
        return $query->leftJoin('wf_states', 'economic_complements.wf_current_state_id', '=', 'wf_states.id');
    }
}
