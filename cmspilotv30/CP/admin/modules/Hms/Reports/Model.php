<?
class CP_Admin_Modules_Hms_Reports_Model extends CP_Common_Lib_ModuleModelAbstract
{
    var $reportsArray = array();

    function __construct() {
        $cpUtil  = Zend_Registry::get('cpUtil');

        $this->reportsArray = array(
            'patientVisitSummary'	  => $this->getReportObj('patientVisitSummary', 'Patient Visit Summary')
           ,'dailyCollectionReport'   => $this->getReportObj('dailyCollectionReport', 'Daily Collection Report')
           ,'revenueByDay'			  => $this->getReportObj('revenueByDay', 'Revenue By Day')
           ,'revenueByMonth' 		  => $this->getReportObj('revenueByMonth', 'Revenue By Month')
           ,'treatmentHistory'		  => $this->getReportObj('treatmentHistory', 'Treatment History')
           ,'visitByDay'              => $this->getReportObj('visitByDay', 'Visit By Day')
           ,'invoiceSummary'          => $this->getReportObj('invoiceSummary', 'Invoice Summary')
           ,'companyInvoiceSummary'   => $this->getReportObj('companyInvoiceSummary', 'Company Invoice Summary')
           ,'panelInvoiceSummary'     => $this->getReportObj('panelInvoiceSummary', 'Panel Invoice Summary')
           ,'expenseReport'           => $this->getReportObj('expenseReport', 'Expense Report')
           ,'stockReport'             => $this->getReportObj('stockReport', 'Stock Report')
           ,'dutyRosterReport'        => $this->getReportObj('dutyRosterReport', 'Duty Roster Report')
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
     function getIncomeByCourse($SQLNeeded = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $text = "";
        $rows = "";
        $sqlStartDate = "";
        $sqlEndDate = "";

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $status     = $fn->getReqParam('specialSearch');

        if ($status == ''){
            $status = 'Due';
        }

        if ($start_date != ''){
            $sqlStartDate = " AND o.creation_date >= '{$start_date}'";
        }

        if ($end_date != ''){
            $sqlEndDate = " AND o.creation_date <= '{$end_date}'";
        }

        //$SQL =  $this->getTraineeByCourseSQL();

        $SQL = "
        SELECT ABS( ABS( SUM( oi.unit_price ) ) ) AS total
              ,c.title as course_title
        FROM `order` o
        JOIN order_item oi ON oi.order_id = o.order_id
        LEFT JOIN course c ON c.course_id = oi.record_id
        WHERE o.order_status = '{$status}'
        {$sqlStartDate}
        {$sqlEndDate}
        GROUP BY oi.record_id
        ORDER BY course_title
        ";

        if ($SQLNeeded == 1){
            return $SQL;
        }

        $result = $db->sql_query($SQL);
        $resultTable = $db->sql_query($SQL);

        $rows = array(
         'course_title'
        ,'total'
        );

        $columns = array(
        'Course'
        ,'Total'
        );

        $text .= $fn->getTableRowsColumns($resultTable, $rows, $columns);

        return $text;
    }
    /**
     *
     */
    function getCompanyPatientSqlByBillType1(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $bill_type = $fn->getReqParam('bill_type');

        if ($bill_type == 'Company') {
            $sql = "
            SELECT DISTINCT o.company_id AS company_patient_id
                           ,o.company_name AS company_patient_name
            FROM `order` o
            WHERE o.company_id != ''
            ORDER BY company_patient_name ASC
            ";
        } else {
            $sql = "
            SELECT DISTINCT o.patient_information_id AS company_patient_id
                           ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS company_patient_name
            FROM `order` o
            WHERE o.patient_information_id != ''
            ORDER BY company_patient_name ASC
            ";
        }

        $result = $db->sql_query($sql);
        $json[] = array("value" => "", "caption" => "Select Patient / Company");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['company_patient_id'], "caption" => $row['company_patient_name']);
        }

        return json_encode($json);

        return $sql;
    }

    /**
     *
     */
    function getCompanyPatientSqlByBillType() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $bill_type = $fn->getReqParam('bill_type');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $patientDetail = $extractor[0];

        if ($bill_type != '') {
             if ($bill_type == 'Company' || $bill_type == 'Panel') {
                if ($bill_type == 'Company'){
                    $bill_type = 'Client';
                }

                $sql = "
                SELECT  company_name AS value
                       ,company_name AS label
                       ,company_id AS id
                       ,company_name AS company_name
                FROM company
                WHERE (company_name LIKE '%{$patientDetail}%')
                AND category = '{$bill_type}'
                ORDER BY company_name ASC
                ";
                $result = $db->sql_query($sql);
            } else {

                $sql = "
                SELECT  CONCAT_WS(' ', first_name, middle_name, last_name) AS value
                       ,CONCAT_WS(' :: ', first_name, middle_name, last_name) AS label
                       ,patient_information_id AS id
                       ,CONCAT_WS(' ', first_name, middle_name, last_name) AS Patient_Name
                FROM patient_information
                WHERE (first_name LIKE '%{$patientDetail}%'
                OR middle_name LIKE '%{$patientDetail}%'
                OR last_name LIKE '%{$patientDetail}%')
                ORDER BY Patient_Name
                ";
                $result = $db->sql_query($sql);
            }

            $dataArray = $dbUtil->getResultsetAsArray($result);
            $arr = json_encode($dataArray);
        }else{
            $arr = array();
        }

        return $arr;
    }
}
