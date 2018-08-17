@extends('layouts.app') 
@section('title', 'Contribuciones') 
@section('styles')
<style>
    .disableddiv {
        pointer-events: none;
        opacity: 0;
    }

    .size-13 {
        font-size: 13px;
    }

    .table-striped-1>tbody>tr:nth-child(4n+1) {
        background-color: #f2f2f2
    }

    .table-hover>tbody>tr:hover {
        background-color: #DBDBDB
    }
</style>
@endsection
 
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        {{ Breadcrumbs::render('edit_affiliate_contributions', $affiliate) }}
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    {{--
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-6">
    @include('contribution.aditional_info',['summary',$summary])
            </div>

            <div class="col-md-6">
    @include('contribution.commitment',['commitment'=>$commitment,'affiliate_id'=>$affiliate_id,'today_date'=>$today_date])
            </div>
        </div>
    </div> --}}
    @if(Muserpol\Helpers\Util::getRol()->id != 36)
    <div class="row">
        <div class="col-md-12 my-content sk-loading">
            <div class="cont">
                <div class="sk-folding-cube">
                    <div class="sk-cube1 sk-cube"></div>
                    <div class="sk-cube2 sk-cube"></div>
                    <div class="sk-cube4 sk-cube"></div>
                    <div class="sk-cube3 sk-cube"></div>
                </div>
            <form>
                <input type="hidden" name="affiliate_id" id="affiliate_id" value="{{$affiliate_id}}">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Aportes</h5>
                        <div class="ibox-tools">
                            {{-- <a class="collapse-link"><i class="fa fa-chevron-up"></i></a> --}}
                            <a class="fullscreen-link" data-toggle="tooltip" data-placement="bottom" title="Pantalla completa">Pantalla Completa <i class="fa fa-expand"></i></a>
                        </div>
                    </div>
                    <div class="ibox-content table-responsive">
                        <table class="table table-striped-1 table-bordered table-hover size-13">
                            <thead>
                                <tr>
                                    <th>A&ntilde;o</th>
                                    <th>Enero</th>
                                    <th>Febrero</th>
                                    <th>Marzo</th>
                                    <th>Abril</th>
                                    <th>Mayo</th>
                                    <th>Junio</th>
                                    <th>Julio</th>
                                    <th>Agosto</th>
                                    <th>Septiembre</th>
                                    <th>Octubre</th>
                                    <th>Noviembre</th>
                                    <th>Diciembre</th>
                                    <th>Editar</th>
                                </tr>
                            </thead>
                            <tbody>
                            @for(;$year_start>=$year_end;$year_start--)
                                <tr>
                                    <td> {{ $year_start }} </td>
                                    @for($i=1;$i<13;$i++)
                                        @php $period=$year_start. '-'.($i<10? '0'.$i:$i). '-01';
                                            $valid_period=true;
                                            if((date( 'Y')==$year_start && date( 'm')<=$i) || ($year_start==1976 && $i<=4) || ($year_start<1976))
                                                $valid_period=false; 
                                        @endphp
                                        @if($valid_period)
                                            @if(isset($contributions[$period]->id))
                                                <td class="numberformat" id="main{{$period}}">{{$contributions[$period]->total}}</td>
                                            @else
                                                <td class="numberformat" id="main{{$period}}">0</td>
                                            @endif
                                        @else
                                            <td>-</td>
                                        @endif
                                    @endfor
                                    <td>
                                        <button class="btn btn-default" type="button" data-toggle="tooltip" data-placement="top" title="Editar" onclick="toggleNestedComp(this)"><i class="fa fa-pencil"></i></button>
                                    </td>
                                </tr>
                                <tr class="tabl2" style="display:none;">
                                    <td>
                                        <table class="table table-striped table-bordered size-13">
                                            <tr>
                                                <td>Sueldo</td>
                                            </tr>
                                            <tr>
                                                <td>Antiguidad</td>
                                            </tr>
                                            <tr>
                                                <td>Categor&iacute;a</td>
                                            </tr>
                                            <tr>
                                                <td>Cotizable</td>
                                            </tr>
                                            <tr>
                                                <td>Aporte</td>
                                            </tr>
                                            <tr>
                                                <td>Reintegro</td>
                                            </tr>
                                        </table>
                                    </td>
                                    @for($i=1;$i<13;$i++)
                                        @php
                                            $period=$year_start. '-'.($i<10? '0'.$i:$i). '-01';
                                            $valid_period=true;
                                            if((date( 'Y')==$year_start && date( 'm')<=$i) || ($year_start==1976 && $i<=4) || ($year_start<1976))
                                                $valid_period=false;
                                        @endphp
                                        @if($valid_period)
                                            @if(isset($contributions[$period]->id))
                                                <td>
                                                    <table class="table table-striped table-bordered size-13">
                                                        <thead style="display: none">
                                                            <tr>
                                                                <td>
                                                                    <input type="hidden" disabled name="iterator[{{$period}}]" value="{{$contributions[$period]->id}}">
                                                                </td>
                                                            </tr>
                                                        </thead>
                                                        <tr>
                                                            <td>
                                                                <div contenteditable="true" class="editcontent numberformat">{{$contributions[$period]->base_wage}} </div>
                                                                <input type="hidden" disabled name="base_wage[{{$period}}]" value="{{$contributions[$period]->base_wage}}">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div contenteditable="true" class="editcontent numberformat seniority_bonus">{{$contributions[$period]->seniority_bonus}} </div>
                                                                <input type="hidden" disabled name="seniority_bonus[{{$period}}]" value="{{$contributions[$period]->seniority_bonus}}">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                {{-- <select class="" name="category[{{$period}}]">
                                                                    @foreach($categories as $category)
                                                                        <option value="{{$category->id}}" @if($category->id == $contributions[$period]->category_id) SELECTED @endif >{{$category->percentage}}</option>
                                                                    @endforeach
                                                                </select> --}}

                                                                <div contenteditable="false" class="numberformat">{{$contributions[$period]->category->percentage}} </div>
                                                                <input type="hidden" disabled name="category[{{$period}}]" value="{{$contributions[$period]->percentage}}">
                                                            </td>
                                                        </tr>                                                        
                                                        <tr>
                                                            <td>
                                                                <div contenteditable="true" class="editcontent numberformat">{{$contributions[$period]->gain}} </div>
                                                                <input type="hidden" disabled name="gain[{{$period}}]" value="{{$contributions[$period]->gain}}">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div contenteditable="true" class="editcontent numberformat">{{$contributions[$period]->total ?? '-'}} </div>
                                                                <input type="hidden" disabled name="total[{{$period}}]" value="{{$contributions[$period]->total??'-'}}">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="numberformat" id="reim{{$period}}">
                                                                {{$reims[$period]->total ?? '-'}}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            @else
                                                <td>
                                                    <table class="table table-bordered table-striped size-13">
                                                        <thead style="display: none">
                                                            <tr>
                                                                <td>
                                                                    <input type="hidden" disabled name="iterator[{{$period}}]" value="0">
                                                                </td>
                                                            </tr>
                                                        </thead>
                                                        <tr>
                                                            <td>
                                                                <div contenteditable="true" class="editcontent numberformat">0</div>
                                                                <input type="hidden" disabled name="base_wage[{{$period}}]" value="0">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div contenteditable="true" class="editcontent numberformat seniority_bonus">0</div>
                                                                <input type="hidden" disabled name="seniority_bonus[{{$period}}]" value="0">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            {{-- <td>
                                                                <select class="" name="category[{{$period}}]">
                                                                    @foreach($categories as $category)
                                                                        <option value="{{$category->id}}">{{$category->percentage}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td> --}}
                                                            <td>
                                                                <div contenteditable="false" class="numberformat">0</div>
                                                                <input type="hidden" disabled name="category[{{$period}}]" value="0">
                                                            </td>
                                                        </tr>                                                        
                                                        <tr>
                                                            <td>
                                                                <div contenteditable="true" class="editcontent numberformat">0</div>
                                                                <input type="hidden" disabled name="gain[{{$period}}]" value="0">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div contenteditable="true" class="editcontent numberformat">0</div>
                                                                <input type="hidden" disabled name="total[{{$period}}]" value="0">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td id="reim{{$period}}}">-</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            @endif
                                        @else
                                            <td></td>
                                        @endif
                                    @endfor
                                        <td>
                                            <button class="btn btn-default" data-toggle="tooltip" data-placement="top" type="button" title="Reintegro" onclick="createReimbursement({{$year_start}})"><i class="fa fa-dollar"></i></button>
                                            <button class="btn btn-default" data-toggle="tooltip" data-placement="left" type="button" title="Guardar" onclick="storeData(this)"><i class="fa fa-save"></i></button>
                                        </td>
                                </tr>
                            @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>
    @endif
</div>
<div class="modal inmodal" id="reimbursement_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
                <h4 class="modal-title">Reintegro</h4>
            </div>
            <div class="modal-body">
                <div class="form-group"><label>Mes</label>
                    <select class="form-control" name="month" id="month">
                        <option value="01">Enero</option>
                        <option value="02">Febrero</option>
                        <option value="03">Marzo</option>
                        <option value="04">Abril</option>
                        <option value="05">Mayo</option>
                        <option value="06">Junio</option>
                        <option value="07">Julio</option>
                        <option value="08">Agosto</option>
                        <option value="09">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                     </select>
                </div>
                <div class="form-group">
                    <label>Sueldo</label>
                    <input id="reim_salary" name="reim_salary" type="text" placeholder="Sueldo" class="form-control">
                    <label>Categor&iacute;a</label>
                    <select class="form-control" name="reim_category" id="reim_category">
                        @foreach($categories as $category)
                            <option value="{{$category->id}}">{{$category->percentage}}</option>
                        @endforeach
                    </select>
                    <label>Total Ganado</label>
                    <input id="reim_gain" name="reim_gain" type="text" placeholder="Total ganado" class="form-control">
                    <label>Aporte</label>
                    <input id="reim_amount" name="reim_amount" type="text" placeholder="Aporte" class="form-control numberformat">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cerrar</button>
                <!--<button type="submit" class="btn btn-primary">Guardar</button>-->
                <button class="btn btn-default" type="button" title="Guardar" onclick="storeReimbursement(this)">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
@endsection
 
@section('scripts')
<script>
    $('.numberformat').each(function(i, obj) {
            Inputmask(moneyInputMask()).mask(obj);        
    });    
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
    
    $('body').addClass("mini-navbar");
    var actual_year = "1700";
    function toggleNestedComp(mode){
        $(mode).closest('tr').next().toggle();
        $(mode).closest('tr').toggleClass('warning');
        $(mode).closest('tr').next().toggleClass('warning');
    }
    function storeData(button){
        $(button).closest('tr').toggle();
        $(button).closest('tr').toggleClass('warning');
        $(button).closest('tr').prev().toggleClass('warning');
        var formdata = $('form').serialize();
        console.log(formdata);
        $.ajax({
            url: "{{asset('store_contributions')}}",
            method: "POST",
            data: formdata,
            beforeSend: function (xhr, settings) {
                if (settings.url.indexOf(document.domain) >= 0) {
                    xhr.setRequestHeader("X-CSRF-Token", "{{csrf_token()}}");
                }
            },
            success: function(result){
                //console.log(result);
                
                 $.each(result, function(index,value){
                    $('#main'+value.month_year).html(value.total);                    
                 });                                  
                 flash('exito');
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                var resp = jQuery.parseJSON(xhr.responseText);
                $.each(resp, function(index, value)
                {                    
                    flash(value,'error',10000);
                });                            
            }
        });
    }
function rei(){

}
$('.editcontent').blur(function() {
    $(this).next('input').val(parseFloat($(this).html().replace(/,/g , '')));
    $(this).next('input').removeAttr('disabled');
    $(this).closest('table').find('tr:first').find('td:first').find('input').removeAttr('disabled');
});
function createReimbursement(year){
    //alert(year);
    this.actual_year = year;
    $('#reimbursement_modal').modal('show');
}
function storeReimbursement(){
    year = this.actual_year;
    month = $('#month').val();
    salary = $('#reim_salary').val();
    category = $('#reim_category').val();
    gain = $('#reim_gain').val();
    total =  $('#reim_amount').val();
    affiliate_id = $("#affiliate_id").val();
    $.ajax({
        url: "{{asset('reimbursement')}}",
        method: "POST",
        data: {affiliate_id:affiliate_id,year:year,month:month,salary:salary,category:category,gain:gain,total:total},
        beforeSend: function (xhr, settings) {
            if (settings.url.indexOf(document.domain) >= 0) {
                xhr.setRequestHeader("X-CSRF-Token", "{{csrf_token()}}");
            }
            //console.log($(button).closest('form').serialize());
        },
        success: function(result){
            $("#reim"+result.month_year).html(result.total);
        },
        error: function(xhr, status, error) {
            console.log(xhr.responseText);
        }
    });

    $('#reimbursement_modal').modal('hide');

}
function setPeriodData(period,amount){
    alert(period+' - '+amount);
    $('#main'+period).html(amount);
}
$('.seniority_bonus').blur(function() {
    console.log("im here"+$(this).html());    
    //$(this).closest('td').prev().html('3333');
    base_wage = parseFloat($(this).parent().parent().prev().find('td:first').find('div:first').text().replace(/,/g , ''));
    extra = parseFloat($(this).text().replace(/,/g , ''));
    console.log(base_wage+" "+extra);
    total = (extra*100)/base_wage/100;    
    //$(this).closest('td').closest('tr').next('tr').find('td:first').find('div:first').text();
    $(this).closest('td').closest('tr').next('tr').find('td:first').find('input:first').removeAttr('disabled');
    $(this).closest('td').closest('tr').next('tr').find('td:first').find('input:first').val(total);
    $(this).closest('td').closest('tr').next('tr').find('td:first').find('div:first').val(total);
});
$(document).ready(function() {
    $('.sk-folding-cube').hide();
    $('.my-content').removeClass('sk-loading')
});

</script>
@endsection