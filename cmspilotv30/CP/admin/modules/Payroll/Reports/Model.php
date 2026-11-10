<?
class CP_Admin_Modules_Payroll_Reports_Model extends CP_Common_Lib_ModuleModelAbstract
{
    var $reportsArray = array();

    function __construct() {

        $this->reportsArray = array( 
            'ir8a'                           => $this->getReportObj('ir8a', 'IR8A Report')
           ,'employeePayslipGeneratedReport' => $this->getReportObj('employeePayslipGeneratedReport', 'Payslip Generated Report')
           ,'employeeSalaryReport'           => $this->getReportObj('employeeSalaryReport', 'Employee Salary Report')
           ,'cPFSummaryReport'               => $this->getReportObj('cPFSummaryReport', 'CPF Summary Report')
           ,'leaveReport'                    => $this->getReportObj('leaveReport', 'Leave Report')
           ,'allowanceReport'                => $this->getReportObj('allowanceReport', 'Allowance Report')
           ,'sDLReport'                      => $this->getReportObj('sDLReport', 'SDL Report')
           ,'employeeTrainingExpiryReport'   => $this->getReportObj('employeeTrainingExpiryReport', 'Employee Training Expiry Report')
           ,'payslipByEmployeeReport'   => $this->getReportObj('payslipByEmployeeReport', 'Payslip by Employee Report')
        );
    }

    function getReportObj($name, $title, $searchFlds = array('dateRange')) {
        //searchFldType: uptoDate, dateRange, activeRange
        $arr = array(
             'name' => $name
            ,'title' => $title
            ,'searchFlds' => $searchFlds
        );

        return $arr;
    }

    /**
     *
     */
    function getEmployeeByEmployeeStatus(){
        $db    = Zend_Registry::get('db');
        $fn    = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $employee_status = $fn->getReqParam('employee_status');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite  = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sql = "
        SELECT DISTINCT e.employee_id, e.first_name
        FROM employee e
        WHERE e.status = '{$employee_status}'
        {$appendSqlSite}
        ORDER BY e.first_name ASC
        ";
        $result = $db->sql_query($sql);
        $json = array();
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['employee_id'], "caption" => $row['first_name']);
        }

        return json_encode($json);
        return $sql;
    }
}
