<?
$cpCfg = array();

$cpCfg['m.aceIms.currentYear'] = date('Y'); 
$cpCfg['cp.forAceIms']      = 0; // Universal config only for Edutrust Institutions

//------------ COURSE --------------//
$cpCfg['m.aceIms.course.hasSubjectLink'] = false;
$cpCfg['m.aceIms.course.hasProgramGroup'] = true;
$cpCfg['m.aceIms.course.hasCourseContactStatus'] = false;
$cpCfg['m.aceIms.course.otherDetailsPvt'] = false;

//------------ COMPANY --------------//
$cpCfg['m.aceIms.company.hasCompanyEnrollment'] = true;

//------------ CONTACT --------------//
$cpCfg['m.aceIms.contact.showCourseLinkPvt'] = false;
$cpCfg['m.aceIms.contact.hasRegisterNo'] = false;
$cpCfg['m.aceIms.contact.hasParentIDCardNo'] = false;
$cpCfg['m.aceIms.contact.hasPrintBtns'] = false;
$cpCfg['m.aceIms.contact.showParentLink'] = false;
$cpCfg['m.aceIms.contact.showCourseLink'] =  false;
$cpCfg['m.aceIms.contact.otherDetailsPvt'] = false;
$cpCfg['m.aceIms.contact.showInterestInSearch'] = true;
$cpCfg['m.aceIms.contact.showBatchInSearch'] = true;
$cpCfg['m.aceIms.contact.hasRegistrationNo'] = false;
$cpCfg['m.aceIms.contact.showCompanyInList'] = true;
$cpCfg['m.aceIms.contact.showSubscribedInList'] = true;
$cpCfg['m.aceIms.contact.hasStudentPass'] = false;
$cpCfg['cp.hasFullWidthForContactEdit'] = true;

//------------ ORDER --------------//
$cpCfg['m.aceIms.order.hasCreditNoteLink'] = true;
$cpCfg['m.aceIms.order.hasMiscReceipt'] = true;
$cpCfg['m.aceIms.ecommerce.order.orderItemDisplayForPvt'] = false;
$cpCfg['m.aceIms.ecommerce.order.orderAmountForPvt']  = false;
$cpCfg['m.aceIms.ecommerce.order.invoiceForPvt'] = false;
$cpCfg['m.aceIms.ecommerce.order.receiptForPvt'] = false;
$cpCfg['m.aceIms.order.hasInsuranceLink'] = false;
$cpCfg['m.aceIms.order.hasEditInvoiceForPvt'] = false;
$cpCfg['m.aceIms.order.hasDeleteInvoiceForPvt'] = false;
$cpCfg['m.aceIms.order.hasEditReceiptForPvt'] = false;
$cpCfg['m.aceIms.order.hasMiscReceiptForPvt'] = false;
//This is used for IaceIms to display - generate monthly invoice button (Enterprise aceIms)
$cpCfg['m.aceIms.ecommerce.order.orderItemDisplayForEnt'] = false;
$cpCfg['m.aceIms.order.hasRefund'] = true;
$cpCfg['m.aceIms.order.invoiceForEnt'] = true;
$cpCfg['m.aceIms.order.hasCheckBoxForInvoiceItem'] = true;
$cpCfg['m.aceIms.ecommerce.order.orderSqlForEnt'] = false;
$cpCfg['m.aceIms.order.hasBookReceipt'] = false;
// Receipt form for Enterprise system
$cpCfg['m.aceIms.order.receiptForEnt'] = false;

//------------ INVOICE --------------//
$cpCfg['m.aceIms.invoice.codeEditable'] = true;

//------------ BATCH --------------//
$cpCfg['m.aceIms.batch.takeAttendance'] = 0;
$cpCfg['m.aceIms.batch.studentFeedback'] = 0;
$cpCfg['m.aceIms.batch.showStudentFeedback'] = 0;
$cpCfg['m.aceIms.batch.showEvaluation'] = 0;
$cpCfg['m.aceIms.batch.hasStudentGrade'] = false;
$cpCfg['m.aceIms.batch.showSubjectPvt'] = false;
$cpCfg['m.aceIms.batch.showClassPvt'] = false;
$cpCfg['m.aceIms.batch.contactLinkPvt'] = false;
$cpCfg['m.aceIms.batch.printAttendanceExcell'] = false;
$cpCfg['m.aceIms.batch.hasAssessorLink'] = true;
$cpCfg['m.aceIms.batch.showTrainerOnly'] = true;
$cpCfg['m.aceIms.batch.hasLabelChangeEnt'] = false; // Changes the label names in batch section

//------------ PARENT --------------//
$cpCfg['m.aceIms.parent.hasSalutation'] = 0;

//------------ REPORTS --------------//
$cpCfg['m.aceIms.reports.showMonthlyEnrollmentFeorPvt'] = false;
$cpCfg['m.aceIms.reports.showReportsForPvt'] = false;
$cpCfg['m.aceIms.reports.showIncomeByStudent'] = false;
$cpCfg['m.aceIms.reports.showIncomeExpenses'] = false;
$cpCfg['m.aceIms.reports.showAttendanceReports'] = false;
$cpCfg['m.aceIms.reports.showStudentStatusReports'] = false;
$cpCfg['m.aceIms.reports.showStudentProgressionReports'] = false;
$cpCfg['m.aceIms.reports.showAttendanceReportBySubject'] = false;
$cpCfg['m.aceIms.reports.showDailyAccountsReport'] = false;
$cpCfg['m.aceIms.reports.showStaffAttendanceReport'] = false; 
$cpCfg['m.aceIms.reports.showStaffAttendanceOverallReport'] = false;
$cpCfg['m.aceIms.reports.showMarketingCallByStaffReport'] = false;
$cpCfg['m.aceIms.reports.showMarketingCallOverallReport'] = false;
$cpCfg['m.aceIms.reports.showSpecialReportsForPvt'] = true;
$cpCfg['m.aceIms.reports.showPaymentOutstandingReport'] = false;
$cpCfg['m.aceIms.reports.showTeacherAttendanceReport'] = false;
$cpCfg['m.aceIms.reports.showMonthlyEnrollmentForPvt'] = false;

//------------ WIDGETS --------------//
$cpCfg['w.aceIms.orderSummary.hasSubsidySummary'] = true;
$cpCfg['w.aceIms.orderSummary.outstandingInvoiceForPvt'] = false;
$cpCfg['w.aceIms.orderSummary.invoiceDueThisMonthForPvt'] = false;
$cpCfg['w.aceIms.orderSummary.lateInvoiceForPvt'] = false;
$cpCfg['w.aceIms.orderSummary.overDueInvoiceForPvt'] = false;
$cpCfg['w.aceIms.orderSummary.invoiceThisMonthForPvt'] = false;
$cpCfg['w.aceIms.orderSummary.invoiceDueLastMonthForPvt'] = false;
$cpCfg['w.aceIms.orderSummary.invoicePaidThisMonthForPvt'] = false;
$cpCfg['w.aceIms.orderSummary.invoicePaidLastMonthForPvt'] = false;
$cpCfg['w.aceIms.orderSummary.invoicePaidLastThreeMonthForPvt'] = false;
$cpCfg['w.aceIms.orderSummary.invoicePaidThisYearForPvt'] = false;
$cpCfg['w.aceIms.attendanceByMonth.invoiceByMonthForPvt'] = false;
$cpCfg['w.aceIms.calendarDisplay.hasPvt'] = false;

// Used for IaceIms
$cpCfg['w.aceIms.orderSummary.alignRightForInstitute'] = false;
$cpCfg['w.aceIms.orderSummary.outstandingInvoiceForInstitute'] = false;
$cpCfg['w.aceIms.orderSummary.invoiceDueThisMonthForInstitute'] = false; 
$cpCfg['w.aceIms.orderSummary.lateInvoiceForInstitute'] = false;
$cpCfg['w.aceIms.orderSummary.overDueInvoiceForInstitute'] = false;
$cpCfg['w.aceIms.orderSummary.invoiceThisMonthForInstitute'] = false;
$cpCfg['w.aceIms.orderSummary.invoiceDueLastMonthForInstitute'] = false;
$cpCfg['w.aceIms.orderSummary.invoicePaidThisMonthForInstitute'] = false;
$cpCfg['w.aceIms.orderSummary.invoicePaidLastMonthForInstitute'] = false;
$cpCfg['w.aceIms.orderSummary.invoicePaidLastThreeMonthForInstitute'] = false;
$cpCfg['w.aceIms.orderSummary.invoicePaidThisYearForInstitute'] = false;

//------------ COURSE LINK --------------//
$cpCfg['m.aceIms.courseLink.hasLabelChangeEnt'] = false; // Changes the label names in the course link section

//------------ PARENT --------------//
$cpCfg['m.aceIms.parent.hasStudentEnrollment'] = false;

return $cpCfg;
