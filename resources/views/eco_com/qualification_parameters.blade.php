@extends('layouts.app')
@section('title', 'Parametros para la calificaci&oacuten')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-md-7">
        {!!Breadcrumbs::render('eco_com_qualification_parameters')!!}
    </div>
    <div class="col-md-5 text-center" style="margin-top:12px;">
        <div class="pull-left">
        </div>
        <div class="pull-right">
            <div class="form-inline">
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3" style="padding-right: 3px">
        <div class="widget-head-color-box blue-bg p-lg text-center">
            <div class="m-b-md">
                <h2 class="font-bold no-margins" data-toggle="tooltip" data-placement="top"
                    title="Parametros para la calificacion">
                    Parametros para la calificaci&oacuten
                </h2>
            </div>
        </div>
        <div class="widget-text-box">
            <ul class="list-group elements-list">
                <li class="list-group-item active" data-toggle="tab" href="#tab-base-wage"><a href="#"><i
                            class="fa fa-puzzle-piece"></i> Sueldos</a></li>
                <li class="list-group-item " data-toggle="tab" href="#tab-eco-com-averages"><a href="#"><i
                            class="fa fa-address-card"></i> Promedios</a></li>
                <li class="list-group-item " data-toggle="tab" href="#tab-complementary-factor"><a href="#"><i
                            class="fa fa-users"></i> Factor de Complementaci&oacuten</a></li>
                <li class="list-group-item " data-toggle="tab" href="#tab-eco-com-procedure"><a href="#"><i
                            class="fa fa-users"></i> Rango de Fechas</a></li>
                <li class="list-group-item " data-toggle="tab" href="#tab-eco-com-import-rents"><a href="#"><i
                            class="fa fa-users"></i> Importar Rentas</a></li>
                <li class="list-group-item " data-toggle="tab" href="#tab-eco-com-import-rents-aps"><a href="#"><i
                            class="fa fa-users"></i> Rentas APS</a></li>
                <li class="list-group-item " data-toggle="tab" href="#tab-eco-com-import-pago-futuro"><a href="#"><i
                            class="fa fa-users"></i> Importar Pago a Futuro</a></li>
                <li class="list-group-item " data-toggle="tab" href="#tab-eco-com-automatic-qualification"><a href="#"><i
                            class="fa fa-users"></i> Calificaci&oacuten Automatica</a></li>
            </ul>
        </div>
    </div>
    <br>
    <div class="col-md-9" style="padding-left: 6px">
        <div class="tab-content">
            <div id="tab-base-wage" class="tab-pane active">
    @include('base_wages.index')
            </div>
            <div id="tab-eco-com-averages" class="tab-pane">
    @include('eco_com.average')
            </div>
            <div id="tab-complementary-factor" class="tab-pane">
    @include('complementary_factor.index')
            </div>
            <div id="tab-eco-com-procedure" class="tab-pane">
            <eco-com-procedure :permissions="{{ $permissions }}"></eco-com-procedure>
            </div>
            <div id="tab-eco-com-import-rents" class="tab-pane">
            <eco-com-import-rents :permissions="{{ $permissions }}"></eco-com-import-rents>
            </div>
            <div id="tab-eco-com-import-rents-aps" class="tab-pane">
            <eco-com-import-rents-aps :permissions="{{ $permissions }}"></eco-com-import-rents-aps>
            </div>
            <div id="tab-eco-com-import-pago-futuro" class="tab-pane">
            <eco-com-import-pago-futuro :permissions="{{ $permissions }}"></eco-com-import-pago-futuro>
            </div>
            <div id="tab-eco-com-automatic-qualification" class="tab-pane">
            <eco-com-automatic-qualification :permissions="{{ $permissions }}" :eco-com-procedures="{{$eco_com_procedures}}"></eco-com-automatic-qualification>
            </div>
        </div>
    </div>
</div>
<br>
@endsection
@section('styles')
<link rel="stylesheet" href="{{asset('/css/datatable.css')}}">
@endsection
@section('scripts')
<script src="{{ asset('/js/datatables.js')}}"></script>
<script src="{{ asset('/js/datatables.js')}}"></script>
<script type="text/javascript">
    $('.datepicker').datepicker({
            format: "mm/yyyy",
            viewMode: "months",
            minViewMode: "months",
            language: "es",
            orientation: "bottom right",
            autoclose: true
        });
    $(document).ready(function(){

            $('#first_level_base_wage-table').DataTable({
                "dom": '<"top">t<"bottom"p>',
                "order": [[ 0, "desc" ]],
                processing: true,
                serverSide: true,
                pageLength: 10,
                autoWidth: false,
                ajax: '{!! route('get_first_level_base_wage') !!}',
                columns: [
                    { data: 'month_year', sClass: "text-center" },
                    { data: 'c1', sClass: "text-right", bSortable: false },
                    { data: 'c2', sClass: "text-right", bSortable: false },
                    { data: 'c3', sClass: "text-right", bSortable: false },
                    { data: 'c4', sClass: "text-right", bSortable: false },
                    { data: 'c5', sClass: "text-right", bSortable: false },
                    { data: 'c6', sClass: "text-right", bSortable: false },
                    { data: 'c7', sClass: "text-right", bSortable: false },
                    { data: 'c8', sClass: "text-right", bSortable: false },
                    { data: 'c9', sClass: "text-right", bSortable: false },
                    { data: 'c10', sClass: "text-right", bSortable: false },
                    { data: 'c11', sClass: "text-right", bSortable: false },
                    { data: 'c12', sClass: "text-right", bSortable: false }
                ]
            });

            $('#second_level_base_wage-table').DataTable({
                "dom": '<"top">t<"bottom"p>',
                "order": [[ 0, "desc" ]],
                processing: true,
                serverSide: true,
                pageLength: 10,
                autoWidth: false,
                ajax: '{!! route('get_second_level_base_wage') !!}',
                columns: [
                    { data: 'month_year', sClass: "text-center" },
                    { data: 'c13', sClass: "text-right", bSortable: false },
                    { data: 'c14', sClass: "text-right", bSortable: false },
                    { data: 'c15', sClass: "text-right", bSortable: false },
                    { data: 'c16', sClass: "text-right", bSortable: false },
                    { data: 'c17', sClass: "text-right", bSortable: false },
                    { data: 'c18', sClass: "text-right", bSortable: false }
                ]
            });

            $('#third_level_base_wage-table').DataTable({
                "dom": '<"top">t<"bottom"p>',
                "order": [[ 0, "desc" ]],
                processing: true,
                serverSide: true,
                pageLength: 10,
                autoWidth: false,
                ajax: '{!! route('get_third_level_base_wage') !!}',
                columns: [
                    { data: 'month_year', sClass: "text-center" },
                    { data: 'c19', sClass: "text-right", bSortable: false },
                    { data: 'c20', sClass: "text-right", bSortable: false },
                    { data: 'c21', sClass: "text-right", bSortable: false },
                    { data: 'c22', sClass: "text-right", bSortable: false },
                    { data: 'c23', sClass: "text-right", bSortable: false },
                    { data: 'c24', sClass: "text-right", bSortable: false },
                    { data: 'c25', sClass: "text-right", bSortable: false },
                    { data: 'c26', sClass: "text-right", bSortable: false }
                ]
            });

            $('#fourth_level_base_wage-table').DataTable({
                "dom": '<"top">t<"bottom"p>',
                "order": [[ 0, "desc" ]],
                processing: true,
                serverSide: true,
                pageLength: 10,
                autoWidth: false,
                ajax: '{!! route('get_fourth_level_base_wage') !!}',
                columns: [
                    { data: 'month_year', sClass: "text-center" },
                    { data: 'c27', sClass: "text-right", bSortable: false },
                    { data: 'c28', sClass: "text-right", bSortable: false },
                    { data: 'c29', sClass: "text-right", bSortable: false },
                    { data: 'c30', sClass: "text-right", bSortable: false },
                    { data: 'c31', sClass: "text-right", bSortable: false },
                    { data: 'c32', sClass: "text-right", bSortable: false },
                    { data: 'c33', sClass: "text-right", bSortable: false },
                    { data: 'c34', sClass: "text-right", bSortable: false }
                ]
            });

        //averages
        var oTable = $('#average_table').DataTable({
            "dom": '<"top">t<"bottom"p>',
            processing: true,
            serverSide: true,
            pageLength: 30,
            autoWidth: false,
            order: [0, "asc"],
            ajax: {
                url: '{!! route('get_averages') !!}',
                data: function (d) {
                    d.year = $('#year').val();
                    d.semester = $('#semester').val();
                }
            },
            columns: [
                { data: 'code', sClass: "text-center" },
                { data: 'correlative', sClass: "text-center" },
                { data: 'degree', sClass: "text-center" },
                { data: 'type', bSortable: false },
                { data: 'rmin', bSortable: false },
                { data: 'rmax', bSortable: false },
                { data: 'average', bSortable: false },
            ]
        });
        $('#refresh-average').on('click', function(e) {
            oTable.draw();
            e.preventDefault();
        });
    });

    $(function() {
            $('#complementary_factor_old_age-table').DataTable({
                "dom": '<"top">t<"bottom"p>',
                "order": [[ 0, "desc" ]],
                processing: true,
                serverSide: true,
                pageLength: 10,
                autoWidth: false,
                ajax: '{!! route('get_complementary_factor_old_age') !!}',
                columns: [
                    { data: 'year', sClass: "text-center" },
                    { data: 'semester', sClass: "text-center", bSortable: false },
                    { data: 'cf1', sClass: "text-right", bSortable: false },
                    { data: 'cf2', sClass: "text-right", bSortable: false },
                    { data: 'cf3', sClass: "text-right", bSortable: false },
                    { data: 'cf4', sClass: "text-right", bSortable: false },
                    { data: 'cf5', sClass: "text-right", bSortable: false }
                ]
            });
        });

        $(function() {
            $('#complementary_factor_widowhood-table').DataTable({
                "dom": '<"top">t<"bottom"p>',
                "order": [[ 0, "desc" ]],
                processing: true,
                serverSide: true,
                pageLength: 10,
                autoWidth: false,
                ajax: '{!! route('get_complementary_factor_widowhood') !!}',
                columns: [
                    { data: 'year', sClass: "text-center" },
                    { data: 'semester', sClass: "text-center", bSortable: false },
                    { data: 'cf1', sClass: "text-right", bSortable: false },
                    { data: 'cf2', sClass: "text-right", bSortable: false },
                    { data: 'cf3', sClass: "text-right", bSortable: false },
                    { data: 'cf4', sClass: "text-right", bSortable: false },
                    { data: 'cf5', sClass: "text-right", bSortable: false }
                ]
            });
        });

</script>
@endsection