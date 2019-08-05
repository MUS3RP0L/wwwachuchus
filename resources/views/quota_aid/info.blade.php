<div class="col-md-12">
    <div class="ibox">
        <div class="ibox-content">
                <div  class="pull-left">
                    <legend>Cuota Mortuoria y Auxilio Mortuorio</legend>
                </div>
                {{-- @can('update',new Muserpol\Models\RetirementFund\RetirementFund) --}}
                <div class="text-right" v-if="!read">
                    <button data-animation="flip" class="btn btn-primary" :class="editing ? 'active': ''" @click="toggle_editing"><i class="fa" :class="editing ?'fa-edit':'fa-pencil'" ></i> Editar </button>
                </div>
                <div class="text-right" v-if="read">
                <a href="{{ url('quota_aid/'.$quota_aid->id)}}" class="btn btn-primary"> <i class="fa fa-eye"></i> Ver</a>
                    {{-- <button data-animation="flip" class="btn btn-primary" :class="editing ? 'active': ''" @click="toggle_editing"><i class="fa" :class="editing ?'fa-edit':'fa-pencil'" ></i> Editar </button> --}}
                </div>
                {{-- @else
                <br>
                    @endcan --}}
                <br>
                <div class="row">
                    {{-- <div class="col-md-1"></div> --}}
                    <div class="col-md-2">
                        <strong> Modalidad:</strong>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" v-model="procedure_modality_name" disabled="">
                    </div>
                    <div class="col-md-2">
                        <strong>Ciudad de Recepci&oacute;n:</strong>
                    </div>
                    <div class="col-md-4">
                        {!! Form::select('city_start_id', $cities, null , ['placeholder' => 'Seleccione ciudad', 'class' => 'form-control','v-model'=>'form.city_start_id',':disabled'=>'!editing'])
                        !!}
                    </div>
                    {{-- <div class="col-md-1"></div> --}}
                </div>
                <br>
                <div class="row">
                    {{-- <div class="col-md-1"></div> --}}
                    <div class="col-md-2">
                        <strong> Fecha de Recepcion:</strong>&nbsp;
                    </div>
                    <div class="col-md-4">
                        @if(Session::get('rol_id') == 43)
                            <input type="date" v-model="form.reception_date" class="form-control" :disabled="!editing" >
                        @else
                            <input type="date" :value="form.reception_date" class="form-control" disabled>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <strong>Regional:</strong>&nbsp;
                    </div>
                    <div class="col-md-4">
                        {!! Form::select('city_end_id', $cities, null , ['placeholder' => 'Seleccione ciudad', 'class' => 'form-control','v-model'=>'form.city_end_id',':disabled'=>'!editing'])
                        !!}
                    </div>
                    {{-- <div class="col-md-1"></div> --}}
                </div>
                <br>
                <div class="row">
                    {{-- <div class="col-md-1"></div> --}}
                    <div class="col-md-2">
                        <strong>Estado:</strong>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" v-model="form.procedure_state_id" ref="modality" name="procedure_state_id" :disabled='!editing' >
                            <option v-for="(state, index) in states" :value="state.id" :key="index">@{{state.name}}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                    </div>
                    {{-- <div class="col-md-1"></div> --}}
                </div>
                <br>
                <div v-show="editing">
                    <div class="text-center">
                        <button class="btn btn-danger" type="button" @click="toggle_editing()"><i class="fa fa-times-circle"></i>&nbsp;&nbsp;<span class="bold">Cancelar</span></button>
                        <button class="btn btn-primary" type="button" @click="update"><i class="fa fa-check-circle"></i>&nbsp;Guardar</button>
                    </div>
                </div>
                <br>
        </div>
    </div>
</div>