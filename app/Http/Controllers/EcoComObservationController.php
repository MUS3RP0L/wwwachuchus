<?php

namespace Muserpol\Http\Controllers;

use Illuminate\Http\Request;
use Muserpol\Models\EconomicComplement\EconomicComplement;
use Log;
use Muserpol\Models\ObservationType;
use Carbon\Carbon;
use Auth;
use DB;
use Muserpol\Helpers\Util;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

class EcoComObservationController extends Controller
{
    public function getObservations($eco_com_id)
    {
        try{
            $this->authorize('read', new ObservationType());
        }catch(AuthorizationException $exception){
            return response()->json([
                'status' => 'error',
                'errors' => ['No tiene permisos para ver las observaciones'],
            ], 403);
        }
        $eco_com = EconomicComplement::find($eco_com_id);
        return $eco_com->observations;
    }
    public function getDeleteObservations($eco_com_id)
    {
        try{
            $this->authorize('read', new ObservationType());
        }catch(AuthorizationException $exception){
            return response()->json([
                'status' => 'error',
                'errors' => ['No tiene permisos para ver las observaciones'],
            ], 403);
        }
        $observations = collect(DB::table('observables')
            ->where('observable_id', $eco_com_id)
            ->where('observable_type', 'like', 'economic_complements')
            ->whereNotNull('deleted_at')
            ->leftJoin('observation_types', 'observables.observation_type_id', '=', 'observation_types.id')
            ->select(['observables.*','observation_types.name', 'observation_types.type'])
            ->get());
        return $observations;
    }
    public function create(Request $request)
    {
        try{
            $this->authorize('create', new ObservationType());
        }catch(AuthorizationException $exception){
            return response()->json([
                'status' => 'error',
                'errors' => ['No tiene permisos para crear observaciones'],
            ], 403);
        }
        try {
            $this->validate($request, [
                'message' => 'required|min:10|max:250',
                'observationTypeId' => 'required',
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => $exception->errors(),
            ], 422);
        }
        $eco_com = EconomicComplement::find($request->ecoComId);
        $observation = ObservationType::find($request->observationTypeId);
        // ?? Puede tener 2 observaciones del mismo tipo??
        $eco_com->observations()->save($observation, [
            'user_id' => Auth::user()->id,
            'date' => Carbon::now(),
            'message' => $request->message,
            'enabled' => $request->enabled
        ]);
        $eco_com->procedure_records()->create([
            'user_id' => Auth::user()->id,
            'record_type_id' => 9,
            'wf_state_id' => Util::getRol()->wf_states->first()->id ?? $eco_com->wf_current_state_id,
            'date' => Carbon::now(),
            'message' => "El usuario " . Auth::user()->username  . " creó la observación " . $observation->name . "."
        ]);
        return 'created';
    }
    public function update(Request $request)
    {
        try{
            $this->authorize('update', new ObservationType());
        }catch(AuthorizationException $exception){
            return response()->json([
                'status' => 'error',
                'errors' => ['No tiene permisos para editar observaciones'],
            ], 403);
        }
        try {
            $this->validate($request, [
                'message' => 'required|min:10|max:250',
                'editObservationTypeId' => 'required',
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => $exception->errors(),
            ], 422);
        }
        $eco_com = EconomicComplement::find($request->ecoComId);
        $observation = ObservationType::find($request->editObservationTypeId);
        if ($eco_com->observations->contains($observation->id)) {
            $old_observation = $eco_com->observations()->find($observation->id);
            $eco_com->observations()->updateExistingPivot($observation->id, [
                'user_id' => Auth::user()->id,
                'date' => Carbon::now(),
                'message' => $request->message,
                'enabled' => $request->enabled
            ]);
            $message = "El usuario " . Auth::user()->username  . " modifico la observación ". $observation->name.": ";
            if($old_observation->pivot->message != $request->message)
            {
                $message = $message . ' Mensaje de - '.$old_observation->pivot->message.' - a - '.$request->message.', ';
            }
            if($old_observation->pivot->enabled != $request->enabled)
            {
                $message = $message . ' de '.Util::getEnabledLabel($old_observation->pivot->enabled).' a '.Util::getEnabledLabel($request->enabled).', ';
            }
            $message = $message. ".";
            $eco_com->procedure_records()->create([
                'user_id' => Auth::user()->id,
                'record_type_id' => 9,
                'wf_state_id' => Util::getRol()->wf_states->first()->id ?? $eco_com->wf_current_state_id,
                'date' => Carbon::now(),
                'message' => $message
            ]);
        } else {
            return response()->json(['errors' => ['El Trámite no tiene esa observación']], 422);
        }
        return "updated";
    }
    public function delete(Request $request)
    {
        try{
            $this->authorize('delete', new ObservationType());
        }catch(AuthorizationException $exception){
            return response()->json([
                'status' => 'error',
                'errors' => ['No tiene permisos para eliminar observaciones'],
            ], 403);
        }
        try {
            $this->validate($request, [
                'observationTypeId' => 'required',
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => $exception->errors(),
            ], 422);
        }
        $eco_com = EconomicComplement::find($request->ecoComId);
        $observation = ObservationType::find($request->observationTypeId);
        if ($eco_com->observations->contains($observation->id)) {
            $eco_com->observations()->updateExistingPivot($observation->id, [
                'deleted_at' => Carbon::now(),
            ]);
            $eco_com->procedure_records()->create([
                'user_id' => Auth::user()->id,
                'record_type_id' => 9,
                'wf_state_id' => Util::getRol()->wf_states->first()->id ?? $eco_com->wf_current_state_id,
                'date' => Carbon::now(),
                'message' => "El usuario " . Auth::user()->username  . " eliminó la observación " . $observation->name . "."
            ]);
        } else {
            return response()->json(['errors' => ['El Trámite no tiene esa observación']], 422);
        }
    }
}
