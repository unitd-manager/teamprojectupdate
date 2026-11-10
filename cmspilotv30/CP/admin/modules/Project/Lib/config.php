<?
$cpCfg = array();

$cpCfg['m.project.hasMultipleCompanyAddress'] = 1;
$cpCfg['m.project.staffFieldLabel']           = "Staff";
$cpCfg['m.project.staffTeamLabel']            = "Team";
$cpCfg['cp.hasFirstRoomValueInStaff']         = 0; // whether to show the default section name fld in staff
$cpCfg['m.project.hasQuotingModule']          = 1;
$cpCfg['cp.showAnchorInLinkPortal']           = 1;
$cpCfg['m.project.hasMultiBranches']          = 0;
$cpCfg['m.project.hasMultiCurrency']          = 0;
$cpCfg['m.project.baseCurrency']              = 'US$';
$cpCfg['m.project.refCurrency']               = 'INR';

$cpCfg['m.project.showSensitiveDetails']      = 0; // Whether to show budget commission for specific staff in Stepworks
$cpCfg['cp.pagetoReturnAfterSave']     = 'detailFromEdit';

//================ ATTENDANCE ================//
$cpCfg['m.project.attendance.hasMultipleSessions']  = 0; // For session wise in and out time

//=============== used in project & opportunity ===================//
$cpCfg['m.project.oppurtunity.hasSameCode']    = 0;

//======================= used in quote & opportunity ============================//
$cpCfg['m.project.quote.CodeStartIndex'] = 3;

//================ COMPANY ================//
$cpCfg['m.project.company.showChineseFields']  = 0;
$cpCfg['m.project.company.showProjOppValues']  = 0;
$cpCfg['m.project.company.showCode']           = 0;
$cpCfg['m.project.hasMultipleCompanyAddress']  = 0;
$cpCfg['m.project.company.showCode']           = 0;
$cpCfg['m.project.company.groupNameDD']        = 0;
$cpCfg['m.project.company.showAttachment']     = 0;
$cpCfg['m.project.company.showProductLink']    = 0;

//================ CONTACT ================//
$cpCfg['m.project.contact.showInterest']        = 1;
$cpCfg['m.project.contact.showEvent']           = 0;
$cpCfg['m.project.contact.showDetail']          = 0;
$cpCfg['m.project.contact.showChineseFields']   = 0;
$cpCfg['m.project.contact.showPersonalAddress'] = 1;
$cpCfg['m.project.contact.showCategory']        = 0;

//================ INVOICE ================//
$cpCfg['m.project.invoice.CodeEditable']          = 0;
$cpCfg['m.project.invoice.hasAutoAffix']          = 0;
$cpCfg['m.project.invoice.showRefValue']          = 0;
$cpCfg['m.project.invoice.currencyDD']            = 0;
$cpCfg['m.project.invoice.showInvoiceItemInPortal']  = 0;

//================ OPPORTUNITY ================//
$cpCfg['m.project.oppurtunity.codeEditable']    = 0;
$cpCfg['m.project.oppurtunity.showQuoteRef']    = 1;
$cpCfg['m.project.opportunity.showProductList'] = 0;

//================ PROJECT ================//
$cpCfg['m.project.project.startDateOnConversion']       = 'estimated_start_date';
$cpCfg['m.project.project.carryForwardTaskTimeFromOpp'] = 1;
$cpCfg['m.project.project.showPaymentTerms']            = 0;
$cpCfg['m.project.project.valueFieldLabel']             = "Project Value";
$cpCfg['m.project.project.valueField']                  = "project_value";
$cpCfg['m.project.project.commissionLabel']             = "Project Commission";
$cpCfg['m.project.project.showStage']                   = 0;
$cpCfg['m.project.project.showRefValue']                = 0;
$cpCfg['m.project.project.showTaskSummaryReport']       = 0;
$cpCfg['m.project.project.showCostingTable']            = 0;
$cpCfg['m.project.project.showInvoiceRef']              = 0;
$cpCfg['m.project.project.codeEditable']                = 0;
$cpCfg['m.project.project.showProductList'] 			= 0;
$cpCfg['m.project.project.defaultSQL']                  = "(
   LOWER(p.status) = 'wip'
OR LOWER(p.status) = 'billable'
OR LOWER(p.status) = 'billed'
)";
$cpCfg['m.project.project.showSecretarialDetails'] 		= 0;  // For Nahvibiz CRM
$cpCfg['m.project.project.showTaxDetails'] 			    = 0;  // For Nahvibiz CRM

//================ QUOTE ================//
$cpCfg['m.project.quote.codeStartIndexFromPro']   = 1;

//================ TASK ================//
$cpCfg['m.project.task.taskListFieldsOrderGroup'] = 1;
$cpCfg['m.project.task.hightlightDue']            = 1;
$cpCfg['m.project.task.hasTaskHistory']           = 0;
$cpCfg['m.project.task.showReleaseStatus']        = 0;
$cpCfg['m.project.task.daysLbl']                  = 0;

//================ TIMESHEET ================//
$cpCfg['m.project.timesheet.daysLbl'] = 0;

$cpCfg['m.core.valuelist.recordTypeArr'] = array(
     "clientType"        => "Client Type"
    ,"contactCategory"   => "Contact Category"
    ,"companyCategory"   => "Company Category"
    ,"contactTitle"      => "Contact Title"
    ,"companySize"       => "Company Size"
    ,"companyIndustry"   => "Company Industry"
    ,"companySource"     => "Company Source"
    ,"companyStatus"     => "Company Status"
    ,"invoiceStatus"     => "Invoice Status"
    ,"invoiceTerms"      => "Invoice Terms"
    ,"invoiceNotes"      => "Invoice Notes"
    ,"invoiceType"       => "Invoice Type"
    ,"opportunityChance" => "Opportunity Chance"
    ,"opportunitySourceChannel" => "Opportunity Source Channel"
    ,"opportunityStatus" => "Opportunity Status"
    ,"projectCategory"   => "Project Category"
    ,"percentCompleted"  => "Percent Completed"
    ,"projectDifficulty" => "Project Difficulty"
    ,"projectStatus"     => "Project Status"
    ,"serviceType"       => "Service Type"
    ,"staffStatus"       => "Staff Status"
    ,"staffTeam"         => "Staff Team"
    ,"staffType"         => "Staff Type"
    ,"supplierType"      => "Supplier Type"
    ,"taskCategory"      => "Task Category"
    ,"thirdPartyItem"    => "Third Party Item"
    ,"taskStatus"        => "Task Status"
    ,"paymentTerms"      => "Payment Terms"
    ,"deliveryTerms"     => "Delivery Terms"
    ,"shipmentType"      => "Shipment Type"
    ,"bank"              => "Bank"
    ,"creditInsurance"   => "Credit Insurance"
    ,"preferredCurrency" => "Preferred Currency"
    ,"unit"              => "Unit"
    ,"quoteType"         => "Quote Type"
    ,"quoteCurrency"     => "Quote Currency"
    ,"quoteStatus"       => "Quote Status"
    ,"quoteCategoryName" => "Quote Category Name"
    ,"quoteCategoryType" => "Quote Category Type"
    ,"quoteItemType"     => "Quote Item Type"
    ,"ideasStatus"       => "Ideas Status"
    ,"ideasByWhen"       => "Ideas By When"
    ,"currency"          => "Currency"
    ,"companyGroupName"  => "Company Group Name"
    ,"invoiceCurrency"   => "Invoice Currency"
);

$cpCfg['m.project.creditCardPayments.monthArr'] = array(
     'Current Month'
    ,'Show All'
);

return $cpCfg;
