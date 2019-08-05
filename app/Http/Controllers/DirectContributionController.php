<?php

namespace Muserpol\Http\Controllers;
use Illuminate\Http\Request;
use Muserpol\Models\Affiliate;
use Muserpol\Models\Contribution\DirectContribution;
use Yajra\Datatables\DataTables;
use Auth;
use DB;
use Log;
use Muserpol\Models\ProcedureType;
use Muserpol\Models\ProcedureRequirement;
use Muserpol\Models\ProcedureModality;
use Muserpol\Models\Spouse;
use Muserpol\Models\Kinship;
use Muserpol\Models\City;
use Muserpol\Helpers\Util;
use Muserpol\Helpers\ID;
use Muserpol\Models\ProcedureState;
use Muserpol\Models\Contribution\DirectContributionSubmittedDocument;
use Muserpol\Models\Address;
use Muserpol\Models\Contribution\Contribution;
use Muserpol\Models\Contribution\AidContribution;
use Muserpol\Models\Contribution\Reimbursement;
use Muserpol\Models\Contribution\AidReimbursement;
use Muserpol\Models\Role;
use Muserpol\Models\Workflow\WorkflowState;
use Muserpol\Models\PaymentType;
class DirectContributionController extends Controller
{
    public function getAllDirectContribution(DataTables $datatables)
    {
        $direct_contributions = DirectContribution::with([
            'affiliate:id,identity_card,city_identity_card_id,first_name,second_name,last_name,mothers_last_name,surname_husband,gender,degree_id,degree_id',
            'city:id,name,first_shortened',
            'procedure_modality:id,name,shortened,procedure_type_id',
        ])->select(
            'id',
            'code',
            'date',
            'affiliate_id',
            'city_id',
            'procedure_modality_id'
        )
            ->where('code', 'not like', '%A')
            ->orderByDesc(DB::raw("split_part(code, '/',1)::integer"));
        return $datatables->eloquent($direct_contributions)
            ->editColumn('affiliate.city_identity_card_id', function ($direct_contribution) {
                $city = City::find($direct_contribution->affiliate->city_identity_card_id);
                return $city ? $city->first_shortened : null;
            })
            ->addColumn('action', function ($direct_contribution) {
                return "<a href='/direct_contribution/" . $direct_contribution->id . "' class='btn btn-default'><i class='fa fa-eye'></i></a>";
            })
            ->make(true);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('direct_contributions.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Affiliate $affiliate)
    {
        $this->authorize('create', DirectContribution::class);
        $user = Auth::User();
        $affiliate = Affiliate::select('affiliates.id', 'identity_card', 'city_identity_card_id', 'registration', 'first_name', 'second_name', 'last_name', 'mothers_last_name', 'surname_husband', 'birth_date', 'gender', 'degrees.name as degree', 'civil_status', 'affiliate_states.name as affiliate_state', 'phone_number', 'cell_phone_number', 'date_derelict', 'date_death', 'reason_death')
            ->leftJoin('degrees', 'affiliates.id', '=', 'degrees.id')
            ->leftJoin('affiliate_states', 'affiliates.affiliate_state_id', '=', 'affiliate_states.id')
            ->find($affiliate->id);
        $procedure_types = ProcedureType::where('module_id', 11)->get();
        $procedure_requirements = ProcedureRequirement::select('procedure_requirements.id', 'procedure_documents.name as document', 'number', 'procedure_modality_id as modality_id')
            ->leftJoin('procedure_documents', 'procedure_requirements.procedure_document_id', '=', 'procedure_documents.id')
            ->orderBy('procedure_requirements.procedure_modality_id', 'ASC')
            ->orderBy('procedure_requirements.number', 'ASC')
            ->get();
        $spouse = Spouse::where('affiliate_id', $affiliate->id)->first();
        if (!isset($spouse->id)) {
            $spouse = new Spouse();
        }
        $modalities = ProcedureModality::whereIn('procedure_type_id', $procedure_types->pluck('id'))->select('id', 'name', 'procedure_type_id')->get();
        $cities = City::get();
        $searcher = new SearcherController();

        $data = [
            'user' => $user,
            'requirements' => $procedure_requirements,
            'procedure_types' => $procedure_types,
            'modalities' => $modalities,
            'affiliate' => $affiliate,
            'cities' => $cities,
            'spouse' => $spouse,
            'searcher' => $searcher,
        ];

        return view('direct_contributions.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $direct_contribution = new DirectContribution();
        $direct_contribution->affiliate_id = $request->affiliate_id;
        $direct_contribution->user_id = Auth::user()->id;
        $direct_contribution->city_id = $request->city_id;
        $direct_contribution->procedure_modality_id = $request->procedure_modality_id;
        $direct_contribution->procedure_state_id = 1;
        // $direct_contribution->contributor_type_id = $request->contributor_type_id;
        $direct_contribution->commitment_date = Util::verifyBarDate($request->commitment_date) ? Util::parseBarDate($request->commitment_date) : $request->commitment_date;
        $direct_contribution->document_number = $request->document_number;
        $direct_contribution->document_date = Util::verifyBarDate($request->document_date) ? Util::parseBarDate($request->document_date) : $request->document_date;
        $direct_contribution->start_contribution_date = Util::verifyMonthYearDate($request->start_contribution_date) ? Util::parseMonthYearDate($request->start_contribution_date) : $request->start_contribution_date;
        $direct_contribution->date = now();
        $direct_contribution->code = Util::getNextCode(Util::getLastCode(DirectContribution::class), "1");
        $direct_contribution->save();

        $requirements = ProcedureRequirement::select('id')->get();
        foreach ($requirements as $requirement) {
            if ($request->input('document' . $requirement->id) == 'checked') {
                $submit = new DirectContributionSubmittedDocument();
                $submit->direct_contribution_id = $direct_contribution->id;
                $submit->procedure_requirement_id = $requirement->id;
                $submit->reception_date = now();
                $submit->comment = $request->input('comment' . $requirement->id);
                $submit->save();
            }
        }

        $affiliate = $direct_contribution->affiliate;
        // save contributor
        if ($request->contributor_type_id == 1) { // affiliate
            $affiliate::updatePersonalInfo($affiliate->id, Util::parseRequest($request->all(), 'contributor'));
        } elseif ($request->contributor_type_id == 2) { // spouse
            $affiliate::updateSpouse($affiliate->id, Util::parseRequest($request->all(), 'contributor'));
        }
        return redirect()->route('direct_contribution.show',$direct_contribution->id );
    }

    /**
     * Display the specified resource.
     *
     * @param  \Muserpol\DirectContribution  $directContribution
     * @return \Illuminate\Http\Response
     */
    //public function show(DirectContribution $directContribution)
    public function show(DirectContribution $directContribution)
    {
        // $d = \Muserpol\Models\Contribution\ContributionProcess::find(55);
        // return $d->contributions;
        // return $d;
        // return 123;

        $affiliate = Affiliate::find($directContribution->affiliate_id);
        if (!sizeOf($affiliate->address) > 0) {
            $affiliate->address[] = new Address();
        }
        $affiliate->phone_number = explode(',', $affiliate->phone_number);
        $affiliate->cell_phone_number = explode(',', $affiliate->cell_phone_number);

        $cities = City::get();
        $cities_pluck = $cities->pluck('first_shortened', 'id');
        $birth_cities = City::all()->pluck('name', 'id');
        
        $states = ProcedureState::get();
        
        $spouse = $affiliate->spouse->first();
        if (!$spouse) {
            $spouse = new Spouse();
        }else{
            $spouse->load([
                'city_identity_card:id,first_shortened',
                'city_birth:id,name',
            ]);
        }


        //GETTIN CONTRIBUTIONS
        $contributions =  Contribution::where('affiliate_id',$affiliate->id)->pluck('total','month_year')->toArray();
        $reimbursements = Reimbursement::where('affiliate_id',$affiliate->id)->pluck('total','month_year')->toArray();

        if($affiliate->date_entry)
            $end = explode('-', Util::parseMonthYearDate($affiliate->date_entry));
        else
            $end = explode('-', '1976-05-01');
        $month_end = $end[1];
        $year_end = $end[0];

        if($affiliate->date_derelict)
            $start = explode('-', Util::parseMonthYearDate($affiliate->date_derelict));
        else
            $start = explode('-', date('Y-m-d'));
        $month_start = $start[1];
        $year_start = $start[0];

        $aid_contributions = AidContribution::where('affiliate_id',$affiliate->id)->pluck('total','month_year')->toArray();
        $aid_reimbursement = AidReimbursement::where('affiliate_id',$affiliate->id)->pluck('total','month_year')->toArray();
        //return  $affiliate->date_death;//Util::parseMonthYearDate($affiliate->date_death);
        
        if($affiliate->date_death)
            $death = explode('/', $affiliate->date_death);
        else
            $death = explode('/', date('d/m/Y'));
        
        $month_death = $death[1];
        $year_death = $death[2];
        $procedure_types = ProcedureType::where('module_id', 11)->get();
        $modalities = ProcedureModality::whereIn('procedure_type_id', $procedure_types->pluck('id'))->select('id','name', 'procedure_type_id')->get();
        
        $requirements = ProcedureRequirement::
                                    select('procedure_requirements.id','procedure_documents.name as document','number','procedure_modality_id as modality_id')                                    
                                    ->whereIn('procedure_requirements.procedure_modality_id',$modalities->pluck('id'))
                                    ->leftJoin('procedure_documents','procedure_requirements.procedure_document_id','=','procedure_documents.id')                                    
                                    ->orderBy('procedure_requirements.procedure_modality_id','ASC')
                                    ->orderBy('procedure_requirements.number','ASC')
                                    ->get();        
                                    
        $submitted = DirectContributionSubmittedDocument::
            select('direct_contribution_submitted_documents.id','procedure_requirements.number','direct_contribution_submitted_documents.procedure_requirement_id','direct_contribution_submitted_documents.comment','direct_contribution_submitted_documents.is_valid')
            ->leftJoin('procedure_requirements','direct_contribution_submitted_documents.procedure_requirement_id','=','procedure_requirements.id')
            ->orderby('procedure_requirements.number','ASC')
            ->where('direct_contribution_submitted_documents.direct_contribution_id',$directContribution->id);

        /*
         !! TODO
         !! Agregar id de estado pagado
        */
        $contribution_processes = $directContribution->contribution_processes()->where('procedure_state_id', 6)->get();
        $procedure_type = $directContribution->procedure_modality->procedure_type;
        
        /**for validate doc*/
        $user = Auth::user();
        if ($directContribution->hasActiveContributionProcess()) {
            $contribution_process =  $directContribution->contribution_processes()->where('procedure_state_id', 1)->first();
            $rol = Util::getRol();
            $module = Role::find($rol->id)->module;
            $wf_current_state = WorkflowState::where('role_id', $rol->id)->where('module_id', '=', $module->id)->first();
            $can_validate = $wf_current_state->id == $contribution_process->wf_state_current_id;
            $can_cancel = ($contribution_process->user_id == $user->id && $contribution_process->inbox_state == true);
            /* workflow */
            $wf_sequences_back = DB::table("wf_states")
                ->where("wf_states.module_id", "=", $module->id)
                ->where('wf_states.sequence_number', '<', WorkflowState::find($contribution_process->wf_state_current_id)->sequence_number)
                ->select(
                    'wf_states.id as wf_state_id',
                    'wf_states.first_shortened as wf_state_name'
                )
                ->get();                        
        }        
        $payment_types = PaymentType::get();
        //print_r($contribution_process->voucher);
        //return 12;        
        $data = [
            'direct_contribution'   =>  $directContribution,
            'contribution_process'   =>  $contribution_process ?? null,
            'contribution_processes'   =>  $contribution_processes,
            'procedure_type'   =>  $procedure_type,
            'affiliate' =>  $affiliate,
            'spouse'    =>  $spouse,
            'cities'    =>  $cities,
            'cities_pluck'  =>  $cities_pluck,
            'birth_cities'  =>  $birth_cities,
            'is_editable'   =>  true,
            'states'    =>  $states,
            'contributions' =>  $contributions,
            'aid_contributions' =>  $aid_contributions,
            'month_end' =>  $month_end,
            'month_start'  =>   $month_start,
            'year_end'  =>  $year_end,
            'year_start'    =>  $year_start,
            'month_death'   =>  $month_death,
            'year_death'    =>  $year_death,
            'reimbursements'    =>  $reimbursements,
            'aid_reimbursements'    =>  $aid_reimbursement,
            'modalities'    =>  $modalities,
            'requirements'  =>  $requirements,
            'procedure_types'   =>  $procedure_types,
            'payment_types' =>  $payment_types,
            'submitted_documents'   =>  $submitted->get(),

            'can_validate' => $can_validate ?? false,
            'can_cancel' => $can_cancel ?? false,
            'wf_sequences_back' => $wf_sequences_back ?? null,
            'user' => $user,
            // 'workflow_records' => $workflow_records,
            // 'first_wf_state' => $first_wf_state,
            // 'wf_states' => $wf_states,
            // 'is_editable' => $is_editable,
        ];        
        return view('direct_contributions.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Muserpol\DirectContribution  $directContribution
     * @return \Illuminate\Http\Response
     */
    public function edit(DirectContribution $directContribution)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Muserpol\DirectContribution  $directContribution
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DirectContribution $directContribution)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Muserpol\DirectContribution  $directContribution
     * @return \Illuminate\Http\Response
     */
    public function destroy(DirectContribution $directContribution)
    {
        //
    }

    /**
     * Edit basic information from direct contribution process
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Muserpol\DirectContribution  $directContribution
     */
    public function updateInformation(Request $request)
    {
        $direct_contribution = DirectContribution::find($request->id);
        // $this->authorize('update', $direct_contribution);
        $direct_contribution->city_id = $request->city_id;
        if($direct_contribution->ret_fun_state_id == ID::state()->eliminado){
            $direct_contribution->code.="A";
        }
        $direct_contribution->commitment_date = $request->commitment_date;
        $direct_contribution->document_number = $request->document_number;
        $direct_contribution->document_date = $request->document_date;
        $direct_contribution->start_contribution_date = $request->start_contribution_date;
        $direct_contribution->date = $request->date;                
        $direct_contribution->save();
        $data = [
            'direct_contribution' => $direct_contribution, 
            'procedure_modality'=>$direct_contribution->procedure_modality,
            'city'=>$direct_contribution->city,            
        ];        
        return $data;
    }

    /**
     * This function edit recepcioned documents
     *
     * @param object Request, int id
     * @return Muserpol\Models\QuotaAidMortuary\QuotaAidMortuary
     */
    public function editRequirements(Request $request, $id) {
        $documents = DirectContributionSubmittedDocument::
            select('procedure_requirements.number','direct_contribution_submitted_documents.procedure_requirement_id')
            ->leftJoin('procedure_requirements','direct_contribution_submitted_documents.procedure_requirement_id','=','procedure_requirements.id')
            ->orderby('procedure_requirements.number','ASC')
            ->where('direct_contribution_submitted_documents.direct_contribution_id',$id)
            ->pluck('direct_contribution_submitted_documents.procedure_requirement_id','procedure_requirements.number');

        $num = $num2 = 0;

        foreach($request->requirements as $requirement){
                $from = $to = 0;
                $comment = null;
                for($i=0;$i<count($requirement);$i++){
                    $from = $requirement[$i]['number'];
                    if($requirement[$i]['status'] == true)
                    {
                        $to = $requirement[$i]['id'];
                        $comment = $requirement[$i]['comment'];
                        $doc = DirectContributionSubmittedDocument::where('direct_contribution_id',$id)->where('procedure_requirement_id',$documents[$from])->first();
                        $doc->procedure_requirement_id = $to;
                        $doc->comment = $comment;
                        $doc->save();
                    }
                }
        }

        // $procedure_requirements = ProcedureRequirement::
        // select('procedure_requirements.id','procedure_documents.name as document','number','procedure_modality_id as modality_id')
        // ->leftJoin('procedure_documents','procedure_requirements.procedure_document_id','=','procedure_documents.id')
        // ->where('procedure_requirements.number','0')
        // ->orderBy('procedure_requirements.procedure_modality_id','ASC')
        // ->orderBy('procedure_requirements.number','ASC')
        // ->get();

        // $quota_aid = QuotaAidMortuary::select('id','procedure_modality_id')->find($id);
    
        // $aditional =  $request->aditional_requirements;
        // $num ="";
        
        // foreach($procedure_requirements as $requirement){
        // $needle = QuotaAidSubmittedDocument::where('quota_aid_mortuary_id',$id)
        // ->where('procedure_requirement_id',$requirement->id)
        // ->first();
        // if(isset($needle)) {
        // if(!in_array($requirement->id,$aditional)){
        //     $num.=$requirement->id.' ';
        //     $needle->delete();
        //     $needle->forceDelete();
        // }
        // } else {
        //     if(in_array($requirement->id,$aditional)) {
        //         $submit = new QuotaAidSubmittedDocument();
        //         $submit->quota_aid_mortuary_id = $quota_aid->id;
        //         $submit->procedure_requirement_id = $requirement->id;
        //         $submit->reception_date = date('Y-m-d');
        //         $submit->comment = "";
        //         $submit->save();
        //     }
        // }
        // }

        return $num;
    }
}
