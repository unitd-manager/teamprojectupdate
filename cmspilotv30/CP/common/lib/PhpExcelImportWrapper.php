<?
class CP_Common_Lib_PhpExcelImportWrapper
{
    //the full file path the uploaded file in the media/temp folder.
    //or specified file path in the import config
    var $excelFilePath;
    var $excelObj;
    var $worksheet;
    //holds the column positions. ex: array('First Name' => A)
    //this property is kept for backward compatibility. Currently the column is
    //stored in the $fldsArr (excelColName) index.
    var $fieldsArrayPos = array();
    var $fldsArr = array();
    var $excludeFldsArr = array();

    var $countRows;
    var $countCols;
    var $curRow;
    var $sheetName = '';
    var $importLogText = '';

    //to store temp parameters for ex: to use in the overrideDefaultProcessCallback function
    var $tmpOpts = array();

    //==================================================================//
    function __construct() {
        set_time_limit(50000);
        ini_set('memory_limit', '512M');
    }

    /**
     *
     */
    function getUploadedFile() {
        $mediaArray = Zend_Registry::get('mediaArray');

        foreach ($_FILES as $key => $value) {
            if ($value['name'] == ""){
                print "
                <div class='left'>
                    Error: Please choose a file to import
                    <br><br>
                    <a class='cpBack' href='#'>Back</a>
                </div>
                ";
                return false;
            }

            //======================================================================//
            $contentType = $value['type'];
            $sourceFile  = $value['tmp_name'];
            $mediaSize   = $value['size'];
            $file_name   = $value['name'];

//            if ($contentType != "application/vnd.ms-excel" && $contentType != "application/download"
//                && $contentType  != 'application/x-msdownload'){
//                print "
//                <div class='left'>
//                    Error: you can only choose xls file format
//                    <br><br>
//                    <a class='cpBack' href='#'>Back</a>
//                </div>
//                ";
//                return false;
//            }

            $tempFile = $mediaArray["tempFolder"] . $file_name;
            $result   =  move_uploaded_file($sourceFile, $tempFile);
            $this->excelFilePath = realpath($tempFile);
        }
        return true;
    }

    /**
     *
     */
    function setWorksheetObj() {
        $mediaArray = Zend_Registry::get('mediaArray');

        require_once 'PHPExcel/IOFactory.php';
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($this->excelFilePath);
        $this->excelObj = $objPHPExcel;
        if ($this->sheetName) {
            $this->worksheet = $objPHPExcel->getSheetByName($this->sheetName);
            if (!$this->worksheet) {
                $msg = "
                Sheet name: {$this->sheetName} not found.<br>
                <a href='javascript:history.back()'>Back</a>
                ";
                exit($msg);
            }
        } else {
            $this->worksheet = $objPHPExcel->getActiveSheet();
        }
        $this->countRows = $this->worksheet->getHighestRow();
        $this->countCols = $this->worksheet->getHighestColumn();

        return true;
    }

    /**
     *
     */
    function setFieldsColName() {
        for ($i = 'A'; $i != 'ZZ'; $i++) {
            $cellPos = $i . '1';
            $fieldName     = $this->worksheet->getCell($cellPos)->getValue();
            $this->fieldsArrayPos[$fieldName] = $i;

            if ($i == $this->countCols){
                break;
            }
        }
        foreach ($this->fldsArr as $fldName => &$fldArr) {
            $excelFld = $fldArr['excelFld'];
            $fldArr['excelColName'] = isset($this->fieldsArrayPos[$excelFld]) ?
                                      $this->fieldsArrayPos[$excelFld] : '' ; //A, B, C .. AA, AB etc
        }

        return true;
    }

    /**
     *
     */
    function importData($cfg) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $modulesArr = Zend_Registry::get('modulesArr');

        $module = $fn->getIssetParam($cfg, 'module');
        ini_set('memory_limit', '1536M');

        //use the fields in this array to determain whether update an existing
        //record or create a new one.
        $matchFieldArr = $fn->getIssetParam($cfg, 'matchFieldArr', array());

        //if the fields in this array are empty then the import of this
        //particular record will not proceed.
        $mandatoryFldsArr = $fn->getIssetParam($cfg, 'mandatoryFldsArr', array());
        $this->fldsArr = $cfg['fldsArr'];

        //print_r($cfg['fldsArr']);
        //die();

        //callback before each row is inserted. You have the chance to amend the data from excel row
        $callbackBeforeInsert = $fn->getIssetParam($cfg, 'callbackBeforeInsert', null);

        //callback after each row is inserted
        $callbackAfterInsert = $fn->getIssetParam($cfg, 'callbackAfterInsert', null);

        //callback will be called after insert of all the records
        $callbackAfterImport = $fn->getIssetParam($cfg, 'callbackAfterImport', null);

        //override callback for each row in the excel
        //if this callback is set then it will be called in the beginning of the loop
        //and the default process of insert/update etc will not be done
        $overrideDefaultProcessCallback = $fn->getIssetParam($cfg, 'overrideDefaultProcessCallback', null);

        $this->sheetName = $fn->getIssetParam($cfg, 'sheetName');
        $startRow = $fn->getIssetParam($cfg, 'startRow', 2);

        $table = $fn->getIssetParam($cfg, 'tableName', $modulesArr[$module]['tableName']);
        $keyField = $fn->getIssetParam($cfg, 'keyField', $modulesArr[$module]['keyField']);
        $this->excelFilePath = $fn->getIssetParam($cfg, 'excelFilePath', '');

        if (!$this->excelFilePath) {
            if (!$this->getUploadedFile()){
                return;
            }
        }
        $this->setWorksheetObj();
        $this->setFieldsColName();

        //-----------------------------//
        $modObj = $fn->getIssetParam($cfg, 'modObj', getCPModelObj($module));

        for ($curRow = $startRow; $curRow <= $this->countRows; $curRow++) { //for 1
            $this->curRow = $curRow;

            if ($overrideDefaultProcessCallback) {
                $callbackArr = $overrideDefaultProcessCallback;
                if (!is_array($overrideDefaultProcessCallback)) {
                    $callbackArr = array($modObj, $overrideDefaultProcessCallback);
                }
                call_user_func($callbackArr, $this->fldsArr, $curRow, $this);
                continue;
            }

            $rec = '';

            /**** if one of the mandatory fields does not exist or empty, then do not progress **/
            $allMandatoryOK = true;
            foreach ($mandatoryFldsArr as $mandatoryFld) {
                $mandatoryFldExcel = $this->fldsArr[$mandatoryFld]['excelFld'];
                $mandatoryFldVal = $this->getExcelFieldValue($mandatoryFldExcel, $curRow);

                if (trim($mandatoryFldVal) == ''){
                    $allMandatoryOK = false;
                    break;
                }
            }

            //if not all mandatory fields are OK then do not insert the record
            if (!$allMandatoryOK){
                continue;
            }

            /**** check for record existance by querying against the match fields ****/
            $rec = array();
            $fa = $this->getFieldsForImport($curRow, $this->fldsArr);

            if (count($matchFieldArr) > 0){
                $macthFieldQueryArr = array();
                foreach ($matchFieldArr as $matchField) {
                    //$macthFieldExcel = $this->fldsArr[$matchField]['excelFld'];
                    //$macthFieldVal = escapeForDb($this->getExcelFieldValue($macthFieldExcel, $curRow));
                    $macthFieldVal = escapeForDb($fa[$matchField]);
                    $macthFieldQueryArr[] = "{$matchField} = '{$macthFieldVal}'";
                }

                //ex: country_id = 1 AND title = 'abcde'
                $macthFieldQuery = join(' AND ', $macthFieldQueryArr);
                $rec = $fn->getRecordByCondition($table, $macthFieldQuery, '', $module);
            }


            $exp = array();
            //callback before insert
            if ($callbackBeforeInsert) {
                $arr = array($modObj, $callbackBeforeInsert);
                call_user_func($arr, $fa, $exp);
            }
            /**** create the record if does not exist otherwise update it ****/
            if(!is_array($rec) || count($rec) == 0){
                $fa = $fn->addCreationDetailsToFieldsArray($fa, $table);
                $keyFieldVal = $fn->addRecord($fa, $table, $this->excludeFldsArr);
                $exp['is_inserted'] = true;
            } else {
                $fa = $fn->addModificationDetailsToFieldsArray($fa, $table);
                $keyFieldVal = $rec[$keyField];
                $whereCondition = "WHERE {$keyField} = {$keyFieldVal}";
                $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, $table, $whereCondition, $this->excludeFldsArr);
                $result = $db->sql_query($SQL);
                $exp['is_inserted'] = false;
            }


            //callback for further processing
            if ($callbackAfterInsert) {
                if (is_array($callbackAfterInsert)) {
                    $arr = $callbackAfterInsert;
                } else {
                    $arr = array($modObj, $callbackAfterInsert);
                }
                call_user_func($arr, $keyFieldVal, $fa, $exp);
            }
        } //end for 1

        //callback after all the records are inserted
        if ($callbackAfterImport) {
            if (is_array($callbackAfterImport)) {
                $arr = $callbackAfterImport;
            } else {
                $arr = array($modObj, $callbackAfterImport);
            }
            call_user_func($arr, $fa, $exp);
        }

        return "
        <script>
           window.opener.location = window.opener.location;
        </script>
        <div class='left'>
            <h1>Import Completed. Please <strong><a href='javascript:window.close();'>close</a></strong> this window</h1>
        </div>
        ";
    }

    /**
     *
     */
    function getExcelFieldValue($fieldName, $rowNo, $emptyValue = ''){
        $fieldValue = '';
        require_once 'PHPExcel/RichText.php';

        $colName = '';
        //AS: commented lines seems to be unwanted which caused some issues (ref: gemsoft to carolina journal hist)
        //check the fieldName is available in the fldsArr
        //if (isset($this->fldsArr[$fieldName])) {
            //$colName = $this->fldsArr[$fieldName]['excelColName'];
        //} else {
            $fieldsArrayPos = $this->fieldsArrayPos;
            $hasColumn = array_key_exists($fieldName, $fieldsArrayPos) ? true : false;
            $colName = array_key_exists($fieldName, $fieldsArrayPos) ? $fieldsArrayPos[$fieldName] : "";
        //}

        if ($colName) {
            $cellAbsPos = $colName . $rowNo;
            $fieldValue = $this->worksheet->getCell($cellAbsPos)->getValue();

            if (gettype($fieldValue) == "object") {
                $fieldValue = $fieldValue->getPlainText();
            }
            $fieldValue = trim($fieldValue);
        } else {
            $fieldValue = $emptyValue;
        }
        return $fieldValue;
    }

    function getEFV($fieldName, $rowNo, $emptyValue = ""){
        return $this->getExcelFieldValue($fieldName, $rowNo, $emptyValue);
    }

    /**
     *
     */
    private function addToFieldsArray($curRow, $arr, $excelFldName, $actualFldName, $defaultValue = ''){
        if(array_key_exists($excelFldName , $this->fieldsArrayPos)){
            $value = $this->getExcelFieldValue($excelFldName, $curRow);
            $arr[$actualFldName] = $value;
        } else if ($defaultValue !== ''){
            $arr[$actualFldName] = $defaultValue;
        }

        return $arr;
    }

    /**
     *
     */
    function getImportFldObj($excelFld, $colPos = 0){
        $expArr = array(
             'keyText' => ''
            ,'sectionType' => ''
            ,'categoryFldKeyInArr' => ''
            ,'refModule' => ''
            ,'refModuleRecAutoCreate' => true
            //ex array('db_field_name' => 'Excel Col Name')
            ,'extraFldsOnCreation' => array()
            //to populate extra lang fields (i.e. chi_title, chi_name) format should be array('field_name' => 'Excel Col Name')
            ,'extraLangFldsOnCreation' => array()
            ,'extraFldsInSqlCondn' => array()
        );
        $retArr = array(
            //it's the column title usually the first row value of a column.
            //ex: 'First Name', 'Last Name', 'Email' etc
            'excelFld' => $excelFld //it's the column title usually the first row value of a column
           ,'specialType' => '' //fetchIdFromRefModule/geo_country/valuelist/section/category/subCategory
           ,'defaultValue' => ''
           ,'refOnly' => false //this field will not be included in database insert if set to true.
           //column position. This is used when reading column using col position instead column header
           ,'colPos' => $colPos
           ,'excelColName' => '' //will be set later during import data. ex: A, B
           ,'exp' => $expArr
        );

        return $retArr;
    }

    /**
     *
     */
    public function getFieldsForImport($curRow, $fldsArr){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        foreach ($fldsArr as $dbFld => $fldArr) {
            $excelFld    = $fldArr['excelFld'];
            $specialType = $fldArr['specialType'];

            /*** if country name is passed then store the actual country code ***/
            if ($specialType == 'geo_country'){
                $country = $this->getExcelFieldValue($excelFld, $curRow);
                $fa[$dbFld] = getCPModelObj('common_geoCountry')->getCodeByName($country);

            /*** if country name is passed then store the actual country code ***/
            } else if ($specialType == 'valuelist'){
                $value = $this->getExcelFieldValue($excelFld, $curRow);
                $fa = $this->addToFieldsArray($curRow, $fa, $excelFld, $dbFld, $fldArr['defaultValue']);

                $keyText = $fn->getIssetParam($fldArr['exp'], 'keyText');

                $exp = array(
                     'extraFldsInSqlCondnArr' => array('key_text' => $keyText)
                    ,'extraFldsOnCreationArr' => array('key_text' => $keyText)
                );

                /*** create the valuelist record if does not exist for the current key text **/
                $fn->getRecordIdByTitleWithAutoCreate('core_valuelist', $value, $exp);

            /*** if section title is passed then get the id - create the section record if not exists ***/
            } else if ($specialType == 'section'){
                $section = $this->getExcelFieldValue($excelFld, $curRow);
                if ($section != ''){
                    $sectionType = $fldArr['exp']['sectionType'];
                    $fa[$dbFld] = getCPModelObj('webBasic_section')
                                  ->getSecIdByTitleWithAutoCreate($section, $sectionType);
                }

            /*** if category title is passed then get the id - create the cat record if not exists ***/
            } else if ($specialType == 'category'){
                $category = $this->getExcelFieldValue($excelFld, $curRow);
                if ($category != ''){
                    $sectionType = $fldArr['exp']['sectionType'];
                    $fa[$dbFld] = getCPModelObj('webBasic_category')
                                  ->getCatIdByTitleWithAutoCreate($category, $sectionType);
                }

            /*** if sub category title is passed then get the id - create the sub cat record if not exists ***/
            } else if ($specialType == 'subCategory'){
                $subCategory = $this->getExcelFieldValue($excelFld, $curRow);
                if ($subCategory != ''){
                    $categoryFldKeyInArr = $fldArr['exp']['categoryFldKeyInArr'];

                    $sectionType = $fldsArr[$categoryFldKeyInArr]['exp']['sectionType'];
                    $category_id = $fa[$categoryFldKeyInArr];
                    $shortDescFldKeyInArr = $fn->getIssetParam($fldArr['exp'], 'shortDescFldKeyInArr', '');
                    $short_desc = ($shortDescFldKeyInArr != '') ? $fa[$shortDescFldKeyInArr] : '';
                    $fa[$dbFld] = getCPModelObj('webBasic_subCategory')
                                  ->getSubCatIdByTitleWithAutoCreate($subCategory, $category_id, $short_desc);
                }

            } else if ($specialType == 'fetchIdFromRefModule'){
                $refModule = $fldArr['exp']['refModule'];
                $refModuleRecAutoCreate = $fn->getIssetParam($fldArr['exp'], 'refModuleRecAutoCreate', true);
                if ($refModule != ''){
                    $title = $this->getExcelFieldValue($excelFld, $curRow);
                    $extraFldsOnCreation = $fn->getIssetParam($fldArr['exp'], 'extraFldsOnCreation', array());
                    $extraLangFldsOnCreation = $fn->getIssetParam($fldArr['exp'], 'extraLangFldsOnCreation', array());
                    $extraFldsInSqlCondn = $fn->getIssetParam($fldArr['exp'], 'extraFldsInSqlCondn', array());
                    $titleField = $fn->getIssetParam($fldArr['exp'], 'titleField');

                    $extraFldsInSqlCondnArr = array();
                    foreach($extraFldsInSqlCondn as $extraFld) {
                        $extraFldExcel = $fldsArr[$extraFld]['excelFld'];
                        $extraFldVal   = $fn->getIssetParam($fa, $extraFld) ;
                        if($extraFldVal == ''){
                            $extraFldVal   = $this->getExcelFieldValue($extraFldExcel, $curRow);
                        }
                        $extraFldsInSqlCondnArr[$extraFld] = $extraFldVal;
                    }

                    $extraFldsOnCreationArr = array();
                    foreach($extraFldsOnCreation as $extraFld) {
                        //ex: $extraFld = Title and the returned excel fld (db fld) = title
                        $extraFldExcel = $fldsArr[$extraFld]['excelFld'];
                        $extraFldVal   = $fn->getIssetParam($fa, $extraFld);
                        if($extraFldVal == ''){
                            $extraFldVal = $this->getExcelFieldValue($extraFldExcel, $curRow);
                        }
                        $extraFldsOnCreationArr[$extraFld] = $extraFldVal;
                    }

                    foreach($extraLangFldsOnCreation as $extraFld => $excelFld) {
                        $extraFldVal   = $this->getExcelFieldValue($excelFld, $curRow);
                        $extraFldsOnCreationArr[$extraFld] = $extraFldVal;
                    }

                    $exp = array(
                         'extraFldsInSqlCondnArr' => $extraFldsInSqlCondnArr
                        ,'extraFldsOnCreationArr' => $extraFldsOnCreationArr
                    );

                    if($titleField != ''){
                        $exp['titleField'] = $titleField;
                    }

                    if ($refModuleRecAutoCreate) {
                        $fa[$dbFld] = $fn->getRecordIdByTitleWithAutoCreate($refModule, $title, $exp);
                    } else {
                        $fa[$dbFld] = $fn->getRecordIdByTitle($refModule, $title, $exp);
                    }
                }


            } else {

                $fa = $this->addToFieldsArray($curRow, $fa, $excelFld, $dbFld, $fldArr['defaultValue']);

                if ($fldArr['refOnly']){
                    $this->excludeFldsArr[] = $dbFld;
                }
            }
        }

        return $fa;
    }

    function addLog($message) {
        $message = "<h3>Row {$this->curRow}: {$message}</h3>";
        $this->importLogText .= $message;
    }

    function getLog($titleMessage = '') {
        $logMessage = $this->importLogText;
        $message = '';
        if ($logMessage) {
        $message = "
        {$titleMessage}
        {$logMessage}
        ";

        }
        return $this->importLogText;
    }
}
