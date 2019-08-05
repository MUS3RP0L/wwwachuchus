<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h2>Datos de la calificacion</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content forum-container">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="col-sm-6">
                                <label class="control-label">Ultimo Sueldo Percibido</label>
                            </div>
                            <div class="col-sm-6">
                                Bs {{ Util::formatMoney($last_base_wage) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-sm-6">
                                @if ($affiliate->globalPayretFun())
                                    <label class="control-label">Total Aportes</label>
                                @else
                                    <label class="control-label">Salario Promedio Cotizable</label>
                                @endif
                            </div>
                            <div class="col-sm-6">
                                Bs {{ Util::formatMoney($retirement_fund->average_quotable) }}
                                <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#averageSalaryQuotable" style="margin-left:15px;"><i class="fa fa-calculator"></i> ver completo</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="col-sm-6">
                                <label class="control-label">Sub Total fondo de retiro</label>
                            </div>
                            <div class="col-sm-6">
                                Bs {{ Util::formatMoney($retirement_fund->subtotal_ret_fun) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-sm-6">
                                <label class="control-label">Total fondo de retiro</label>
                            </div>
                            <div class="col-sm-6">
                                Bs {{ Util::formatMoney($retirement_fund->total_ret_fun) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <table class="table table-hover no-margins">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Monto</th>
                                    <th>Cite</th>
                                    <th>Fecha de Cite</th>
                                    <th>#</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($retirement_fund->discount_types as $discount)
                                <tr>
                                    <td>{{ $discount->name }}</td>
                                    <td>{{ Util::formatMoney($discount->pivot->amount) }}</td>
                                    <td>{{ $discount->pivot->code }}</td>
                                    <td>{{ $discount->pivot->date }}</td>
                                    <td>{{ $discount->pivot->note_code }}</td>
                                    <td>{{ $discount->pivot->note_code_date }}</td>
                                </tr>
                                @if ($discount->id == 3 && $retirement_fund->info_loans->count() > 0)
                                <tr>
                                    <td colspan="6">
                                        <table class="table table-hover no-margins">
                                            <thead>
                                                <tr>
                                                    <th>CI</th>
                                                    <th>Nombre del garante</th>
                                                    <th>Monto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($retirement_fund->info_loans as $info_loan)
                                                <tr>
                                                    <td>{{$info_loan->affiliate_guarantor->ciWithExt()}}</td>
                                                    <td>{{$info_loan->affiliate_guarantor->fullName()}}</td>
                                                    <td>{{ Util::formatMoney($info_loan->amount)}}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-lg-12">
<div class="ibox">
    <div class="ibox-title">
        <h5>Cuotas partes para los derechohabientes (Fondo de Retiro)</h5>
    </div>
    <div class="ibox-content">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>NOMBRE DEL DERECHOHABIENTE</th>
                    <th>% DE ASIGNACION</th>
                    <th>MONTO</th>
                    <th>PARENTESCO</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    {{-- <th class="text-info">100.00 %</th> --}}
                    <th class="text-info">Bs {{ Util::formatMoney($retirement_fund->total_ret_fun) }}</th>
                    <th></th>
                </tr>
            </tfoot>
            <tbody>
                @foreach ($retirement_fund->ret_fun_beneficiaries as $beneficary)
                    <tr>
                        <td>{{ $beneficary->fullName() }}</td>
                        <td>{{ $beneficary->percentage }}</td>
                        <td>{{ Util::formatMoney($beneficary->amount_ret_fun) }}</td>
                        <td>{{ $beneficary->kinship->name ?? 'SIN PARENTESCO' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>
@if ($affiliate->hasAvailability())
<div class="col-lg-12">
    <div class="ibox">
        <div class="ibox-title">
            <h5>RECONOCIMIENTO DE APORTES EN DISPONIBILIDAD</h5>
        </div>
        <div class="ibox-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Total aportes en disponibilidad</td>
                        <td>{{ Util::formatMoney($retirement_fund->subtotal_availability) }} <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#availability-modal" style="margin-left:15px;"><i class="fa fa-calculator"></i> ver completo</button></td>
                    </tr>
                    <tr>
                        <td>Aportes en disponibilidad con rendimiento</td>
                        <td>{{ Util::formatMoney($retirement_fund->total_availability) }}</td>
                    </tr>
                    <tr>
                        <td>Reconocimiento de Aportes en Disponibilidad</td>
                        <td>{{ Util::formatMoney($retirement_fund->total_availability) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                    </tr>
                    @foreach ($array_discounts_availability as $item)
                        <tr>
                            <td>{{$item['name']}} </td>
                            <td>Bs {{ Util::formatMoney($item['amount'])}}</td>
                        </tr>
                    @endforeach
                    <tr class="success">
                        <td>{{end($array_discounts_availability)['name']}}</td>
                        <td>Bs {{Util::formatMoney(end($array_discounts_availability)['amount'])}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="col-lg-12">
    <div class="ibox">
        <div class="ibox-title">
            <h5>cuotas partes para los derechohabientes (RECONOCIMIENTO DE APORTES EN DISPONIBILIDAD)</h5>
        </div>
        <div class="ibox-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>NOMBRE DEL DERECHOHABIENTE</th>
                        <th>% DE ASIGNACION</th>
                        <th>MONTO</th>
                        <th>PARENTESCO</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        {{--
                        <th class="text-info">100.00 %</th> --}}
                        <th class="text-info">Bs {{ Util::formatMoney($retirement_fund->total_availability) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($retirement_fund->ret_fun_beneficiaries as $beneficary)
                    <tr>
                        <td>{{ $beneficary->fullName() }}</td>
                        <td>{{ $beneficary->percentage }}</td>
                        <td>{{ Util::formatMoney($beneficary->amount_availability) }}</td>
                        <td>{{ $beneficary->kinship->name ?? 'SIN PARENTESCO' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="col-lg-12">
    <div class="ibox">
        <div class="ibox-title">
            <h5>cuotas partes para los derechohabientes (FONDO DE RETIRO + RECONOCIMIENTO DE APORTES EN DISPONIBILIDAD)</h5>
        </div>
        <div class="ibox-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>NOMBRE DEL DERECHOHABIENTE</th>
                        <th>% DE ASIGNACION</th>
                        <th>MONTO</th>
                        <th>PARENTESCO</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        {{--
                        <th class="text-info">100.00 %</th> --}}
                        <th class="text-info">Bs {{ Util::formatMoney($retirement_fund->total) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($retirement_fund->ret_fun_beneficiaries as $beneficary)
                    <tr>
                        <td>{{ $beneficary->fullName() }}</td>
                        <td>{{ $beneficary->percentage }}</td>
                        <td>{{ Util::formatMoney($beneficary->amount_total) }}</td>
                        <td>{{ $beneficary->kinship->name ?? 'SIN PARENTESCO' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="modal inmodal" id="averageSalaryQuotable" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
                <h4 class="modal-title">SALARIO PROMEDIO COTIZABLE</h4>
            </div>
            <div class="modal-body">
                <div class="col-lg-12">
                    <table class="table table-striped" id="datatables-certification">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Periodo</th>
                                <th>Haber Basico</th>
                                <th>Categoria</th>
                                <th>Salario Cotizable</th>
                                <th>Total Aporte</th>
                                <th>Aporte FRPS</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<div class="modal inmodal" id="availability-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
                <h4 class="modal-title">Reconocimiento de Aportes en Disponibilidad</h4>
            </div>
            <div class="modal-body">
                <div class="col-lg-12">
                    <table class="table table-striped" id="datatables-availability">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Periodo</th>
                                <th>Haber Basico</th>
                                <th>Categoria</th>
                                <th>Salario Cotizable</th>
                                <th>Total Aporte</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


@section('scripts')
<script src="{{ asset('/js/datatables.js')}}"></script>
<script>
    $(document).ready(function () {
        var datatable_contri = $('#datatables-certification').DataTable({
            responsive: true,
            order: [],
            ajax: "{{ url('get_data_certification', $retirement_fund->id) }}",
            lengthMenu: [[60, -1], [60, "Todos"]],
            dom: '< "html5buttons"B>lTfgitp',
            buttons:[
                { extend: 'copy'},
                { extend: 'csv'},
                { extend: 'excel', title: "{!! $retirement_fund->id.'-'.date('Y-m-d') !!}"},
            ],
            columns:[
                {data: 'DT_Row_Index' },
                {data: 'month_year' },
                {data: 'base_wage'},
                {data: 'seniority_bonus'},
                {data: 'quotable_salary'},
                {data: 'total'},
                {data: 'retirement_fund'},
            ],
        });
        var datatable_availability = $('#datatables-availability').DataTable({
            responsive: true,
            order: [],
            ajax: "{{ url('get_data_availability', $retirement_fund->id) }}",
            lengthMenu: [[60, -1], [60, "Todos"]],
            dom: '< "html5buttons"B>lTfgitp',
            buttons:[
                { extend: 'copy'},
                { extend: 'csv'},
                { extend: 'excel', title: "Dispobiblidad - {!! $retirement_fund->id.'-'.date('Y-m-d') !!}"},
            ],
            columns:[
                {data: 'DT_Row_Index' },
                {data: 'month_year' },
                {data: 'base_wage'},
                {data: 'seniority_bonus'},
                {data: 'quotable_salary'},
                {data: 'total'},
            ],
        });
    });
</script>
@endsection