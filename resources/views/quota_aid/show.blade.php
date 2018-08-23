@extends('layouts.app')

@section('title', 'Cuota Mortuoria y Auxilio Mortuorio')
@section('styles')
<style>
.elements-list .list-group-item:hover{
    cursor: pointer;
}

.progressbar-container{

    /* height: 100px; */
  width: 100%;
  position: realtive;
  z-index: 1;
  display: block;
}
.progressbar li{
    float: left;
    width: 10%;
    position: relative;
    text-align: center;
}
.progressbar li:before{
  content:"1";
  width: 30px;
  height: 30px;
}
.progressbar li:before{
  content:"1";
  width: 30px;
  height: 30px;
  border: 2px solid #bebebe;
  display: block;
  margin: 0 auto 10px auto;
  border-radius: 50%;
  line-height: 27px;
  background: white;
  color: #bebebe;
  text-align: center;
  font-weight: bold;
}

.progressbar{
  counter-reset: step;
}
.progressbar li:before{
  content:counter(step);
  counter-increment: step;
  width: 30px;
  height: 30px;
  border: 2px solid #bebebe;
  display: block;
  margin: 0 auto 10px auto;
  border-radius: 50%;
  line-height: 27px;
  background: white;
  color: #bebebe;
  text-align: center;
  font-weight: bold;
}
.progressbar li:after{
  content: '';
  position: absolute;
  width:100%;
  height: 3px;
  background: #979797;
  top: 15px;
  left: -50%;
  z-index: -1;
}
.progressbar li:first-child:after{
    content: none;
}
.progressbar li.active:first-child + li.active:after{
    border-color: #3aac5d;
    background: #3aac5d;
    color: white
}
.progressbar li.active + li:after{
    /* background: #3aac5d; */
}
.progressbar li.active + li:before{
    border-color: #3aac5d;
    background: #3aac5d;
    color: white
}
</style>
@endsection
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-md-7">
        {!!Breadcrumbs::render('show_quota_aid', $quota_aid)!!}
    </div>
    <div class="col-md-5 text-center" style="margin-top:12px;">
            <div class="pull-left">
                @if(Muserpol\Helpers\Util::getRol()->id == 40)
            <button class="btn btn-primary dim" type="button" data-toggle="tooltip" data-placement="top" title="Imprimir recepción" onclick="printJS({printable:'{!! route('quota_aid_print_reception', $quota_aid->id) !!}', type:'pdf', modalMessage: 'Generando documentos de impresión por favor espere un momento.', showModal:true})"><i class="fa fa-print"></i></button>
            @endif

            @if(Muserpol\Helpers\Util::getRol()->id == 15)
            <button class="btn btn-primary dim" type="button" data-toggle="tooltip" data-placement="top" title="Imprimir Certificacion de Archivo" onclick="printJS({printable:'{!! route('ret_fun_print_file', $affiliate->id) !!}', type:'pdf', modalMessage: 'Generando documentos de impresión por favor espere un momento.', showModal:true})"><i class="fa fa-print"></i></button>
            @endif

            @if(Muserpol\Helpers\Util::getRol()->id == 14)
            <button class="btn btn-primary dim" type="button" data-toggle="tooltip" data-placement="top" title="Imprimir Dictamen Legal" onclick="printJS({printable:'{!! route('ret_fun_print_legal_dictum', $quota_aid->id) !!}', type:'pdf', modalMessage: 'Generando documentos de impresión por favor espere un momento.', showModal:true})"><i class="fa fa-print"></i></button>
            @endif

            @if(Muserpol\Helpers\Util::getRol()->id == 28)
            <button class="btn btn-primary dim" type="button" data-toggle="tooltip" data-placement="top" title="Imprimir Revisi&oacute;n de Jefatura" onclick="printJS({printable:'{!! route('ret_fun_print_headship_review', $quota_aid->id) !!}', type:'pdf', modalMessage: 'Generando documentos de impresión por favor espere un momento.', showModal:true})"><i class="fa fa-print"></i></button>
            @endif

            @if(Muserpol\Helpers\Util::getRol()->id == 29)
            <button class="btn btn-primary dim" type="button" data-toggle="tooltip" data-placement="top" title="Imprimir Resoluci&oacute;n Legal" onclick="printJS({printable:'{!! route('ret_fun_print_legal_resolution', $quota_aid->id) !!}', type:'pdf', modalMessage: 'Generando documentos de impresión por favor espere un momento.', showModal:true})"><i class="fa fa-print"></i></button>
            @endif

            @if(Muserpol\Helpers\Util::getRol()->id == 11)
            <button class="btn btn-primary dim" type="button" data-toggle="tooltip" data-placement="top" title="Imprimir Certificacion de Documentacion Presentada y Revisada" onclick="printJS({printable:'{!! route('ret_fun_print_legal_review', $quota_aid->id) !!}', type:'pdf', modalMessage: 'Generando documentos de impresión por favor espere un momento.', showModal:true})"><i class="fa fa-print"></i></button>
            @endif
            @can('view', new Muserpol\Models\Contribution\Contribution)
            <a  href="{{ url('ret_fun/'.$quota_aid->id.'/selectcontributions')}}" >
                <button class="btn btn-primary btn-sm dim"  data-toggle="tooltip" data-placement="top" title="Clasificar Aportes">
                <i class="fa fa-list-alt" style="font-size:15px"></i> Clasificar Aportes
                </button>
            </a>
            @endcan
            @can('qualify', $quota_aid)
            <a href="{{route('ret_fun_qualification', $quota_aid->id)}}">
                <button class="btn btn-info btn-sm dim" type="button" data-toggle="tooltip" data-placement="top" title="Calificacion" ><i class="fa fa-dollar" style="font-size:15px;"></i> Calificacion</button>
            </a>
            @endcan
            <span data-toggle="modal" data-target="#ModalRecordRetFun">
                <button type="button" class="btn btn-info btn-sm dim" data-toggle="tooltip" data-placement="top" title="Historial del Trámite">
                    <i class="fa fa-history" style="font-size:15px;"></i> Historial del Trámite
                </button>
            </span>
            {{-- @include('ret_fun.ret_fun_record', ['ret_fun_records' => $ret_fun_records,]) --}}
        </div>
        <div class="pull-right">
            @if ($can_validate)
        <sweet-alert-modal inline-template :doc-id="{{$quota_aid->id}}" :inbox-state="{{$quota_aid->inbox_state ? 'true' : 'false'}}" :doc-user-id="{{$quota_aid->user_id}}" :auth-id="{{ $user->id}}"  >
                    <transition name="fade" mode="out-in" :duration="300" enter-active-class="animated tada" leave-active-class="animated bounceOutRight">
                        <div v-if="status == true" key="one" data-toggle="tooltip" data-placement="top" title="Cancelar Revision del Trámite">
                            {{-- <button data-toggle="tooltip" data-placement="top" title="Trámite ya procesado" class="btn btn-primary btn-circle btn-outline btn-lg active" type="button" :disabled="! status == false " ><i class="fa fa-check"></i></button> --}}
                            <button  class="btn btn-danger btn-circle btn-outline btn-lg active" type="button" @click="cancelModal()" v-if="itisMine"><i class="fa fa-times"></i></button>
                        </div>
                        <div v-else key="two" data-toggle="tooltip" data-placement="top" title="Procesar Trámite">
                            <button class="btn btn-primary btn-circle btn-outline btn-lg" type="button" @click="confirmModal()" :disabled="! status == false " ><i class="fa fa-check"></i></button>
                        </div>
                    </transition>
                </sweet-alert-modal>
            @endif
        </div>
    </div>
</div>

{{-- <div class="row">
    <div class="col-lg-12" style="margin-top: 15px;">
        <div class="progressbar-container">
            <ul class="progressbar" style="list-style:none">
                <li class="">{{ $first_wf_state->name }}</li>
                @foreach ($wf_states as $index=>$w)
                    @if($w->sequence_number+1 <= $quota_aid->wf_state->sequence_number)
                        <li class="active">{{ $w->name }}</li>
                    @else
                        <li class="">{{ $w->name }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
</div> --}}


@if(Session::has('message'))
    <br>
    <div class="alert alert-danger alert-dismissable">
        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
        {{Session::get('message')}}
    </div>

@endif


    <div class="row">

            <div class="col-md-3" style="padding-right: 3px">
                    <div class="widget-head-color-box yellow-bg p-lg text-center">
                        <div class="m-b-md">
                        <h2 class="font-bold no-margins" data-toggle="tooltip" data-placement="top" title="Ver Affiliado ">
                        <a  href="{{route('affiliate.show', $affiliate->id)}}"  style="color: #fff"> {{ $quota_aid->affiliate->fullNameWithDegree() }}</a>
                        </h2>
                            <h3 class="text-center" data-toggle="tooltip" data-placement="top" title="Cedula de Identidad"><strong>{{  $quota_aid->affiliate->ciWithExt() }}</strong></h3>
                            <h4 class="text-center" data-toggle="tooltip" data-placement="top" title="Matricula"><strong>{{  $quota_aid->affiliate->registration }}</strong></h4>
                        </div>
                    </div>
                    <div class="widget-text-box">
                            <ul class="list-group elements-list">
                                <li class="list-group-item active" data-toggle="tab" href="#tab-ret-fun"><a href="#"><i class="glyphicon glyphicon-piggy-bank"></i> Cuota Mortuoria Y Auxilio Mortuorio</a></li>
                                @if($quota_aid->procedure_modality_id == 4)
                                <li class="list-group-item " data-toggle="tab" href="#tab-affiliate" ><a href="#"><i class="fa fa-user"></i> Affiliado </a></li>
                                @endif
                                <li class="list-group-item " data-toggle="tab" href="#tab-beneficiaries"><a href="#"><i class="fa fa-users"></i> Beneficiarios</a></li>
                                <li class="list-group-item " data-toggle="tab" href="#tab-summited-document"><a href="#"><i class="fa fa-file"></i> Documentos Presentados</a></li>
                                <li class="list-group-item " data-toggle="tab" href="#tab-folder"><a href="#"><i class="fa fa-copy"></i> Archivos</a></li>
                                <li class="list-group-item " data-toggle="tab" href="#tab-observations"><a href="#"><i class="fa fa-eye-slash"></i> Observaciones</a></li>
                            </ul>
                    </div>
                    <br>
                <tag-list :ret-fun-id="{{ $quota_aid->id }}"></tag-list>
            </div>
            <br>
            <div class="col-md-9" style="padding-left: 6px">

                    <div class="tab-content">
                            <div id="tab-ret-fun" class="tab-pane active">
                                @can('update',$quota_aid)                                
                                    <quota-aid-info :quota_aid="{{ $quota_aid }}" :rf_city_start="{{$quota_aid->city_start}}" :rf_city_end="{{$quota_aid->city_end}}" :rf_procedure_modality=" {{$quota_aid->procedure_modality}}" :states="{{ $states }}" inline-template>
                                        @include('quota_aid.info', ['quota_aid'=>$quota_aid,'cities'=>$birth_cities])
                                    </quota-aid-info>
                                @endcan
                            </div>
                            <div id="tab-affiliate" class="tab-pane">

                                    <affiliate-show  :affiliate="{{ $affiliate }}" :cities="{{$cities}}" inline-template>
                                        @include('affiliates.affiliate_personal_information',['affiliate'=>$affiliate,'cities'=>$cities_pluck,'birth_cities'=>$birth_cities,'is_editable'=>$is_editable])
                                    </affiliate-show>

                            </div>
                            <div id="tab-beneficiaries" class="tab-pane">

                                    {{-- @can('view',new Muserpol\Models\RetirementFund\RetFunBeneficiary) --}}
                                        @include('quota_aid.beneficiaries_list', ['beneficiaries'=>$beneficiaries,'cities'=>$cities,'kinships'=>$kinships])
                                    {{-- @endcan --}}

                            </div>
                            <div id="tab-summited-document" class="tab-pane">

                                    {{-- @can('view',new Muserpol\Models\RetirementFund\RetFunSubmittedDocument) --}}
                                        {{-- @include('ret_fun.legal_review', ['affiliate'=>$affiliate,'quota_aid'=>$quota_aid,'documents'=>$documents])                                 --}}
                                <quota-aid-step1-requirements-edit 
                                    :quota_aid="{{ $quota_aid }}" 
                                    :modalities="{{ $modalities }}" 
                                    :requirements="{{ $requirements }}" 
                                    :user="{{ $user }}" 
                                    :cities="{{ $cities }}" 
                                    :procedure-types="{{$procedure_types}}" 
                                    :submitted="{{$submit_documents}}" 
                                    :rol="{{Muserpol\Helpers\Util::getRol()->id}}"
                                inline-template>

                                    @include('quota_aid.step1_requirements_edit')

                                </quota-aid-step1-requirements-edit>
                                    {{-- @endcan --}}

                            </div>

                            <div id="tab-folder" class="tab-pane">

                                    {{-- @can('view',new Muserpol\Models\AffiliateFolder)
                                        @include('affiliates.folder', ['folders'=>$affiliate->affiliate_folders,'procedure_modalities'=>$procedure_modalities,'affiliate_id'=>$affiliate->id])
                                    @endcan --}}

                            </div>
                            <div id="tab-observations" class="tab-pane">
                                    {{-- @include('ret_fun.observation') --}}
                            </div>

                        </div>

            </div>

    </div>
    <br>

@endsection

@section('styles')
<link rel="stylesheet" href="{{asset('/css/datatable.css')}}">
@endsection

@section('jss')
<script src="{{ asset('/js/datatables.js')}}"></script>
<script>
    $( document ).ready(function() {
        $('#folderDialog').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget)
            var id=button.data('id')
            var cod_folder = button.data('codfile')
            var num_folder = button.data('folnum')
            var moda_id =button.data('modid');
            var note = button.data('note');
            var is_paid = button.data('ispaid');

            var modal = $(this)
            $('#id_folder').val(id)
            //revisar esta parte con el nuevo disenio
            //if(typeof(is_paid) === "boolean"){
                if(is_paid == true){
                    $(".modal-body #paid").prop("checked", true);
                }
                if(is_paid == false){
                    $(".modal-body #nopaid").prop("checked", true);
                }
            //}

            modal.find('.modal-body #cod_folder').val(cod_folder)
            modal.find('.modal-body #num_folder').val(num_folder)
            modal.find('.modal-body #note').val(note)
            // console.log($('#mod_id').val(moda_id))
        });
        $('#eliminar').on('show.bs.modal', function (event) {
            var modal = $(this)
            var button = $(event.relatedTarget)
            // console.log('metodo 2')
            var folder_id = button.data('elim')
            // console.log($('#cod_file_eli').val(cod_folder))
            modal.find('.modal-header #folder_id').val(folder_id)
        });
        // console.log( "del show... " );
        
    });
</script>
@endsection