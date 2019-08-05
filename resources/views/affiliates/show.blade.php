@extends('layouts.app')

@section('title', 'Afiliados')
@section('styles')
<style>
#fixedheight {
    table-layout: fixed;
}

#fixedheight td {
    width: 25%;
}

#fixedheight td div {
    height: 20px;
    overflow: hidden;
    text-overflow: ellipsis;

}
th.ellipsis-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.table-hover>tbody>tr:hover {
        background-color: #DBDBDB
    }
/* .table-striped-1>tbody>tr:nth-child(2n+1){background-color:#f2f2f2} */
</style>
<link rel="stylesheet" href="{{asset('/css/datatables.css')}}">
@endsection
@section('content')
<div class="row  wrapper border-bottom white-bg page-heading">
    <div class="col-lg-7">
        {{ Breadcrumbs::render('show_affiliate', $affiliate) }}
    </div>
    <div class="col-lg-5" style="margin-top:12px;">
        @can('create', new Muserpol\Models\RetirementFund\RetirementFund)
            @if($has_ret_fun)
                <a href="#" id="disabled-button-wrapper" class="tooltip-wrapper disabled" data-toggle="tooltip" data-placement="top" title="El Afiliado ya tiene un tr&aacute;mite de Fondo de Retiro">
                    <button class="btn btn-info btn-sm  dim" type="button"  disabled><i class="fa fa-paste"></i> </button>
                </a>
            @else
                <a href="{{route('create_ret_fun', $affiliate->id)}}">
                    <button class="btn btn-info btn-sm  dim" type="button" data-toggle="tooltip" data-placement="top" title="Iniciar tr&aacute;mite de FONDO DE RETIRO"><i class="fa fa-paste"></i> </button>
                </a>
            @endif
        @endcan
        @can('create', new Muserpol\Models\QuotaAidMortuary\QuotaAidMortuary)
            <a href="{{route('create_quota_aid', $affiliate->id)}}">
                <button class="btn btn-warning btn-sm  dim" type="button" data-toggle="tooltip" data-placement="top" title="Iniciar tr&aacute;mite de Cuota y Auxilio Morturorio"><i class="fa fa-heartbeat" style="font-size:15px;"></i> </button>
            </a>
        @endcan
        @can('create', new Muserpol\Models\Contribution\ContributionProcess)            
            @if($has_direct_contribution)
                <a href="#" id="disabled-button-wrapper" class="tooltip-wrapper disabled" data-toggle="tooltip" data-placement="top" title="El Afiliado ya tiene un tr&aacute;mite de Aportes directos">
                    <button class="btn btn-warning btn-sm  dim" type="button"  disabled><i class="fa fa-paste"></i> </button>
                </a>
            @else
                <a href="{{route('create_direct_contribution', $affiliate->id)}}">
                    <button class="btn btn-warning btn-sm  dim" type="button" data-toggle="tooltip" data-placement="top" title="Iniciar tr&aacute;mite de Aportes"><i class="fa fa-dollar" style="font-size:15px;"></i> </button>
                </a>
            @endif
        @endcan
        @can('create', new Muserpol\Models\Voucher)
            @foreach ($voucher_types as $voucher_type)
            <button class="btn btn-info btn-sm  dim" type="button" href="#tab-charge{{$voucher_type->id}}" data-toggle="tab" data-placement="top" title="{{ $voucher_type->name }}"><i class="fa fa-money"></i> {{ $voucher_type->name }}</button>
            @endforeach            
        @endcan

        {{-- @if('create', new Muserpol\Models\ChargeType) --}}
        {{-- @can('view',new Muserpol\Models\Contribution\Contribution)
        <a href="{{route('show_contribution', $affiliate->id)}}" >
            <button class="btn btn-info btn-sm  dim" type="button" data-toggle="tooltip" data-placement="top" title="Ver Aportes"><i class="fa fa-dollar"> </i> APORTES ACTIVO </button>
        </a>
        <a href="{{route('show_aid_contribution', $affiliate->id)}}" >
            <button class="btn btn-info btn-sm  dim" type="button" data-toggle="tooltip" data-placement="top" title="Aportes Auxilio Mortuorio"><i class="fa fa-dollar"> </i> APORTES PASIVO </button>
        </a>
        @endcan --}}
        <span data-toggle="modal" data-target="#ModalRecord">
            <button type="button" class="btn btn-info btn-sm dim" data-toggle="tooltip" data-placement="top" title="Historial del Afiliado">
                <i class="fa fa-history" style="font-size:15px;"></i> HISTORIAL
            </button>
        </span>
        @include('affiliates.affiliate_record', ['affiliate_records'=>$affiliate_records])
    </div>
</div>
@if(Session::has('message'))
    <br>
    <div class="alert alert-danger alert-dismissable">
        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
        {{Session::get('message')}}
    </div>

@endif
<div class="row">

    <div class="col-md-3" style="padding-right: 3px">
            <div class="widget-head-color-box navy-bg p-sm text-center">
                <div class="m-b-md">
                    <div class="row m-l-sm">
                        <h3 class="pull-left" data-toggle="tooltip" data-placement="top" title="Codigo único de Afiliado"><strong>{{  $affiliate->id }}</strong></h3>
                    </div>
                    <h2 class="font-bold no-margins link-affiliate" data-toggle="tooltip" data-placement="top" title="Ir al afiliado ">
                        <a  href="{{route('affiliate.show', $affiliate->id)}}" class="link-affiliate"> {{ $affiliate->fullNameWithDegree() }}</a>
                    </h2>
                    <h3 class="text-center" data-toggle="tooltip" data-placement="top" title="Cédula de Identidad"><strong>{{  $affiliate->ciWithExt() }}</strong></h3>
                    <h4 class="text-center" data-toggle="tooltip" data-placement="top" title="Matricula"><strong>{{  $affiliate->registration }}</strong></h4>
                </div>
            </div>
            <div class="widget-text-box">
                    <ul class="list-group elements-list">
                        <li class="list-group-item active" data-toggle="tab" href="#tab-affiliate"><a href="#"><i class="fa fa-address-book"></i> Información Personal </a></li>
                        <li class="list-group-item " data-toggle="tab" href="#tab-police-info"><a href="#"><i class="fa fa-address-card"></i> Información Policial </a></li>
                        <li class="list-group-item " data-toggle="tab" href="#tab-spouse-info"><a href="#"><i class="fa fa-user"></i> Información de Conyuge </a></li>
                        <li class="list-group-item " data-toggle="tab" href="#tab-contributions"><a href="#" ><i class="fa fa-money "></i> Aportes</a></li>
                        <li class="list-group-item " data-toggle="tab" href="#tab-documents-scanned"><a href="#" ><i class="fa fa-upload"></i> Documentos Escaneados</a></li>
                        <li class="list-group-item " data-toggle="tab" href="#tab-ret-fun"><a href="#"><i class="{{ Util::IconModule(3)}}"></i> Fondo de Retiro</a></li>
                        <li class="list-group-item " data-toggle="tab" href="#tab-quota-aid-mortuory"><a href="#"><i class="{{ Util::IconModule(4)}}"></i> Cuota y Auxilio Mortuorio</a></li>
                        <li class="list-group-item " data-toggle="tab" href="#tab-direct-contribution"><a href="#"><i class="{{ Util::IconModule(11)}}"></i> Aportes directos</a></li>
                        <li class="list-group-item " data-toggle="tab" href="#tab-eco-com"><a href="#"><i class="{{ Util::IconModule(2)}}"></i> Complemento Económico</a></li>
                        {{-- <li class="list-group-item " data-toggle="tab"><a href="#tab-aid-mortuory"><i class="{{ Util::IconModule(5)}}"></i> Auxilio Mortuorio </a></li> --}}
                        <li class="list-group-item " data-toggle="tab" href="#tab-devolutions"><a href="#"><i class="fa fa-balance-scale"></i> Devoluciones</a></li>
                        <li class="list-group-item " data-toggle="tab" href="#tab-observations"><a href="#"><i class="fa fa-eye-slash"></i> Observaciones</a></li>
                        <li class="list-group-item tab-affiliate-records" data-toggle="tab" href="#tab-affiliate-records"><a href="#"><i class="fa fa-eye-slash"></i> Historial</a></li>

                    </ul>
            </div>
            <tag-list :doc-id="{{ $affiliate->id }}" type="affiliate"></tag-list>
    </div>
    <br>
    <div class="col-md-9" style="padding-left: 6px">

            <div class="tab-content">         
                    <div id="tab-affiliate" class="tab-pane active">
                        <affiliate-show  :affiliate="{{ $affiliate }}" :cities="{{ $cities }}" inline-template>
                            @include('affiliates.affiliate_personal_information',['affiliate'=>$affiliate,'cities'=>$cities,'birth_cities'=>$birth_cities,'is_editable'=>$is_editable])
                        </affiliate-show>

                    </div>
                    <div id="tab-police-info" class="tab-pane">

                        <affiliate-police :affiliate="{{ $affiliate }}" :categories="{{$categories_1}}" inline-template>
                            @include('affiliates.affiliate_police_information', ['affiliate'=>$affiliate])
                        </affiliate-police>

                    </div>
                    <div id="tab-spouse-info" class="tab-pane">

                        <spouse-show :spouse="{{ $spouse }}" :affiliate-id="{{ $affiliate->id }}" :cities="{{ $cities }}" inline-template>
                            @include('spouses.spouse_personal_information', ['spouse'=>$spouse])
                        </spouse-show>

                    </div>
                    <div id="tab-contributions" class="tab-pane">
                        @include('contribution.affiliate_contribution_show',
                        [
                            'contributions' =>  $contributions,
                            'month_end' =>  $month_end,
                            'month_start'  =>   $month_start,
                            'year_end'  =>  $year_end,
                            'year_start'    =>  $year_start
                        ])
                        
                        @include('contribution.affiliate_aid_contribution_show',
                        [
                            'contributions' =>  $aid_contributions,
                            'month_end' =>  $month_death,
                            'month_start'  =>   $month_end,
                            'year_end'  =>  $year_death,
                            'year_start'    =>  $year_end
                        ])
                    </div>
                    <div id="tab-documents-scanned" class="tab-pane">
                        @include('affiliates.scanned_documents',['affiliate'=>$affiliate,'scanned_documents'=>$affiliate->scanned_documents])
                    </div>
                    <div id="tab-ret-fun" class="tab-pane">
                        @can('update',$retirement_fund)
                            <ret-fun-info :retirement_fund="{{ $retirement_fund }}" :rf_city_start="{{$retirement_fund->city_start}}" :rf_city_end="{{$retirement_fund->city_end}}" :rf_procedure_modality=" {{$retirement_fund->procedure_modality}}" :states="{{ $states }}" :read="true" inline-template>
                                @include('ret_fun.info', ['retirement_fund'=>$retirement_fund,'cities'=>$birth_cities])
                            </ret-fun-info>
                        @endcan
                    </div>
                    <div id="tab-eco-com" class="tab-pane">
                        <div class="ibox">
                            <div class="ibox-title">
                                <h2 class="pull-left">Trámites de Complemento Economico</h2>
                                <div class="ibox-tools">
                                    <button class="btn btn-primary dim"
                                            type="button"
                                            data-toggle="tooltip"
                                            data-placement="top"
                                            title="Imprimir Certificacion de pagos realizados"
                                            onclick='printJS({printable: "{{route("eco_com_print_certification_all_eco_coms", [$affiliate->id])}}", type: "pdf", modalMessage: "Generando documentos de impresión, por favor espere un momento.", showModal: true})'
                                    >
                                    <i class="fa fa-print"></i>
                                    Imprimir
                                    </button>
                                </div>
                            </div>
                            <div class="ibox-content">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr class="success">
                                            <th># de Trámite</th>
                                            <th>Gestion</th>
                                            <th>Fecha de Ingreso del Trámite</th>
                                            <th>Modalidad</th>
                                            <th>Ubicación</th>
                                            <th>Estado</th>
                                            <th>Total</th>
                                            <th>Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($eco_coms as $eco_com)
                                                <tr>
                                                <td>{{$eco_com->code}}</td>
                                                <td>{{$eco_com->eco_com_procedure->fullName() }}</td>
                                                <td>{{$eco_com->reception_date }}</td>
                                                <td>{{$eco_com->eco_com_modality->procedure_modality->name }}</td>
                                                <td>{{$eco_com->wf_state->first_shortened }}</td>
                                                <td>{{$eco_com->eco_com_state->name }}</td>
                                                <td>{{Util::formatMoney($eco_com->total)}}</td>
                                                <td style="vertical-align:middle">
                                                    <a href="/eco_com/{{$eco_com->id}}">
                                                        <button class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></button>
                                                    </a>
                                                </td>
                                                </tr>
                                                @if ($eco_com->discount_types->count() > 0)
                                                    <tr class="danger">
                                                        <td colspan="2" rowspan="{{ $eco_com->discount_types->count() + 1  }}" >
                                                        </td>
                                                        <td colspan="2" rowspan="{{ $eco_com->discount_types->count() + 1  }}" style="vertical-align:middle">
                                                            <strong>
                                                                Amortizaciones
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                    @foreach ($eco_com->discount_types as $d)
                                                    <tr class="danger">
                                                        <td colspan="2">{{$d->name}}</td>
                                                        <td>{{Util::formatMoney($d->pivot->amount)}}</td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="tab-quota-aid-mortuory" class="tab-pane">
                        @can('update',$quota_aid)
                        <quota-aid-info :quota_aid="{{ $quota_aid }}" :rf_city_start="{{$quota_aid->city_start}}" :rf_city_end="{{$quota_aid->city_end}}"
                            :rf_procedure_modality=" {{$quota_aid->procedure_modality}}" :states="{{ $states }}" :read="true" inline-template>
                            @include('quota_aid.info', ['quota_aid'=>$quota_aid,'cities'=>$birth_cities])
                        </quota-aid-info>
                        @endcan
                    </div>
                    @if(isset($direct_contribution->id))
                    <div id="tab-direct-contribution" class="tab-pane">
                        {{-- @can('update',$direct_contribution) --}}                        
                            <direct-contribution-info 
                                :direct_contribution="{{ $direct_contribution }}" 
                                :city="{{json_encode($direct_contribution->city_id)}}"
                                :procedure_modality="{{$direct_contribution->procedure_modality}}" 
                                :states="{{ $states }}" :read="true"  inline-template>
                                @include('direct_contributions.info', ['direct_contribution'=>$direct_contribution,'cities'=>$birth_cities])
                            </direct-contribution-info>
                        {{-- @endcan --}}
                    </div>
                    @endif
                    {{-- <div id="tab-aid-mortuory" class="tab-pane"> //auxilio mortuorio



                    </div> --}}
                    @can('create', new Muserpol\Models\Voucher)
                        @foreach ($voucher_types as $voucher_type)
                            <div id="tab-charge{{$voucher_type->id}}" class="tab-pane">
                                <generate-charge
                                    :payment_types = "{{ $payment_types }}"
                                    :affiliate_id = "{{ $affiliate->id }}"
                                    :vouchers = "{{ $vouchers }}"
                                    :voucher_type = "{{ $voucher_type  }}"
                                ></generate-charge>
                            </div>
                        @endforeach                        
                    @endcan
                    {{-- <div class="row">
                        <div class="col-lg-12">
                            <div class="ibox">
                                <div class="ibox-title">                    
                                        <direct-contribution-payment
                                            :contribution_process="{{ $contribution_process }}"
                                            :voucher = "{{ $voucher }}"
                                            :payment_types = "{{ $payment_types }}"             
                                        ></direct-contribution-payment>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    

                    <div id="tab-observations" class="tab-pane">
                        <affiliate-observations :affiliate="{{ $affiliate }}" :permissions="{{ $permissions }}" :observation-types="{{ $observation_types }}"></affiliate-observations>
                    </div>
                    <div id="tab-devolutions" class="tab-pane">
                        <affiliate-devolutions :affiliate="{{ $affiliate }}" :permissions="{{ $permissions }}"></affiliate-devolutions>
                    </div>
                    <div id="tab-affiliate-records" class="tab-pane">
                        <affiliate-record :affiliate="{{ $affiliate }}" :permissions="{{ $permissions }}"></affiliate-record>
                    </div>



                </div>

    </div>

</div>



{{-- <div class="wrapper wrapper-content animated fadeInRight">

    <div class="row">
        <div class="col-md-6">
            <affiliate-show  :affiliate="{{ $affiliate }}" inline-template>
                   @include('affiliates.affiliate_personal_information',['affiliate'=>$affiliate,'cities'=>$cities,'birth_cities'=>$birth_cities])
            </affiliate-show>
        </div>
        <div class="col-md-6">
            <affiliate-police :affiliate="{{ $affiliate }}" inline-template>
                @include('affiliates.affiliate_police_information', ['affiliate'=>$affiliate])
            </affiliate-police>
        </div>
        <div class="col-md-6">
            @include('affiliates.information_of_the_procedure')
        </div>
    </div>

</div> --}}

@endsection

@section('styles')
<link rel="stylesheet" href="{{asset('/css/datatable.css')}}">
<style>
    .elements-list .list-group-item:hover{ cursor: pointer; }
</style>
@endsection
@section('jss')
<script src="{{ asset('/js/datatables.js')}}"></script>
<script>
$(document).ready(function() {

    function moneyInputMask() {

            return {
                alias: "numeric",
                groupSeparator: ",",
                autoGroup: true,
                digits: 2,
                digitsOptional: false,
                placeholder: "0"
            };
        }
        $('.numberformat').each(function(i, obj) {
            Inputmask(moneyInputMask()).mask(obj);
        });
    //revisar dependecias XD
    //revisar dependecias XD
    // $('.file-box').each(function() {
    //     animationHover(this, 'pulse');
    // });
} );
</script>
@endsection
