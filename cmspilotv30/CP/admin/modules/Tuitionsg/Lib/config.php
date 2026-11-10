<?
$cpCfg = array();

$cpCfg['m.tuitionsg.currentYear'] = date('Y');

//------------ COURSE --------------//
$cpCfg['m.tuitionsg.course.hasSubjectLink'] = false;
$cpCfg['m.tuitionsg.course.hasProgramGroup'] = true;
$cpCfg['m.tuitionsg.course.hasCourseContactStatus'] = false;
$cpCfg['m.tuitionsg.course.otherDetailsPvt'] = false;

//------------ COMPANY --------------//
$cpCfg['m.tuitionsg.company.hasCompanyEnrollment'] = true;

//------------ CONTACT --------------//
$cpCfg['m.tuitionsg.contact.showCourseLinkPvt'] = false;
$cpCfg['m.tuitionsg.contact.hasRegisterNo'] = false;
$cpCfg['m.tuitionsg.contact.hasParentIDCardNo'] = false;
$cpCfg['m.tuitionsg.contact.hasPrintBtns'] = false;
$cpCfg['m.tuitionsg.contact.showParentLink'] = false;
$cpCfg['m.tuitionsg.contact.showCourseLink'] =  false;
$cpCfg['m.tuitionsg.contact.otherDetailsPvt'] = false;
$cpCfg['m.tuitionsg.contact.showInterestInSearch'] = true;
$cpCfg['m.tuitionsg.contact.showBatchInSearch'] = true;
$cpCfg['m.tuitionsg.contact.hasRegistrationNo'] = false;
$cpCfg['m.tuitionsg.contact.showCompanyInList'] = true;
$cpCfg['m.tuitionsg.contact.showSubscribedInList'] = true;
$cpCfg['m.tuitionsg.contact.hasStudentPass'] = false;
$cpCfg['cp.hasFullWidthForContactEdit'] = true;

//------------ ORDER --------------//
$cpCfg['m.tuitionsg.order.hasCreditNoteLink'] = true;
$cpCfg['m.tuitionsg.order.hasMiscReceipt'] = true;
$cpCfg['m.tuitionsg.ecommerce.order.orderItemDisplayForPvt'] = false;
$cpCfg['m.tuitionsg.ecommerce.order.orderAmountForPvt']  = false;
$cpCfg['m.tuitionsg.ecommerce.order.invoiceForPvt'] = false;
$cpCfg['m.tuitionsg.ecommerce.order.receiptForPvt'] = false;
$cpCfg['m.tuitionsg.order.hasInsuranceLink'] = false;
$cpCfg['m.tuitionsg.order.hasEditInvoiceForPvt'] = false;
$cpCfg['m.tuitionsg.order.hasDeleteInvoiceForPvt'] = false;
$cpCfg['m.tuitionsg.order.hasEditReceiptForPvt'] = false;
$cpCfg['m.tuitionsg.order.hasMiscReceiptForPvt'] = false;
//This is used for Ituitionsg to display - generate monthly invoice button (Enterprise tuitionsg)
$cpCfg['m.tuitionsg.ecommerce.order.orderItemDisplayForEnt'] = false;
$cpCfg['m.tuitionsg.order.hasRefund'] = true;
$cpCfg['m.tuitionsg.order.invoiceForEnt'] = true;
$cpCfg['m.tuitionsg.order.hasCheckBoxForInvoiceItem'] = true;
$cpCfg['m.tuitionsg.ecommerce.order.orderSqlForEnt'] = false;
$cpCfg['m.tuitionsg.order.hasBookReceipt'] = false;
// Receipt form for Enterprise system
$cpCfg['m.tuitionsg.order.receiptForEnt'] = false;

//------------ INVOICE --------------//
$cpCfg['m.tuitionsg.invoice.codeEditable'] = true;

//------------ BATCH --------------//
$cpCfg['m.tuitionsg.batch.takeAttendance'] = 0;
$cpCfg['m.tuitionsg.batch.studentFeedback'] = 0;
$cpCfg['m.tuitionsg.batch.showStudentFeedback'] = 0;
$cpCfg['m.tuitionsg.batch.showEvaluation'] = 0;
$cpCfg['m.tuitionsg.batch.hasStudentGrade'] = false;
$cpCfg['m.tuitionsg.batch.showSubjectPvt'] = false;
$cpCfg['m.tuitionsg.batch.showClassPvt'] = false;
$cpCfg['m.tuitionsg.batch.contactLinkPvt'] = false;
$cpCfg['m.tuitionsg.batch.printAttendanceExcell'] = false;
$cpCfg['m.tuitionsg.batch.hasAssessorLink'] = true;
$cpCfg['m.tuitionsg.batch.showTrainerOnly'] = true;
$cpCfg['m.tuitionsg.batch.hasLabelChangeEnt'] = false; // Changes the label names in batch section

//------------ PARENT --------------//
$cpCfg['m.tuitionsg.parent.hasSalutation'] = 0;

//------------ REPORTS --------------//
$cpCfg['m.tuitionsg.reports.showMonthlyEnrollmentForPvt'] = false;
$cpCfg['m.tuitionsg.reports.showReportsForPvt'] = false;
$cpCfg['m.tuitionsg.reports.showIncomeByStudent'] = false;
$cpCfg['m.tuitionsg.reports.showIncomeExpenses'] = false;
$cpCfg['m.tuitionsg.reports.showAttendanceReports'] = false;
$cpCfg['m.tuitionsg.reports.showStudentStatusReports'] = false;
$cpCfg['m.tuitionsg.reports.showStudentProgressionReports'] = false;
$cpCfg['m.tuitionsg.reports.showAttendanceReportBySubject'] = false;
$cpCfg['m.tuitionsg.reports.showDailyAccountsReport'] = false;
$cpCfg['m.tuitionsg.reports.showStaffAttendanceReport'] = false;
$cpCfg['m.tuitionsg.reports.showStaffAttendanceOverallReport'] = false;
$cpCfg['m.tuitionsg.reports.showMarketingCallByStaffReport'] = false;
$cpCfg['m.tuitionsg.reports.showMarketingCallOverallReport'] = false;
$cpCfg['m.tuitionsg.reports.showSpecialReportsForPvt'] = true;
$cpCfg['m.tuitionsg.reports.showIncomeByStudentEntReport'] = false;
$cpCfg['m.tuitionsg.reports.showTeacherAttendanceReport'] = false;

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

// Used for Ituitionsg
$cpCfg['w.tuitionsg.orderSummary.alignRightForInstitute'] = false;
$cpCfg['w.tuitionsg.orderSummary.outstandingInvoiceForInstitute'] = false;
$cpCfg['w.tuitionsg.orderSummary.invoiceDueThisMonthForInstitute'] = false;
$cpCfg['w.tuitionsg.orderSummary.lateInvoiceForInstitute'] = false;
$cpCfg['w.tuitionsg.orderSummary.overDueInvoiceForInstitute'] = false;
$cpCfg['w.tuitionsg.orderSummary.invoiceThisMonthForInstitute'] = false;
$cpCfg['w.tuitionsg.orderSummary.invoiceDueLastMonthForInstitute'] = false;
$cpCfg['w.tuitionsg.orderSummary.invoicePaidThisMonthForInstitute'] = false;
$cpCfg['w.tuitionsg.orderSummary.invoicePaidLastMonthForInstitute'] = false;
$cpCfg['w.tuitionsg.orderSummary.invoicePaidLastThreeMonthForInstitute'] = false;
$cpCfg['w.tuitionsg.orderSummary.invoicePaidThisYearForInstitute'] = false;

//------------ COURSE LINK --------------//
$cpCfg['m.tuitionsg.courseLink.hasLabelChangeEnt'] = false; // Changes the label names in the course link section

//------------ PARENT --------------//
$cpCfg['m.tuitionsg.parent.hasStudentEnrollment'] = false;

return $cpCfg;
