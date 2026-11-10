<?
$cpCfg = array();
$cpCfg['m.payroll.passTypeArray'] = array (
     "Citizen"
    ,"PR"
    ,"EP"
    ,"SP"
    ,"WP"
    ,"DP"
);

// Payroll config variables
$cpCfg['m.payrollManagement.hasSdlDeduction'] = 0;
$cpCfg['cp.mrpProducts']                 = 0;
$cpCfg['m.jobInformation.allowance1Lbl'] = 'Transport';
$cpCfg['m.jobInformation.allowance2Lbl'] = 'Entertainment';
$cpCfg['m.jobInformation.allowance3Lbl'] = 'Food';
$cpCfg['m.jobInformation.allowance4Lbl'] = 'Shift Allowance';
$cpCfg['m.jobInformation.allowance5Lbl'] = 'Others';
$cpCfg['m.jobInformation.deduction1Lbl'] = 'Housing';
$cpCfg['m.jobInformation.deduction2Lbl'] = 'Safety Items';
$cpCfg['m.jobInformation.deduction3Lbl'] = 'Others';
$cpCfg['m.payroll.startYear'] = '2020';
$arrayYear = array();
for ($i = $cpCfg['m.payroll.startYear']; $i<=date('Y'); $i++) {
    $arrayYear += [ $i => $i ];
}
$cpCfg['m.payroll.yearArr'] = $arrayYear;

$cpCfg['cp.captureAutoLogin'] = true;
$cpCfg['m.project.hasMultiCurrency'] = false;
$cpCfg['cp.attendanceTable']  = 'attendance';
$cpCfg['cp.footerCompanyLink']  = "<a href='http://www.usoftsolutions.com' target='_blank'><u>USS</u> <img height='25px' src='../admin/images/footer_right.png'/></a>";

$cpCfg['m.common.dashboard.pageCSSClass'] = 'hideboth';

$cpCfg['paymentReminder'] = 0;
$cpCfg['paymentReminder2'] = 0;

$cpCfg['cp.hasAccessModule'] = 1;
$cpCfg['m.webBasic.section.hasBanner']          = 1;
$cpCfg['m.core.staff.showUserGroup']            = 1;
$cpCfg['cp.hasProjectMg']                       = 1;
$cpCfg['m.enggCrm.oppurtunity.showQuoteRef']    = false;
$cpCfg['m.enggCrm.task.hasTaskHistory']         = 1;
$cpCfg['m.enggCrm.hasMultiBranches']            = 0;
$cpCfg['m.enggCrm.hasMultipleCompanyAddress']   = 0;
$cpCfg['m.enggCrm.hasMultiCurrency']            = 1;
$cpCfg['m.enggCrm.project.showRefValue']        = 1;
$cpCfg['m.enggCrm.invoice.showRefValue']        = 1;
$cpCfg['baseCurrency']                          = "INR";
$cpCfg['m.enggCrm.invoice.showLocationInEditForm'] = 1;
$cpCfg['m.enggCrm.project.addShippingAddressInPO'] = 1;
$cpCfg['m.core.valuelist.hasCode'] = 1; // used for year array for holidays in timesheet print ARIF
$cpCfg['cp.chooseSiteLbl'] = "Choose Country:";

$cpCfg['m.core.valuelist.recordTypeArr'] = array(
     "clientType"               => "Client Type"
    ,"contactCategory"          => "Contact Category"
    ,"companyCategory"          => "Company Category"
    ,"contactTitle"             => "Contact Title"
    ,"companySize"              => "Company Size"
    ,"companyIndustry"          => "Company Industry"
    ,"companySource"            => "Company Source"
    ,"companyStatus"            => "Company Status"
    ,"candidateQualification"   => "Qualification"
    ,"invoiceStatus"            => "Invoice Status"
    ,"invoiceTerms"             => "Invoice Terms"
    ,"invoiceNotes"             => "Invoice Notes"
    ,"invoiceType"              => "Invoice Type"
    ,"opportunityChance"        => "Opportunity Chance"
    ,"opportunitySourceChannel" => "Opportunity Source Channel"
    ,"opportunityStatus"        => "Opportunity Status"
    ,"projectCategory"          => "Project Category"
    ,"percentCompleted"         => "Percent Completed"
    ,"projectDifficulty"        => "Project Difficulty"
    ,"projectStatus"            => "Project Status"
    ,"serviceType"              => "Service Type"
    ,"staffStatus"              => "Staff Status"
    ,"staffTeam"                => "Staff Team"
    ,"staffType"                => "Staff Type"
    ,"supplierType"             => "Supplier Type"
    ,"taskCategory"             => "Task Category"
    ,"thirdPartyItem"           => "Third Party Item"
    ,"paymentTerms"             => "Payment Terms"
    ,"deliveryTerms"            => "Delivery Terms"
    ,"shipmentType"             => "Shipment Type"
    ,"bank"                     => "Bank"
    ,"taskStatus"               => "Task Status"
    ,"creditInsurance"          => "Credit Insurance"
    ,"preferredCurrency"        => "Preferred Currency"
    ,"unit"                     => "Unit"
    ,"quoteType"                => "Quote Type"
    ,"quoteCurrency"            => "Quote Currency"
    ,"quoteStatus"              => "Quote Status"
    ,"quoteCategoryName"        => "Quote Category Name"
    ,"quoteCategoryType"        => "Quote Category Type"
    ,"quoteItemType"            => "Quote Item Type"
    ,"ideasStatus"              => "Ideas Status"
    ,"ideasByWhen"              => "Ideas By When"
    ,"currency"                 => "Currency"
    ,"companyGroupName"         => "Company Group Name"
    ,"invoiceCurrency"          => "Invoice Currency"
    ,"taskHistoryPriority"      => "Task History Priority"
    ,"typeOfLeave"              => "Type of Leave"
    ,"employeeWorkType"         => "Employee Work Type"
    ,"positionType"             => "Position Type"
    ,"paymentType"              => "Payment Type"
    ,"invoiceTypes"             => "Invoice Types"
    ,"quoteSignatory"           => "Quote Signatory"
    ,"callRegistryIndustry"     => "Lead Industry"
    ,"callRegistryReffer"       => "Lead Reference"
    ,"quoteColumn"              => "Quote Column"
    ,"employeeStatus"           => "Employee Status"
    ,"nationality"              => "Nationality"
    ,"employeeCategory"         => "Employee Category"
    ,"singaporeHolidays"        => "Singapore Holidays"
    ,"timesheetType"            => "Timesheet Type"
    ,"services"                 => "Services"
    ,"projectDesignation"       => "Project Designation"
    ,"team"                     => "Team"
    ,"paymentQuoteType"         => "Payment Quote Type"
    ,"checklist"         => "Checklist"
    ,"popaymentType"         => "Payment"
    ,"warrantyType"         => "Warranty"
    ,"deliveryType"         => "Delivery"
    ,"priceBasisType"         => "PriceBasis"
    ,"documentType"         => "Document"
    ,"jobStatus"            => "Job Status"
    ,"contractType"         => "Contract Type"
    ,"paymentStatus"        => "Payment Status"
);

/* For help content */
$cpCfg['m.webBasic.section.showAdsBannerLink'] = false;
$cpCfg['m.webBasic.section.showCountry'] = 0;
$cpCfg['m.webBasic.section.showRefTitle'] = false;
$cpCfg['m.webBasic.section.showSpecialSearch'] = 0;
$cpCfg['m.webBasic.section.btnPosArr'] = array('Top');
$cpCfg['m.webBasic.content.hasRelatedContent'] = 0;
$cpCfg['m.webBasic.content.hasHistory'] = 0;
$cpCfg['m.webBasic.content.hasTab'] = 0;
$cpCfg['m.webBasic.section.showExtLinkField'] = 0;
$cpCfg['m.webBasic.section.showIntLinkField'] = 0;
$cpCfg['m.webBasic.section.showDescription'] = 0;
$cpCfg['m.webBasic.section.hasMemberOnly'] = 0;
$cpCfg['m.webBasic.section.hasNonMember'] = 0;
$cpCfg['m.webBasic.section.showMetaData'] = 0;
$cpCfg['m.webBasic.section.hasCSSStyleName'] = 0;
$cpCfg['m.webBasic.displayShowInNavFld'] = false;
$cpCfg['m.webBasic.section.hasCaption'] = 0;
$cpCfg['m.webBasic.section.showOkForWeb'] = false;
$cpCfg['m.webBasic.section.showOkForMobile'] = false;
$cpCfg['m.webBasic.section.showNoFollow'] = false;
$cpCfg['m.webBasic.section.hasAdSpaces'] = false;
$cpCfg['m.webBasic.section.showAliasToSection'] = false;
$cpCfg['m.webBasic.section.hasPicture'] = 0;
$cpCfg['m.webBasic.content.showCountry'] = 0;
$cpCfg['m.webBasic.content.showCloudTags'] = 0;
$cpCfg['m.webBasic.content.showMemberOnly'] = 0;
$cpCfg['m.webBasic.content.showCorrespondent'] = 0;
$cpCfg['m.webBasic.content.showJurisdiction'] = 0;
$cpCfg['m.webBasic.content.showMonthYearFilter'] = 0;
$cpCfg['m.webBasic.content.showExtLinkField'] = 0;
$cpCfg['m.webBasic.content.showIntLinkField'] = 0;
$cpCfg['m.webBasic.content.showLatest'] = 0;
$cpCfg['m.webBasic.content.showFavourite'] = 0;
$cpCfg['m.webBasic.content.showShortDesc'] = 0;
$cpCfg['m.webBasic.content.showEmbedCode'] = 0;
$cpCfg['m.webBasic.content.showMetaData'] = 0;
$cpCfg['m.webBasic.content.showDownloadBrochure'] = 0;
$cpCfg['m.webBasic.content.showOkForWeb'] = 0;
$cpCfg['m.webBasic.content.showOkForMobile'] = 0;
$cpCfg['m.webBasic.content.showAuthor'] = 0;
$cpCfg['m.webBasic.content.hasRelatedPics'] = false;
$cpCfg['m.webBasic.content.showOtherPicture'] = 0;
$cpCfg['m.webBasic.content.showComments'] = false;
$cpCfg['m.webBasic.category.showRefTitle'] = false;

$cpCfg['m.webBasic.section.recordTypeArr'] = array (
     'Get Started'
    ,'Lead'
    ,'Opportunity'
    ,'Project'
    ,'Company'
    ,'Contact'
    ,'============='
    ,'Help'
);

$cpCfg['m.webBasic.content.recordTypeArr'] = array (
     'Get Started'
    ,'Lead'
    ,'Opportunity'
    ,'Project'
    ,'Company'
    ,'Contact'
    ,'============='
    ,'Record'
    ,'============='
    ,'Help'
);
$cpCfg['m.webBasic.content.specialSearchArr'] = array(
    'Published'
   ,'Not-Published'
   ,'Latest'
   ,'Favourite'
   ,'Flagged'
   ,'Not-Flagged'
);

$cpCfg['m.webBasic.category.recordTypeArr'] = array(
     'Content'
    ,'Enquiry Form'
    ,'My Profile'
    ,'============='
    ,'View Basket'
    ,'Shipping Details'
    ,'Confirm Order'
    ,'============='
    ,'Order Success'
    ,'Order Fail'
    ,'Order Cancel'
    ,'============='
);

return $cpCfg;