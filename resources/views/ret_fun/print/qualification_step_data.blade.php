@extends('print_global.print')
@section('content')
<div>
    <div class="font-bold uppercase m-b-5 counter">
        INFORMACIÓN TÉCNICA
    </div>
    @include('ret_fun.print.interval_types', ['ret_fun' => $retirement_fund, 'type'=>'ret_fun' ])
    <div class="block">
        <table class="table-info w-100 m-b-10">
            <thead class="bg-grey-darker">
                <tr class="font-medium text-white text-sm uppercase">
                    <td colspan='3' class="px-15 text-center">
                        DATOS ECONOMICOS DEL AFILIADO
                    </td>
                </tr>
            </thead>
            <tbody class="table-striped">
                @if ( $affiliate->globalPayRetFun() )
                    <tr class="text-sm">
                        <td class="text-left px-10 py-3 uppercase">TOTAL APORTES</td>
                        <td class="text-right uppercase font-bold px-5 py-3"> {{ Util::formatMoney($retirement_fund->average_quotable) }} </td>
                        <td class="text-center uppercase font-bold px-5 py-3"> Bs. </td>
                    </tr>
                    <tr class="text-sm">
                        @if ($retirement_fund->procedure_modality->procedure_type_id == 1)
                            <td class="text-left px-10 py-3 uppercase">Interés del 1.05%</td>
                        @else
                            <td class="text-left px-10 py-3 uppercase">CON RENDIMIENTO DEL 5% ANUAL</td>
                        @endif
                        <td class="text-right uppercase font-bold px-5 py-3"> {{ Util::formatMoney($yield) }} </td>
                        <td class="text-center uppercase font-bold px-5 py-3"> Bs. </td>
                    </tr>
                    <tr class="text-sm">
                        <td class="text-left px-10 py-3 uppercase">menos gastos administrativos de 10%</td>
                        <td class="text-right uppercase font-bold px-5 py-3"> {{ Util::formatMoney($less_administrative_expenses) }} </td>
                        <td class="text-center uppercase font-bold px-5 py-3"> Bs. </td>
                    </tr>
                @else
                    <tr class="text-sm">
                        <td class="w-60 text-left px-10 py-3 uppercase">ultimo sueldo percibido</td>
                        <td class="w-25 text-right uppercase font-bold px-5 py-3"> {{ Util::formatMoney($affiliate->getLastBaseWage()) ?? '-' }} </td>
                        <td class="w-15  text-center uppercase font-bold px-5 py-3"> Bs. </td>
                    </tr>
                    <tr class="text-sm">
                        <td class="text-left px-10 py-3 uppercase">salario promedio cotizable</td>
                        <td class="text-right uppercase font-bold px-5 py-3"> {{ Util::formatMoney($retirement_fund->average_quotable) }} </td>
                        <td class="text-center uppercase font-bold px-5 py-3"> Bs. </td>
                    </tr>
                    <tr class="text-sm">
                        <td class="text-left px-10 py-3 uppercase">densidad total de cotizaciones</td>
                        <td class="text-right uppercase font-bold px-5 py-3"> {{$total_quotes}} </td>
                        <td class="text-center uppercase font-bold px-5 py-3"> meses </td>
                    </tr>
                @endif
            </tbody>
        </table>
        <table class="table-info w-100 m-b-10">
            <thead class="bg-grey-darker">
                <tr class="font-medium text-white text-sm uppercase">
                    <td colspan='3' class="px-15 text-center">
                        DATOS ECONOMICOS DEL AFILIADO
                    </td>
                </tr>
            </thead>
            <tbody class="table-striped">
                @if (sizeOf($discounts)>0)
                    <tr class="text-sm">
                        <td class="text-left px-10 py-3 uppercase">
                            @if ($affiliate->globalPayRetFun())
                                total pago global por {{ $retirement_fund->procedure_modality->name }}
                            @else
                                total fondo de retiro
                            @endif
                        </td>
                        <td class="text-right uppercase font-bold px-5 py-3"> {{ Util::formatMoney($retirement_fund->subtotal_ret_fun) }} </td>
                        <td class="text-center uppercase font-bold px-5 py-3"> Bs. </td>
                    </tr>
                @endif
                @foreach ($discounts as $d)
                    <tr class="text-sm">
                        <td class="w-60 text-left px-10 py-3 uppercase">{{ $d->name }}</td>
                        <td class="w-25 text-right uppercase px-5 py-3"> {{ Util::formatMoney($d->pivot->amount) }} </td>
                        <td class="w-15  text-center uppercase px-5 py-3"> Bs. </td>
                    </tr>
                @endforeach
                @foreach ($array_discounts_combi as $item)
                    <tr class="text-sm">
                        <td class="text-left px-10 py-3 uppercase">{{$item['name']}}</td>
                        <td class="text-right uppercase px-5 py-3"> {{ Util::formatMoney($item['amount']) }} </td>
                        <td class="text-center uppercase px-5 py-3"> Bs. </td>
                    </tr>
                @endforeach
                <tr class="text-lg">
                    <td class="text-left px-10 py-3 uppercase font-bold">
                        @if ($affiliate->globalPayretFUn())
                            total pago global por {{ $retirement_fund->procedure_modality->name }}
                        @else
                        total fondo de retiro
                        @endif
                    </td>
                    <td class="text-right uppercase font-bold px-5 py-3"> {{ Util::formatMoney($retirement_fund->total_ret_fun) }} </td>
                    <td class="text-center uppercase font-bold px-5 py-3"> Bs. </td>
                </tr>
            </tbody>
        </table>
    </div>
    @include('ret_fun.print.qualification_beneficiaries_fair_share', ['beneficiaries'=>$beneficiaries, 'type'=>'normal'])
    @include('ret_fun.print.signature_footer',['user'=>$user])
</div>
@endsection