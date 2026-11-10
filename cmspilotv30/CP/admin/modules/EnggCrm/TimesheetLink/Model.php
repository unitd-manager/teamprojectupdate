<?
class CP_Admin_Modules_EnggCrm_TimesheetLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('staff_id', 'Please select the staff');
        $validate->validateData('entry_date', 'Please enter the date');
        $validate->validateData('hours', 'Please enter the hours');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditValidate() {
        return $this->getNewValidate();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'entry_date');
        $fa = $fn->addToFieldsArray($fa, 'hours');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'task_id');
        $fa = $fn->addToFieldsArray($fa, 'project_id');
        $fa = $fn->addToFieldsArray($fa, 'opportunity_id');

        return $fa;
    }

    /**
     *
     */
    function getAddRecordFromList() {
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $this->setFields1();
        $fa = &$this->fieldsArray;
        $fa['creation_date'] = date('Y-m-d H:i:s');
        $SQL    = $dbUtil->getInsertSQLStringFromArray($this->fieldsArray, "timesheet");
        $result = $db->sql_query($SQL);
        $id     = $db->sql_nextid();

        $fnMod = includeCPClass('ModuleFns', 'enggCrm_timesheet');
        $fnMod->refreshValuesBasedOnTimeRecord($id);

        $text = "
        <script>
            window.opener.UtilDocument.refreshPage();
            window.close();
        </script>
        ";

        return $text;
    }

    /**
     *
     */
    function getStaffRate($staff_id) {
        $db = Zend_Registry::get('db');
        
        if ($staff_id == '') {
            return;
        }
        
        $SQL    = "SELECT staff_rate FROM staff WHERE staff_id = {$staff_id}";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        $staff_rate = $row['staff_rate'];

        return $staff_rate;
    }

    /**
     *
     */
    function getTimesheetSummaryByMonth() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $result = Zend_Registry::get('result');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $start_date = $fn->getReqParam('entry_date_1');
        $end_date   = $fn->getReqParam('entry_date_2');
        if ($start_date == '' || $end_date == '') {
            $start_date = date('Y-m-1');
            $end_date = date('Y-m-d');
        }
        $filename = 'timesheetSummaryByMonth_' . date('d-m-Y') . '.xls';

        $fn->getAddHeaderForExcelReport($filename);

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();

        $commonCellStyle = array(
            'borders' => array(
                    'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => '00000000'),
                    ),
            ),
        );

        $boldStyle = array(
            'font' => array(
                'bold' => true,
            )
        );
        $headerRowHeight = 60;
        $rowHeight = 25;
        $colWidth  = 20;

        $SQL = "
        SELECT s.staff_id
              ,ts.entry_date
              ,SUM(ts.hours) AS hours
        FROM staff s
        JOIN timesheet ts  ON (ts.staff_id = s.staff_id)
        WHERE ts.entry_date BETWEEN '{$start_date}' AND '{$end_date}'
        GROUP BY s.staff_id
                ,ts.entry_date
        ORDER BY s.staff_id
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        //store timesheet data in array for later processing
        //array('staff_id' => array('yyyy-mm-dd' => hours1, 'yyyy-mm-dd' => hours2 .... ) )
        $tsArr = array();
        while ($row = $db->sql_fetchrow($result)) {
            if (!isset($tsArr[$row['staff_id']])) {
                $tsArr[$row['staff_id']] = array();
                $staffArr = &$tsArr[$row['staff_id']];
            }
            $staffArr[$row['entry_date']] = $row['hours'];
        }

        $start_date_obj      = strtotime($start_date);
        $start_date_obj_temp = $start_date_obj;
        $end_date_obj        = strtotime($end_date);

        $col = 'A';
        $rowCount = 1;

        //write header content
        $sheet->setCellValue($col . $rowCount, 'Staff/Date');
        $sheet->getColumnDimension($col)->setWidth($colWidth);
        $sheet->getRowDimension($rowCount)->setRowHeight($headerRowHeight);
        $col++;

        $startWeekDay = date('d', $start_date_obj_temp);
        $startCol     = $col;
        $startCol2    = $col;
        $endCol       = '';
        while ($start_date_obj_temp <= $end_date_obj) {
            $title = '';
            $dayOfWeek = date('D', $start_date_obj_temp); //ex: Sun
            $dayOfWeek = substr($dayOfWeek, 0, 1);
            $dayOfWeek2 = date('D', $start_date_obj_temp); //ex: Sun
            $day = date('d', $start_date_obj_temp);

            $title2 = date('j/n', $start_date_obj_temp); //ex: 8/3 S -- (Sunday)
            $sheet->getColumnDimension($col)->setWidth(6);
            $sheet->setCellValue($col . $rowCount, $day);
            $sheet->setCellValue(($col . ($rowCount + 1)), $title2);
            $sheet->setCellValue(($col . ($rowCount + 2)), $dayOfWeek);
            if ($dayOfWeek2 == 'Sun' || $start_date_obj_temp == $end_date_obj) {
                $month      = date('M', $start_date_obj_temp);
                $endWeekDay = date('d', $start_date_obj_temp);
                $title      = $month . ' ' . $startWeekDay . ' - ' . $endWeekDay;
                $sheet->mergeCells("{$startCol}{$rowCount}:{$col}{$rowCount}");
                $sheet->getStyle("{$startCol}{$rowCount}")->getAlignment()
                      ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->setCellValue($startCol . $rowCount, $title);
                $col++;
                $sheet->setCellValue($col . $rowCount, 'Total no. of hours inputted');
                $sheet->getStyle("{$col}{$rowCount}")->getAlignment()->setWrapText(true);
                $startCol = $col;
                $startCol++;
                $startWeekDay = date('d', strtotime('+1 day', $start_date_obj_temp));
            }

            $start_date_obj_temp = strtotime('+1 day', $start_date_obj_temp);
            $col++;
        }

        $col = $cpUtil->getSubtractString($col);
        $endCol = $col;
        $sheet->getStyle("{$startCol2}{$rowCount}:{$endCol}{$rowCount}")
              ->getFill()
              ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
              ->getStartColor()
              ->setRGB('DDDD00');

        $rowCount++;
        $sheet->getRowDimension($rowCount)->setRowHeight($rowHeight);
        $rowCount++;
        $sheet->getRowDimension($rowCount)->setRowHeight($rowHeight);
        $rowCount++;
        

        //write body data

        $SQL = "
        SELECT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM staff s
        WHERE s.published = 1
        ORDER BY staff_name
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $sheet->getRowDimension($rowCount)->setRowHeight($rowHeight);
            $col = 'A';
            $staff_id   = $row['staff_id'];
            $staff_name = $row['staff_name'];
            $sheet->setCellValue($col . $rowCount, $staff_name);
            $col++;

            $start_date_obj_temp = $start_date_obj;
            $total = '';
            while ($start_date_obj_temp <= $end_date_obj) {
                $date = date('Y-m-d', $start_date_obj_temp);
                $dayOfWeek2 = date('D', $start_date_obj_temp); //ex: Sun
                $hour = '';
                if (isset($tsArr[$staff_id][$date])) {
                    $hour = $tsArr[$staff_id][$date];
                }
                $total += $hour;
                $sheet->setCellValue($col . $rowCount, $hour);

                if ($dayOfWeek2 == 'Sun' || $start_date_obj_temp == $end_date_obj) {
                    $col++;
                    $sheet->setCellValue($col . $rowCount, $total);
                    $sheet->getStyle("{$col}{$rowCount}")->applyFromArray($boldStyle);
                    $total = '';
                }
                $start_date_obj_temp = strtotime('+1 day', $start_date_obj_temp);
                $col++;
            }
            $rowCount++;
        }

        $rowCount--;
        $sheet->getStyle("B1:{$endCol}{$rowCount}")->getAlignment()
              ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $fn->setRepeatedBorder($sheet, 'A', $endCol, 1, $rowCount, $commonCellStyle);
        $sheet->getStyle("A1:A{$rowCount}")->applyFromArray($boldStyle);
        
        $fn->getSaveExcelReport($objPHPExcel);
        
        exit();

        $objPHPExcel->getActiveSheet()->duplicateStyleArray(
            array(
            'font' => array( 'bold' => true ),
            'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
            ), 'A1:F1'
        );


        $rowCount = 1;

  
        $objPHPExcel->getActiveSheet()->duplicateStyleArray(
                array(
                'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                ), 'A1:F' . $rowCount
        );

        exit();
        
        $fn->getSaveExcelReport($objPHPExcel, $file_name);
    }
}
