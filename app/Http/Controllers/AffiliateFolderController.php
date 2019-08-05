<?php

namespace Muserpol\Http\Controllers;

use Illuminate\Http\Request;
use Muserpol\Models\AffiliateFolder;
use Muserpol\Models\Module;
use Muserpol\Models\Affiliate;

class AffiliateFolderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $folder = new AffiliateFolder;
        // $folder=AffiliateFolder::find($request->affiliatefolder_id);
        $this->authorize('create', $folder);
        $folder->affiliate_id = $request->affiliate_id;
        $folder->procedure_modality_id = $request->procedure_modality_id;
        $folder->code_file = $request->code_file;
        $folder->folder_number = $request->folder_number;
        $folder->note = $request->note;
        if($request->is_paid == 'paid')
            $folder->is_paid = true;
        if($request->is_paid == 'nopaid')
            $folder->is_paid = false;
        $folder->save();
        return back()->withInput();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $folder=AffiliateFolder::find($request->folder_id);
        $this->authorize('delete', $folder);
        $folder->delete();
        return back()->withInput();
    }

    public function updateFileCode(Request $request) {

        //return $request->affiliate_id;
        $affiliate = Affiliate::find($request->affiliate_id);
        $affiliate->file_code = $request->file_code;
        $affiliate->save();
        return back()->withInput();
    }
    
    public function editFolder(Request $request){        
        $folder=AffiliateFolder::find($request->folder_id);
        $this->authorize('update', $folder);
        $folder->affiliate_id = $request->affiliate_id;
        $folder->procedure_modality_id = $request->procedure_modality_id;
        $folder->code_file = $request->code_file;
        $folder->folder_number = $request->folder_number;
        $folder->note = $request->note;
        if($request->is_paid == 'paid')
            $folder->is_paid = true;
        if($request->is_paid == 'nopaid')
            $folder->is_paid = false;
        $folder->save();
        return back()->withInput();
    }
}
