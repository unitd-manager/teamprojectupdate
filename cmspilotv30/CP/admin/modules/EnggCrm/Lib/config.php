<?
$cpCfg = array();

$cpCfg['m.enggCrm.hasMultipleCompanyAddress'] = 1;
$cpCfg['m.enggCrm.staffFieldLabel']           = "Staff";
$cpCfg['m.enggCrm.staffTeamLabel']            = "Team";
$cpCfg['cp.hasFirstRoomValueInStaff']         = 0; // whether to show the default section name fld in staff
$cpCfg['m.enggCrm.hasQuotingModule']          = 1;
$cpCfg['cp.showAnchorInLinkPortal']           = 1;
$cpCfg['m.enggCrm.hasMultiBranches']          = 0;
$cpCfg['m.enggCrm.hasMultiCurrency']          = 0;
$cpCfg['m.enggCrm.baseCurrency']              = 'US$';
$cpCfg['m.enggCrm.refCurrency']               = 'INR';

$cpCfg['m.enggCrm.showSensitiveDetails']      = 0; // Whether to show budget commission for specific staff in Stepworks
$cpCfg['cp.pagetoReturnAfterSave']     = 'detailFromEdit';

//================ ATTENDANCE ================//
$cpCfg['m.enggCrm.attendance.hasMultipleSessions']  = 0; // For session wise in and out time

//=============== used in enggCrm & opportunity ===================//
$cpCfg['m.enggCrm.oppurtunity.hasSameCode']    = 0;

//======================= used in quote & opportunity ============================//
$cpCfg['m.enggCrm.quote.CodeStartIndex'] = 3;

//================ COMPANY ================//
$cpCfg['m.enggCrm.company.showChineseFields']  = 0;
$cpCfg['m.enggCrm.company.showProjOppValues']  = 0;
$cpCfg['m.enggCrm.company.showCode']           = 0;
$cpCfg['m.enggCrm.hasMultipleCompanyAddress']  = 0;
$cpCfg['m.enggCrm.company.showCode']           = 0;
$cpCfg['m.enggCrm.company.groupNameDD']        = 0;
$cpCfg['m.enggCrm.company.showAttachment']     = 0;
$cpCfg['m.enggCrm.company.showProductLink']    = 0;

//================ CONTACT ================//
$cpCfg['m.enggCrm.contact.showEvent']           = 0;
$cpCfg['m.enggCrm.contact.showDetail']          = 0;
$cpCfg['m.enggCrm.contact.showChineseFields']   = 0;
$cpCfg['m.enggCrm.contact.showPersonalAddress'] = 1;
$cpCfg['m.enggCrm.contact.showCategory']        = 0;

//================ EMPLOYEE ================//
$cpCfg['m.enggCrm.employee.showCategory']        = 0;
$cpCfg['m.enggCrm.employee.showEvent']           = 0;
$cpCfg['m.enggCrm.employee.showDetail']          = 0;
$cpCfg['m.enggCrm.employee.showChineseFields']   = 0;
$cpCfg['m.enggCrm.employee.showPersonalAddress'] = 1;

//================ INVOICE ================//
$cpCfg['m.enggCrm.invoice.CodeEditable']          = 0;
$cpCfg['m.enggCrm.invoice.hasAutoAffix']          = 0;
$cpCfg['m.enggCrm.invoice.showRefValue']          = 0;
$cpCfg['m.enggCrm.invoice.currencyDD']            = 0;
$cpCfg['m.enggCrm.invoice.showInvoiceItemInPortal']  = 0;
$cpCfg['m.enggCrm.invoice.showInvoiceTermsInEditForm'] = 1;
$cpCfg['m.enggCrm.invoice.showLocationInEditForm'] = 0;

//================ OPPORTUNITY ================//
$cpCfg['m.enggCrm.oppurtunity.codeEditable']    = 0;
$cpCfg['m.enggCrm.oppurtunity.showQuoteRef']    = 1;
$cpCfg['m.enggCrm.opportunity.showProductList'] = 0;

//================ PROJECT ================//
$cpCfg['m.enggCrm.project.startDateOnConversion']       = 'estimated_start_date';
$cpCfg['m.enggCrm.project.carryForwardTaskTimeFromOpp'] = 1;
$cpCfg['m.enggCrm.project.showPaymentTerms']            = 0;
$cpCfg['m.enggCrm.project.valueFieldLabel']             = "Project Value";
$cpCfg['m.enggCrm.project.valueField']                  = "project_value";
$cpCfg['m.enggCrm.project.commissionLabel']             = "Project Commission";
$cpCfg['m.enggCrm.project.showStage']                   = 0;
$cpCfg['m.enggCrm.project.showTaskSummaryReport']       = 0;
$cpCfg['m.enggCrm.project.showCostingTable']            = 0;
$cpCfg['m.enggCrm.project.showInvoiceRef']              = 0;
$cpCfg['m.enggCrm.project.codeEditable']                = 0;
$cpCfg['m.enggCrm.project.showProductList'] 			= 0;
$cpCfg['m.enggCrm.project.defaultSQL']                  = "(
   LOWER(p.status) = 'wip'
OR LOWER(p.status) = 'billable'
OR LOWER(p.status) = 'billed'
)";
$cpCfg['m.enggCrm.project.addShippingAddressInPO']      = 0;

//================ QUOTE ================//
$cpCfg['m.enggCrm.quote.codeStartIndexFromPro']   = 1;

//================ TASK ================//
$cpCfg['m.enggCrm.task.taskListFieldsOrderGroup'] = 1;
$cpCfg['m.enggCrm.task.hightlightDue']            = 1;
$cpCfg['m.enggCrm.task.hasTaskHistory']           = 0;
$cpCfg['m.enggCrm.task.showReleaseStatus']        = 0;
$cpCfg['m.enggCrm.task.daysLbl']                  = 0;

//================ TIMESHEET ================//
$cpCfg['m.enggCrm.timesheet.daysLbl'] = 0;

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
    ,"enggCrmCategory"   => "enggCrm Category"
    ,"percentCompleted"  => "Percent Completed"
    ,"enggCrmDifficulty" => "enggCrm Difficulty"
    ,"enggCrmStatus"     => "enggCrm Status"
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

$cpCfg['m.enggCrm.creditCardPayments.monthArr'] = array(
     'Current Month'
    ,'Show All'
);

/************** PURCHASE ORDER *************/
$cpCfg['m.enggCrm.purchaseOrder.statusArr'] = array (
     'new'
    ,'confirmed'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

/************** SUPPLIER *************/
$cpCfg['m.enggCrm.supplier.hasDiscountPercent'] = false;
$cpCfg['m.enggCrm.supplier.hasCstNo'] = false;
$cpCfg['m.enggCrm.supplier.hasTinNo'] = false;

//------------- ORDER -------------//
$cpCfg['m.enggCrm.order.itemsMainModule'] = 'ecommerce_product';
$cpCfg['m.enggCrm.product.hasProductItem'] = false;
$cpCfg['m.enggCrm.order.showOrganization'] = false;
$cpCfg['m.enggCrm.order.hasDiscount'] = false;
$cpCfg['m.enggCrm.order.showShipmentStatus'] = false;
$cpCfg['m.enggCrm.order.statusArr'] = array (
     'New'
    ,'Due'
    ,'Paid'
    ,'Cancelled'
    ,'Pending'
);
$cpCfg['m.enggCrm.order.addGstAmountToOrderTotal'] = false;
$cpCfg['m.enggCrm.order.showAttachment'] = 0;
$cpCfg['m.enggCrm.order.showReceiptButton'] = 1;
$cpCfg['m.enggCrm.order.showInvoiceButton'] = 1;
$cpCfg['m.enggCrm.order.showInvoicePortalDisplay'] = 1;
$cpCfg['m.enggCrm.order.showReceiptPortalDisplay'] = 1;
$cpCfg['m.enggCrm.order.showOrderItemDisplay'] = 1;

//------------- PAYROLL -------------//
$cpCfg['m.payroll.jobInformation.showCategory'] = 0;
$cpCfg['m.project.hasMultipleCompanyAddress']   = 0;
$cpCfg['m.project.employee.showEvent']          = 0;
$cpCfg['m.project.jobInformation.showEvent']          = 0;

return $cpCfg;
