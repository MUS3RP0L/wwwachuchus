@extends('layouts.app')
@section('title', 'Fondo de Retiro')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        {{ Breadcrumbs::render('eco_com') }}
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="text-center m-t-lg table-responsive">
                <table class="table table-striped table-bordered table-hover display" id="datatables-retirement-funds"
                    cellspacing="0" width="100%" style="font-size: 12px">
                    <tfoot>
                        <tr style=>
                            
                            <th style="padding:5px; width:20px;"><input type="text" class="form-control"
                                    style="width:100%"></th>
                            <th style="padding:5px; width:20px;"><input type="text" class="form-control"
                                    style="width:100%"></th>
                            <th style="padding:5px; width:20px;"><input type="text" class="form-control"
                                    style="width:100%"></th>
                            <th style="padding:5px; width:280px"><input type="text" class="form-control"
                                    style="width:100%"></th>
                            <th style="padding:5px; width:280px"><input type="text" class="form-control"
                                    style="width:100%"></th>
                            <th style="padding:5px; width:280px"><input type="text" class="form-control"
                                    style="width:100%"></th>
                            <th style="padding:5px; width:280px"><input type="text" class="form-control"
                                    style="width:100%"></th>
                            <th style="padding:5px; width:280px"><input type="text" class="form-control"
                                    style="width:100%"></th>
                            <th style="padding:5px; width:280px"><input type="text" class="form-control"
                                    style="width:100%"></th>
                            <th style="padding:5px; width:280px"><input type="text" class="form-control"
                                    style="width:100%"></th>
                            <th style="padding:5px; width:280px"><input type="text" class="form-control"
                                    style="width:100%"></th>
                           
                           
                         
                        </tr>
                    </tfoot>
                    <thead>
                        <tr>
                            <th>Num</th>
                            <th># de Trámite</th>
                            <th>C.I Beneficiario</th>
                            <th>Nombre del Beneficiario</th>
                            <th>Regional</th>
                            <th>Semestre</th>
                            <th>Modalidad</th>
                            <th>Ubicacion</th>
                            <th>Estado de Bandeja</th>
                            <th>Total Complemento</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                </table>
                {{--
                <ret-fun-index></ret-fun-index> --}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{asset('/css/datatables.css')}}">
<style>
    td.highlight {
        background-color: #e3eaef !important;
    }

    .table-hover tbody tr:hover td,
    .table-hover tbody tr:hover th {
        background-color: #e3eaef;
    }

    .yellow-row {
        background-color: #ffe6b3 !important;
    }

    thead,
    tfoot {
        display: table-header-group;
    }
</style>
@endsection

@section('scripts')
<script src="{{ asset('/js/datatables.js')}}"></script>
<script>
    (function() {
        //added responsive table affiliate
        // document.getElementsByName('SimpleTable')[0].className+='table-responsive';
        var datatable_ret_fun = $('#datatables-retirement-funds').DataTable({
            "processing": true,
            "serverSide": true,
            language: {
                "decimal": "",
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
                "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Entradas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            responsive: true,
            // fixedHeader: {
            //     header: true,
            //     footer: true,
            //     headerOffset: $('#navbar-fixed-top').outerHeight()
            // },

            order: [],
            /* para personalizar el orden
             columnDefs: [
               { type: 'monthYear', targets: 0 }
            ],
            */
            ajax: "{{ url('get_all_eco_com') }}",
            lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50,100, "Todos"]],
            //dom:"<'row'<'col-sm-6'l><'col-sm-6'>><'row'<'col-sm-12't>><'row'<'col-sm-5'i>><'row'<'bottom'p>>",
            dom: '< "html5buttons"B>Tgitp',
            buttons:[
                {extend: 'colvis', text: 'Columnas Visibles', columnText: function ( dt, idx, title ) { return (idx+1)+': '+title; }},
                { extend: 'copy', text: 'Copiar'},
                { extend: 'csv'},
                { extend: 'excel', text:"Descargar en Excel", title: "Trámites de Fondo de Retiro "+moment().format('L_Hmm'), exportOptions: { columns: ':visible' }},
            ],
            columns:[
                { data: 'id' },
                { data: 'code' },
                { data: 'eco_com_beneficiary_identity_card' },
                { data: 'eco_com_beneficiary_full_name' },
                { data: 'eco_com_city_name'},
                { data: 'eco_com_procedure_year'},
                { data: 'procedure_modality' },
                { data: 'wf_state_name' },
                { data: 'eco_com_inbox_state' },
                { data: 'total' },
                { data: 'action' },
            ],
        });
        $('.btn.btn-default.buttons-collection.buttons-colvis').on('click', function () {
            $('div.dt-button-background').remove()
        });
        datatable_ret_fun.columns().every( function () {
            var that = this;
            $('input',this.footer()).on('keyup change', function () {
                if ( that.search() !== this.value ) {
                    that.search( this.value ).draw();
                }
            });
        });
        // $(document).on('click','.btn-received', function(){
        //     datatable_ret_fun.columns(0).search($(this).data('id')).draw();   
        // });
    })();

</script>
@endsection