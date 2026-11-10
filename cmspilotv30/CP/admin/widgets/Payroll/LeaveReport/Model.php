<?
class CP_Admin_Widgets_Payroll_LeaveReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT l.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
              ,e.citizen
              ,e.nric_no
              ,e.fin_no
              ,e.status AS employee_status
        FROM `leave` l
        LEFT JOIN (employee e) ON (e.employee_id = l.employee_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');

        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'l';

        $year        = $fn->getReqParam('year');
        $employee_id = $fn->getReqParam('employee_id');

        $start_date = $year . '-01-01';
        $end_date = $year . '-12-31';

        $searchVar->sqlSearchVar[] = "(l.from_date BETWEEN '{$start_date}' AND '{$end_date}' OR l.to_date BETWEEN '{$start_date}' AND '{$end_date}')";
        $searchVar->sqlSearchVar[] = "l.employee_id = {$employee_id}";

        $searchVar->sortOrder = "l.leave_type ASC";
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'payroll_leaveReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     */
    function getExportToExcel(){
        $tv       = Zend_Registry::get('tv');
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "LeaveReport_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $actSheet = &$objPHPExcel->getActiveSheet();
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Employee Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NRIC/FIN');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Type of Leave');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Leave from date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Leave to date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'No Of Days (Current Month)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'No Of Days (Next Month)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Reason');
       /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $appendSql = '';
        $year        = $fn->getReqParam('year');
        $employee_id = $fn->getReqParam('employee_id');

        $start_date = $year . '-01-01';
        $end_date   = $year . '-12-31';

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND l.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT l.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
              ,e.citizen
              ,e.nric_no
              ,e.fin_no
              ,e.status AS employee_status
        FROM `leave` l
        LEFT JOIN (employee e) ON (e.employee_id = l.employee_id)
        WHERE (l.from_date BETWEEN '{$start_date}' AND '{$end_date}' OR l.to_date BETWEEN '{$start_date}' AND '{$end_date}')
          AND l.employee_id = {$employee_id}
          {$appendSqlSite}
        ORDER BY l.leave_type ASC
        ";
        $result = $db->sql_query($SQL);
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                $ic_no = $row['nric_no'];
            } else {
                $ic_no = $row['fin_no'];
            }

            $from_date = $dateUtil->formatDate($row['from_date'], 'DD MMM YYYY');
            $to_date = $dateUtil->formatDate($row['to_date'], 'DD MMM YYYY');

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $ic_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['leave_type']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $from_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $to_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['no_of_days']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['no_of_days_next_month']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['reason']);

            $count++;
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:J{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    /**
     *
     */
    function getExportToExcel1($dataArray = ''){
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $fa = array(
              'employee_name'  => $phpExcel->getFldObj('Emplloyee Name')
             ,'nric_no'        => $phpExcel->getFldObj('NRIC/FIN')
             ,'leave_type'     => $phpExcel->getFldObj('Type of Leave')
             ,'from_date'      => $phpExcel->getFldObj('Leave from Date')
             ,'to_date'        => $phpExcel->getFldObj('Leave to Date')
             ,'no_of_days'     => $phpExcel->getFldObj('No Of Days (Current Month)')
             ,'no_of_days_next_month'  => $phpExcel->getFldObj('No Of Days (Next Month)')
             ,'reason'     => $phpExcel->getFldObj('Reason')
        );

        $file_name = "LeaveReport_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getTotalLeaveDays($employee_id, $leave_type, $year){
        $db    = Zend_Registry::get('db');
        $fn    = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $start_date = $year . '-01-01';
        $end_date   = $year . '-12-31';

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND l.site_id = {$cpSiteIdSession}";
        }

        $sqlTotalLeave = "
        SELECT SUM(l.no_of_days) AS total_leave_days_current_month
              ,SUM(l.no_of_days_next_month) AS total_leave_days_next_month
        FROM `leave` l
        WHERE l.employee_id = {$employee_id}
          AND l.leave_type = '{$leave_type}'
          {$appendSqlSite}
          AND (l.from_date BETWEEN '{$start_date}' AND '{$end_date}' OR l.to_date BETWEEN '{$start_date}' AND '{$end_date}')
        ";
        $resultTotalLeave = $db->sql_query($sqlTotalLeave);
        $rowTotalLeave = $db->sql_fetchrow($resultTotalLeave);

        $total_leave_days = $rowTotalLeave['total_leave_days_current_month'] + $rowTotalLeave['total_leave_days_next_month'];

        return $total_leave_days;
    }
}