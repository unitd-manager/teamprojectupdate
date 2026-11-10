<?
class CPL_Admin_Modules_EnggCrm_Reports_Model extends CP_Admin_Modules_EnggCrm_Reports_Model
{
    var $reportsArray = array();

    function __construct() {
        $cpUtil  = Zend_Registry::get('cpUtil');

        $this->reportsArray = array(
            'standardReports' => array(
               'title' => 'Standard Reports'
              ,'reports' => array(
                   'overallSalesSummary' => $this->getReportObj('enggCrm_overallSalesSummary', 'Overall Sales Summary')
                  ,'projectReport'       => $this->getReportObj('enggCrm_projectReport', 'Project Report')
                  ,'contractReport'       => $this->getReportObj('enggCrm_contractReport', 'Contract Report')
              )
            )

            ,'financialReports' => array(
               'title' => 'Financial Reports'
              ,'reports' => array(
                   'invoiceByMonthReports'     => $this->getReportObj('enggCrm_invoiceByMonthReports', 'Invoice By Month')
                  ,'invoiceByYearReports'      => $this->getReportObj('enggCrm_invoiceByYearReports', 'Invoice by Year')
                  ,'statementofAccountsReport' => $this->getReportObj('enggCrm_statementofAccountsReport', 'Statement of Accounts Report')
                  ,'ageingReport'              => $this->getReportObj('enggCrm_ageingReport', 'Ageing Report')
              )
            )

            ,'payrollReports' => array(
               'title' => 'Payroll Reports'
              ,'reports' => array(
                   'employeePayslipGeneratedReport' => $this->getReportObj('payroll_employeePayslipGeneratedReport', 'Payslip Generated Report')
                  ,'employeeSalaryReport'           => $this->getReportObj('payroll_employeeSalaryReport', 'Employee Salary Report')
                  ,'employeeTrainingExpiryReport'   => $this->getReportObj('payroll_employeeTrainingExpiryReport', 'Employee Training Expiry Report')
                  ,'cPFSummaryReport'               => $this->getReportObj('payroll_cPFSummaryReport', 'CPF Summary Report')
                  ,'ir8a'                           => $this->getReportObj('payroll_ir8aReport', 'IR8A Report')
              )
            )
        );
    }

    /**
     *
     */
    function getCompanyNameByJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $client_type = $fn->getReqParam('client_type');

        $appendSQL = "";
        if ($client_type == "Client"){
            $SQL = "
            SELECT DISTINCT c.company_id
                  ,c.company_name
            FROM company c
            WHERE c.category = 'Client'
            AND c.company_name != ''
            AND c.company_name IS NOT NULL
            ORDER BY c.company_name
            ";
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
        } else {
            $SQL = "
            SELECT DISTINCT s.supplier_id
                  ,s.company_name
            FROM supplier s
            WHERE s.company_name != ''
            AND s.company_name IS NOT NULL
            ORDER BY s.company_name
            ";
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
        }

        $json = array();
        if ($client_type == "" || $numRows == 0){
            $json[] = array("value" => "", "caption" => "Please Select");
            return json_encode($json);
        }

        while ($row = $db->sql_fetchrow($result)) {
            if ($client_type == "Client"){
              $json[] = array("value" => $row['company_id'], "caption" => $row['company_name']);
            } else {
              $json[] = array("value" => $row['supplier_id'], "caption" => $row['company_name']);
            }
        }

        return json_encode($json);
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
}
