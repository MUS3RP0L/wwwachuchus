<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>PLATAFORMA VIRTUAL - MUSERPOL {{ $title ?? '' }}</title>
    <link rel="stylesheet" href="{{ public_path('css/materialicons.css') }}" media="all" />
    <link rel="stylesheet" href="{{ public_path('css/wkhtml.css') }}" media="all" />
</head>

<body>
    <div class="page-break">
        <table class="w-100 ">
            <tr>
                <th class="w-20 text-left no-padding no-margins align-middle">
                    <div class="text-center">
                        <img src="{{ public_path('images/logo.jpg') }}" class="w-100">
                    </div>
                </th>
                <th class="w-50 align-top">
                    <span class="font-semibold uppercase leading-tight text-md">
                    {{ $institution ?? 'MUTUAL DE SERVICIOS AL POLICÍA "MUSERPOL"' }} <br>
                    {{ $direction ?? 'DIRECCIÓN DE BENEFICIOS ECONÓMICOS' }} <br>
                    @if(isset($unit1))
                        {!! $unit1 !!}
                    @endif
                    {{ $unit ?? 'UNIDAD DE OTORGACIÓN DE FONDO DE RETIRO POLICIAL, CUOTA MORTUORIA Y AUXILIO MORTUORIO' }}
                </span>
                </th>
                <th class="w-20 no-padding no-margins align-top">
                    {{-- @if(isset($code))
                    <table class="table-code no-padding no-margins">
                        <tbody>
                            <tr>
                                <td class="text-center bg-grey-darker text-xxs text-white">Nº de Trámite</td>
                                <td class="text-bold text-base">{!! $code !!}</td>
                            </tr>
                            <tr>
                                <td class="text-center bg-grey-darker text-xxs text-white">Área</td>
                                <td class="text-xs">{!! $area ?? '' !!}</td>
                            </tr>
                            <tr>
                                <td class="text-center bg-grey-darker text-xxs text-white">Usuario</td>
                                <td class="text-xs">{!! $user->username !!}</td>
                            </tr>
                            <tr>
                                <td class="text-center bg-grey-darker text-xxs text-white">Fecha</td>
                                <td class="text-xs uppercase">{!! $date !!}</td>
                            </tr>
                        </tbody>
                    </table>
                    @else
                    <table class="table-code align-top no-padding no-margins">
                        <tbody>
                            <tr>
                                <td class="text-center bg-grey-darker text-xxs text-white">Área</td>
                                <td class="text-xs">{!! $area ?? '' !!}</td>
                            </tr>
                            <tr>
                                <td class="text-center bg-grey-darker text-xxs text-white">Usuario</td>
                                <td class="text-xs">{!! $user->username ?? '' !!}</td>
                            </tr>
                            <tr>
                                <td class="text-center bg-grey-darker text-xxs text-white">Fecha</td>
                                <td class="text-xs uppercase">{{ $date ?? '' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    @endif --}}
                </th>
            </tr>
            <tr>
                <td colspan="3" style="border-bottom: 1px solid #22292f;"></td>
            </tr>
            <tr>
                <td colspan="3" class="font-bold text-center text-xl uppercase">
                    {{ $title }} @if (isset($subtitle))
                    <br><span class="font-medium text-lg">{!! $subtitle ?? '' !!}</span> @endif
                </td>
            </tr>
            {{--
            <tr>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td colspan="3"></td>
            </tr> --}}

        </table>

        <div class="block">

            @yield('content')
            <table class="table-info w-100 m-b-5">
                <thead class="bg-grey-darker">
                    <tr class="font-medium text-white text-sm text-center">
                        <td>Nº</td>
                        <td>NOMBRES Y APELLIDOS</td>
                        <td>CÉDULA DE IDENTIDAD</td>
                        <td>FRPS</td>
                        <td>RECONOCIMIENTO DISP.</td>
                        <td>TOTAL A PAGAR</td>
                    <tr></tr>
                </thead>
                <tbody>
                    @php
                        $index = 1
                    @endphp
                    @foreach ($filter as $item)
                        <tr>
                            <td class="px-5 text-right">{{ $index++ }}</td>
                            <td class="px-15 text-left">{{ $item->affiliate->fullName() }}</td>
                            <td class="px-15 text-left">{{ $item->affiliate->ciWithExt() }}</td>
                            <td class="px-15 text-right">{{ Util::formatMoney($item->total_ret_fun) }}</td>
                            <td class="px-15 text-right">{{ Util::formatMoney($item->total_availability) ?? 0 }}</td>
                            <td class="px-15 text-right">{{ Util::formatMoney($item->total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <footer>
            @yield('footer')
        </footer>
    </div>
</body>

</html>