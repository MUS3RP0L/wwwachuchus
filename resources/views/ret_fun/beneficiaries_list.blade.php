
<ret-fun-beneficiaries-show :beneficiaries2="{{ $beneficiaries }}" :beneficiaries-backend="{{ $beneficiaries }}" :ret-fun-id="{{ $retirement_fund->id }}" :procedure-modality-id="{{ $retirement_fund->procedure_modality_id }}" :original-beneficiaries-backend="{{ $beneficiaries }}" :cities="{{$cities}}" :kinships="{{$kinships}}" inline-template>
    <div class="col-lg-12">
        <div class="ibox">
    
            <div class="ibox-content">
                <div class="text-right">
                    @can('update',new Muserpol\Models\RetirementFund\RetFunBeneficiary)
                    <button data-animation="flip" class="btn btn-primary" :class="editing ? 'active': ''" @click="toggle_editing"><i class="fa" :class="editing ?'fa-edit':'fa-pencil'" ></i> Editar</button>
                  
                    @else
                    <br>
                    @endcan
                    <br><br>
                </div>
                
                <div>
                <ret-fun-beneficiary v-for="(beneficiary, index) in beneficiaries"
                    :key='index'
                    :beneficiary="beneficiary"
                    :cities="cities"
                    :kinships="kinships"
                    :editable="editing"
                    :index="index"
                    v-on:remove="removeBeneficiary(index)"
                    >
                
                </ret-fun-beneficiary>
                </div>
                <div class="row" v-if="editing && canAddBeneficiary() ">
                    <div class="col-md-5"></div>
                    <div class="col-md-1">
                        <button class="btn btn-success add-beneficiary-button" @click="addBeneficiary()" type="button"><i class="fa fa-plus"></i></button>
                    </div>
                    <div class="col-md-6"></div>
                </div>
                <br>
                <div class="text-center" v-show="editing">
                    <button class="btn btn-danger" type="button" @click="cancel"><i class="fa fa-times-circle"></i>&nbsp;&nbsp;<span class="bold">Cancelar</span></button>
                    <button class="btn btn-primary" type="button" @click="update"><i class="fa fa-check-circle"></i>&nbsp;Guardar</button>
                </div>
                
            </div>
        </div>
        
       
    </div>
</ret-fun-beneficiaries-show>
<ret-fun-beneficiary-testimony-list :beneficiaries="{{ $beneficiaries }}" :doc-id="{{ $retirement_fund->id }}" type="retFun">
</ret-fun-beneficiary-testimony-list>