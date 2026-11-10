<?
$cpCfg = array();

$cpCfg['m.edukloud.currentYear'] = date('Y'); 

//------------ COURSE --------------//
$cpCfg['m.edukloud.course.hasSubjectLink'] = false;
$cpCfg['m.edukloud.course.hasProgramGroup'] = true;
$cpCfg['m.edukloud.course.hasCourseContactStatus'] = false;
$cpCfg['m.edukloud.course.otherDetailsPvt'] = false;

//------------ COMPANY --------------//
$cpCfg['m.edukloud.company.hasCompanyEnrollment'] = true;

//------------ CONTACT --------------//
$cpCfg['m.edukloud.contact.showCourseLinkPvt'] = false;
$cpCfg['m.edukloud.contact.hasRegisterNo'] = false;
$cpCfg['m.edukloud.contact.hasParentIDCardNo'] = false;
$cpCfg['m.edukloud.contact.hasPrintBtns'] = false;
$cpCfg['m.edukloud.contact.showParentLink'] = false;
$cpCfg['m.edukloud.contact.showCourseLink'] =  false;
$cpCfg['m.edukloud.contact.otherDetailsPvt'] = false;
$cpCfg['m.edukloud.contact.showInterestInSearch'] = true;
$cpCfg['m.edukloud.contact.showBatchInSearch'] = true;
$cpCfg['m.edukloud.contact.hasRegistrationNo'] = false;
$cpCfg['m.edukloud.contact.showCompanyInList'] = true;
$cpCfg['m.edukloud.contact.showSubscribedInList'] = true;
$cpCfg['m.edukloud.contact.hasStudentPass'] = false;

//------------ ORDER --------------//
$cpCfg['m.edukloud.order.hasCreditNoteLink'] = true;
$cpCfg['m.edukloud.order.hasMiscReceipt'] = true;
$cpCfg['m.edukloud.ecommerce.order.orderItemDisplayForPvt'] = false;
$cpCfg['m.edukloud.ecommerce.order.orderAmountForPvt']  = false;
$cpCfg['m.edukloud.ecommerce.order.invoiceForPvt'] = false;
$cpCfg['m.edukloud.ecommerce.order.receiptForPvt'] = false;
$cpCfg['m.edukloud.order.hasInsuranceLink'] = false;
$cpCfg['m.edukloud.order.hasEditInvoiceForPvt'] = false;
$cpCfg['m.edukloud.order.hasDeleteInvoiceForPvt'] = false;
$cpCfg['m.edukloud.order.hasEditReceiptForPvt'] = false;
$cpCfg['m.edukloud.order.hasMiscReceiptForPvt'] = false;
//This is used for Iedukloud to display - generate monthly invoice button (Enterprise edukloud)
$cpCfg['m.edukloud.ecommerce.order.orderItemDisplayForEnt'] = false;
$cpCfg['m.edukloud.order.hasRefund'] = true;
$cpCfg['m.edukloud.order.invoiceForEnt'] = true;
$cpCfg['m.edukloud.order.hasCheckBoxForInvoiceItem'] = true;
$cpCfg['m.edukloud.ecommerce.order.orderSqlForEnt'] = false;
$cpCfg['m.edukloud.order.hasBookReceipt'] = false;
// Receipt form for Enterprise system
$cpCfg['m.edukloud.order.receiptForEnt'] = false;

//------------ INVOICE --------------//
$cpCfg['m.edukloud.invoice.codeEditable'] = true;

//------------ BATCH --------------//
$cpCfg['m.edukloud.batch.takeAttendance'] = 0;
$cpCfg['m.edukloud.batch.studentFeedback'] = 0;
$cpCfg['m.edukloud.batch.showStudentFeedback'] = 0;
$cpCfg['m.edukloud.batch.showEvaluation'] = 0;
$cpCfg['m.edukloud.batch.hasStudentGrade'] = false;
$cpCfg['m.edukloud.batch.showSubjectPvt'] = false;
$cpCfg['m.edukloud.batch.showClassPvt'] = false;
$cpCfg['m.edukloud.batch.contactLinkPvt'] = false;
$cpCfg['m.edukloud.batch.printAttendanceExcell'] = false;
$cpCfg['m.edukloud.batch.hasAssessorLink'] = true;
$cpCfg['m.edukloud.batch.showTrainerOnly'] = true;
$cpCfg['m.edukloud.batch.hasLabelChangeEnt'] = false; // Changes the label names in batch section

//------------ PARENT --------------//
$cpCfg['m.edukloud.parent.hasSalutation'] = 0;

//------------ REPORTS --------------//
$cpCfg['m.edukloud.reports.showMonthlyEnrollmentForPvt'] = false;
$cpCfg['m.edukloud.reports.showReportsForPvt'] = false;
$cpCfg['m.edukloud.reports.showIncomeByStudent'] = false;
$cpCfg['m.edukloud.reports.showIncomeExpenses'] = false;
$cpCfg['m.edukloud.reports.showAttendanceReports'] = false;
$cpCfg['m.edukloud.reports.showStudentStatusReports'] = false;
$cpCfg['m.edukloud.reports.showStudentProgressionReports'] = false;
$cpCfg['m.edukloud.reports.showAttendanceReportBySubject'] = false;
$cpCfg['m.edukloud.reports.showDailyAccountsReport'] = false;
$cpCfg['m.edukloud.reports.showStaffAttendanceReport'] = false; 
$cpCfg['m.edukloud.reports.showStaffAttendanceOverallReport'] = false;
$cpCfg['m.edukloud.reports.showMarketingCallByStaffReport'] = false;
$cpCfg['m.edukloud.reports.showMarketingCallOverallReport'] = false;
$cpCfg['m.edukloud.reports.showSpecialReportsForPvt'] = true;
$cpCfg['m.edukloud.reports.showIncomeByStudentEntReport'] = false;
$cpCfg['m.edukloud.reports.showTeacherAttendanceReport'] = false;

//------------ WIDGETS --------------//
$cpCfg['w.edukloud.orderSummary.hasSubsidySummary'] = true;
$cpCfg['w.edukloud.orderSummary.outstandingInvoiceForPvt'] = false;
$cpCfg['w.edukloud.orderSummary.invoiceDueThisMonthForPvt'] = false;
$cpCfg['w.edukloud.orderSummary.lateInvoiceForPvt'] = false;
$cpCfg['w.edukloud.orderSummary.overDueInvoiceForPvt'] = false;
$cpCfg['w.edukloud.orderSummary.invoiceThisMonthForPvt'] = false;
$cpCfg['w.edukloud.orderSummary.invoiceDueLastMonthForPvt'] = false;
$cpCfg['w.edukloud.orderSummary.invoicePaidThisMonthForPvt'] = false;
$cpCfg['w.edukloud.orderSummary.invoicePaidLastMonthForPvt'] = false;
$cpCfg['w.edukloud.orderSummary.invoicePaidLastThreeMonthForPvt'] = false;
$cpCfg['w.edukloud.orderSummary.invoicePaidThisYearForPvt'] = false;
$cpCfg['w.edukloud.attendanceByMonth.invoiceByMonthForPvt'] = false;
$cpCfg['w.edukloud.calendarDisplay.hasPvt'] = false;

// Used for Iedukloud
$cpCfg['w.edukloud.orderSummary.alignRightForInstitute'] = false;
$cpCfg['w.edukloud.orderSummary.outstandingInvoiceForInstitute'] = false;
$cpCfg['w.edukloud.orderSummary.invoiceDueThisMonthForInstitute'] = false; 
$cpCfg['w.edukloud.orderSummary.lateInvoiceForInstitute'] = false;
$cpCfg['w.edukloud.orderSummary.overDueInvoiceForInstitute'] = false;
$cpCfg['w.edukloud.orderSummary.invoiceThisMonthForInstitute'] = false;
$cpCfg['w.edukloud.orderSummary.invoiceDueLastMonthForInstitute'] = false;
$cpCfg['w.edukloud.orderSummary.invoicePaidThisMonthForInstitute'] = false;
$cpCfg['w.edukloud.orderSummary.invoicePaidLastMonthForInstitute'] = false;
$cpCfg['w.edukloud.orderSummary.invoicePaidLastThreeMonthForInstitute'] = false;
$cpCfg['w.edukloud.orderSummary.invoicePaidThisYearForInstitute'] = false;

//------------ COURSE LINK --------------//
$cpCfg['m.edukloud.courseLink.hasLabelChangeEnt'] = false; // Changes the label names in the course link section

//------------ PARENT --------------//
$cpCfg['m.edukloud.parent.hasStudentEnrollment'] = false;

return $cpCfg;
