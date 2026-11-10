<?
class CP_Admin_Modules_Core_Valuelist_View extends CP_Common_Modules_Core_Valuelist_View
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $fnMod       = includeCPClass('ModuleFns', 'core_valuelist');
        $vlArray     = $cpCfg['m.core.valuelist.recordTypeArr'];
        $lnArray     = $cpCfg['cp.availableLanguages'];
        $vlArrayKeys = array_keys($vlArray);

        $rowCounter = 0;
        $rows         = "";
        $title        = "";
        $keyText      = "";
        $code         = "";
        $groupHeading = "";
        $textValue    = "";
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $title = '';
            foreach ($lnArray as $key => $value) {
                $fieldValue = $ln->gfv($row, "value", 0, $key);
                $fieldValue = substr( strip_tags($fieldValue) , 0, 100) . "";

                if($key == "eng") {
                    $title .= $listObj->getGoToDetailText($rowCounter, $fieldValue);
                } else {
                    $title .= $listObj->getListDataCell($fieldValue);
                }
            }

            if($vlArrayKeys[0] === 0) {
                $keyText = $listObj->getListDataCell($row['key_text']);
            } else {
                $vlName = isset($vlArray[$row['key_text']]) ? $vlArray[$row['key_text']] : "";
                $keyText = $listObj->getListDataCell($vlName);
            }

            if($cpCfg['m.core.valuelist.hasCode'] == 1) {
                $code = $listObj->getListDataCell($row['code']);
            }

            if($cpCfg['m.core.valuelist.hasGroupHead'] == 1) {
                $groupHeading = $listObj->getListDataCell($fn->getYesNo($row['is_group_heading']));
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$title}
            {$keyText}
            {$code}
            {$groupHeading}
            {$listObj->getListDataCell($row['valuelist_id'], 'center')}
            {$listObj->getListSortOrderField($row, 'valuelist_id')}
            {$listObj->getListRowEnd($row['valuelist_id'])}
            ";
            $rowCounter++ ;
        }

        foreach ($lnArray as $key => $value) {
            $fieldName = $ln->getFieldPrefix($key) . 'value';
            $textValue .= $listObj->getListHeaderCell("Text ({$value})", "v.{$fieldName}");
        }

        if($cpCfg['m.core.valuelist.hasCode'] == 1) {
            $code = $listObj->getListHeaderCell('Code' , 'v.code');
        }

        if($cpCfg['m.core.valuelist.hasGroupHead'] == 1) {
            $groupHeading = $listObj->getListHeaderCell('Group Head?', 'v.is_group_heading');
        }

        $text = "
        {$listObj->getListHeader()}
        {$textValue}
        {$listObj->getListHeaderCell($ln->gd('m.core.valuelist.lbl.valuelistName', 'Value List Name') , 'v.key_text')}
        {$code}
        {$groupHeading}
        {$listObj->getListHeaderCell($ln->gd('m.core.valuelist.lbl.id', 'ID'), 'v.valuelist_id', 'headerCenter')}
        {$listObj->getListSortOrderImage('v')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $key_text = $fn->getReqParam('key_text');
        $exp = array('useKey' => 1);

        $fnMod  = includeCPClass('ModuleFns', 'core_valuelist');

        $vlArray  = $cpCfg['m.core.valuelist.recordTypeArr'];
        asort($vlArray);

        $fieldset = "
        {$formObj->getDDRowByArr($ln->gd('m.core.valuelist.lbl.valuelistName', 'Value List Name'), 'key_text', $vlArray, $key_text, $exp)}
        {$formObj->getTBRow($ln->gd('m.core.valuelist.lbl.value', 'Value'), 'value')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $formObj->mode = $tv['action'];

        $description  = '';
        $code         = '';
        $groupHeading = '';

        if ($cpCfg['m.core.valuelist.hasDescription'] == 1) {
            $description = $formObj->getTARow($ln->gd('m.core.valuelist.lbl.description', 'Description') , 'description', $row['description']);
        }

        if ($cpCfg['m.core.valuelist.hasCode'] == 1) {
            $code = $formObj->getTBRow("Code" , "code" , $row['code']);
        }

        if ($cpCfg['m.core.valuelist.hasGroupHead'] == 1) {
            $expGroupHeading = array('useKey' => true);
            $yesNoArr = array(1 => 'Yes', 0 => 'No');
            $groupHeading = $formObj->getRRow('Group Head?', 'is_group_heading',
                                              $row['is_group_heading'], $yesNoArr, $expGroupHeading);
        }

        $fnMod = includeCPClass('ModuleFns', 'core_valuelist');
        $exp = array('useKey' => 1);

        $fnMod  = includeCPClass('ModuleFns', 'core_valuelist');
        $vlArray  = $cpCfg['m.core.valuelist.recordTypeArr'];
        asort($vlArray);

        $fielset1  = "
        {$formObj->getDDRowByArr($ln->gd('m.core.valuelist.lbl.valuelistName', 'Value List Name'), 'key_text', $vlArray, $row['key_text'], $exp)}
        {$formObj->getTARow($ln->gd('m.core.valuelist.lbl.value', 'Value'), 'value', $ln->gfv($row, 'value', '0'))}
        {$description}
        {$code}
        {$groupHeading}
		";


        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.core.valuelist.lbl.valuelistDetails', 'Valuelist Details'), $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
    }

    /**
     *
     */
    function getImportData(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $mediaArray = Zend_Registry::get('mediaArray');

        set_time_limit(50000);
        require_once("{$cpCfg['commonIncludePath']}PHPExcel.php");
        require_once("{$cpCfg['commonIncludePath']}PHPExcel/IOFactory.php");

        $key_text  = isset($_REQUEST['key_text']) ? $_REQUEST['key_text'] : "";

        foreach ($_FILES as $key => $value) {
            if($value['name'] == "") {
                print "Error: Please choose a file to import <a href=\"javascript:history.back();\">Back</a>";
                return;
            }

            //======================================================================//
            $contentType = $value['type'];
            $sourceFile  = $value['tmp_name'];
            $mediaSize   = $value['size'];
            $file_name   = $value['name'];

            if($contentType != "application/vnd.ms-excel" && $contentType != "application/x-msdownload" && $contentType != "application/download") {
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

                $value  = $cpUtil->getExcelFieldValue2("value"         , $curRow);
                if($value == "") {
                    continue;
                }

                $SQL = "
                SELECT *
                FROM valuelist
                WHERE key_text = '{$dbUtil->replaceForDB($key_text)}'
                  AND value    = '{$value}'
                ";
                $result      = $db->sql_query($SQL);
                $numRows = $db->sql_numrows($result);

                $fa = array();
                $fa['key_text'] = $key_text;
                $fa['value']    = $cpUtil->getExcelFieldValue2("value"   , $curRow);
                $fa['modification_date']  = date("Y-m-d H:i:s");

                $this->setImportFields($curRow);

                if($numRows > 0) {
                    $row = $db->sql_fetchrow($result);

                    $whereCondition = "WHERE contact_id = {$row['contact_id']}";
                    $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "contact", $whereCondition);
                    $result         = $db->sql_query($SQL);
                    $contact_id      = $row['contact_id'];

                } else {
                    $fa['creation_date']  = date("Y-m-d H:i:s");
                    $SQL         = $dbUtil->getInsertSQLStringFromArray($fa, "contact");
                    $result      = $db->sql_query($SQL);
                    $contact_id  = $db->sql_nextid();
                }
            }
        }

        $text = "
        <script>
            window.opener.UtilDocument.refreshPage();
        </script>
        <h3>Import Complete. Please close this window.</h3>
        ";

        return $text;
    }

    /**
     *
     */
    function getShowValuesInModal() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $key_text = $fn->getReqParam('key_text');

        $SQL = "
        SELECT value
        FROM valuelist
        WHERE key_text = '{$key_text}'
        ORDER BY value
                ,sort_order
        ";
        $result  = $db->sql_query($SQL);

		$rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
			<tr class='even'>
                <td class='value'>{$row['value']}</td>
                <td><input type='button' value='Set' class='set_{$key_text} w50'/></td>
			</tr>
			";
        }


        $text = "
        <div class='valuelist-selection'>
            <table class='list'>
                {$rows}
            </table>
        </div>
		";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $key_text = $fn->getReqParam('key_text');

        $fnMod  = includeCPClass('ModuleFns', 'core_valuelist');
        $vlArray  = $cpCfg['m.core.valuelist.recordTypeArr'];

        asort($vlArray);
        $vlArrayKeys = array_keys($vlArray);

        $text = "
        <td>
            <select class='combo150' name='key_text' >
                <option value=''>{$ln->gd('m.core.valuelist.lbl.valuelist', 'Valuelist')}</option>
                {$cpUtil->getDropDown1($vlArray, $key_text, 1)}
            </select>
        </td>
        ";

        return $text;
    }
}