<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <td class="px-15 text-center text-sm uppercase">
                fecha de ingreso
            </td>
            <td class="px-15 text-center text-sm uppercase">
                fecha de desvinculación
            </td>
            <td class="px-15 text-center text-sm uppercase">
                GRADO
            </td>
            <td class="px-15 text-center text-sm uppercase">
                categoría
            </td>
        </tr>
    </thead>
    <tbody>
        <tr class="text-sm">
            <td class="text-center uppercase font-bold px-5 py-3">{{ Util::formatMonthYear($affiliate->date_entry) }}</td>
            <td class="text-center uppercase font-bold px-5 py-3">{{ Util::formatMonthYear($affiliate->date_derelict) }}</td>
            <td class="text-center uppercase font-bold px-5 py-3">{{ $affiliate->degree->shortened ?? null }}</td>
            <td class="text-center uppercase font-bold px-5 py-3">{{ $affiliate->category->name ?? null }}</td>
        </tr>
    </tbody>
</table>