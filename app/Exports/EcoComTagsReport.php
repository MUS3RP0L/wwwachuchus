<?php

namespace Muserpol\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Muserpol\Models\EconomicComplement\EconomicComplement;
use Muserpol\Models\Tag;
use DB;
class EcoComTagsReport implements WithMultipleSheets
{
    use Exportable;
    protected $eco_com_procedure_id;

    public function __construct(int $eco_com_procedure_id)
    {
        $this->eco_com_procedure_id = $eco_com_procedure_id;
    }
    public function sheets(): array
    {
        $sheets = [];
        $tags = Tag::all();
        $eco_coms = EconomicComplement::with('tags')
            ->select(
                'economic_complements.id as id',
                'economic_complements.affiliate_id as NUP',
                'economic_complements.code as eco_com_code',
                'economic_complements.reception_date as fecha_recepcion',
                'beneficiary.identity_card as ci_ben',
                'beneficiary_city.first_shortened as ext_ben',
                // 'concat_ws(' ', beneficiary.identity_card,beneficiary_city.first_shortened) as ci_completo_ben,
                'beneficiary.first_name as primer_nombre_ben',
                'beneficiary.second_name as segundo_nombre_ben',
                'beneficiary.last_name as apellido_paterno_ben',
                'beneficiary.mothers_last_name as apellido_materno_ben',
                'beneficiary.surname_husband as apellido_de_casado_ben',
                'beneficiary.birth_date as fecha_nac_ben',
                'beneficiary.phone_number as telefonos_ben',
                'beneficiary.cell_phone_number as celulares_ben',
                'beneficiary.official as oficialia_ben',
                'beneficiary.book as libro_ben',
                'beneficiary.departure as partida_ben',
                'beneficiary.marriage_date as fecha_matrimonio_ben',
                'affiliates.identity_card as ci_causa',
                'affiliate_city.first_shortened as exp_causa',
                // concat_ws(' ', affiliates.identity_card,affiliate_city.first_shortened) as ci_completo_causa,
                'affiliates.first_name as primer_nombre_causahabiente',
                'affiliates.second_name as segundo_nombre_causahabiente',
                'affiliates.last_name as ap_paterno_causahabiente',
                'affiliates.mothers_last_name as ap_materno_causahabiente',
                'affiliates.surname_husband as ape_casada_causahabiente',
                'affiliates.birth_date as fecha_nacimiento',
                'affiliates.nua as codigo_nua_cua',
                'eco_com_city.name as regional',
                'procedure_modalities.name as tipo_de_prestacion',
                'eco_com_reception_types.name as reception_type',
                'eco_com_category.name as categoria',
                'eco_com_degree.name as grado',
                'pension_entities.name',
                'economic_complements.sub_total_rent as total_ganado_renta_pensión_SENASIR',
                'economic_complements.reimbursement as reintegro_SENASIR',
                'economic_complements.dignity_pension  as renta_dignidad_SENASIR',
                'economic_complements.aps_total_fsa as fraccion_saldo_acumulada_APS',
                'economic_complements.aps_total_cc as fraccion_compensacion_cotizaciones_APS',
                'economic_complements.aps_total_fs as fraccion_solidaria_vejez_APS',
                'economic_complements.total_rent as total_renta',
                'economic_complements.total_rent_calc as total_renta_neto',
                'economic_complements.seniority as antiguedad',
                'economic_complements.salary_reference as salario_referencial',
                'economic_complements.salary_quotable as salario_cotizable',
                'economic_complements.difference as diferencia',
                'economic_complements.total_amount_semester as total_semestre',
                'economic_complements.complementary_factor as factor_complementario',
                DB::raw(
                'round(economic_complements.total_amount_semester * round(economic_complements.complementary_factor/100, 2), 2) as total_complemento',
                ),
                'economic_complements.total as total_liquido_pagable',
                'wf_states.first_shortened as ubicacion',
                'eco_com_modalities.name as tipo_beneficiario',
                'workflows.name as flujo'
            )
            ->info()
            ->beneficiary()
            ->affiliateInfo()
            ->wfstates()
            ->has('tags')
            ->ecoComProcedure($this->eco_com_procedure_id)
            ->get();
        foreach ($tags as $t) {
            $sheets[] = new EcoComTagSheet($t, $eco_coms);
        }
        return $sheets;
    }
}
