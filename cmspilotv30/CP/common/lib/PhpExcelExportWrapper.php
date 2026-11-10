<?
class CP_Common_Lib_PhpExcelExportWrapper
{
    var $excelObj;
    var $worksheet;

    var $headerStyle;
    var $boldStyle;
    var $rightStyle;

    /**
     *
     */
    function __construct() {
        $tv = Zend_Registry::get('tv');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $this->excelObj = new PHPExcel();
        $this->headerStyle = array(
             'font' => array('bold' => true)
            ,'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'cccccc'))
            ,'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                )
            )
        );
        $this->boldStyle = array(
             'font' => array('bold' => true)
        );
        $this->rightStyle = array(
             'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
        );
    }

    /**
     *
     */
    function getExportHeader($filename) {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary ");
    }

    /**
     *
     */
    function exportData($cfg) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fldsArr   = $cfg['fldsArr'];
        $dataArray = $cfg['dataArray'];
        $filename  = isset($cfg['filename']) ? $cfg['filename'] : $this->getSaveFileName();
        $headerRowPos = $fn->getIssetParam($cfg, 'headerRowPos', 1);
        $dataRowStartPos = $fn->getIssetParam($cfg, 'dataRowStartPos', 2);
        $print = $fn->getIssetParam($cfg, 'print');

        if ($print) {
            $filename = str_replace('.xls', '.pdf', $filename);
        }
        //array(instance, method). ex: array($this, writeExtraInfo)
        $module = $fn->getIssetParam($cfg, 'module', $tv['module']);
        $modObj = $fn->getIssetParam($cfg, 'modObj', getCPModelObj($module));
        $callbackBeforeSave = $fn->getIssetParam($cfg, 'callbackBeforeSave', null);
        $callbackAfterEachRow = $fn->getIssetParam($cfg, 'callbackAfterEachRow', null);

        $objPHPExcel = $this->excelObj;

        $this->getExportHeader($filename);

        $rowc = $dataRowStartPos;
        $colc = 0;

        $actSheet = &$objPHPExcel->getActiveSheet();

        //set values for header field in excel sheet
        foreach ($fldsArr as $dbFld => $fldArr) {
            $excelFld = $fldArr['excelFld']; //excel field title
            $actSheet->setCellValueByColumnAndRow($colc, $headerRowPos, $excelFld);
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($colc);
            $colWidth = $fldArr['colWidth'];
            if ($colWidth) {
                $actSheet->getColumnDimension($colAlphabet)->setWidth($colWidth);
            } else {
                $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
            }

            if ($fldArr['horizontalAlign']) {
                $horizontalAlign = strtoupper($fldArr['horizontalAlign']);
                $horizontalAlign = 'HORIZONTAL_' . $horizontalAlign;
                $horizontalAlign = constant("PHPExcel_Style_Alignment::$horizontalAlign");
                $actSheet->getStyle("{$colAlphabet}{$headerRowPos}")->getAlignment()->setHorizontal($horizontalAlign);
            }
            $colc++;
        }

        //format excel header
        $headerStyle = $this->headerStyle;

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A{$headerRowPos}:{$lastCol}{$headerRowPos}")->applyFromArray($headerStyle);

        foreach ($dataArray as $row){
            $colc = 0;

            foreach ($fldsArr as $dbFld => $fldArr) {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row[$dbFld]);
            }

            if ($callbackAfterEachRow) {//i.e. to insert order item in second row after the order details in previous row
                $parArr = array(
                    'actSheet' => $actSheet
                   ,'exportWrapper' => $this //instance of this PhpExcelExportWrapper
                   ,'dataRow' => $row
                   ,'rowCounter' => $rowc
                   ,'colCounter' => $colc
                );
                $arr = array($modObj, $callbackAfterEachRow);
                $retArr = call_user_func($arr, $parArr);
                
                $rowc = $fn->getIssetParam($retArr, 'rowCounter', $rowc);
                $colc = $fn->getIssetParam($retArr, 'colCounter', $colc);
            }            
            $rowc++;
        }

        //callback for further writing
        if ($callbackBeforeSave) {
            $parArr = array(
                'actSheet' => $actSheet
               ,'exportWrapper' => $this //instance of this PhpExcelExportWrapper
            );

            /** 24/07/2013 BSAS
            removed the & symbol from $parArr as it gave the error 
            Fatal error: Call-time pass-by-reference has been removed in 5.4
            refer: http://stackoverflow.com/questions/8971261/php-5-4-call-time-pass-by-reference-easy-fix-available
            **/
            //call_user_func($callbackBeforeSave, &$parArr);

            call_user_func($callbackBeforeSave, $parArr);
        }

        if ($print) {
            $objWriter = new PHPExcel_Writer_PDF($objPHPExcel);
        } else {
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        }
        $objWriter->save('php://output');
    }

    /**
     *
     */
    function getFldObj($excelFld){
        $retArr = array(
            'excelFld'     => $excelFld
           ,'colWidth' => '' //in number of chars
           ,'horizontalAlign' => '' //left/right/middle
           ,'exp' => array()
        );

        return $retArr;
    }

    /**
     *
     */
    function setRepeatedBorder($sheet, $colStart, $colEnd, $rowStart, $rowEnd, $styleArray) {
        $colS = $colStart;
        $rowS = $rowStart;

        while ($colS != $colEnd) {
            $sheet->getStyle("{$colS}1:{$colS}{$rowEnd}")->applyFromArray($styleArray);
            $colS++;
        }
        while ($rowS <= $rowEnd) {
            $sheet->getStyle("{$colStart}{$rowS}:{$colEnd}{$rowS}")->applyFromArray($styleArray);
            $rowS++;
        }
    }

    /**
     *
     */
    function getCoulmnNameFromNumber($num) {
        $numeric = ($num - 1) % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval(($num - 1) / 26);
        if ($num2 > 0) {
            return getNameFromNumber($num2) . $letter;
        } else {
            return $letter;
        }
    }
    
    function getSaveFileName() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');

        $title = $modulesArr[$tv['module']]['title'];
        $title = str_replace(' ', '_', $title);
        $file_name = $title . '_' . date('d-m-Y') . '.xls';

        return $file_name;
        
    }
    
    /***
    QUICK USAGE OF PHPEXCEL FUNCTIONS
    
    To set the values by row number & column number
    ===============================================
    $objPHPExcel = new PHPExcel();
    $actSheet = &$objPHPExcel->getActiveSheet();

    $rowc = 1;
    $colc = 0;

    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
    

    Set the value explicitly as a string
    ====================================
    $actSheet->setCellValueExplicit('A1', '0029', PHPExcel_Cell_DataType::TYPE_STRING);

    Get column name by number
    ====================================
    $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);

    Auto Width to Columns
    ====================================
    $colName = PHPExcel_Cell::stringFromColumnIndex($colc);
    $actSheet->getColumnDimension($colName)->setAutoSize(true);

    ***/
    
}