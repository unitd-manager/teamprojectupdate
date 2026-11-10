<?
class CP_Admin_Modules_Labsg_Reports_Model extends CP_Common_Lib_ModuleModelAbstract
{
    var $reportsArray = array();

    function __construct() {
        $cpUtil  = Zend_Registry::get('cpUtil');

        $this->reportsArray = array(
            'patientVisitSummary'	 		 => $this->getReportObj('patientVisitSummary', 'Patient Visit Summary')
           ,'patientVisitDetailReport'       => $this->getReportObj('patientVisitDetailReport', 'Patient Visit Detail Report')
           ,'dailyCollectionReport' 		 => $this->getReportObj('dailyCollectionReport', 'Daily Collection Report')
           ,'masterFinanceSummaryReport'     => $this->getReportObj('masterFinanceSummaryReport', 'Master Finance Summary Report')
           ,'revenueByDay'			         => $this->getReportObj('revenueByDay', 'Revenue By Day')
           ,'revenueByMonth' 			     => $this->getReportObj('revenueByMonth', 'Revenue By Month')
           ,'treatmentHistory'		         => $this->getReportObj('treatmentHistory', 'Treatment History')
           ,'visitByDay'                     => $this->getReportObj('visitByDay', 'Visit By Day')
           ,'invoiceSummary'                 => $this->getReportObj('invoiceSummary', 'Invoice Summary')
           ,'ageingReport'                   => $this->getReportObj('ageingReport', 'Ageing Report')
           ,'clientSummaryReport'            => $this->getReportObj('clientSummaryReport', 'Client Summary Report')
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
    function getCompanyPatientSqlByBillType(){
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
                           ,o.first_name AS company_patient_name
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
}
