<?php
namespace Muserpol\Http\Controllers;

use Muserpol\Models\Contribution\AidContribution;
use Illuminate\Http\Request;
use Muserpol\Models\Affiliate;
use Carbon\Carbon;
use Yajra\Datatables\DataTables;
use Ixudra\Curl\Facades\Curl;
use Muserpol\Models\User;
use Validator;
use Log;
use Muserpol\Models\Voucher;
use Muserpol\Helpers\Util;
use Muserpol\Models\City;
use Muserpol\Models\Spouse;
use Auth;
use Muserpol\Models\Contribution\AidCommitment;
use Muserpol\Models\Contribution\ContributionRate;
use Session;

class AidContributionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return 0;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Display the specified resource.
     *use Muserpol\Models\AffiliateState;
     * @param  \Muserpol\AidContribution  $aid_contribution
     * @return \Illuminate\Http\Response
     */
    public function show(Affiliate $affiliate)
    {
        ///       $this->authorize('view',new Contribution);        
        $data = [
            'affiliate' => $affiliate,            
        ];
        return view('contribution.aid_show',$data);               
    }

    public function getAllCommitmentAid($id)
    {
        $commitment = AidContribution::where('affiliate_id', $id)
            ->orderBy('month_year', 'desc')
            ->first();
        $array_date = explode('-', $commitment->month_year);
        $gestion = $array_date[1];
        $month = $array_date[0];
        $type = $commitment->type;
        $quotable = $commitment->quotable;
        $rent = $commitment->rent;
        $dignity_rent = $commitment->dignity_rent;
        $total = $commitment->total;
        $data = [
            'year' => $gestion,
        ];
        return view ('contribution.aid_contribution', $data);
    }
        
    public function aidContributions($affiliate_id)
    {

        $affiliate = Affiliate::find($affiliate_id);
         $list = $this->getContributionDebt($affiliate->id,3);
         $data = [
            'affiliate'=>$affiliate, 
            'list' => $list
        ];
        return view ('contribution.aid_contribution', $data);
    }
    public function getAllContributionsAid (DataTables $datatables, $affiliate_id)
    //Muestra todos los aportes de auxilio mortuorio del aportante
    {
        $affiliate = Affiliate::find($affiliate_id);
        //$aid_contributions = $affiliate->aid_contributions;
        $aid_contributions = AidContribution::where('affiliate_id',$affiliate->id)->orderBy('month_year','DESC')->get();
        return $datatables->of($aid_contributions)
                        ->addIndexColumn()
                        ->addColumn('year', function($aid_contribution)
                        {
                            return Carbon::parse($aid_contribution->month_year)->year;
                        })
                        ->addColumn('month', function($aid_contribution)
                        {
                            return Carbon::parse($aid_contribution->month_year)->month;
                        })
                          ->make(true);

        $year = Carbon::parse($aid_contributions->month_year)->year;
        $month = Carbon::parse($aid_contributions->month_year)->month;
        $type = $aid_contributions->type;
        $quotable = $aid_contributions->quotable;
        $rent = $aid_contributions->rent;
        $dignity_rent = $aid_contributions->dignity_rent;
        $total = $aid_contributions->total;
        $data = [
            'affiliate' =>  $affiliate,
            'year' =>  $year,
            'month' => $month,
            'type' => $type,
            'quotable' => $quotable,
            'rent' => $rent,
            'dignity_rent' => $dignity_rent,
            'total' => $total,
        ];
        return ($data);
    }
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Muserpol\AidContribution  $aid_contribution
     * @return \Illuminate\Http\Response
     */

     public function edit(AidContribution $aidcontribution)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Muserpol\AidContribution  $aid_contribution
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contribution $contribution)
    {
        //
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \Muserpol\AidContribution  $aid_contribution
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contribution $contribution)
    {
        //
    }

    public function directContributions(Affiliate $affiliate = null){

        $commitment = AidCommitment::where('affiliate_id',$affiliate->id)->where('state','ALTA')->first();
        // if(!isset($commitment->id))
        // {            
        //     Session::flash('message','No se encontró compromiso de pago');
        //     return redirect('affiliate/'.$affiliate->id);    
        // }        
        $contributions = AidContribution::where('affiliate_id', $affiliate->id)->orderBy('month_year', 'DESC')->get();        
        //$last_contribution = AidContribution::where('affiliate_id',$affiliate->id)->orderBy('month_year','desc')->first();
        $rate = ContributionRate::where('month_year',date('Y').'-'.date('m').'-01')->first();        

        $summary = array(
            'aid' => $contributions->sum('total'),
            'total' => $contributions->sum('total'),
            'interest'  =>  $contributions->sum('interest'),
            'dateentry' => Util::getStringDate(Util::parseMonthYearDate($affiliate->date_entry))
        );

        $data = [
            'new_contributions' => $this->getContributionDebt($affiliate->id,4),            
            'commitment'    =>  $commitment,
            'affiliate' =>  $affiliate,
            'summary'   =>  $summary,
            'last_quotable' =>  $last_contribution->quotable ?? 0,
            'today_date'    =>  date('Y-m-d'),
            'rate'  =>  $rate,
        ];

        return view('contribution.affiliate_direct_aid_contribution', $data);        
    }

    public function getAffiliateContributions(Affiliate $affiliate)
    {                
        $date_derelict = $affiliate->date_derelict;                
        if(!$date_derelict){
            Session::flash('message','Verifique la fecha desvinculación del afiliado antes de continuar');
            return redirect('affiliate/'.$affiliate->id);
        }

        $affiliate_id = $affiliate->id;
        $affiliate = Affiliate::find($affiliate_id);
        // dd($affiliate);
        $contributions = AidContribution::where('affiliate_id', $affiliate->id)->orderBy('month_year', 'DESC')->get();
        $group = [];
        $aid = 0;
        foreach ($contributions as $contribution) {
            $group[$contribution->month_year] = $contribution;
            $aid = $contribution->total + $aid;
        }
        $total = $aid;
        //$dateentry = Util::getStringDate($affiliate->date_derelict);
        $dateentry = Util::parseMonthYearDate($affiliate->date_derelict);
        //return $dateentry;
        if($dateentry == NULL || $dateentry == "")
        $dateentry = "2017-01-01";
        $end = explode('-', $dateentry);
        $newcontributions = [];
        $month_end = $end[1];
        $year_end = $end[0];
        $month_start = (date('m') - 1);
        $year_start = date('Y');
        $summary = array(
            'aid' => $aid,
            'total' => $total,
            'dateentry' => $dateentry
        );
        $cities = City::all()->pluck('first_shortened', 'id');
        $cities_objects = City::all();
        $birth_cities = City::all()->pluck('name', 'id');
        //get Commitment data
        $aid_commitment = AidCommitment::where('affiliate_id', $affiliate->id)->first();
        if (!isset($aid_commitment->id)) {
            $aid_commitment = new AidCommitment();
            $aid_commitment->id = 0;
            $aid_commitment->affiliate_id = $affiliate->id;
        }
        $spouse = Spouse::where('affiliate_id', $affiliate->id)->first();               
        $data = [
            'contributions' => $group,
            'affiliate_id' => $affiliate->id,
            'year_start' => $year_start,
            'year_end' => $year_end,
            'summary' => $summary,
            'affiliate' => $affiliate,
            'cities' => $cities,
            'cities_objects' => $cities_objects,
            'birth_cities' => $birth_cities,
            'new_contributions' => $this->getContributionDebt($affiliate->id, 3),
            'aid_commitment' => $aid_commitment,
            'spouse' => $spouse,
            'today_date' => date('Y-m-d'),
        ];
        //return  date('Y-m-d');
        return view('contribution.affiliate_aid_contributions_edit', $data);
    }

    public function storeContributions(Request $request)
    {                
        //*********START VALIDATOR************//
        
        $rules = [];
        $messages = [];
        $input_data = $request->all();
        if(!empty($request->iterator))
        { 
            foreach ($request->iterator as $key => $iterator) 
            {   
                if(isset($input_data['rent'][$key]))
                    $input_data['rent'][$key]= strip_tags($request->rent[$key]);
                if(isset($input_data['dignity_rent'][$key]))
                $input_data['dignity_rent'][$key]= strip_tags($request->dignity_rent[$key]);
                
                $input_data['total'][$key]= strip_tags($request->total[$key]);
                $array_rules = [                       
                    'rent.'.$key =>  'numeric',
                    'dignity_rent.'.$key =>  'numeric|min:1',
                    'total.'.$key =>  'required|numeric|min:1'
                ];
                $rules=array_merge($rules,$array_rules);
            }
            $validator = Validator::make($input_data,$rules);
            if($validator->fails()){
                return response()->json($validator->errors(), 400);
            }
         //*********END VALIDATOR************//
        $contributions = [];
        //$this->authorize('update',new Contribution);
        foreach ($request->iterator as $key => $iterator) {
            $contribution = AidContribution::where('affiliate_id', $request->affiliate_id)->where('month_year', $key)->first();
            if (isset($contribution->id)) {
                $contribution->total = strip_tags($request->total[$key]) ?? $contribution->total;
               
                if(!isset($request->rent[$key]) || $contribution->rent == "")
                    $contribution->rent = 0;
                else
                     $contribution->rent = strip_tags($request->rent[$key]) ?? $contribution->rent;
                                
                if(!isset($request->dignity_rent[$key]) || $contribution->dignity_rent == "")
                    $contribution->dignity_rent = 0;
                else 
                    $contribution->dignity_rent = strip_tags($request->dignity_rent[$key]) ?? $contribution->dignity_rent;
                $contribution->interest = 0;
                $contribution->save();
            } else {
                $contribution = new AidContribution();
                $contribution->user_id = Auth::user()->id;
                $contribution->affiliate_id = $request->affiliate_id;
                
                if(!isset($request->rent[$key]) || $contribution->rent == "")
                    $contribution->rent = 0;
                else 
                    $contribution->rent = strip_tags($request->rent[$key]) ?? 0;
                $contribution->month_year = $key;
                
                if(!(isset($request->dignity_rent[$key])) || $contribution->dignity_rent == "")
                    $contribution->dignity_rent = 0;
                else
                    $contribution->dignity_rent = strip_tags($request->dignity_rent[$key]) ?? 0;
                $contribution->total = strip_tags($request->total[$key]) ?? 0;
                $contribution->quotable = $contribution->rent-$contribution->dinity_rent;
                $contribution->type = 'PLANILLA';
                $contribution->interest = 0;
                $contribution->save();
            }            
            array_push($contributions, $contribution);
        }
        return $contributions;
        }
    }

    public function getInterest(Request $request)
    {
        //Obtiene el interes a partir del subsiguiente mes que debe pagar. Ej. de enero corre el interes desde marzo
        $dateStart = Carbon::createFromDate($request->con['year'], $request->con['month'], '01')->addMonths(2)->format('d/m/Y');
        $dateEnd = Carbon::parse(Carbon::now()->toDateString())->format('d/m/Y');
        $rate = ContributionRate::where('month_year',date('Y').'-'.date('m').'-01')
                                ->first();
        $uri = 'https://www.bcb.gob.bo/calculadora-ufv/frmCargaValores.php?txtFecha=' . $dateStart . '&txtFechaFin=' . $dateEnd . '&txtMonto=' .($request->con['sueldo']-$request->con['dignity_rent'])/100*$rate['mortuary_aid']. '&txtCalcula=2';
        $foo = file_get_contents($uri);
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $json = '';
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //if( ($json = curl_exec($ch) ) === false)
        //return $foo;
        if( $foo === false)
        {
            Log::info("Error ".$httpcode ." ".$foo);
            return response('error', 500);
        }
        else
        {
            Log::info("Success: ".$httpcode. " ".$foo );
            $foo = [$foo,
                    $rate];
                return $foo;
        }
    }

    public function storeDirectContribution(Request $request)    
    {                
        //*********START VALIDATOR************//                
        $rules=[];                
        $biz_rules = [];
            $has_commitment = false;            
            $commitment = AidCommitment::where('affiliate_id',$request->afid)->where('state','ALTA')->first();
            
            if(!isset($commitment->id)){                
                $has_commitment = true;                                    
                $biz_rules = [
                    'has_commitment'    =>  $has_commitment?'required':'',                
                ];            
                $validator = Validator::make($request->all(),$biz_rules);
                if($validator->fails()){            
                    return response()->json($validator->errors(), 406);
                }
            }            
            $key = 0;           
            foreach ($request->aportes as $ap)
            {                            
                $aporte=(object)$ap;
                $cont = AidContribution::where('affiliate_id',$request->afid)->where('month_year',$aporte->year.'-'.$aporte->month.'-01')->first();                
                $has_contribution = false;
                if(isset($cont->id)){
                    $has_contribution = true;                    
                }                                
                $biz_rules = [
                    'has_contribution.'.$key    =>  $has_contribution?'required':'',
                ];
                
                $rules=array_merge($rules,$biz_rules);
                
                $array_rules = [
                    'aportes.'.$key.'.sueldo' =>  'required|numeric|min:0',
                    'aportes.'.$key.'.dignity_rent' =>  'min:0',                    
                    'aportes.'.$key.'.subtotal' =>  'required|numeric|min:0',
                    'aportes.'.$key.'.interes' =>  'required|numeric',
                    'aportes.'.$key.'.year' =>  'required|numeric|min:1700',
                    'aportes.'.$key.'.month' =>  'required|numeric|min:1|max:12',
                ];
                $key++;
                $rules=array_merge($rules,$array_rules);
            }            
        $rules = array_merge($rules,$biz_rules);
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){            
            return response()->json($validator->errors(), 406);
        }
         //*********END VALIDATOR************//                 
        $result = [];      
        $stored_contributions = [];
        foreach ($request->aportes as $ap)  // guardar 1 a 3 reg en contribuciones
        {
            $aporte=(object)$ap;

            if($aporte->sueldo>0)
            {
            $affiliate = Affiliate::find($request->afid);
            $aid_contribution = new AidContribution();
            $aid_contribution->user_id = Auth::user()->id;
            $aid_contribution->affiliate_id = $affiliate->id;            
            // $aid_contribution->month_year = Carbon::createFromDate($aporte->year, $aporte->month,1)."";
            $aid_contribution->month_year = $aporte->year.'-'.$aporte->month.'-01';
            $aid_contribution->type='DIRECTO';
            if(is_numeric($aporte->dignity_rent)){
                $aid_contribution->dignity_rent = $aporte->dignity_rent;
                $aid_contribution->quotable = $aporte->sueldo - $aporte->dignity_rent;
            }else{
                $aid_contribution->dignity_rent = 0;
                $aid_contribution->quotable = $aporte->sueldo;
            }
            $aid_contribution->rent = $aporte->sueldo;
            $aid_contribution->total = $aporte->subtotal;
            $aid_contribution->interest = $aporte->interes;
            $aid_contribution->save();
            array_push($result, [
                'total'=>$aid_contribution->total,
                'month_year'=>$aporte->year.'-'.$aporte->month.'-01',
                    ]);
            array_push($stored_contributions,$aid_contribution);
            }
        }
        
        $voucher_code = Voucher::select('id', 'code')->orderby('id', 'desc')->first();
        if (!isset($voucher_code->id))
            $code = Util::getNextCode(""); 
        else
            $code = Util::getNextCode($voucher_code->code);
        $voucher = new Voucher();
        $voucher->user_id = Auth::user()->id;
        $voucher->affiliate_id = $request->afid;
        $voucher->voucher_type_id = 1;//$request->tipo; 1 default as Pago de aporte directo
        $voucher->total = $request->total;
        $voucher->payment_date = Carbon::now();
        $voucher->code = $code;
        $voucher->save();
                
        $data = [
            'aid_contribution'  =>  $result,
            'aid_contributions' => $stored_contributions,
            'voucher_id'    => $voucher->id,
            'affiliate_id'  =>  $affiliate->id,
        ];
        return $data;
    }
   
    private function getContributionDebt($affiliate_id,$number){        
        $contributions = [];
        $month = date('m');
        $year = date('Y');
        while ($number--) {
            $month--;
            if ($month == 0) {
                $month = 12;
                $year--;
            }
            $year_month = $year.'-'.($month<10?'0'.$month:$month).'-01';
            $contribution = AidContribution::where('affiliate_id',$affiliate_id)->where('month_year',$year_month)->first();
            if(!isset($contribution->id))
                array_push (
                    $contributions,
                    array('year' => $year, 'month' => $month<10?'0'.$month:$month, 'monthyear' => $year_month, 'sueldo' => 0, 'auxilio_mortuorio' => 0, 'interes' => 0,'dignity_rent' => 0, 'subtotal' => 0, 'affiliate_id' => $affiliate_id)
                );
        }
        $contributions = array_reverse($contributions);       
        return $contributions;
    }
}