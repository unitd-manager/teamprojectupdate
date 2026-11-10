<?
$cpCfg = array();

//Give the size in MB
$cpCfg['cp.maxUploadLimit']          = '2';
$cpCfg['cp.imageFileTypeExts']       = '*.gif; *.jpg; *.png';
//$cpCfg['cp.attachmentFileTypeExts']  = '*.pdf; *.doc; *.docx; *.DOC; *.DOCX; *.PDF; *.PPT; *.ppt; *.XLS; *.xls; *.XLSX; *.xlsx';
//================ OPPORTUNITY ================//
$cpCfg['m.manPower.oppurtunity.codeEditable']    = 0;
$cpCfg['m.manPower.oppurtunity.showQuoteRef']    = 1;
$cpCfg['m.manPower.opportunity.hasAgentMail']    = true;

//================ PROJECT ================//
$cpCfg['m.manPower.project.startDateOnConversion']       = 'estimated_start_date';
$cpCfg['m.manPower.project.carryForwardTaskTimeFromOpp'] = 1;
$cpCfg['m.manPower.project.showPaymentTerms']            = 0;
$cpCfg['m.manPower.project.valueFieldLabel']             = "Project Value";
$cpCfg['m.manPower.project.valueField']                  = "project_value";
$cpCfg['m.manPower.project.commissionLabel']             = "Project Commission";
$cpCfg['m.manPower.project.showStage']                   = 0;
$cpCfg['m.manPower.project.showRefValue']                = 0;
$cpCfg['m.manPower.project.showTaskSummaryReport']       = 0;
$cpCfg['m.manPower.project.showCostingTable']            = 0;
$cpCfg['m.manPower.project.showInvoiceRef']              = 0;
$cpCfg['m.manPower.project.codeEditable']                = 0;
/*$cpCfg['m.manPower.project.defaultSQL']                  = "(
   LOWER(p.status) = 'wip'
OR LOWER(p.status) = 'billable'
OR LOWER(p.status) = 'billed'
)"; */

//================ CALL REGISTRY ================//
$cpCfg['m.manPower.callRegistry.hasCandidate'] = true;
$cpCfg['m.manPower.callRegistry.companyFromProjectModuleForCrm'] = false;
$cpCfg['m.manPower.callRegistry.hasNoOfCandidate'] = true;

/********************* STAFF ******************/
$cpCfg['m.manPower.staff.showFldSensitiveDetails'] = 0; // Whether to show budget commission for specific staff in Stepworks
$cpCfg['m.manPower.hasStaffGroup']                 = 0;
$cpCfg['m.manPower.staff.showStaffDescription']    = 0;
$cpCfg['m.manPower.staff.showCountry']             = 0;
$cpCfg['m.manPower.staff.showUserGroup']           = 0;
$cpCfg['m.manPower.staff.showShortCode']           = 0;
$cpCfg['m.manPower.staff.hasZipCode']              = false;
$cpCfg['m.manPower.staff.hasChangePasswordNextLogin']  = false;
$cpCfg['m.manPower.staff.hasStaffCommission']      = false;
$cpCfg['m.manPower.staff.hasCommissionDetails']    = false;

/********************* AGENT ******************/
$cpCfg['m.manPower.agent.showCategory']               = 0;

/********************* INVOICE ******************/
$cpCfg['m.manPower.invoice.hasMultiBranches']           = 0;
$cpCfg['m.manPower.invoice.hasMultipleCompanyAddress']  = 0;
$cpCfg['m.manPower.invoice.hasMultiCurrency']           = 0;
$cpCfg['m.manPower.invoice.showRefValue']               = 0;
$cpCfg['m.manPower.invoice.currencyDD']                 = 0;
$cpCfg['m.manPower.invoice.hasAutoAffix']          		= 0;
$cpCfg['m.manPower.invoice.CodeEditable']          		= 0;

//------------ CONTACT -------------//
$cpCfg['m.manPower.contact.showEvent']        = 0;
$cpCfg['m.manPower.contact.showInterest']     = 1;
$cpCfg['m.manPower.contact.showAttachment']   = 0;
$cpCfg['m.manPower.contact.showOrders']       = false;
$cpCfg['m.manPower.contact.hasWebLogin']      = 1;
$cpCfg['m.manPower.contact.showUsername']     = 0;
$cpCfg['m.manPower.contact.showStaffDetail']  = 0;
$cpCfg['m.manPower.contact.hasCompanyTable']  = 0;
$cpCfg['m.manPower.contact.showContentLink']  = false;
$cpCfg['m.manPower.contact.hasSalutation']    = false;
$cpCfg['m.manPower.contact.showInterestInImport'] = false;
$cpCfg['m.manPower.contact.showLangPrefernce'] = false;
$cpCfg['m.manPower.contact.flagInvalidEmails'] = false;
$cpCfg['m.manPower.contact.specialSearchArr'] = array(
     "Subscribed"
    ,"Not-Subscribed"
    ,"Flagged"
    ,"Not-Flagged"
);

$cpCfg['m.manPower.project.stateListArr'] = array(
	 "Alabama"
	,"Alaska"
	,"Arizona"
	,"Arkansas"
	,"California"
	,"Colorado"
	,"Connecticut"
	,"Delaware"
	,"Florida"
	,"Georgia"
	,"Hawaii"
	,"Idaho"
	,"Illinois"
	,"Indiana"
	,"Iowa"
	,"Kansas"
	,"Kentucky"
	,"Louisiana"
	,"Maine"
	,"Maryland"
	,"Massachusetts"
	,"Michigan"
	,"Minnesota"
	,"Mississippi"
	,"Missouri"
	,"Montana"
	,"Nebraska"
	,"Nevada"
	,"New Hampshire"
	,"New Jersey"
	,"New Mexico"
	,"New York"
	,"North Carolina"
	,"North Dakota"
	,"Ohio"
	,"Oklahoma"
	,"Oregon"
	,"Pennsylvania"
	,"Rhode Island"
	,"South Carolina"
	,"South Dakota"
	,"Tennessee"
	,"Texas"
	,"Utah"
	,"Vermont"
	,"Virginia"
	,"Washington"
	,"West Virginia"
	,"Wisconsin"
	,"Wyoming"
);

return $cpCfg;
