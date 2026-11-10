<?
class CP_Admin_Modules_Ecard_Contact_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $email = $row['email'];
            
            $opened = $row['email_opened'];
            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getGoToDetailText($rowCounter, $row['last_name'])}
            <td><div align='left'><a href='mailto:{$email}'>{$email}</a></div></td>
            {$listObj->getListDataCell($fn->getYesNo($opened), 'center')}
            {$listObj->getListDataCell($row['login_count'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['contact_id'])}
            {$listObj->getListRowEnd($row['contact_id'])}
            ";

            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('First Name', 'c.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'c.last_name')}
        {$listObj->getListHeaderCell('Email', 'c.email')}
        {$listObj->getListHeaderCell('Invitation Email Opened', 'email_opened', 'headerCenter')}
        {$listObj->getListHeaderCell('Login Count', 'c.login_count', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
    	{$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fielset = "
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    //========================================================//
    //==================================================================//
    //==================================================================//
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');

        $formObj->mode  = $tv['action'];

        $fieldset1 = "
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getYesNoRRow('Newsletter Subscribed', 'subscribe', $row['subscribe'])}
        ";
                
        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');

        $rows = "";

        $text = "
        ";

        return $text;
    }

    //==================================================================//
    //==================================================================//
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
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
    
    //==================================================================//
    function getImportData(){
        $lang = Zend_Registry::get('lang');
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
            
            if ($contentType != "application/vnd.ms-excel" && $contentType != "application/download"){
                print "Error: you can only choose xls file format <a href=\"javascript:history.back();\">Back</a>";
                return;
            }
            
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
                
                $fa['first_name']   = $this->getExcelFieldValue("first_name"  , $curRow);
                $fa['last_name']    = $this->getExcelFieldValue("last_name"   , $curRow);
                $fa['email']        = $this->getExcelFieldValue("email"       , $curRow);
                $fa['pass_word']    = $this->getExcelFieldValue("password"  , $curRow);
                $fa['published']    = 1;
                $fa['subscribe']    = 1;

                $email = $dbUtil->replaceForDB($fa['email']);
                
                $SQLContact = "
                SELECT c.* 
                FROM contact c
                WHERE c.email = '{$email}'
                ";
                $resultContact = $db->sql_query($SQLContact);
                $numRows       = $db->sql_numrows($resultContact);
                
                if($numRows == 0){
                    $SQL        = $dbUtil->getInsertSQLStringFromArray($fa, "contact");
                    $result     = $db->sql_query($SQL);
                    $contact_id = $db->sql_nextid();
                } else {
                    $rowContact = $db->sql_fetchrow($resultContact);
	   	            $whereCondition = "WHERE contact_id = {$rowContact['contact_id']}";
                    $SQL        = $dbUtil->getUpdateSQLStringFromArray($fa, "contact", $whereCondition);
                    $result     = $db->sql_query($SQL);
                    $contact_id = $rowContact['contact_id'];
                }
                $SQL = "
                UPDATE contact 
                SET random_no = md5({$contact_id})
                WHERE contact_id = {$contact_id}
                ";
                $result = $db->sql_query($SQL);
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
    function getExportData(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $result = Zend_Registry::get('result');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Contacts_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'First Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Last Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Email');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invitation Email Opened');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Login Count');
        
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
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $opened = $row['email_opened'];
            $opened = $fn->getYesNo($opened);
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['first_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['last_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['email']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $opened);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['login_count']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
   
    //=============================================================//

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $am = Zend_Registry::get('am');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $interest_id    = $fn->getReqParam('interest_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');
        $category       = $fn->getReqParam('category');
        $interestText   = "";
        
        if ($cpCfg['showInterestInContact'] == 1) {
            $sqlCombo = "
            SELECT interest_id
                  ,title 
            FROM interest 
            ORDER BY title
            ";

            $interestText = "
            <td>
                <select name='interest_id' >
                    <option value=''>Interest Group</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $interest_id)}
                </select>
            </td>
            ";
        }

        //==================================================================//
        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
        );

        $text = "
        {$interestText}
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";

        
        return $text;
    }
}