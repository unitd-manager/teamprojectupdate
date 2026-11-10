<?
$cpCfg = array();

$cpCfg['m.pms.currentYear'] = date('Y'); 
$cpCfg['cp.forAceIms']      = 0; // Universal config only for Edutrust Institutions

//------------ COURSE --------------//
$cpCfg['m.pms.course.hasSubjectLink'] = false;
$cpCfg['m.pms.course.hasProgramGroup'] = false;
$cpCfg['m.pms.course.hasCourseContactStatus'] = false;
$cpCfg['m.pms.course.otherDetailsPvt'] = false;

//------------ COMPANY --------------//
$cpCfg['m.pms.company.hasCompanyEnrollment'] = true;

//------------ CONTACT --------------//
$cpCfg['m.pms.contact.showCourseLinkPvt'] = false;
$cpCfg['m.pms.contact.hasRegisterNo'] = false;
$cpCfg['m.pms.contact.hasParentIDCardNo'] = false;
$cpCfg['m.pms.contact.hasPrintBtns'] = false;
$cpCfg['m.pms.contact.showParentLink'] = false;
$cpCfg['m.pms.contact.showCourseLink'] =  false;
$cpCfg['m.pms.contact.otherDetailsPvt'] = false;
$cpCfg['m.pms.contact.showInterestInSearch'] = true;
$cpCfg['m.pms.contact.showBatchInSearch'] = true;
$cpCfg['m.pms.contact.hasRegistrationNo'] = false;
$cpCfg['m.pms.contact.showCompanyInList'] = true;
$cpCfg['m.pms.contact.showSubscribedInList'] = true;
$cpCfg['m.pms.contact.hasStudentPass'] = false;

//------------ ORDER --------------//
$cpCfg['m.pms.order.hasCreditNoteLink'] = true;
$cpCfg['m.pms.order.hasMiscReceipt'] = true;
$cpCfg['m.pms.ecommerce.order.orderItemDisplayForPvt'] = false;
$cpCfg['m.pms.ecommerce.order.orderAmountForPvt']  = false;
$cpCfg['m.pms.ecommerce.order.invoiceForPvt'] = false;
$cpCfg['m.pms.ecommerce.order.receiptForPvt'] = false;
$cpCfg['m.pms.order.hasInsuranceLink'] = false;
$cpCfg['m.pms.order.hasEditInvoiceForPvt'] = false;
$cpCfg['m.pms.order.hasDeleteInvoiceForPvt'] = false;
$cpCfg['m.pms.order.hasEditReceiptForPvt'] = false;
$cpCfg['m.pms.order.hasMiscReceiptForPvt'] = false;
//This is used for Ipms to display - generate monthly invoice button (Enterprise pms)
$cpCfg['m.pms.ecommerce.order.orderItemDisplayForEnt'] = false;
$cpCfg['m.pms.order.hasRefund'] = true;
$cpCfg['m.pms.order.invoiceForEnt'] = true;
$cpCfg['m.pms.order.hasCheckBoxForInvoiceItem'] = true;
$cpCfg['m.pms.ecommerce.order.orderSqlForEnt'] = false;
$cpCfg['m.pms.order.hasBookReceipt'] = false;
// Receipt form for Enterprise system
$cpCfg['m.pms.order.receiptForEnt'] = false;

//------------ INVOICE --------------//
$cpCfg['m.pms.invoice.codeEditable'] = true;

//------------ BATCH --------------//
$cpCfg['m.pms.batch.multiSiteBranch'] = 0;

$cpCfg['m.pms.batch.takeAttendance'] = 0;
$cpCfg['m.pms.batch.studentFeedback'] = 0;
$cpCfg['m.pms.batch.showStudentFeedback'] = 0;
$cpCfg['m.pms.batch.showEvaluation'] = 0;
$cpCfg['m.pms.batch.hasStudentGrade'] = false;
$cpCfg['m.pms.batch.showSubjectPvt'] = false;
$cpCfg['m.pms.batch.showClassPvt'] = false;
$cpCfg['m.pms.batch.contactLinkPvt'] = false;
$cpCfg['m.pms.batch.printAttendanceExcell'] = false;
$cpCfg['m.pms.batch.hasAssessorLink'] = true;
$cpCfg['m.pms.batch.showTrainerOnly'] = true;
$cpCfg['m.pms.batch.hasLabelChangeEnt'] = false; // Changes the label names in batch section

//------------ PARENT --------------//
$cpCfg['m.pms.parent.hasSalutation'] = 0;

//------------ REPORTS --------------//
$cpCfg['m.pms.reports.showMonthlyEnrollmentFeorPvt'] = false;
$cpCfg['m.pms.reports.showReportsForPvt'] = false;
$cpCfg['m.pms.reports.showIncomeByStudent'] = false;
$cpCfg['m.pms.reports.showIncomeExpenses'] = false;
$cpCfg['m.pms.reports.showAttendanceReports'] = false;
$cpCfg['m.pms.reports.showStudentStatusReports'] = false;
$cpCfg['m.pms.reports.showStudentProgressionReports'] = false;
$cpCfg['m.pms.reports.showAttendanceReportBySubject'] = false;
$cpCfg['m.pms.reports.showDailyAccountsReport'] = false;
$cpCfg['m.pms.reports.showStaffAttendanceReport'] = false; 
$cpCfg['m.pms.reports.showStaffAttendanceOverallReport'] = false;
$cpCfg['m.pms.reports.showMarketingCallByStaffReport'] = false;
$cpCfg['m.pms.reports.showMarketingCallOverallReport'] = false;
$cpCfg['m.pms.reports.showSpecialReportsForPvt'] = true;
$cpCfg['m.pms.reports.showPaymentOutstandingReport'] = false;
$cpCfg['m.pms.reports.showTeacherAttendanceReport'] = false;
$cpCfg['m.pms.reports.showMonthlyEnrollmentForPvt'] = false;

//------------ WIDGETS --------------//
$cpCfg['w.pms.orderSummary.hasSubsidySummary'] = true;
$cpCfg['w.pms.orderSummary.outstandingInvoiceForPvt'] = false;
$cpCfg['w.pms.orderSummary.invoiceDueThisMonthForPvt'] = false;
$cpCfg['w.pms.orderSummary.lateInvoiceForPvt'] = false;
$cpCfg['w.pms.orderSummary.overDueInvoiceForPvt'] = false;
$cpCfg['w.pms.orderSummary.invoiceThisMonthForPvt'] = false;
$cpCfg['w.pms.orderSummary.invoiceDueLastMonthForPvt'] = false;
$cpCfg['w.pms.orderSummary.invoicePaidThisMonthForPvt'] = false;
$cpCfg['w.pms.orderSummary.invoicePaidLastMonthForPvt'] = false;
$cpCfg['w.pms.orderSummary.invoicePaidLastThreeMonthForPvt'] = false;
$cpCfg['w.pms.orderSummary.invoicePaidThisYearForPvt'] = false;
$cpCfg['w.pms.attendanceByMonth.invoiceByMonthForPvt'] = false;
$cpCfg['w.pms.calendarDisplay.hasPvt'] = false;

// Used for IPMS
$cpCfg['w.pms.orderSummary.alignRightForInstitute'] = false;
$cpCfg['w.pms.orderSummary.outstandingInvoiceForInstitute'] = false;
$cpCfg['w.pms.orderSummary.invoiceDueThisMonthForInstitute'] = false; 
$cpCfg['w.pms.orderSummary.lateInvoiceForInstitute'] = false;
$cpCfg['w.pms.orderSummary.overDueInvoiceForInstitute'] = false;
$cpCfg['w.pms.orderSummary.invoiceThisMonthForInstitute'] = false;
$cpCfg['w.pms.orderSummary.invoiceDueLastMonthForInstitute'] = false;
$cpCfg['w.pms.orderSummary.invoicePaidThisMonthForInstitute'] = false;
$cpCfg['w.pms.orderSummary.invoicePaidLastMonthForInstitute'] = false;
$cpCfg['w.pms.orderSummary.invoicePaidLastThreeMonthForInstitute'] = false;
$cpCfg['w.pms.orderSummary.invoicePaidThisYearForInstitute'] = false;

//------------ COURSE LINK --------------//
$cpCfg['m.pms.courseLink.hasLabelChangeEnt'] = false; // Changes the label names in the course link section

//------------ PARENT --------------//
$cpCfg['m.pms.parent.hasStudentEnrollment'] = true;

return $cpCfg;
