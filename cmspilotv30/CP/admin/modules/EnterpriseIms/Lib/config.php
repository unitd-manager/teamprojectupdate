<?
$cpCfg = array();

$cpCfg['m.enterpriseIms.currentYear'] = date('Y'); 

//------------ COURSE --------------//
$cpCfg['m.enterpriseIms.course.hasSubjectLink'] = false;
$cpCfg['m.enterpriseIms.course.hasProgramGroup'] = true;
$cpCfg['m.enterpriseIms.course.hasCourseContactStatus'] = false;
$cpCfg['m.enterpriseIms.course.otherDetailsPvt'] = false;

//------------ COMPANY --------------//
$cpCfg['m.enterpriseIms.company.hasCompanyEnrollment'] = true;

//------------ CONTACT --------------//
$cpCfg['m.enterpriseIms.contact.showCourseLinkPvt'] = false;
$cpCfg['m.enterpriseIms.contact.hasRegisterNo'] = false;
$cpCfg['m.enterpriseIms.contact.hasParentIDCardNo'] = false;
$cpCfg['m.enterpriseIms.contact.hasPrintBtns'] = false;
$cpCfg['m.enterpriseIms.contact.showParentLink'] = false;
$cpCfg['m.enterpriseIms.contact.showCourseLink'] =  false;
$cpCfg['m.enterpriseIms.contact.otherDetailsPvt'] = false;
$cpCfg['m.enterpriseIms.contact.showInterestInSearch'] = true;
$cpCfg['m.enterpriseIms.contact.showBatchInSearch'] = true;
$cpCfg['m.enterpriseIms.contact.hasRegistrationNo'] = false;
$cpCfg['m.enterpriseIms.contact.showCompanyInList'] = true;
$cpCfg['m.enterpriseIms.contact.showSubscribedInList'] = true;
$cpCfg['m.enterpriseIms.contact.hasStudentPass'] = false;

//------------ ORDER --------------//
$cpCfg['m.enterpriseIms.order.hasCreditNoteLink'] = true;
$cpCfg['m.enterpriseIms.order.hasMiscReceipt'] = true;
$cpCfg['m.enterpriseIms.ecommerce.order.orderItemDisplayForPvt'] = false;
$cpCfg['m.enterpriseIms.ecommerce.order.orderAmountForPvt']  = false;
$cpCfg['m.enterpriseIms.ecommerce.order.invoiceForPvt'] = false;
$cpCfg['m.enterpriseIms.ecommerce.order.receiptForPvt'] = false;
$cpCfg['m.enterpriseIms.order.hasInsuranceLink'] = false;
$cpCfg['m.enterpriseIms.order.hasEditInvoiceForPvt'] = false;
$cpCfg['m.enterpriseIms.order.hasDeleteInvoiceForPvt'] = false;
$cpCfg['m.enterpriseIms.order.hasEditReceiptForPvt'] = false;
$cpCfg['m.enterpriseIms.order.hasMiscReceiptForPvt'] = false;
//This is used for IenterpriseIms to display - generate monthly invoice button (Enterprise enterpriseIms)
$cpCfg['m.enterpriseIms.ecommerce.order.orderItemDisplayForEnt'] = false;
$cpCfg['m.enterpriseIms.order.hasRefund'] = true;
$cpCfg['m.enterpriseIms.order.invoiceForEnt'] = true;
$cpCfg['m.enterpriseIms.order.hasCheckBoxForInvoiceItem'] = true;
$cpCfg['m.enterpriseIms.ecommerce.order.orderSqlForEnt'] = false;
$cpCfg['m.enterpriseIms.order.hasBookReceipt'] = false;
// Receipt form for Enterprise system
$cpCfg['m.enterpriseIms.order.receiptForEnt'] = false;

//------------ INVOICE --------------//
$cpCfg['m.enterpriseIms.invoice.codeEditable'] = true;

//------------ BATCH --------------//
$cpCfg['m.enterpriseIms.batch.takeAttendance'] = 0;
$cpCfg['m.enterpriseIms.batch.studentFeedback'] = 0;
$cpCfg['m.enterpriseIms.batch.showStudentFeedback'] = 0;
$cpCfg['m.enterpriseIms.batch.showEvaluation'] = 0;
$cpCfg['m.enterpriseIms.batch.hasStudentGrade'] = false;
$cpCfg['m.enterpriseIms.batch.showSubjectPvt'] = false;
$cpCfg['m.enterpriseIms.batch.showClassPvt'] = false;
$cpCfg['m.enterpriseIms.batch.contactLinkPvt'] = false;
$cpCfg['m.enterpriseIms.batch.printAttendanceExcell'] = false;
$cpCfg['m.enterpriseIms.batch.hasAssessorLink'] = true;
$cpCfg['m.enterpriseIms.batch.showTrainerOnly'] = true;
$cpCfg['m.enterpriseIms.batch.hasLabelChangeEnt'] = false; // Changes the label names in batch section

//------------ PARENT --------------//
$cpCfg['m.enterpriseIms.parent.hasSalutation'] = 0;

//------------ REPORTS --------------//
$cpCfg['m.enterpriseIms.reports.showMonthlyEnrollmentForPvt'] = false;
$cpCfg['m.enterpriseIms.reports.showReportsForPvt'] = false;
$cpCfg['m.enterpriseIms.reports.showIncomeByStudent'] = false;
$cpCfg['m.enterpriseIms.reports.showIncomeExpenses'] = false;
$cpCfg['m.enterpriseIms.reports.showAttendanceReports'] = false;
$cpCfg['m.enterpriseIms.reports.showStudentStatusReports'] = false;
$cpCfg['m.enterpriseIms.reports.showStudentProgressionReports'] = false;
$cpCfg['m.enterpriseIms.reports.showAttendanceReportBySubject'] = false;
$cpCfg['m.enterpriseIms.reports.showDailyAccountsReport'] = false;
$cpCfg['m.enterpriseIms.reports.showStaffAttendanceReport'] = false; 
$cpCfg['m.enterpriseIms.reports.showStaffAttendanceOverallReport'] = false;
$cpCfg['m.enterpriseIms.reports.showMarketingCallByStaffReport'] = false;
$cpCfg['m.enterpriseIms.reports.showMarketingCallOverallReport'] = false;
$cpCfg['m.enterpriseIms.reports.showSpecialReportsForPvt'] = true;
$cpCfg['m.enterpriseIms.reports.showIncomeByStudentEntReport'] = false;
$cpCfg['m.enterpriseIms.reports.showTeacherAttendanceReport'] = false;

//------------ WIDGETS --------------//
$cpCfg['w.enterpriseIms.orderSummary.hasSubsidySummary'] = true;
$cpCfg['w.enterpriseIms.orderSummary.outstandingInvoiceForPvt'] = false;
$cpCfg['w.enterpriseIms.orderSummary.invoiceDueThisMonthForPvt'] = false;
$cpCfg['w.enterpriseIms.orderSummary.lateInvoiceForPvt'] = false;
$cpCfg['w.enterpriseIms.orderSummary.overDueInvoiceForPvt'] = false;
$cpCfg['w.enterpriseIms.orderSummary.invoiceThisMonthForPvt'] = false;
$cpCfg['w.enterpriseIms.orderSummary.invoiceDueLastMonthForPvt'] = false;
$cpCfg['w.enterpriseIms.orderSummary.invoicePaidThisMonthForPvt'] = false;
$cpCfg['w.enterpriseIms.orderSummary.invoicePaidLastMonthForPvt'] = false;
$cpCfg['w.enterpriseIms.orderSummary.invoicePaidLastThreeMonthForPvt'] = false;
$cpCfg['w.enterpriseIms.orderSummary.invoicePaidThisYearForPvt'] = false;
$cpCfg['w.enterpriseIms.attendanceByMonth.invoiceByMonthForPvt'] = false;
$cpCfg['w.enterpriseIms.calendarDisplay.hasPvt'] = false;

// Used for IenterpriseIms
$cpCfg['w.enterpriseIms.orderSummary.alignRightForInstitute'] = false;
$cpCfg['w.enterpriseIms.orderSummary.outstandingInvoiceForInstitute'] = false;
$cpCfg['w.enterpriseIms.orderSummary.invoiceDueThisMonthForInstitute'] = false; 
$cpCfg['w.enterpriseIms.orderSummary.lateInvoiceForInstitute'] = false;
$cpCfg['w.enterpriseIms.orderSummary.overDueInvoiceForInstitute'] = false;
$cpCfg['w.enterpriseIms.orderSummary.invoiceThisMonthForInstitute'] = false;
$cpCfg['w.enterpriseIms.orderSummary.invoiceDueLastMonthForInstitute'] = false;
$cpCfg['w.enterpriseIms.orderSummary.invoicePaidThisMonthForInstitute'] = false;
$cpCfg['w.enterpriseIms.orderSummary.invoicePaidLastMonthForInstitute'] = false;
$cpCfg['w.enterpriseIms.orderSummary.invoicePaidLastThreeMonthForInstitute'] = false;
$cpCfg['w.enterpriseIms.orderSummary.invoicePaidThisYearForInstitute'] = false;

//------------ COURSE LINK --------------//
$cpCfg['m.enterpriseIms.courseLink.hasLabelChangeEnt'] = false; // Changes the label names in the course link section

//------------ PARENT --------------//
$cpCfg['m.enterpriseIms.parent.hasStudentEnrollment'] = false;

return $cpCfg;
