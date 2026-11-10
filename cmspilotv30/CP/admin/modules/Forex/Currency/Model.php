<?
class CP_Admin_Modules_Forex_Currency_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
    	
        $SQL = "
        SELECT c.* 
        FROM currency c
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function getQuickSearch() {
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "c.currency_id  = '{$tv['record_id']}'";

        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.currency_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.country LIKE '%{$tv['keyword']}%'
                    OR c.currency_name LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('currency_name', 'Please enter the currency');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        
        $validate->resetErrorArray();

        $validate->validateData('currency_name', 'Please enter the currency');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'currency_name');
        $fa = $fn->addToFieldsArray($fa, 'country');
        $fa = $fn->addToFieldsArray($fa, 'we_sell');
        $fa = $fn->addToFieldsArray($fa, 'we_buy');
        $fa = $fn->addToFieldsArray($fa, 'published');
        
        return $fa;
    }

    //==================================================================//
    function getExportData($dataArray){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Currency_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Currency Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Buying Rate');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Selling Rate');
        
        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );
        
        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);
        //$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
        
        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        //============================================================================= //
        foreach ($dataArray as $row){
            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['currency_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['we_buy']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['we_sell']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

    //==================================================================//
    function getImportData(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $mediaArray = Zend_Registry::get('mediaArray');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');
        
        set_time_limit(50000);
        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';
        
        foreach ($_FILES as $key => $value) {
            if ($value['name'] == ""){
                print "Error: Please choose a file to import <a href=\"javascript:history.back();\">Back</a>";
                return;
            }
            
            //======================================================================//
            $contentType = $value['type'];
            $sourceFile  = $value['tmp_name'];
            $mediaSize   = $value['size'];
            $file_name   = $value['name'];
            
            //if ($contentType != "application/vnd.ms-excel" && $contentType != "application/download" && $contentType != "application/x-msdownload"){
            //    print "Error: you can only choose xls file format <a href=\"javascript:history.back();\">Back</a>";
            //    return;
            //}
            
            $tempFile    = $mediaArray["tempFolder"] . $file_name;
            $result      =  move_uploaded_file($sourceFile, $tempFile);
            
            $fileName  = realpath($tempFile);
            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($fileName);
            $this->worksheet = $objPHPExcel->getActiveSheet();
            $countRows       = $this->worksheet->getHighestRow();
            $countCols       = $this->worksheet->getHighestColumn();
            
            for ($i = 'A'; $i <= $countCols; $i++) {
                $cellPos = $i . '1';
                $fieldName      = $this->worksheet->getCell($cellPos)->getValue();
                $fieldsArray[]  = $fieldName;
                $this->fieldsArrayPos[$fieldName] = $i;
            }
            
            for ($curRow = 2; $curRow <= $countRows; $curRow++) {
                $fieldsArray = array();
                $fa = &$fieldsArray;
                
                $fa['currency_name'] = $this->getExcelFieldValue("Currency Code", $curRow);
                $fa['we_buy']  = $this->getExcelFieldValue("Buying Rate", $curRow);
                $fa['we_sell'] = $this->getExcelFieldValue("Selling Rate", $curRow);
                $fa['modification_date'] = date("Y-m-d H:i:s");
                
                $currency_name = $dbUtil->replaceForDB($fa['currency_name']);
                
                $SQL = "
                SELECT c.* 
                FROM currency c
                WHERE c.currency_name = '{$currency_name}'
                ";
                $result= $db->sql_query($SQL);
                $numRows       = $db->sql_numrows($result);
                
                if($numRows == 0){
                    $SQL        = $dbUtil->getInsertSQLStringFromArray($fa, "currency");
                    $result     = $db->sql_query($SQL);
                    $currency_id = $db->sql_nextid();
                } else {
                    $row = $db->sql_fetchrow($result);
	   	            $whereCondition = "WHERE currency_id = {$row['currency_id']}";
                    $SQL        = $dbUtil->getUpdateSQLStringFromArray($fa, "currency", $whereCondition);
                    $result     = $db->sql_query($SQL);
                    $currency_id = $row['currency_id'];
                }
            }
        }
        
        $text = "
        <script>
           window.opener.location = window.opener.location;
        </script>
        <h3>Import Complete. Please close this window.</h3>
        ";
        
        return $text;
    }

    //==================================================================//
    function getExcelFieldValue($fieldName, $rowNo, $emptyValue = ""){
       global $dbUtil;
    
       $fieldValue = "";
       require_once 'PHPExcel/RichText.php';
    
       $fieldsArrayPos = $this->fieldsArrayPos;
    
       $hasColumn =   array_key_exists($fieldName , $fieldsArrayPos) ? 1 : 0;
    
       if ($hasColumn){
          $cellPos    = array_key_exists($fieldName , $fieldsArrayPos) ? $fieldsArrayPos[$fieldName] : "";
    
          if ($cellPos != ""){
             $cellAbsPos = $cellPos . $rowNo;
             $fieldValue = $this->worksheet->getCell($cellAbsPos)->getValue();
    
             if (gettype($fieldValue)=="object") {
                 $fieldValue = $fieldValue->getPlainText();
             }
    
             $fieldValue = trim($fieldValue);
          }
       }
       else {
          $fieldValue = $emptyValue;
       }
    
       return $fieldValue;
    }

}
