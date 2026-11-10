<?
class CP_Admin_Widgets_ManPower_MarketingCallByStaffReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    //Staff Name |No of Calls| Month | Status
    function getSQL(){
        $SQL = "
        SELECT DISTINCT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,(SELECT COUNT(*)
                FROM call_registry cr1
                WHERE cr1.staff_id = s.staff_id
                ) AS no_of_calls
        FROM staff s
        JOIN (call_registry cr) ON (s.staff_id = cr.staff_id)
        ";
        
               /*  $SQL = "
        SELECT DISTINCT cr.staff_id
              ,cr.status
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
			  ,(SELECT COUNT(*)
				FROM call_registry
				WHERE cr.staff_id = s.staff_id
				) AS no_of_calls
        FROM call_registry cr
        LEFT JOIN (staff s) ON (s.staff_id = cr.staff_id)
         "; */
        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');

        $start_date  = $fn->getReqParam('start_date');
        $end_date    = $fn->getReqParam('end_date');
        $month       = $fn->getReqParam('month');
        $year        = $fn->getReqParam('year');
        $status      = $fn->getReqParam('status');

        $searchVar   = $this->searchVar;
        $searchVar->mainTableAlias = 'cr';

        $current_year= date('Y');

        if ($month != ''){
            if ($year != '') {
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            } else {
                $year = date('Y');
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            }
            $searchVar->sqlSearchVar[] = "cr.contact_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }

        if ($start_date != ''){
            $searchVar->sqlSearchVar[] = "cr.date >= '{$start_date}'";
        }
        
        if ($end_date != ''){
            $searchVar->sqlSearchVar[] = "cr.date <= '{$end_date}'";
        }
        
        if ($status != ''){
            $searchVar->sqlSearchVar[] = "cr.status = '{$status}'";
        }

       // $searchVar->sortOrder = 'c.registration_no';
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'manPower_marketingCallByStaffReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     *
     */
    function getExportToExcel($dataArray = ''){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
         
        $fa = array(
              'staff_name'    => $phpExcel->getFldObj('Staff Name')
             ,'no_of_calls'   => $phpExcel->getFldObj('No of Calls')
        );

        $file_name = "TraineeByBatch_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

}