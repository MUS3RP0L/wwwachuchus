<?php

namespace Muserpol\Http\Controllers;

use Illuminate\Http\Request;
use Muserpol\Models\Affiliate;
use Muserpol\Models\ScannedDocument;
use Muserpol\Models\ProcedureDocument;
use Muserpol\Models\AffiliateSubmittedDocument;
use Storage;
use Response;
use File;
class ScannedDocumentController extends Controller
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

    public function create_document($affiliate_id){
        $affiliate =Affiliate::find($affiliate_id);
        $procedure_documents = ProcedureDocument::all();
        if($affiliate_id){
        $affiliate_submitted_documents = AffiliateSubmittedDocument::all();
                
        $data = array('affiliate'=>$affiliate,'affiliate_submitted_documents'=>$affiliate_submitted_documents);
        }else{
        $affiliate_submitted_documents="";

        }
        
        return view('affiliates.create_scanned_document',$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         store(Request $request)
    {
        //
        // return $request->all();
        logger($request->all());
        $affiliate = Affiliate::find($request->affiliate_id);
        
        /////////////////////////
        $path = $request->file('archivo')->store('pdfs');
        // return $path;
        $document = new ScannedDocument;
        $document->affiliate_id = $request->affiliate_id;
        $document->url_file = $path;
        $document->procedure_document_id = $request->procedure_document_id;
        $document->comment = $request->comment;
        $document->due_date = $request->due_date;
        $document->save();
       
        return redirect('affiliate/'.$request->affiliate_id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $document = ScannedDocument::find($id);

        if(isset($document->id))
        {
            $response = Response::make( Storage::get($document->url_file), 200); 
            $response->header('Content-Type', 'application/pdf'); 
            return $response; 
        }

        return 'no existe el archivo con el id '.$id;
    
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
    public function destroy($id)
    {
        //
    }
}
