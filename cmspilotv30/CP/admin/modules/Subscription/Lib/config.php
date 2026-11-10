<?
$cpCfg = array();

$cpCfg['m.subscription.currentYear'] = date('Y'); 

//------------ COURSE --------------//
$cpCfg['m.subscription.course.hasSubjectLink'] = false;
$cpCfg['m.subscription.course.hasProgramGroup'] = true;
$cpCfg['m.subscription.course.hasCourseContactStatus'] = false;
$cpCfg['m.subscription.course.otherDetailsPvt'] = false;

//------------ COMPANY --------------//
$cpCfg['m.subscription.company.hasCompanyEnrollment'] = true;

//------------ CONTACT --------------//
$cpCfg['m.subscription.contact.showCourseLinkPvt'] = false;
$cpCfg['m.subscription.contact.hasRegisterNo'] = false;
$cpCfg['m.subscription.contact.hasParentIDCardNo'] = false;
$cpCfg['m.subscription.contact.hasPrintBtns'] = false;
$cpCfg['m.subscription.contact.showParentLink'] = false;
$cpCfg['m.subscription.contact.showCourseLink'] =  false;
$cpCfg['m.subscription.contact.otherDetailsPvt'] = false;
$cpCfg['m.subscription.contact.showInterestInSearch'] = true;
$cpCfg['m.subscription.contact.showBatchInSearch'] = true;
$cpCfg['m.subscription.contact.hasRegistrationNo'] = false;
$cpCfg['m.subscription.contact.showCompanyInList'] = true;
$cpCfg['m.subscription.contact.showSubscribedInList'] = true;
$cpCfg['m.subscription.contact.hasStudentPass'] = false;

//------------ ORDER --------------//
$cpCfg['m.subscription.order.hasCreditNoteLink'] = true;
$cpCfg['m.subscription.order.hasMiscReceipt'] = true;
$cpCfg['m.subscription.ecommerce.order.orderItemDisplayForPvt'] = false;
$cpCfg['m.subscription.ecommerce.order.orderAmountForPvt']  = false;
$cpCfg['m.subscription.ecommerce.order.invoiceForPvt'] = false;
$cpCfg['m.subscription.ecommerce.order.receiptForPvt'] = false;
$cpCfg['m.subscription.order.hasInsuranceLink'] = false;
$cpCfg['m.subscription.order.hasEditInvoiceForPvt'] = false;
$cpCfg['m.subscription.order.hasDeleteInvoiceForPvt'] = false;
$cpCfg['m.subscription.order.hasEditReceiptForPvt'] = false;
$cpCfg['m.subscription.order.hasMiscReceiptForPvt'] = false;
//This is used for Isubscription to display - generate monthly invoice button (Enterprise subscription)
$cpCfg['m.subscription.ecommerce.order.orderItemDisplayForEnt'] = false;
$cpCfg['m.subscription.order.hasRefund'] = true;
$cpCfg['m.subscription.order.invoiceForEnt'] = true;
$cpCfg['m.subscription.order.hasCheckBoxForInvoiceItem'] = true;
$cpCfg['m.subscription.ecommerce.order.orderSqlForEnt'] = false;
$cpCfg['m.subscription.order.hasBookReceipt'] = false;
// Receipt form for Enterprise system
$cpCfg['m.subscription.order.receiptForEnt'] = false;

//------------ INVOICE --------------//
$cpCfg['m.subscription.invoice.codeEditable'] = true;


//------------ REPORTS --------------//
$cpCfg['m.subscription.reports.showMonthlyEnrollmentForPvt'] = false;
$cpCfg['m.subscription.reports.showReportsForPvt'] = false;
$cpCfg['m.subscription.reports.showIncomeByStudent'] = false;
$cpCfg['m.subscription.reports.showIncomeExpenses'] = false;
$cpCfg['m.subscription.reports.showAttendanceReports'] = false;
$cpCfg['m.subscription.reports.showStudentStatusReports'] = false;
$cpCfg['m.subscription.reports.showStudentProgressionReports'] = false;
$cpCfg['m.subscription.reports.showAttendanceReportBySubject'] = false;
$cpCfg['m.subscription.reports.showDailyAccountsReport'] = false;
$cpCfg['m.subscription.reports.showStaffAttendanceReport'] = false; 
$cpCfg['m.subscription.reports.showStaffAttendanceOverallReport'] = false;
$cpCfg['m.subscription.reports.showMarketingCallByStaffReport'] = false;
$cpCfg['m.subscription.reports.showMarketingCallOverallReport'] = false;
$cpCfg['m.subscription.reports.showSpecialReportsForPvt'] = true;
$cpCfg['m.subscription.reports.showIncomeByStudentEntReport'] = false;
$cpCfg['m.subscription.reports.showTeacherAttendanceReport'] = false;

//------------ WIDGETS --------------//
$cpCfg['w.subscription.orderSummary.hasSubsidySummary'] = true;
$cpCfg['w.subscription.orderSummary.outstandingInvoiceForPvt'] = false;
$cpCfg['w.subscription.orderSummary.invoiceDueThisMonthForPvt'] = false;
$cpCfg['w.subscription.orderSummary.lateInvoiceForPvt'] = false;
$cpCfg['w.subscription.orderSummary.overDueInvoiceForPvt'] = false;
$cpCfg['w.subscription.orderSummary.invoiceThisMonthForPvt'] = false;
$cpCfg['w.subscription.orderSummary.invoiceDueLastMonthForPvt'] = false;
$cpCfg['w.subscription.orderSummary.invoicePaidThisMonthForPvt'] = false;
$cpCfg['w.subscription.orderSummary.invoicePaidLastMonthForPvt'] = false;
$cpCfg['w.subscription.orderSummary.invoicePaidLastThreeMonthForPvt'] = false;
$cpCfg['w.subscription.orderSummary.invoicePaidThisYearForPvt'] = false;
$cpCfg['w.subscription.attendanceByMonth.invoiceByMonthForPvt'] = false;
$cpCfg['w.subscription.calendarDisplay.hasPvt'] = false;

// Used for Isubscription

$cpCfg['w.subscription.orderSummary.alignRightForInstitute'] = false;
$cpCfg['w.subscription.orderSummary.outstandingInvoiceForInstitute'] = false;
$cpCfg['w.subscription.orderSummary.invoiceDueThisMonthForInstitute'] = false; 
$cpCfg['w.subscription.orderSummary.lateInvoiceForInstitute'] = false;
$cpCfg['w.subscription.orderSummary.overDueInvoiceForInstitute'] = false;
$cpCfg['w.subscription.orderSummary.invoiceThisMonthForInstitute'] = false;
$cpCfg['w.subscription.orderSummary.invoiceDueLastMonthForInstitute'] = false;
$cpCfg['w.subscription.orderSummary.invoicePaidThisMonthForInstitute'] = false;
$cpCfg['w.subscription.orderSummary.invoicePaidLastMonthForInstitute'] = false;
$cpCfg['w.subscription.orderSummary.invoicePaidLastThreeMonthForInstitute'] = false;
$cpCfg['w.subscription.orderSummary.invoicePaidThisYearForInstitute'] = false;

//------------ COURSE LINK --------------//
$cpCfg['m.subscription
.courseLink.hasLabelChangeEnt'] = false; // Changes the label names in the course link section

//------------ PARENT --------------//
$cpCfg['m.subscription
.parent.hasStudentEnrollment'] = false;

return $cpCfg;
