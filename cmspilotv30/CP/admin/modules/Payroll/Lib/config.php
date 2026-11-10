<?
$cpCfg = array();
//------------ GENERAL --------------//
$cpCfg['m.jobInformation.allowance1Lbl'] = "Transport"; // Used in Payslip, Job information & Payroll Management
$cpCfg['m.jobInformation.allowance2Lbl'] = "Entertainment";
$cpCfg['m.jobInformation.allowance3Lbl'] = "Allowance 3";
$cpCfg['m.jobInformation.allowance4Lbl'] = "Allowance 4";
$cpCfg['m.jobInformation.allowance5Lbl'] = "Allowance 5";
$cpCfg['m.jobInformation.deduction1Lbl'] = "Deduction 1"; // Used in Payslip, Job information & Payroll Management
$cpCfg['m.jobInformation.deduction2Lbl'] = "Deduction 2";
$cpCfg['m.jobInformation.deduction3Lbl'] = "Deduction 3";

//------------ LEAVE --------------//
// Annual Leave for employees [no of leave days eligible => no of years worked]
$cpCfg['m.payroll.leave.yearlyAnnualLeave'] = array (
	  "7"  => "1st Year"
	 ,"8"  => "2nd Year"
	 ,"9"  => "3rd Year"
	 ,"10" => "4th Year"
	 ,"11" => "5th Year"
	 ,"12" => "6th Year"
	 ,"13" => "7th Year"
	 ,"14" => "8th Year and thereafter"
);

$cpCfg['m.payroll.leave.leaveType'] = array (
      "Absent"
     ,"Annual Leave"
     ,"Hospitalization Leave"
     ,"Sick Leave"
);

$cpCfg['m.payroll.leave.leaveStatusArr'] = array(
      "Applied"
     ,"Approved"
     ,"Cancelled"
     ,"HR Approved"
     ,"Denied"
     ,"Hold"
     ,"Waiting for Approval"
);

//------------ LOAN --------------//
$cpCfg['m.payroll.loan.loanStatusArr'] = array (
	  "Active"
	 ,"Applied"
	 ,"Approved"
	 ,"Closed"
	 ,"Denied"
	 ,"Hold"
	 ,"Waiting for Approval"
);

$cpCfg['m.payroll.loan.loanTypeArr'] = array (
      "Personal Loan"
     ,"Home Loan"
     ,"Car Loan"
     ,"Other"
);

// Set both variables $cpCfg['m.payroll.startYear'] and $cpCfg['m.payroll.yearArr'] in config_last of local system to override start year
$cpCfg['m.payroll.startYear'] = '2016';
$arrayYear = array();
for ($i = $cpCfg['m.payroll.startYear']; $i<=date('Y'); $i++) {
    $arrayYear += [ $i => $i ];
}
$cpCfg['m.payroll.yearArr'] = $arrayYear;

/*
$cpCfg['m.payroll.yearArr'] = array (
    '2016' => '2016'
   ,'2017' => '2017'
   ,'2018' => '2018'
   ,'2019' => '2019'
   ,'2020' => '2020'
);
*/

$cpCfg['m.payrollManagement.hasSdlDeduction'] = 1;
$cpCfg['m.payrollManagement.sdlDeductionForLocalEmployeesOnly'] = 0;

//------------ PAYROLL MANAGEMENT --------------//
$cpCfg['m.payroll.payrollManagement.allowPreviousMonthsEdit'] = "No"; //enable / disable payslip edition for previous months
$cpCfg['m.payrollManagement.AutoChangePayslipDate'] = 0;
$cpCfg['m.payrollManagement.hasEmployeeSignature'] = 1;

// Total number of working days in a month for 5 days working group
$cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForFiveDaysWork'] = array (
	  "01" => "21"
	 ,"02" => "20"
	 ,"03" => "23"
	 ,"04" => "21"
	 ,"05" => "22"
	 ,"06" => "22"
	 ,"07" => "21"
	 ,"08" => "23"
	 ,"09" => "22"
	 ,"10" => "21"
	 ,"11" => "22"
	 ,"12" => "22"
);

// Total number of working days in a month for 5.5 days working group
$cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForFiveHalfDaysWork'] = array (
	  "01" => "23.5"
	 ,"02" => "22"
	 ,"03" => "25"
	 ,"04" => "23.5"
	 ,"05" => "24"
	 ,"06" => "24"
	 ,"07" => "23.5"
	 ,"08" => "25"
	 ,"09" => "24"
	 ,"10" => "23.5"
	 ,"11" => "24"
	 ,"12" => "24.5"
);

// Total number of working days in a month for 6 days working group
$cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForSixDaysWork'] = array (
	  "01" => "26"
	 ,"02" => "24"
	 ,"03" => "27"
	 ,"04" => "26"
	 ,"05" => "26"
	 ,"06" => "26"
	 ,"07" => "26"
	 ,"08" => "27"
	 ,"09" => "26"
	 ,"10" => "26"
	 ,"11" => "26"
	 ,"12" => "27"
);

//------------ TRAINING --------------//
$cpCfg['m.payroll.training.statusArr'] = array (
      "Active"
     ,"Archive"
);

//------------ REPORTS --------------//
$cpCfg['cp.ir8aFormForYear'] = '2021';
$cpCfg['cp.ir8aForm.DirectorFeeAgmDate'] = '31/12/2021';
$cpCfg['cp.ir8aForm.DirectorFeeAgmDateInAis'] = '2021-12-31'; // same as above. used for formatting issue.
$cpCfg['cp.ir8aForm.grossCommissionFromDate'] = '01/01/2021';
$cpCfg['cp.ir8aForm.grossCommissionToDate'] = '31/12/2021';
$cpCfg['cp.ir8aForm.fromDate'] = '20210101';
$cpCfg['cp.ir8aForm.toDate'] = '20211231';

return $cpCfg;