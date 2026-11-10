<?
$cpCfg = array();

$cpCfg['m.agileIms.currentYear'] = date('Y'); 
$cpCfg['cp.forAgileIms']      = 0; // Universal config only for Edutrust Institutions

//------------ COURSE --------------//
$cpCfg['m.agileIms.course.hasSubjectLink'] = false;
$cpCfg['m.agileIms.course.hasProgramGroup'] = true;
$cpCfg['m.agileIms.course.hasCourseContactStatus'] = false;
$cpCfg['m.agileIms.course.otherDetailsPvt'] = false;

//------------ COMPANY --------------//
$cpCfg['m.agileIms.company.hasCompanyEnrollment'] = true;

//------------ CONTACT --------------//
$cpCfg['m.agileIms.contact.showCourseLinkPvt'] = false;
$cpCfg['m.agileIms.contact.hasRegisterNo'] = false;
$cpCfg['m.agileIms.contact.hasParentIDCardNo'] = false;
$cpCfg['m.agileIms.contact.hasPrintBtns'] = false;
$cpCfg['m.agileIms.contact.showParentLink'] = false;
$cpCfg['m.agileIms.contact.showCourseLink'] =  false;
$cpCfg['m.agileIms.contact.otherDetailsPvt'] = false;
$cpCfg['m.agileIms.contact.showInterestInSearch'] = true;
$cpCfg['m.agileIms.contact.showBatchInSearch'] = true;
$cpCfg['m.agileIms.contact.hasRegistrationNo'] = false;
$cpCfg['m.agileIms.contact.showCompanyInList'] = true;
$cpCfg['m.agileIms.contact.showSubscribedInList'] = true;
$cpCfg['m.agileIms.contact.hasStudentPass'] = false;
$cpCfg['cp.hasFullWidthForContactEdit'] = true;

//------------ ORDER --------------//
$cpCfg['m.agileIms.order.hasCreditNoteLink'] = true;
$cpCfg['m.agileIms.order.hasMiscReceipt'] = true;
$cpCfg['m.agileIms.ecommerce.order.orderItemDisplayForPvt'] = false;
$cpCfg['m.agileIms.ecommerce.order.orderAmountForPvt']  = false;
$cpCfg['m.agileIms.ecommerce.order.invoiceForPvt'] = false;
$cpCfg['m.agileIms.ecommerce.order.receiptForPvt'] = false;
$cpCfg['m.agileIms.order.hasInsuranceLink'] = false;
$cpCfg['m.agileIms.order.hasEditInvoiceForPvt'] = false;
//This is used for IagileIms to display - generate monthly invoice button (Enterprise agileIms)
$cpCfg['m.agileIms.ecommerce.order.orderItemDisplayForEnt'] = false;
$cpCfg['m.agileIms.order.hasRefund'] = true;
$cpCfg['m.agileIms.order.invoiceForEnt'] = true;
$cpCfg['m.agileIms.order.hasCheckBoxForInvoiceItem'] = true;
$cpCfg['m.agileIms.ecommerce.order.orderSqlForEnt'] = false;

//------------ INVOICE --------------//
$cpCfg['m.agileIms.invoice.codeEditable'] = true;

//------------ BATCH --------------//
$cpCfg['m.agileIms.batch.takeAttendance'] = 0;
$cpCfg['m.agileIms.batch.studentFeedback'] = 0;
$cpCfg['m.agileIms.batch.showStudentFeedback'] = 0;
$cpCfg['m.agileIms.batch.showEvaluation'] = 0;
$cpCfg['m.agileIms.batch.hasStudentGrade'] = false;
$cpCfg['m.agileIms.batch.showSubjectPvt'] = false;
$cpCfg['m.agileIms.batch.showClassPvt'] = false;
$cpCfg['m.agileIms.batch.contactLinkPvt'] = false;
$cpCfg['m.agileIms.batch.printAttendanceExcell'] = false;
$cpCfg['m.agileIms.batch.showTrainerOnly'] = true;
$cpCfg['m.agileIms.batch.hasLabelChangeEnt'] = false; // Changes the label names in batch section

//------------ PARENT --------------//
$cpCfg['m.agileIms.parent.hasSalutation'] = 0;

//------------ REPORTS --------------//
$cpCfg['m.agileIms.reports.showMonthlyEnrollmentFeorPvt'] = false;
$cpCfg['m.agileIms.reports.showReportsForPvt'] = false;
$cpCfg['m.agileIms.reports.showIncomeByStudent'] = false;
$cpCfg['m.agileIms.reports.showIncomeExpenses'] = false;
$cpCfg['m.agileIms.reports.showAttendanceReports'] = false;
$cpCfg['m.agileIms.reports.showStudentStatusReports'] = false;
$cpCfg['m.agileIms.reports.showStudentProgressionReports'] = false;
$cpCfg['m.agileIms.reports.showAttendanceReportBySubject'] = false;
$cpCfg['m.agileIms.reports.showDailyAccountsReport'] = false;
$cpCfg['m.agileIms.reports.showStaffAttendanceReport'] = false; 
$cpCfg['m.agileIms.reports.showStaffAttendanceOverallReport'] = false;
$cpCfg['m.agileIms.reports.showMarketingCallByStaffReport'] = false;
$cpCfg['m.agileIms.reports.showMarketingCallOverallReport'] = false;
$cpCfg['m.agileIms.reports.showSpecialReportsForPvt'] = true;
$cpCfg['m.agileIms.reports.showPaymentOutstandingReport'] = false;
$cpCfg['m.agileIms.reports.showTeacherAttendanceReport'] = false;
$cpCfg['m.agileIms.reports.showMonthlyEnrollmentForPvt'] = false;

//------------ WIDGETS --------------//
$cpCfg['w.agileIms.orderSummary.hasSubsidySummary'] = true;
$cpCfg['w.agileIms.orderSummary.outstandingInvoiceForPvt'] = false;
$cpCfg['w.agileIms.orderSummary.invoiceDueThisMonthForPvt'] = false;
$cpCfg['w.agileIms.orderSummary.lateInvoiceForPvt'] = false;
$cpCfg['w.agileIms.orderSummary.overDueInvoiceForPvt'] = false;
$cpCfg['w.agileIms.orderSummary.invoiceThisMonthForPvt'] = false;
$cpCfg['w.agileIms.orderSummary.invoiceDueLastMonthForPvt'] = false;
$cpCfg['w.agileIms.orderSummary.invoicePaidThisMonthForPvt'] = false;
$cpCfg['w.agileIms.orderSummary.invoicePaidLastMonthForPvt'] = false;
$cpCfg['w.agileIms.orderSummary.invoicePaidLastThreeMonthForPvt'] = false;
$cpCfg['w.agileIms.orderSummary.invoicePaidThisYearForPvt'] = false;
$cpCfg['w.agileIms.attendanceByMonth.invoiceByMonthForPvt'] = false;
$cpCfg['w.agileIms.calendarDisplay.hasPvt'] = false;

// Used for IagileIms
$cpCfg['w.agileIms.orderSummary.alignRightForInstitute'] = false;
$cpCfg['w.agileIms.orderSummary.outstandingInvoiceForInstitute'] = false;
$cpCfg['w.agileIms.orderSummary.invoiceDueThisMonthForInstitute'] = false; 
$cpCfg['w.agileIms.orderSummary.lateInvoiceForInstitute'] = false;
$cpCfg['w.agileIms.orderSummary.overDueInvoiceForInstitute'] = false;
$cpCfg['w.agileIms.orderSummary.invoiceThisMonthForInstitute'] = false;
$cpCfg['w.agileIms.orderSummary.invoiceDueLastMonthForInstitute'] = false;
$cpCfg['w.agileIms.orderSummary.invoicePaidThisMonthForInstitute'] = false;
$cpCfg['w.agileIms.orderSummary.invoicePaidLastMonthForInstitute'] = false;
$cpCfg['w.agileIms.orderSummary.invoicePaidLastThreeMonthForInstitute'] = false;
$cpCfg['w.agileIms.orderSummary.invoicePaidThisYearForInstitute'] = false;

//------------ COURSE LINK --------------//
$cpCfg['m.agileIms.courseLink.hasLabelChangeEnt'] = false; // Changes the label names in the course link section

//------------ PARENT --------------//
$cpCfg['m.agileIms.parent.hasStudentEnrollment'] = false;

return $cpCfg;
