<?php

namespace Muserpol\Http\Controllers;

use Illuminate\Http\Request;
use Muserpol\Models\EconomicComplement\EconomicComplement;
use Illuminate\Support\Facades\Auth;
use Muserpol\Helpers\Util;
use Muserpol\Models\City;
use Muserpol\Models\Spouse;
use Muserpol\Models\Note;
use Muserpol\Models\ProcedureRequirement;
use Muserpol\Models\EconomicComplement\EcoComLegalGuardian;
use Muserpol\Models\Affiliate;
use Muserpol\Models\Address;
use Muserpol\Models\EconomicComplement\EcoComProcedure;
use Muserpol\Models\ProcedureState;
use Muserpol\Models\PensionEntity;
use Muserpol\User;
use Muserpol\Models\ProcedureType;
use Muserpol\Models\ProcedureModality;
use Muserpol\Models\Degree;
use Muserpol\Models\Category;
use Log;
use Muserpol\Models\AffiliateState;
use Muserpol\Models\EconomicComplement\EcoComBeneficiary;
use Carbon\Carbon;
use DB;
use Muserpol\Models\EconomicComplement\EcoComSubmittedDocument;
use Muserpol\Models\Role;
use Muserpol\Models\Workflow\WorkflowState;
use Muserpol\Models\EconomicComplement\EcoComRent;
use Muserpol\Models\ObservationType;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use Muserpol\Models\EconomicComplement\EcoComState;
use Illuminate\Validation\ValidationException;
use Muserpol\Models\DiscountType;
use Muserpol\Models\ComplementaryFactor;
use Muserpol\Models\EconomicComplement\EcoComLegalGuardianType;
use Muserpol\Helpers\ID;
use Muserpol\Models\EconomicComplement\EcoComReceptionType;
use Muserpol\Models\EconomicComplement\EconomicComplementRecord;

class EconomicComplementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $modalities =  ProcedureModality::all()->pluck('name');
        // $cities =  City::all()->pluck('name');
        // $wf_states =  WorkflowState::where('module_id', 3)->get()->pluck('first_shortened');
        $data = [
            // 'modalities' => $modalities,
            // 'cities' => $cities,
            // 'wf_states' => $wf_states,
        ];
        return view('eco_com.index', $data);
    }
    public function getAllEcoCom(DataTables $datatables)
    {
        $eco_coms = EconomicComplement::select(
            DB::RAW("
            economic_complements.id as id,
            economic_complements.code,
            economic_complements.reception_date,
            city_eco_com.name as eco_com_city_name,
            concat_ws(' ',extract(year from eco_com_procedures.year), eco_com_procedures.semester) as eco_com_procedure_year,
            procedure_modalities.name as procedure_modality,
            CASE WHEN economic_complements.inbox_state THEN 'Validado' ELSE 'Pendiente' END as eco_com_inbox_state,
            wf_states.first_shortened as wf_state_name,
            pension_entities.name as pension_entity_name,
            economic_complements.total,
            affiliates.identity_card as affiliate_identity_card,
            trim(regexp_replace(concat_ws(' ', affiliates.first_name, affiliates.second_name, affiliates.last_name, affiliates.mothers_last_name, affiliates.surname_husband), '\s+', ' ', 'g'))  as affiliate_full_name,
            eco_com_applicants.identity_card as eco_com_beneficiary_identity_card,
            trim(regexp_replace(concat_ws(' ', eco_com_applicants.first_name, eco_com_applicants.second_name, eco_com_applicants.last_name, eco_com_applicants.mothers_last_name, eco_com_applicants.surname_husband), '\s+', ' ', 'g'))  as eco_com_beneficiary_full_name
            "))
            ->leftJoin('cities as city_eco_com', 'economic_complements.city_id', '=', 'city_eco_com.id' )
            ->leftJoin('eco_com_procedures', 'economic_complements.eco_com_procedure_id', '=', 'eco_com_procedures.id' )
            ->leftJoin('eco_com_modalities', 'economic_complements.eco_com_modality_id', '=', 'eco_com_modalities.id' )
            ->leftJoin('procedure_modalities', 'eco_com_modalities.procedure_modality_id', '=', 'procedure_modalities.id' )
            ->leftJoin('wf_states', 'economic_complements.wf_current_state_id', '=', 'wf_states.id' )
            ->leftJoin('affiliates', 'economic_complements.affiliate_id', '=', 'affiliates.id')
            ->leftJoin('pension_entities', 'affiliates.pension_entity_id', '=', 'pension_entities.id')
            ->leftJoin('eco_com_applicants', 'eco_com_applicants.economic_complement_id', '=', 'economic_complements.id')
            ->where('economic_complements.code', 'not like', '%A')
            ->orderByDesc(DB::raw("split_part(economic_complements.code, '/',3)::integer desc, split_part(economic_complements.code, '/',2), split_part(economic_complements.code, '/',1)::integer"));
            return $datatables->eloquent($eco_coms)
                ->filterColumn('code', function($query, $keyword) {
                    $sql = "economic_complements.code ilike ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('eco_com_beneficiary_identity_card', function($query, $keyword) {
                    $sql = "eco_com_applicants.identity_card ilike ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('eco_com_beneficiary_full_name', function($query, $keyword) {
                    $sql = "trim(regexp_replace(concat_ws(' ', eco_com_applicants.first_name, eco_com_applicants.second_name, eco_com_applicants.last_name, eco_com_applicants.mothers_last_name, eco_com_applicants.surname_husband), '\s+', ' ', 'g')) ilike ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('affiliate_identity_card', function($query, $keyword) {
                    $sql = "affiliates.identity_card ilike ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('affiliate_full_name', function($query, $keyword) {
                    $sql = "trim(regexp_replace(concat_ws(' ', affiliates.first_name, affiliates.second_name, affiliates.last_name, affiliates.mothers_last_name, affiliates.surname_husband), '\s+', ' ', 'g')) ilike ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('eco_com_city_name', function($query, $keyword) {
                    $sql = "city_eco_com.name ilike ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('eco_com_procedure_year', function($query, $keyword) {
                    $sql = "concat_ws(' ',extract(year from eco_com_procedures.year), eco_com_procedures.semester) ilike ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('procedure_modality', function($query, $keyword) {
                    $sql = "procedure_modalities.name ilike ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('pension_entity_name', function($query, $keyword) {
                    $sql = "pension_entities.name ilike ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('wf_state_name', function($query, $keyword) {
                    $sql = "wf_states.first_shortened ilike ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('eco_com_inbox_state', function($query, $keyword) {
                    $sql = "CASE WHEN economic_complements.inbox_state THEN 'Validado' ELSE 'Pendiente' END ilike ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->addColumn('action', function ($eco_com) {
                    return "<a href='/eco_com/" . $eco_com->id . "' class='btn btn-default'><i class='fa fa-eye'></i></a>";
                })
                ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($affiliate_id, $eco_com_procedure_id)
    {
        $affiliate = Affiliate::with(['pension_entity'])->find($affiliate_id);
        $has_economic_complement = $affiliate->hasEconomicComplementWithProcedure($eco_com_procedure_id);
        if ($has_economic_complement) {
            return redirect()->action('EconomicComplementController@show', ['id' => $affiliate->economic_complements()->where('eco_com_procedure_id', $eco_com_procedure_id)->first()->id]);
        }
        if ($affiliate->observations()->where('enabled', false)->whereIn('id', ObservationType::where('description', 'like', 'Denegado')->get()->pluck('id'))->count() > 0) {
            return redirect()->action('AffiliateController@show', ['id' => $affiliate->id]);
        }
        $cities = City::all();
        $eco_com_beneficiary = new EcoComBeneficiary();
        $eco_com_beneficiary->phone_number = explode(',', $eco_com_beneficiary->phone_number);
        $eco_com_beneficiary->cell_phone_number = explode(',', $eco_com_beneficiary->cell_phone_number);
        if (!sizeOf($eco_com_beneficiary->address) > 0) {
            $eco_com_beneficiary->address[] = array('zone' => null, 'street' => null, 'number_address' => null, 'city_address_id' => null);
        }
        $requirements = ProcedureRequirement::select('procedure_requirements.id', 'procedure_documents.name as document', 'number', 'procedure_modality_id as modality_id')
            ->leftJoin('procedure_documents', 'procedure_requirements.procedure_document_id', '=', 'procedure_documents.id')
            ->orderBy('procedure_requirements.procedure_modality_id', 'ASC')
            ->orderBy('procedure_requirements.number', 'ASC')
            ->get();
        $user = Auth::user();
        $last_eco_com = $affiliate->economic_complements()->orderByDesc('id')->get()->first();
        if ($last_eco_com) {
            $last_eco_com->procedure_modality_id = $last_eco_com->eco_com_modality->procedure_modality_id;
        } else {
            $last_eco_com = new EconomicComplement();
        }
        $modalities = ProcedureModality::where('procedure_type_id', ID::procedureType()->eco_com)->get();
        $pension_entities = PensionEntity::all();
        $degrees = Degree::all();
        $categories = Category::all();
        $eco_com_legal_guardian_types = EcoComLegalGuardianType::all();
        $eco_com_reception_types = EcoComReceptionType::all();
        $data = [
            'affiliate' => $affiliate,
            'cities' => $cities,
            'eco_com_beneficiary' => $eco_com_beneficiary,
            'requirements' => $requirements,
            'user' => $user,
            'last_eco_com' => $last_eco_com,
            'eco_com_procedure_id' => $eco_com_procedure_id,
            'modalities' => $modalities,
            'pension_entities' => $pension_entities,
            'degrees' => $degrees,
            'categories' => $categories,
            'eco_com_legal_guardian_types' => $eco_com_legal_guardian_types,
            'eco_com_reception_types' => $eco_com_reception_types,
        ];

        return view('eco_com.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        logger($request->all());
        try {
            $this->authorize('create', new EconomicComplement());
        } catch (AuthorizationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => ['No tiene permisos para crear el Trámite'],
            ], 403);
        }
        $eco_com_procedure = EcoComProcedure::find($request->eco_com_procedure_id);
        if (!$eco_com_procedure) {
            abort(500, "ERROR");
        }
        $affiliate = Affiliate::find($request->affiliate_id);
        $has_economic_complement = $affiliate->hasEconomicComplementWithProcedure($request->eco_com_procedure_id);
        if ($has_economic_complement) {
            return redirect()->action('EconomicComplementController@show', ['id' => $affiliate->economic_complements()->where('eco_com_procedure_id', $request->eco_com_procedure_id)->first()->id]);
        }
        /**
         ** update affiliate police info
         */
        if($request->reception_type == ID::ecoCom()->inclusion){
            // $affiliate->category_id = $request->affiliate_category_id;
            $service_year = $request->affiliate_service_years;
            $service_month = $request->affiliate_service_months;
            if ($service_year > 0 || $service_month > 0) {
                if ($service_month > 0) {
                    $service_year++;
                }
                $category = Category::where('from', '<=', $service_year)
                    ->where('to', '>=', $service_year)
                    ->first();
                if ($category) {
                    $affiliate->category_id = $category->id;
                    $affiliate->service_years = $request->affiliate_service_years;
                    $affiliate->service_months = $request->affiliate_service_months;
                }
            }
            $affiliate->degree_id = $request->affiliate_degree_id;
            $affiliate->pension_entity_id = $request->pension_entity_id;
            $affiliate->date_derelict = Util::verifyMonthYearDate($request->affiliate_date_derelict) ? Util::parseMonthYearDate($request->affiliate_date_derelict) : $request->affiliate_date_derelict;
            $affiliate->save();
        }
        /**
         ** create Economic complement 
         */
        $economic_complement = new EconomicComplement();
        $economic_complement->user_id = Auth::user()->id;
        $economic_complement->affiliate_id = $affiliate->id;
        $economic_complement->eco_com_modality_id = ProcedureModality::find($request->modality_id)->eco_com_modalities()->where('name', 'like', '%normal%')->first()->id;
        $economic_complement->eco_com_state_id = ID::ecoComState()->in_process;
        $economic_complement->eco_com_procedure_id = $request->eco_com_procedure_id;
        $economic_complement->workflow_id = ID::workflow()->eco_com_normal;
        $wf_state = WorkflowState::where('role_id', Util::getRol()->id)->whereIn('sequence_number', [0,1])->first();
        if(!$wf_state){
            Log::info("error al crear el trámite");
            return;
        }
        $economic_complement->wf_current_state_id = $wf_state->id;
        $economic_complement->city_id = $request->city_id;
        $economic_complement->degree_id = $affiliate->degree->id;
        $economic_complement->category_id = $affiliate->category->id;
        $economic_complement->year = Carbon::parse($eco_com_procedure->year)->year . '-01-01'; // !! TODO Borrar columna
        $economic_complement->semester = $eco_com_procedure->semester; // !! TODO Borrar columna
        $economic_complement->code = Util::getLastCodeEconomicComplement($request->eco_com_procedure_id);
        $economic_complement->reception_date = now();
        $economic_complement->inbox_state = true;
        $economic_complement->state = 'Received'; // !! TODO Borrar columna
        $economic_complement->eco_com_reception_type_id = $request->reception_type;

        if ($request->pension_entity_id == ID::pensionEntity()->senasir) {
            $economic_complement->sub_total_rent = Util::parseMoney($request->sub_total_rent);
            $economic_complement->reimbursement = Util::parseMoney($request->reimbursement);
            $economic_complement->dignity_pension = Util::parseMoney($request->dignity_pension);
            $economic_complement->aps_disability = Util::parseMoney($request->aps_disability);
            $economic_complement->aps_total_fsa = null;
            $economic_complement->aps_total_cc = null;
            $economic_complement->aps_total_fs = null;
        } else {
            $economic_complement->aps_total_fsa = Util::parseMoney($request->aps_total_fsa);
            $economic_complement->aps_total_cc = Util::parseMoney($request->aps_total_cc);
            $economic_complement->aps_total_fs = Util::parseMoney($request->aps_total_fs);
            $economic_complement->aps_disability = Util::parseMoney($request->aps_disability);
            $economic_complement->sub_total_rent = null;
            $economic_complement->reimbursement = null;
            $economic_complement->dignity_pension = null;
        }
        $economic_complement->save();
        /**
         ** has affiliate observation
         */
        $observations = $affiliate->observations()->where('type', 'AT')->get();
        foreach ($observations as $o) {
            $economic_complement->observations()->save($o, [
                'user_id' => $o->pivot->user_id,
                'date' => $o->pivot->date,
                'message' => $o->pivot->message,
                'enabled' => false
            ]);
            // $record = new EconomicComplementRecord();
            // $record->user_id = Auth::user()->id;
            // $record->economic_complement_id = $economic_complement->id;
            // $record->message = "El usuario " . User::find($o->user_id)->username  . " creó la observación " . $o->name . ".";
            // $record->save();
        }
        /**
         ** verify observation id = 6
         */
        if($request->reception_type == ID::ecoCom()->inclusion){

            $number_docs = ProcedureModality::find($request->modality_id)->procedure_requirements->pluck('number')->unique()->sort();
            if ($number_docs->contains(0)) {
                $number_docs = $number_docs->slice(1);
            }
            $count = 0;
            foreach ($request->all() as $key => $value) {
                if (strpos($key, 'document') !== false  && $value == 'checked') {
                    $count++;
                }
            }
            if ($count != $number_docs->count()) {
                $economic_complement->observations()->save(ObservationType::find(6), [
                    'user_id' => auth()->id(),
                    'date' => now(),
                    'message' => 'Documentación incompleta (Observación adicionada automáticamente)',
                    'enabled' => false
                ]);
            }
        }
        /**
         ** Save legal guardian
         */
        if ($request->has_legal_guardian == 'on') {
            $legal_guardian = new EcoComLegalGuardian();
            $legal_guardian->economic_complement_id = $economic_complement->id;
            $legal_guardian->eco_com_legal_guardian_type_id = $request->legal_guardian_type_id;
            $legal_guardian->city_identity_card_id = $request->legal_guardian_city_identity_card_id;
            $legal_guardian->identity_card = $request->legal_guardian_identity_card;
            $legal_guardian->last_name = $request->legal_guardian_last_name;
            $legal_guardian->mothers_last_name = $request->legal_guardian_mothers_last_name;
            $legal_guardian->first_name = $request->legal_guardian_first_name;
            $legal_guardian->second_name = $request->legal_guardian_second_name;
            $legal_guardian->surname_husband = $request->legal_guardian_surname_husband;
            $legal_guardian->phone_number = implode(',', $request->legal_guardian_phone_number ?? []);
            $legal_guardian->cell_phone_number = implode(',', $request->legal_guardian_cell_phone_number ?? []);
            $legal_guardian->due_date = Util::verifyBarDate($request->legal_guardian_due_date) ? Util::parseBarDate($request->legal_guardian_due_date) : $request->legal_guardian_due_date;
            $legal_guardian->is_duedate_undefined = $request->legal_guardian_is_duedate_undefined == 'on';
            if ($request->legal_guardian_is_duedate_undefined == 'on') {
                $legal_guardian->due_date = null;
            }
            $legal_guardian->number_authority = $request->legal_guardian_number_authority;
            $legal_guardian->notary_of_public_faith = $request->legal_guardian_notary_of_public_faith;
            $legal_guardian->notary = $request->legal_guardian_notary;
            $legal_guardian->date_authority = Util::verifyBarDate($request->legal_guardian_date_authority) ? Util::parseBarDate($request->legal_guardian_date_authority) : $request->legal_guardian_date_authority;
            $legal_guardian->gender = $request->legal_guardian_gender;
            $legal_guardian->save();
        }
        /**
         ** Save eco com beneficiary
         */
        $eco_com_beneficiary = new EcoComBeneficiary();
        $eco_com_beneficiary->economic_complement_id = $economic_complement->id;
        $eco_com_beneficiary->city_identity_card_id = $request->eco_com_beneficiary_city_identity_card_id;
        $eco_com_beneficiary->identity_card = $request->eco_com_beneficiary_identity_card;
        $eco_com_beneficiary->last_name = $request->eco_com_beneficiary_last_name;
        $eco_com_beneficiary->mothers_last_name = $request->eco_com_beneficiary_mothers_last_name;
        $eco_com_beneficiary->first_name = $request->eco_com_beneficiary_first_name;
        $eco_com_beneficiary->second_name = $request->eco_com_beneficiary_second_name;
        $eco_com_beneficiary->surname_husband = $request->eco_com_beneficiary_surname_husband ?? null;
        $eco_com_beneficiary->birth_date = Util::verifyBarDate($request->eco_com_beneficiary_birth_date) ? Util::parseBarDate($request->eco_com_beneficiary_birth_date) : $request->eco_com_beneficiary_birth_date;
        $eco_com_beneficiary->nua = $request->eco_com_beneficiary_nua;
        $eco_com_beneficiary->gender = $request->eco_com_beneficiary_gender;
        $eco_com_beneficiary->civil_status = $request->eco_com_beneficiary_civil_status;
        $eco_com_beneficiary->phone_number = trim(implode(",", $request->eco_com_beneficiary_phone_number ?? []));
        $eco_com_beneficiary->cell_phone_number = trim(implode(",", $request->eco_com_beneficiary_cell_phone_number ?? []));
        $eco_com_beneficiary->city_birth_id = $request->eco_com_beneficiary_city_birth_id;
        $eco_com_beneficiary->due_date = Util::verifyBarDate($request->eco_com_beneficiary_due_date) ? Util::parseBarDate($request->eco_com_beneficiary_due_date) : $request->eco_com_beneficiary_due_date;
        $eco_com_beneficiary->is_duedate_undefined = $request->eco_com_beneficiary_is_duedate_undefined == 'on';
        if ($request->eco_com_beneficiary_is_duedate_undefined == 'on') {
            $eco_com_beneficiary->due_date = null;
        }
        $eco_com_beneficiary->save();

        /**
         ** observacion mayor de 25 en orfandad
         */
        if ($request->modality_id == ID::ecoCom()->orphanhood && $eco_com_beneficiary->birth_date) {
            $beneficiary_years = intval(explode(' ', Util::calculateAge($eco_com_beneficiary->birth_date, null)[0]));
            if ($beneficiary_years > 25) {
                $economic_complement->observations()->save(ObservationType::find(36), [
                    'user_id' => auth()->id(),
                    'date' => now(),
                    'message' => 'Excluido - Huerfano(a) cumplio 25 años. (Observación adicionada automáticamente)',
                    'enabled' => false
                ]);
            }
        }
        /**
         ** Update or create address
         */
        if ($request->eco_com_beneficiary_address_id) {
            if ($economic_complement->isOldAge()) {
                if (!$affiliate->address->contains($request->eco_com_beneficiary_address_id)) {
                    $affiliate->address()->attach($request->eco_com_beneficiary_address_id);
                }
            }
            $eco_com_beneficiary->address()->attach($request->eco_com_beneficiary_address_id);
        } else {
            if ($request->eco_com_beneficiary_city_address_id) {
                if ($affiliate->address->count() > 0 && $economic_complement->isOldAge()) {
                    $eco_com_beneficiary->address()->attach($affiliate->address->first()->id);
                }else{
                    $address = new Address();
                    $address->city_address_id = $request->eco_com_beneficiary_city_address_id ?? ID::cityId()->BN;
                    $address->zone = $request->eco_com_beneficiary_zone;
                    $address->street = $request->eco_com_beneficiary_street;
                    $address->number_address = $request->eco_com_beneficiary_number_address;
                    $address->save();
                    $eco_com_beneficiary->address()->save($address);
                    if ($economic_complement->isOldAge()) {
                        $affiliate->address()->save($address);
                    }
                }
            }
        }
        $eco_com_beneficiary->save();

        /**
         ** update affiliate and spouse
         */
        switch ($request->modality_id) {
                // vejez update affiliate
            case ID::ecoCom()->old_age:
                $affiliate->city_identity_card_id = $request->eco_com_beneficiary_city_identity_card_id;
                $affiliate->identity_card = $request->eco_com_beneficiary_identity_card;
                $affiliate->last_name = $request->eco_com_beneficiary_last_name;
                $affiliate->mothers_last_name = $request->eco_com_beneficiary_mothers_last_name;
                $affiliate->first_name = $request->eco_com_beneficiary_first_name;
                $affiliate->second_name = $request->eco_com_beneficiary_second_name;
                $affiliate->surname_husband = $request->eco_com_beneficiary_surname_husband ?? null;
                $affiliate->birth_date = Util::verifyBarDate($request->eco_com_beneficiary_birth_date) ? Util::parseBarDate($request->eco_com_beneficiary_birth_date) : $request->eco_com_beneficiary_birth_date;
                $affiliate->nua = $request->eco_com_beneficiary_nua;
                $affiliate->gender = $request->eco_com_beneficiary_gender;
                $affiliate->civil_status = $request->eco_com_beneficiary_civil_status;
                $affiliate->phone_number = trim(implode(",", $request->eco_com_beneficiary_phone_number ?? []));
                $affiliate->cell_phone_number = trim(implode(",", $request->eco_com_beneficiary_cell_phone_number ?? []));
                $affiliate->city_birth_id = $request->eco_com_beneficiary_city_birth_id;
                $affiliate->due_date = Util::verifyBarDate($request->eco_com_beneficiary_due_date) ? Util::parseBarDate($request->eco_com_beneficiary_due_date) : $request->eco_com_beneficiary_due_date;
                $affiliate->is_duedate_undefined = $request->eco_com_beneficiary_is_duedate_undefined == 'on';
                if ($request->eco_com_beneficiary_is_duedate_undefined == 'on') {
                    $affiliate->due_date = null;
                }
                $affiliate->save();
                break;
                // viudedad update or create spouse
            case ID::ecoCom()->widowhood:
                $spouse = Spouse::where('affiliate_id', $affiliate->id)->first();
                if (!$spouse) {
                    $spouse = new Spouse();
                }
                $spouse->user_id = Auth::user()->id;
                $spouse->affiliate_id = $affiliate->id;
                $spouse->city_identity_card_id = $request->eco_com_beneficiary_city_identity_card_id;
                $spouse->identity_card = $request->eco_com_beneficiary_identity_card;
                $spouse->registration = "";
                $spouse->last_name = $request->eco_com_beneficiary_last_name;
                $spouse->mothers_last_name = $request->eco_com_beneficiary_mothers_last_name;
                $spouse->first_name = $request->eco_com_beneficiary_first_name;
                $spouse->second_name = $request->eco_com_beneficiary_second_name;
                $spouse->surname_husband = $request->eco_com_beneficiary_surname_husband ?? null;
                $spouse->civil_status = $request->eco_com_beneficiary_civil_status;
                $spouse->birth_date = Util::verifyBarDate($request->eco_com_beneficiary_birth_date) ? Util::parseBarDate($request->eco_com_beneficiary_birth_date) : $request->eco_com_beneficiary_birth_date;
                $spouse->city_birth_id = $request->eco_com_beneficiary_city_birth_id;
                // $spouse->gender = $request->eco_com_beneficiary_gender;
                // $spouse-> = trim(implode(",", $request->eco_com_beneficiary_phone_number));
                // $spouse-> = trim(implode(",", $request->eco_com_beneficiary_cell_phone_number));
                $spouse->due_date = Util::verifyBarDate($request->eco_com_beneficiary_due_date) ? Util::parseBarDate($request->eco_com_beneficiary_due_date) : $request->eco_com_beneficiary_due_date;
                $spouse->is_duedate_undefined = $request->eco_com_beneficiary_is_duedate_undefined == 'on';
                if ($request->eco_com_beneficiary_is_duedate_undefined == 'on') {
                    $spouse->due_date = null;
                }
                $spouse->save();

                /**
                 *update affiliate
                 */
                $affiliate->identity_card = $request->affiliate_identity_card;
                $affiliate->city_identity_card_id = $request->affiliate_city_identity_card_id;
                $affiliate->last_name = $request->affiliate_last_name;
                $affiliate->mothers_last_name = $request->affiliate_mothers_last_name;
                $affiliate->first_name = $request->affiliate_first_name;
                $affiliate->second_name = $request->affiliate_second_name;
                $affiliate->surname_husband = $request->affiliate_surname_husband ?? null;
                $affiliate->birth_date = Util::verifyBarDate($request->affiliate_birth_date) ? Util::parseBarDate($request->affiliate_birth_date) : $request->affiliate_birth_date;
                $affiliate->gender = $request->affiliate_gender;
                $affiliate->save();

                break;
            default:

                break;
        }

        /**
         ** save documents
         */
        $requirements = ProcedureRequirement::where('procedure_modality_id', $request->modality_id)->get();
        foreach ($requirements  as  $requirement) {
            if ($request->input('document' . $requirement->id) == 'checked') {
                $submit = new EcoComSubmittedDocument();
                $submit->economic_complement_id = $economic_complement->id;
                $submit->procedure_requirement_id = $requirement->id;
                $submit->reception_date = date('Y-m-d');
                $submit->comment = $request->input('comment' . $requirement->id);
                $submit->save();
                $affiliate->submitted_documents()->create([
                    'user_id'=>auth()->user()->id,
                    'reception_date'=>now(),
                    'procedure_document_id'=>$requirement->procedure_document_id,
                    'status'=>true,
                ]);
            }
        }
        if ($request->additional_requirements) {
            foreach ($request->additional_requirements  as  $requirement) {
                $submit = new EcoComSubmittedDocument();
                $submit->economic_complement_id = $economic_complement->id;
                $submit->procedure_requirement_id = $requirement;
                $submit->reception_date = date('Y-m-d');
                $submit->comment = null;
                $submit->save();
            }
        }
        return redirect()->action('EconomicComplementController@show', ['id' => $economic_complement->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('read', new EconomicComplement());
        $economic_complement = EconomicComplement::with([
            'wf_state:id,name',
            'workflow:id,name',
            'eco_com_modality:id,name,shortened,procedure_modality_id',
            'eco_com_reception_type:id,name'
        ])->findOrFail($id);
        $affiliate = $economic_complement->affiliate;
        $degrees = Degree::all();
        $categories = Category::all();

        $states = ProcedureState::all();
        $pension_entities = PensionEntity::all();
        /*
        * for affiliate info
        */
        $cities = City::get();
        $cities_pluck = City::all()->pluck('first_shortened', 'id');
        $birth_cities = City::all()->pluck('name', 'id');
        $is_editable = false;
        $affiliate->phone_number = explode(',', $affiliate->phone_number);
        $affiliate->cell_phone_number = explode(',', $affiliate->cell_phone_number);
        if (!sizeOf($affiliate->address) > 0) {
            $affiliate->address[] = array('zone' => null, 'street' => null, 'number_address' => null, 'city_address_id' => null);
        }
        //police info
        $affiliate_states = AffiliateState::all()->pluck('name', 'id');

        /**
         ** for requirements
         */
        $user = User::find(Auth::user()->id);
        $procedure_types = ProcedureType::where('module_id', ID::module()->eco_com)->get();
        $procedure_requirements = ProcedureRequirement::select('procedure_requirements.id', 'procedure_documents.name as document', 'number', 'procedure_modality_id as modality_id')
            ->leftJoin('procedure_documents', 'procedure_requirements.procedure_document_id', '=', 'procedure_documents.id')
            ->orderBy('procedure_requirements.procedure_modality_id', 'ASC')
            ->orderBy('procedure_requirements.number', 'ASC')
            ->get();
        $procedure_modalities = ProcedureModality::where('procedure_type_id', '=', ID::procedureType()->eco_com)->select('id', 'name', 'procedure_type_id')->get();
        $submitted = EcoComSubmittedDocument::select('eco_com_submitted_documents.id', 'procedure_requirements.number', 'eco_com_submitted_documents.procedure_requirement_id', 'eco_com_submitted_documents.comment', 'eco_com_submitted_documents.is_valid')
            ->leftJoin('procedure_requirements', 'eco_com_submitted_documents.procedure_requirement_id', '=', 'procedure_requirements.id')
            ->orderby('procedure_requirements.number', 'ASC')
            ->where('eco_com_submitted_documents.economic_complement_id', $id);

        /**
         ** for validation and submit
         */
        $rol = Util::getRol();
        $module = Role::find($rol->id)->module;
        $wf_current_state = WorkflowState::where('role_id', $rol->id)->where('module_id', '=', $module->id)->first();
        $can_validate = optional($wf_current_state)->id == $economic_complement->wf_current_state_id;
        $can_cancel = ($economic_complement->user_id == $user->id && $economic_complement->inbox_state == true);

        $wf_sequences_back = DB::table("wf_states")
            ->where("wf_states.module_id", "=", $module->id)
            ->where('wf_states.sequence_number', '<', WorkflowState::find($economic_complement->wf_current_state_id)->sequence_number)
            ->select(
                'wf_states.id as wf_state_id',
                'wf_states.first_shortened as wf_state_name'
            )
            ->get();

        /**
         ** for observations
         */
        $observation_types = ObservationType::where('module_id', Util::getRol()->module_id)->where('type', 'T')->get();
        // $affiliate_observations = AffiliateObservation::where('affiliate_id', $economic_complement->affiliate_id)->get();
        // foreach($affiliate_observations as $observation){
        //     if($observation->observationType->type=='AT')
        //     {
        //         $eco_com_observation = EconomicComplementObservation::where('economic_complement_id',$economic_complement->id)
        //         ->where('observation_type_id',$observation->observation_type_id)
        //         ->first();
        //         if(!$eco_com_observation)
        //         {
        //             $new_observation = ObservationType::find($observation->observation_type_id);
        //             $observations_types->push($new_observation);
        //             // ($observations_types,$new_observation);   
        //         }
        //     }
        // }

        /**
         ** Permissions
         */
        $permissions = Util::getPermissions(
            ObservationType::class,
            EconomicComplement::class,
            EcoComLegalGuardian::class,
            EcoComBeneficiary::class,
            Note::class
        );
        $permissions->push(['operation' => 'amortize_economic_complement', 'value' => Gate::allows('amortize', $economic_complement)]);

        /**
         ** legal guardian types
         */
        $eco_com_legal_guardian_types = EcoComLegalGuardianType::all();
        $data = [
            'economic_complement' => $economic_complement,
            'affiliate' => $affiliate,
            'states' => $states,
            'pension_entities' => $pension_entities,
            'cities' => $cities,
            'cities_pluck' => $cities_pluck,
            'birth_cities' => $birth_cities,
            'is_editable' => $is_editable,

            'degrees' => $degrees,
            'categories' => $categories,
            'affiliate_states' => $affiliate_states,

            'user' => $user,
            'procedure_modalities' => $procedure_modalities,
            'requirements' => $procedure_requirements,
            'procedure_types' => $procedure_types,
            'submitted' =>  $submitted->pluck('eco_com_submitted_documents.procedure_requirement_id', 'procedure_requirements.number'),
            'submit_documents' => $submitted->get(),

            'can_validate' => $can_validate,
            'can_cancel' => $can_cancel,
            'wf_sequences_back' => $wf_sequences_back,

            'observation_types' =>  $observation_types,

            'permissions' =>  $permissions,

            'eco_com_legal_guardian_types' =>  $eco_com_legal_guardian_types,
        ];
        return view('eco_com.show', $data);
    }
    public function updateAffiliatePoliceEcoCom(Request $request)
    {
        try {
            $this->authorize('update', new EconomicComplement());
        } catch (AuthorizationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => ['No tiene permisos para editar el Trámite'],
            ], 403);
        }
        $affiliate = Affiliate::where('id', '=', $request->id)->first();
        // $this->authorize('update', $affiliate);
        $affiliate->date_entry = Util::verifyMonthYearDate($request->date_entry) ? Util::parseMonthYearDate($request->date_entry) : $request->date_entry;
        $affiliate->item = $request->item;
        $affiliate->category_id = $request->category_id;
        $service_year = $request->service_years;
        $service_month = $request->service_months;
        if ($service_year > 0 || $service_month > 0) {
            if ($service_month > 0) {
                $service_year++;
            }
            $category = Category::where('from', '<=', $service_year)
                ->where('to', '>=', $service_year)
                ->first();
            if ($category) {
                $affiliate->category_id = $category->id;
                $affiliate->service_years = $request->service_years;
                $affiliate->service_months = $request->service_months;
            }
        }
        $affiliate->degree_id = $request->degree_id;
        $affiliate->pension_entity_id = $request->pension_entity_id;
        $affiliate->save();
        $economic_complement = EconomicComplement::find($request->eco_com_id);
        $economic_complement->degree_id = $request->degree_id;
        $economic_complement->category_id = $affiliate->category_id;
        $economic_complement->save();
        return array('affiliate' => $affiliate);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function updateInformation(Request $request)
    {
        try {
            $this->authorize('update', new EconomicComplement());
        } catch (AuthorizationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => ['No tiene permisos para editar el Trámite'],
            ], 403);
        }
        $economic_complement = EconomicComplement::findOrFail($request->id);
        // $economic_complement->degree_id = $request->degree_id;
        // $economic_complement->category_id = $request->category_id;
        $economic_complement->city_id = $request->city_id;
        if (Util::getRol()->id == 5) {
            $economic_complement->reception_date = $request->reception_date;
        }
        $economic_complement->save();
        /**
         * update affiliate info
         */
        // $affiliate = $economic_complement->affiliate;
        // $affiliate->degree_id = $request->degree_id;
        // $affiliate->category_id = $request->category_id;
        // $affiliate->pension_entity_id = $request->pension_entity_id;
        // $affiliate->save();
        return $economic_complement;
    }
    public function firstStep()
    {
        $this->authorize('create', new EconomicComplement());
        $cities = City::all();
        $data = [
            'cities' => $cities,
        ];
        return view('eco_com.first_step', $data);
    }

    public function getReceptionType(Request $request)
    {
        $reception_type_id = ID::ecoCom()->inclusion;
        if (!$request->modality_id) {
            return $reception_type_id;
        }
        if ($request->last_eco_com_id) {
            $eco_com = EconomicComplement::find($request->last_eco_com_id);
            if ($eco_com->eco_com_modality->procedure_modality_id == $request->modality_id) {
                $reception_type_id = ID::ecoCom()->habitual;
            }
        }
        return $reception_type_id;
    }
    public function getTypeBeneficiary(Request $request)
    {
        if (!$request->affiliate_id) {
            return null;
        }
        $affiliate = Affiliate::find($request->affiliate_id);
        if ($request->last_eco_com_id) {
            $eco_com = EconomicComplement::find($request->last_eco_com_id);
            if ($eco_com->eco_com_modality->procedure_modality_id == $request->modality_id) {
                $eco_com_beneficiary = $eco_com->eco_com_beneficiary()->with('address')->first();
                if ($eco_com_beneficiary) {
                    if (!sizeOf($eco_com_beneficiary->address) > 0) {
                        $eco_com_beneficiary->address[] = array('zone' => null, 'street' => null, 'number_address' => null, 'city_address_id' => null);
                    }
                    $eco_com_beneficiary->phone_number = $this->parsePhone($eco_com_beneficiary->phone_number ?? '');
                    $eco_com_beneficiary->cell_phone_number = $this->parsePhone($eco_com_beneficiary->cell_phone_number ?? '');
                } else {
                    $eco_com_beneficiary = new EcoComBeneficiary();
                }
                $eco_com_beneficiary->address;
                return $eco_com_beneficiary;
            }
        }
        switch ($request->modality_id) {
            case ID::ecoCom()->old_age:
                $affiliate->load([
                    'address'
                ]);
                $affiliate->phone_number = $this->parsePhone($affiliate->phone_number) ?? '';
                $affiliate->cell_phone_number = $this->parsePhone($affiliate->cell_phone_number) ?? '';
                $affiliate->address;
                return $affiliate;
                break;
            case ID::ecoCom()->widowhood:
                $spouse = Spouse::where('affiliate_id', $affiliate->id)->first();
                if (!$spouse) {
                    // $spouse = new Spouse();
                    $spouse = new EcoComBeneficiary();
                }
                if ($spouse instanceof Spouse) {
                    $spouse->address = array('zone' => null, 'street' => null, 'number_address' => null, 'city_address_id' => null);
                }else{
                    $spouse->address;
                }
                // $spouse->phone_number = $this->parsePhone($spouse->phone_number ?? '') ;
                // $spouse->cell_phone_number = $this->parsePhone($spouse->cell_phone_number ?? '') ;
                $spouse->phone_number = [array('value' => null)];
                $spouse->cell_phone_number = [array('value' => null)];
                // $spouse->address;
                return $spouse;
                break;
            default:
                $ben = new EcoComBeneficiary();
                $ben->phone_number = [array('value' => null)];
                $ben->cell_phone_number = [array('value' => null)];
                $ben->address;
                return $ben;
                break;
        }
        return null;
    }
    public function getRentsFirstSemester(Request $request)
    {
        if ($request->last_eco_com_id) {
            $eco_com_procedure = EcoComProcedure::find($request->current_procedure_id);
            $eco_com = EconomicComplement::find($request->last_eco_com_id);
            if ($eco_com->eco_com_procedure->semester == 'Primer' && $eco_com->eco_com_procedure->getYear() == $eco_com_procedure->getYear()) {
                return $eco_com;
            }
        }
        return new EconomicComplement();
    }
    public function parsePhone($phones)
    {
        $array_phone = [];
        foreach (explode(',', $phones) as $phone) {
            $json_phone = new \stdClass;
            $json_phone->value = $phone;
            array_push($array_phone, $json_phone);
        }
        return $array_phone;
    }
    public function editRequirements(Request $request, $id)
    {
        try {
            $this->authorize('update', new EconomicComplement());
        } catch (AuthorizationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => ['No tiene permisos para editar el Trámite'],
            ], 403);
        }
        $num = $count = 0;
        $eco_com = EconomicComplement::find($id);
        // ? Algun dia
        $submitted_documents = $eco_com->submitted_documents()->delete();
        foreach ($request->requirements  as  $requirement) {
            foreach ($requirement as $r) {
                if ($r['status']) {
                    $count++;
                    $submit = new EcoComSubmittedDocument();
                    $submit->economic_complement_id = $eco_com->id;
                    $submit->procedure_requirement_id = $r['id'];
                    $submit->reception_date = date('Y-m-d');
                    $submit->comment = $r['comment'];
                    $submit->save();
                }
            }
        }
        $procedure_requirements = ProcedureRequirement::select('procedure_requirements.id', 'procedure_documents.name as document', 'number', 'procedure_modality_id as modality_id')
            ->leftJoin('procedure_documents', 'procedure_requirements.procedure_document_id', '=', 'procedure_documents.id')
            ->where('procedure_requirements.number', '0')
            ->orderBy('procedure_requirements.procedure_modality_id', 'ASC')
            ->orderBy('procedure_requirements.number', 'ASC')
            ->get();

        $aditional =  $request->additional_requirements;
        $num = "";
        foreach ($procedure_requirements as $requirement) {
            $needle = EcoComSubmittedDocument::where('economic_complement_id', $id)
                ->where('procedure_requirement_id', $requirement->id)
                ->first();
            if (isset($needle)) {
                if (!in_array($requirement->id, $aditional)) {
                    $num .= $requirement->id . ' ';
                    $needle->delete();
                    $needle->forceDelete();
                }
            } else {
                if (in_array($requirement->id, $aditional)) {
                    $submit = new EcoComSubmittedDocument();
                    $submit->economic_complement_id = $eco_com->id;
                    $submit->procedure_requirement_id = $requirement->id;
                    $submit->reception_date = date('Y-m-d');
                    $submit->comment = "";
                    $submit->save();
                }
            }
        }
        /**
         ** verify observation id = 6
         */
        $number_docs = ProcedureModality::find($eco_com->eco_com_modality->procedure_modality_id)->procedure_requirements->pluck('number')->unique()->sort();
        if ($number_docs->contains(0)) {
            $number_docs = $number_docs->slice(1);
        }
        if ($count != $number_docs->count()) {
            if(!$eco_com->observations->contains(6)){
                $eco_com->observations()->save(ObservationType::find(6), [
                    'user_id' => auth()->id(),
                    'date' => now(),
                    'message' => 'Documentación incompleta (Observación adicionada automáticamente)',
                    'enabled' => false
                ]);
            }else{
                $eco_com->observations()->updateExistingPivot(6, [
                    'user_id' => auth()->id(),
                    'date' => now(),
                    'message' => 'Documentación incompleta (Observación adicionada automáticamente)',
                    'enabled' => false
                ]);
            }
        }else{
            if($eco_com->observations->contains(6)){
                $eco_com->observations()->updateExistingPivot(6, [
                    'user_id' => auth()->id(),
                    'date' => now(),
                    'message' => 'Documentación incompleta (Observación adicionada automáticamente)',
                    'enabled' => true
                ]);
            }
        }
        return $num;
    }
    public function getEcoCom($id)
    {
        try {
            $this->authorize('read', new EconomicComplement());
        } catch (AuthorizationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => ['No tiene permisos para ver el Trámite'],
            ], 403);
        }
        $rol = Util::getRol();
        $discount_type_id = null;
        switch ($rol->id) {
            case 7: //contabiliadad
                $discount_type_id = 4;
                break;
            case 16: //prestamo
                $discount_type_id = 5;
                break;
            case 4: // complemento
                $discount_type_id = 6;
                break;
        }
        $eco_com = EconomicComplement::with('discount_types')->findOrFail($id);
        $eco_com->discount_amount = optional(optional($eco_com->discount_types()->where('discount_type_id', $discount_type_id)->first())->pivot)->amount;
        return $eco_com;
    }
    public function updateRents(Request $request)
    {
        try {
            $this->authorize('update', new EconomicComplement());
        } catch (AuthorizationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => ['No tiene permisos para editar el Trámite'],
            ], 403);
        }
        $economic_complement = EconomicComplement::with('discount_types')->find($request->id);
        if ($economic_complement->eco_com_state->eco_com_state_type_id == ID::ecoComStateType()->pagado || $economic_complement->eco_com_state->eco_com_state_type_id == ID::ecoComStateType()->enviado) {
            $eco_com_state = $economic_complement->eco_com_state;
            return response()->json([
                'status' => 'error',
                'msg' => 'Error',
                'errors' => ['No se puede modificar las rentas del trámite ' . $economic_complement->code . 'porque se encuentra en estado de ' . $eco_com_state->name],
            ], 422);
        }
        if ($request->pension_entity_id == ID::pensionEntity()->senasir) {
            $economic_complement->sub_total_rent = Util::parseMoney($request->sub_total_rent);
            $economic_complement->reimbursement = Util::parseMoney($request->reimbursement);
            $economic_complement->dignity_pension = Util::parseMoney($request->dignity_pension);
            $economic_complement->aps_disability = Util::parseMoney($request->aps_disability);
            $economic_complement->aps_total_fsa = null;
            $economic_complement->aps_total_cc = null;
            $economic_complement->aps_total_fs = null;
        } else {
            $economic_complement->aps_total_fsa = Util::parseMoney($request->aps_total_fsa);
            $economic_complement->aps_total_cc = Util::parseMoney($request->aps_total_cc);
            $economic_complement->aps_total_fs = Util::parseMoney($request->aps_total_fs);
            $economic_complement->aps_disability = Util::parseMoney($request->aps_disability);
            $economic_complement->sub_total_rent = null;
            $economic_complement->reimbursement = null;
            $economic_complement->dignity_pension = null;
        }
        $economic_complement->save();
        if ($request->pension_entity_id == ID::pensionEntity()->senasir) {
            $economic_complement->total_rent =
            $economic_complement->sub_total_rent -
            $economic_complement->reimbursement -
            $economic_complement->dignity_pension +
            $economic_complement->aps_disability;
        }else{
            $economic_complement->total_rent =
            $economic_complement->aps_total_fsa +
            $economic_complement->aps_total_cc +
            $economic_complement->aps_total_fs +
            $economic_complement->aps_disability;
        }
        $economic_complement->save();
        $discount_type_id = null;
        $rol = Util::getRol();
        switch ($rol->id) {
            case 7: //contabiliadad
                $discount_type_id = 4;
                break;
            case 16: //prestamo
                $discount_type_id = 5;
                break;
            case 4: // complemento
                $discount_type_id = 6;
                break;
        }
        if (Gate::allows('qualify', $economic_complement)) {
            $economic_complement->qualify();
        }
        $economic_complement->discount_amount = optional(optional($economic_complement->discount_types()->where('discount_type_id', $discount_type_id)->first())->pivot)->amount;
        return $economic_complement;
    }
    public function saveAmortization(Request $request)
    {
        $eco_com = EconomicComplement::with('discount_types')->find($request->id);
        try {
            $this->authorize('amortize', $eco_com);
        } catch (AuthorizationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => ['No tiene permisos para realizar la amortizacion'],
            ], 403);
        }
        try {
            $this->validate($request, [
                'amount' => 'required|numeric|min:1',
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Error',
                'errors' => $exception->errors(),
            ], 422);
        }
        if ($eco_com->eco_com_state->eco_com_state_type_id == ID::ecoComStateType()->pagado || $eco_com->eco_com_state->eco_com_state_type_id == ID::ecoComStateType()->enviado) {
            $eco_com_state = $eco_com->eco_com_state;
            return response()->json([
                'status' => 'error',
                'msg' => 'Error',
                'errors' => ['No se puede realizar la amortización porque el trámite ' . $eco_com->code . ' se encuentra en estado de ' . $eco_com_state->name],
            ], 422);
        }
        $rol = Util::getRol();
        $discount_type_id = null;
        switch ($rol->id) {
            case 7: //contabiliadad
                $discount_type_id = 4;
                break;
            case 16: //prestamo
                $discount_type_id = 5;
                break;
            case 4: // complemento
                $discount_type_id = 6;
                break;
        }
        $discount_type = DiscountType::findOrFail($discount_type_id);
        if ($eco_com->discount_types->contains($discount_type->id)) {
            $eco_com->discount_types()->updateExistingPivot($discount_type->id, ['amount' => $request->amount, 'date' => now(), 'message' => $request->message]);
        } else {
            $eco_com->discount_types()->save($discount_type, ['amount' => $request->amount, 'date' => now(), 'message' => $request->message]);
        }
        //detach
        // if ($eco_com->discount_types->contains($discount_type->id)) {
        //     $eco_com->discount_types()->detach($discount_type->id);
        // }
        $eco_com->procedure_records()->create([
            'user_id' => Auth::user()->id,
            'record_type_id' => 10,
            'wf_state_id' => Util::getRol()->wf_states->first()->id,
            'date' => Carbon::now(),
            'message' => "El usuario " . Auth::user()->username  . " amortizó " . $request->amount . "."
        ]);
        if (Gate::allows('qualify', $eco_com)) {
            $eco_com->qualify();
        }
        $eco_com = EconomicComplement::with('discount_types')->find($request->id);
        $eco_com->discount_amount = optional(optional($eco_com->discount_types()->where('discount_type_id', $discount_type_id)->first())->pivot)->amount;
        return $eco_com;
        // case 4: //complemento
        // $start_procedure = EconomicComplementProcedure::where('id','=', 2)->first();
        //     $complemento = EconomicComplement::where('id', $request->id_complemento)->first();
        //     $complemento->amount_replacement = $request->amount_amortization;
        //     $complemento->save();
        //     $sum = 0;
        //     while ($start_procedure) {
        //         $eco_com = $start_procedure->economic_complements()->where('affiliate_id', '=', $complemento->affiliate_id)->first();
        //         if ($eco_com) {
        //             if ($eco_com->amount_replacement) {
        //                 $sum += $eco_com->amount_replacement;
        //             }
        //         }
        //         $start_procedure = EconomicComplementProcedure::where('id', '=', Util::semesternext(Carbon::parse($start_procedure->year)->year, $start_procedure->semester))->first();
        //         Log::info("whille");
        //     }
        //     $devolution = Devolution::where('affiliate_id', '=', $complemento->affiliate_id)->where('observation_type_id', '=', 13)->first();
        //     if ($devolution) {
        //         $devolution->balance = $devolution->total - $sum;
        //         $devolution->save();
        //     }
        //     break;
        // Session::flash('message', 'Se guardo la Amortización.');

        // if ($complemento->total_rent > 0) {
        //     EconomicComplement::calculate($complemento, $complemento->total_rent, $complemento->sub_total_rent, $complemento->reimbursement, $complemento->dignity_pension, $complemento->aps_total_fsa, $complemento->aps_total_cc, $complemento->aps_total_fs, $complemento->aps_disability);
        //     $complemento->save();
        // }

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $this->authorize('delete', new EconomicComplement());
        } catch (AuthorizationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => ['No tiene permisos para eliminar el Trámite'],
            ], 403);
        }
        if ($id) {
            $eco_com = EconomicComplement::find($id);
            $eco_com->code = $eco_com->code . 'A';
            $eco_com->save();
            $eco_com->eco_com_beneficiary()->delete();
            $eco_com->eco_com_legal_guardian()->delete();
            $eco_com->submitted_documents()->delete();
            $eco_com->wf_records()->delete();
            $eco_com->notes()->delete();
            $eco_com->procedure_records()->delete();
            $eco_com->observations()->detach();
            $eco_com->discount_types()->detach();
            $eco_com->tags()->detach();
            $eco_com->delete();
            return response()->json([
                'message' => 'deleted',
            ], 204);
        }
        return [];
    }
    // public function averages()
    // {
    //     $year_list = EcoComProcedure::orderByDesc('year')->pluck('year')->map(function ($item, $key) {
    //         return Carbon::parse($item)->year;
    //     })->unique()->toArray();
    //     $year_list = array_combine($year_list, $year_list);
    //     $semester_list = EcoComProcedure::all()->pluck('semester')->unique()->toArray();
    //     $semester_list = array_combine($semester_list, $semester_list);

    //     $data = [
    //         'year_list' => $year_list,
    //         'semester_list' => $semester_list,
    //     ];
    //     return view('eco_com.average', $data);
    // }
    public function getAverageData(Request $request)
    {
        $year = $request->year;
        $semester = $request->semester;
        if (!$request->has('year') || !$request->has('semester')) {
            $procedure = EcoComProcedure::find(Util::getEcoComCurrentProcedure()->first());
            $year = Carbon::parse($procedure->year)->year;
            $semester = $procedure->semester;
        }
        $average_list = EcoComRent::select(DB::raw("degrees.shortened as degree, procedure_modalities.name as type,eco_com_rents.minor as rmin,eco_com_rents.higher as rmax, eco_com_rents.average as average "))
            ->leftJoin('procedure_modalities', 'eco_com_rents.procedure_modality_id', '=', 'procedure_modalities.id')
            ->leftJoin('degrees', 'eco_com_rents.degree_id', '=', 'degrees.id')
            ->whereYear('eco_com_rents.year', '=', $year)
            ->where('eco_com_rents.semester', '=', $semester)
            ->orderBy('degrees.correlative', 'ASC')
            ->orderBy('procedure_modalities.id', 'ASC');

        return Datatables::of($average_list)
            ->addColumn('degree', function ($average_list) {
                return $average_list->degree;
            })
            ->editColumn('type', function ($average_list) {
                return $average_list->type;
            })
            ->editColumn('rmin', function ($average_list) {
                return $average_list->rmin;
            })
            ->editColumn('rmax', function ($average_list) {
                return $average_list->rmax;
            })
            ->editColumn('average', function ($average_list) {
                return $average_list->average;
            })
            ->make(true);
    }
    public function printAverage()
    {
        return null;
    }
    public function qualificationParameters()
    {
        // averages
        $year_list = EcoComProcedure::orderByDesc('year')->pluck('year')->map(function ($item, $key) {
            return Carbon::parse($item)->year;
        })->unique()->toArray();
        $year_list = array_combine($year_list, $year_list);
        $semester_list = EcoComProcedure::all()->pluck('semester')->unique()->toArray();
        $semester_list = array_combine($semester_list, $semester_list);

        // complementary factor
        $year = null;
        $semester = null;
        if (Util::getEcoComCurrentProcedure()->count() > 0)  {
            $procedure = EcoComProcedure::find(Util::getEcoComCurrentProcedure()->first());
            $year = Carbon::parse($procedure->year)->year;
            $semester = $procedure->semester;
            if (ComplementaryFactor::whereYear('year', '=', $year)->where('semester', '=', $semester)->where('hierarchy_id', '=', 1)->first()) {
                $complementary_factor = ComplementaryFactor::whereYear('year', '=', $year)->where('semester', '=', $semester)->where('hierarchy_id', '=', 1)->first();
                $cf1_old_age = $complementary_factor->old_age;
                $cf1_widowhood = $complementary_factor->widowhood;
            } else {
                $cf1_old_age = "";
                $cf1_widowhood = "";
            }
            if (ComplementaryFactor::whereYear('year', '=', $year)->where('semester', '=', $semester)->where('hierarchy_id', '=', 2)->first()) {
                $complementary_factor = ComplementaryFactor::whereYear('year', '=', $year)->where('semester', '=', $semester)->where('hierarchy_id', '=', 2)->first();
                $cf2_old_age = $complementary_factor->old_age;
                $cf2_widowhood = $complementary_factor->widowhood;
            } else {
                $cf2_old_age = "";
                $cf2_widowhood = "";
            }
    
            if (ComplementaryFactor::whereYear('year', '=', $year)->where('semester', '=', $semester)->where('hierarchy_id', '=', 3)->first()) {
                $complementary_factor = ComplementaryFactor::whereYear('year', '=', $year)->where('semester', '=', $semester)->where('hierarchy_id', '=', 3)->first();
                $cf3_old_age = $complementary_factor->old_age;
                $cf3_widowhood = $complementary_factor->widowhood;
            } else {
                $cf3_old_age = "";
                $cf3_widowhood = "";
            }
    
            if (ComplementaryFactor::whereYear('year', '=', $year)->where('semester', '=', $semester)->where('hierarchy_id', '=', 4)->first()) {
                $complementary_factor = ComplementaryFactor::whereYear('year', '=', $year)->where('semester', '=', $semester)->where('hierarchy_id', '=', 4)->first();
                $cf4_old_age = $complementary_factor->old_age;
                $cf4_widowhood = $complementary_factor->widowhood;
            } else {
                $cf4_old_age = "";
                $cf4_widowhood = "";
            }
    
            if (ComplementaryFactor::whereYear('year', '=', $year)->where('semester', '=', $semester)->where('hierarchy_id', '=', 5)->first()) {
                $complementary_factor = ComplementaryFactor::whereYear('year', '=', $year)->where('semester', '=', $semester)->where('hierarchy_id', '=', 5)->first();
                $cf5_old_age = $complementary_factor->old_age;
                $cf5_widowhood = $complementary_factor->widowhood;
            } else {
                $cf5_old_age = "";
                $cf5_widowhood = "";
            }
        }

        /**
         ** Permissions
         */

        $permissions = Util::getPermissions(
            EcoComProcedure::class
        );
        $data = [
            'complementary_factor' => new ComplementaryFactor(),
            'year' => $year,
            'semester' => $semester,
            'cf1_old_age' => $cf1_old_age ?? [],
            'cf1_widowhood' => $cf1_widowhood ?? [],
            'cf2_old_age' => $cf2_old_age ?? [],
            'cf2_widowhood' => $cf2_widowhood ?? [],
            'cf3_old_age' => $cf3_old_age ?? [],
            'cf3_widowhood' => $cf3_widowhood ?? [],
            'cf4_old_age' => $cf4_old_age ?? [],
            'cf4_widowhood' => $cf4_widowhood ?? [],
            'cf5_old_age' => $cf5_old_age ?? [],
            'cf5_widowhood' => $cf5_widowhood ?? [],

            'year_list' => $year_list,
            'semester_list' => $semester_list,

            'permissions' => $permissions,
        ];

        return view('eco_com.qualification_parameters', $data);
    }
    public function getRecord($id)
    {
        $eco_com = EconomicComplement::find($id);
        $procedure_records = $eco_com->procedure_records()->with(['user:id,username', 'wf_state:id,name', 'record_type:id,name'])->orderByDesc('date')->get();
        $workflow_records = $eco_com->wf_records()->with(['user:id,username','wf_state:id,name', 'record_type:id,name'])->orderByDesc('date')->get();
        $note_records = $eco_com->notes()->orderByDesc('date')->get();
        return compact('procedure_records', 'workflow_records', 'note_records');
    }
}
