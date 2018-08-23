<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
 */

use Muserpol\DataTables\AffiliateContributionsDataTable;
use Muserpol\Models\Template;
use Illuminate\Support\Facades\Blade;
use Muserpol\Models\RetirementFund\RetirementFund;
use Muserpol\Models\RetirementFund\RetFunProcedure;

Route::get('/logout', 'Auth\LoginController@logout');
Route::get('/minor', 'HomeController@minor')->name("minor");

Auth::routes();

//afiliates
Route::group(['middleware' => ['auth']], function () {
	App::setLocale("es");

	Route::get('/changerol', 'UserController@changerol')->name('changerol');
	Route::post('postchangerol', 'UserController@postchangerol');

	Route::group(['middleware' => ['session']], function () {

		Route::get('/', 'HomeController@index')->name("main");

	//Roles y permisos
		Route::resource('permission', 'PermissionController');

		Route::resource('user', 'UserController');
		Route::get('user/create', 'UserController@create');
		Route::post('registrar', ['as' => 'registrar', 'uses' => 'UserController@store']);
		Route::get('/{id}/edit', 'UserController@edit');
		Route::post('/update/{id}', 'UserController@store');
		Route::get('user/inactive/{user}', 'UserController@inactive');
		Route::get('user/active/{user}', 'UserController@active');
	//Route::get('users/index','UserController@index');
		Route::get('usersGetData', 'UserController@getUserDatatable')->name('user_list');

        //ROUTES TO E SYSTEM PARAMENTERS
		Route::get('ret_fun_settings', 'HomeController@retFunSettings');
		Route::resource('ret_fun_procedure', 'RetFunProcedureController');

		Route::resource('affiliate', 'AffiliateController');

		Route::patch('/update_affiliate/{affiliate}', 'AffiliateController@update')->name('update_affiliate');
		Route::patch('/update_affiliate_police/{affiliate}', 'AffiliateController@update_affiliate_police')->name('update_affiliate_police');

		Route::patch('/update_beneficiaries/{retirement_fund}', 'RetirementFundController@updateBeneficiaries')->name('update_beneficiaries');

	//SpouseControler
		Route::patch('/update_spouse/{affiliate_id}', 'SpouseController@update')->name('update_spouse');

		Route::get('get_all_affiliates', 'AffiliateController@getAllAffiliates');

	//Scanned Documents
		Route::resource('scanned_documents','ScannedDocumentController');
		Route::get('document_scanned/{affiliate_id}','ScannedDocumentController@create_document')->name('document_scanned');	


	//retirement fund
	//RetirementFundRequirements
	//Route::resource('ret_fun', 'RetirementFundRequirementController@retFun');
		Route::get('affiliate/{affiliate}/ret_fun', 'RetirementFundRequirementController@retFun');
	// Route::get('/home', 'HomeController@index')->name('home');
		Route::get('get_all_ret_fun', 'RetirementFundController@getAllRetFun');
		Route::resource('ret_fun', 'RetirementFundController');
		Route::get('ret_fun/{ret_fun_id}/qualification', 'RetirementFundController@qualification')->name('ret_fun_qualification');
		Route::get('ret_fun/{ret_fun_id}/get_average_quotable', 'RetirementFundController@getAverageQuotable');
		Route::get('ret_fun/{ret_fun_id}/qualification/certification', 'RetirementFundController@qualificationCertification')->name('qualification_certification');
		Route::get('ret_fun/{ret_fun_id}/save_average_quotable', 'RetirementFundController@saveAverageQuotable')->name('save_average_quotable');
		Route::patch('ret_fun/{ret_fun_id}/save_total_ret_fun', 'RetirementFundController@saveTotalRetFun')->name('save_total_ret_fun');
		Route::patch('ret_fun/{ret_fun_id}/save_percentages', 'RetirementFundController@savePercentages')->name('save_percentages');
		Route::patch('ret_fun/{ret_fun_id}/save_percentages_availability', 'RetirementFundController@savePercentagesAvailability')->name('save_percentages_availability');
		Route::patch('ret_fun/{ret_fun_id}/save_total_ret_fun_availability', 'RetirementFundController@saveTotalRetFunAvailability')->name('save_total_ret_fun_availability');
		Route::get('get_data_certification/{ret_fun_id}', 'RetirementFundController@getDataQualificationCertification')->name('get_data_certification');
		Route::get('get_data_availability/{ret_fun_id}', 'RetirementFundController@getDataQualificationAvailability')->name('get_data_availability');
		Route::get('affiliate/{affiliate}/procedure_create', 'RetirementFundRequirementController@generateProcedure');
		Route::resource('ret_fun_observation','RetirementFundObservationController');
		Route::post('ret_fun/{ret_fun_id}/edit_requirements', 'RetirementFundController@editRequirements')->name('edit_requirements');

	//Retirement Fund Certification
		Route::get('ret_fun/{retirement_fund}/print/reception', 'RetirementFundCertificationController@printReception')->name('ret_fun_print_reception');
		Route::get('affiliate/{affiliate}/print/file', 'RetirementFundCertificationController@printFile')->name('ret_fun_print_file');
		Route::get('ret_fun/{retirement_fund}/print/legal_review', 'RetirementFundCertificationController@printLegalReview')->name('ret_fun_print_legal_review');
		Route::get('ret_fun/{retirement_fund}/print/beneficiaries_qualification', 'RetirementFundCertificationController@printBeneficiariesQualification')->name('ret_fun_print_beneficiaries_qualification');
		Route::get('ret_fun/{retirement_fund}/print/qualification_average_salary_quotable', 'RetirementFundCertificationController@printQualificationAverageSalaryQuotable')->name('ret_fun_print_qualification_average_salary_quotable');
		Route::get('ret_fun/{retirement_fund}/print/data_qualification', 'RetirementFundCertificationController@printDataQualification')->name('ret_fun_print_data_qualification');
		Route::get('ret_fun/{retirement_fund}/print/data_qualification_availability', 'RetirementFundCertificationController@printDataQualificationAvailability')->name('ret_fun_print_data_qualification_availability');
		Route::get('ret_fun/{retirement_fund}/print/data_qualification_ret_fun_availability', 'RetirementFundCertificationController@printDataQualificationRetFunAvailability')->name('ret_fun_print_data_qualification_ret_fun_availability');
		Route::get('ret_fun/{retirement_fund}/print/all_qualification', 'RetirementFundCertificationController@printAllQualification')->name('ret_fun_print_all_qualification');
		Route::get('ret_fun/{affiliate}/print/ret_fun_commitment_letter', 'RetirementFundCertificationController@printRetFunCommitmentLetter')->name('print_ret_fun_commitment_letter');
		Route::get('ret_fun/{affiliate}/print/voucher/{voucher}', 'RetirementFundCertificationController@printVoucher')->name('ret_fun_print_voucher');
		Route::get('print_contributions_quote', 'RetirementFundCertificationController@printDirectContributionQuote');
		Route::get('ret_fun/{retirement_fund}/print/legal_dictum', 'RetirementFundCertificationController@printLegalDictum')->name('ret_fun_print_legal_dictum');
		Route::get('ret_fun/{retirement_fund}/print/headship_review', 'RetirementFundCertificationController@printHeadshipReview')->name('ret_fun_print_headship_review');
		Route::get('ret_fun/{retirement_fund}/print/legal_resolution', 'RetirementFundCertificationController@printLegalResolution')->name('ret_fun_print_legal_resolution');

	//Quota Aid Certification
		Route::get('quota_aid/{affiliate}/print/quota_aid_commitment_letter', 'QuotaAidCertificationController@printQuotaAidCommitmentLetter')->name('print_quota_aid_commitment_letter');
		Route::get('quota_aid/{affiliate}/print/quota_aid_voucher/{voucher}', 'QuotaAidCertificationController@printVoucherQuoteAid')->name('quota_aid_print_voucher');
		Route::get('print_contributions_quote_aid', 'QuotaAidCertificationController@printDirectContributionQuoteAid');

		Route::get('quota_aid/{quota_aid}/print/reception', 'QuotaAidCertificationController@printReception')->name('quota_aid_print_reception');

		Route::get('affiliate/{affiliate}/ret_fun/create', 'RetirementFundController@generateProcedure')->middleware('affiliate_has_ret_fun')->name('create_ret_fun');
		Route::post('ret_fun/{retirement_fund}/legal_review/create', 'RetirementFundController@storeLegalReview')->name('store_ret_fun_legal_review_create');

		Route::patch('/update_information_rf', 'RetirementFundController@updateInformation')->name('update_information_rf');
	// tags
		Route::resource('/tag', "TagController");
		Route::get('/tag_wf_state', "TagController@wfState")->name('tag_wf_state');
		Route::get('/tag_ret_fun/{ret_fun_id}', "TagController@retFun")->name('tag_ret_fun');
		Route::post('/update_tag_ret_fun/{ret_fun_id}', "TagController@updateRetFun")->name('update_tag_ret_fun');
		Route::get('get_tags', 'TagController@getTags')->name('tag_list');
		Route::get('/get_tag/{tag_id}', 'TagController@getTag');
		Route::get('/tag_wf_state_list', 'TagController@tagWfState');
		Route::get('/get_tag_wf_state', 'TagController@getTagWfState');
		Route::post('/update_tag_wf_state', 'TagController@updateTagWfState');

	//QuotaAidMortuory
		Route::get('affiliate/{affiliate}/quota_aid/create', 'QuotaAidMortuaryController@generateProcedure')->name('create_quota_aid');
		Route::get('get_all_quota_aid', 'QuotaAidMortuaryController@getAllQuotaAid');
		Route::resource('quota_aid', 'QuotaAidMortuaryController');

		Route::resource('affiliate_folder', 'AffiliateFolderController');
		Route::post('editFolder', 'AffiliateFolderController@editFolder')->name('editFolder');
		Route::post('deleteFolder', 'AffiliateFolderController@destroy')->name('deleteFolder');
        //searcherController
		Route::get('search/{ci}', 'SearcherController@search');
		Route::get('search_ajax', 'SearcherController@searchAjax');

        //Contributions
		Route::resource('contribution', 'ContributionController');
		Route::get('affiliate/{affiliate}/contribution/edit', 'ContributionController@getAffiliateContributions')->name('edit_contribution');
		Route::get('affiliate/{affiliate}/contribution/detail', 'ContributionController@getContributionsByMonth')->name('detail_contribution');		
		Route::get('affiliate/{affiliate}/contribution/direct', 'ContributionController@directContributions')->name('direct_contribution');
		Route::post('store_contributions', 'ContributionController@storeContributions');

		Route::resource('reimbursement', 'ReimbursementController');
		Route::resource('aid_reimbursement', 'AidReimbursementController');

		//selectContributions
		Route::get('ret_fun/{ret_fun_id}/selectcontributions', 'ContributionController@selectContributions')->name('select_contributions');
		Route::post('ret_fun/savecontributions', 'ContributionController@saveContributions')->name('save_contributions');

		//contributions certification
		//contributions certification 60, disponibilidad, item 0
		Route::get('ret_fun/{retirement_fund}/print/certification', 'RetirementFundCertificationController@printCertification')->name('ret_fun_print_certification');
		Route::get('ret_fun/{retirement_fund}/print/cer_availability', 'RetirementFundCertificationController@printCertificationAvailability')->name('ret_fun_print_certification_availability');
		Route::get('ret_fun/{retirement_fund}/print/cer_itemcero', 'RetirementFundCertificationController@printCertificationItem0')->name('ret_fun_print_certification_item0');
		Route::get('ret_fun/{retirement_fund}/print/security_certification', 'RetirementFundCertificationController@printCertificationSecurity')->name('ret_fun_print_security_certification');
		Route::get('ret_fun/{retirement_fund}/print/contributions_certification', 'RetirementFundCertificationController@printCertificationContributions')->name('ret_fun_print_contributions_certification');		
        //AidContributions
		Route::resource('aid_contribution', 'AidContributionController');
		Route::get('affiliate/{affiliate}/aid_contribution/edit', 'AidContributionController@getAffiliateContributions')->name('edit_aid_contribution');
		Route::get('affiliate/{affiliate}/aid_contribution/direct', 'AidContributionController@directContributions')->name('direct_aid_contribution');		
		Route::post('store_aid_contributions', 'AidContributionController@storeContributions');
		Route::get('affiliate/{affiliate}/aid_contribution', 'AidContributionController@show')->name('show_aid_contribution');
        //Route::get('get_affiliate_aid_contributions/{affiliate}', 'AidContributionController@getAffiliateAidContributionsDatatables')->name('affiliate_aid_contributions');


        //Contributions
		Route::resource('contribution', 'ContributionController');
		Route::get('affiliate/{affiliate}/contribution/create', 'ContributionController@generateContribution')->name('create_contribution');
		Route::get('affiliate/{affiliate}/contribution', 'ContributionController@show')->name('show_contribution');
		Route::get('get_affiliate_contributions/{affiliate}', 'ContributionController@getAffiliateContributionsDatatables')->name('affiliate_contributions');
		Route::get('affiliate/{affiliate_id}/aid/contributions', 'AidContributionController@aidContributions');
		Route::get('get_aid_contributions/{affiliate}', 'AidContributionController@getAllContributionsAid')->name('affiliate_aid_contributions');

	// Route::get('AidContribution', function(){
	// 	return view('aid_contribution');
	// });
	// Route::get('get_affiliate_contributions/{affiliate_id}', function (AffiliateContributionsDataTable $dataTable, $affiliate_id) {
	// 	return $dataTable->with('affiliate_id', $affiliate_id)
	// 					 ->render('contribution.show');
	// });
	// Route::get('get_affiliate_contributions/{affiliate}', 'ContributionController@getAffiliateContributions')->name('affiliate_contributions');

		Route::post('get-interest', 'ContributionController@getInterest');
		Route::post('get-interest-aid', 'AidContributionController@getInterest');
		Route::get('calculate_reimbursement/{affiliate}/{amount}/{month}','ReimbursementController@caculateContribution');
		Route::post('contribution_save', 'ContributionController@storeDirectContribution');
		Route::post('aid_contribution_save', 'AidContributionController@storeDirectContribution');
		Route::post('print_contributions_quote', 'RetirementFundCertificationController@printDirectContributionQuote');

		Route::get('print_contributions_quote', 'RetirementFundCertificationController@printDirectContributionQuote');
		Route::get('print_contributions_quote_aid', 'QuotaAidCertificationController@printDirectContributionQuoteAid');
        //Commitments
		Route::resource('commitment', 'ContributionCommitmentController');
		Route::resource('aid_commitment', 'AidCommitmentController');


		//inbox
		Route::get('inbox', function () {
			return redirect('inbox/received');
		});
		Route::get('inbox/received', 'InboxController@received')->name('inbox_received');
		Route::get('inbox/edited', 'InboxController@edited')->name('inbox_edited');
		Route::post('inbox_send_forward', 'InboxController@sendForward')->name('inbox_send_forward');
		Route::post('inbox_send_backward', 'InboxController@sendBackward')->name('inbox_send_backward');
		Route::patch('inbox_validate_doc/{doc_id}', 'InboxController@validateDoc')->name('inbox_validate_doc');
		Route::patch('inbox_invalidate_doc/{doc_id}', 'InboxController@invalidateDoc')->name('inbox_validate_doc');


		//dictamen legal routes
		Route::get('ret_fun/{retirement_fund}/dictamen_legal', 'RetirementFundController@dictamenLegal')->name('ret_fun_dictamen_legal');		

		//helpers route

	});
});


