@extends('layouts.sinmenu')

@section('title', 'Autentificacion')

@section('content')


    <div>
        <div>

            <h1 class="logo-name">M+</h1>

        </div>
        
        <p></p>
        
        @foreach($roles as $role)
        {!! Form::open(['url' => 'postchangerol', 'role' => 'form']) !!}
        <div class="info-box">
            
                @if($role->module->id==1)
                <span class="info-box-icon bg-red">
                    <i class="glyphicon glyphicon-hdd"></i>,
                </span>
                @endif
                @if($role->module->id==2 || $role->module->id==8 )
                <span class="info-box-icon bg-aqua">
                    <i class="fa fa-fw fa-puzzle-piece fa-lg"></i>
                </span>
                @endif     
                @if($role->module->id==3)
                <span class="info-box-icon bg-yellow">
                    <i class="glyphicon glyphicon-piggy-bank"></i>
                </span>
                
                @endif

                @if($role->module->id==6 || $role->module->id==9)
                <span class="info-box-icon bg-green">
                    <i class="fa fa-fw fa-money "></i>
                </span>
                
                @endif
                @if($role->module->id==4 ||$role->module->id==5 )
                <span class="info-box-icon bg-green">
                    <i class="fa fa-fw fa-heartbeat "></i>
                </span>
                
                @endif

                @if($role->module->id==7)
                <span class="info-box-icon bg-green">
                    <i class="fa  fa-balance-scale "></i>
                </span>
                
                @endif
                @if($role->module->id==10)
                <span class="info-box-icon bg-green">
                    <i class="fa  fa-map "></i>
                </span>
                
                @endif
                
               

            <div class="info-box-content">
                <input type="hidden" name="rol_id" value={{$role->id}}"">

                <span class="info-box-text"> {{ $role->module->name }} </span>
                <br>

                @if($role->module->id==1)   
                <button type="submit" class="btn btn-block btn-raised btn-danger"><i class="glyphicon glyphicon-share-alt"></i>  {{$role->name}}</a>
                @endif 
                @if($role->module->id==2 || $role->module->id==8 )
                <button type="submit" class="btn btn-block btn-raised btn-info"><i class="glyphicon glyphicon-share-alt"></i>  {{$role->name}}</a>
                @endif     
                @if($role->module->id==3)
                <button type="submit" class="btn btn-block btn-raised btn-warning"><i class="glyphicon glyphicon-share-alt"></i>  {{$role->name}}</a>
                
                @endif
                @if($role->module->id==6 || $role->module->id==9)
                <button type="submit" class="btn btn-block btn-raised btn-success"><i class="glyphicon glyphicon-share-alt"></i>  {{$role->name}}</a>
                
                @endif
                @if($role->module->id==4 ||$role->module->id==5 ||$role->module->id==7 ||$role->module->id==10 )
                <button type="submit" class="btn btn-block btn-raised btn-success"><i class="glyphicon glyphicon-share-alt"></i>  {{$role->name}}</a>
                
                @endif

                
              
              {{-- <span class="info-box-number">90<small>%</small></span> --}}
            </div>
            <!-- /.info-box-content -->
        </div>
        {!! Form::close() !!}
        @endforeach

    </div>
 
@endsection
