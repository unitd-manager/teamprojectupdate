<?
class CP_Admin_Lib_SpecialAction extends CP_Common_Lib_SpecialAction
{

    //==================================================================//
    function getFilterCombo() {
        $dbUtil = Zend_Registry::get('dbUtil');

        return $dbUtil->getFilterComboSectionCatSubCat();
    }

    //==================================================================//
    function getUpdateLangFiles() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        set_time_limit(5000);

        $langArray = $cpCfg['cp.availableLanguages'];

        foreach ($cpCfg['cp.availableLanguages'] as $key => $value) {
            $this->updateLangFilesActual($key);
        }

        // $updateFromTesSever = $fn->getReqParam('updateFromTesSever', 0);

        // if ($updateFromTesSever == 0) {
        //     $cpUtil->redirect("index.php?_topRm={$tv['topRm']}&module=core_translation");
        // }
    }

    //==================================================================//
    function updateLangFilesActual($lang) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $orderBy = 'key_text';
        if ($cpCfg['cp.hasMultiYears']) {
            $orderBy = 'cp_year, key_text';
        }
        $SQL = "
        SELECT * FROM translation
        ORDER BY {$orderBy}
        ";
        $result = $db->sql_query($SQL);

        $text = "<? \n\n\$LANGARR = array();\n\n";

        $textLang = $lang != 'eng' ?  $lang . '_value' : 'value' ;

        while ($row = $db->sql_fetchrow($result)) {

            $textVal = $row[$textLang] != '' ? $row[$textLang] : $row['value'];
            //$textVal = str_replace("'", "\'", $textVal);
            $textVal = str_replace("\"", "\\\"", $textVal);

            $key_text = $row['key_text'];
            if ($cpCfg['cp.hasMultiYears']) {
                $key_text = $row['cp_year'] . '-' . $row['key_text'];
            }
            $text .= "\$LANGARR['{$key_text}'] = \"{$textVal}\";\n" ;
        }

        //==================================================================//
        $text .= "\n/*** FROM VALUE LIST TABLE ***/\n";

        $SQL    = 'SELECT * FROM valuelist ORDER BY key_text';
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            //$textVal = str_replace("'", "\'", $row[$textLang]);
            $textVal = str_replace("\"", "\\\"", $textVal);

            $keyVal = str_replace("'", "\'", $row['value']);
            $text .= "\$LANGARR['{$keyVal}'] = \"{$textVal}\";\n" ;
        }

        //==================================================================//
        $filename = realpath("{$cpCfg['cp.langFilesPath']}lang-{$lang}.php");

        $filenamePath = "{$cpCfg['cp.langFilesPath']}lang-{$lang}.php";

        if (!file_exists($filenamePath)) {
            if ($fh = fopen($filenamePath, "w")) {
                fwrite($fh, $text);
                fclose($fh);
            }

        } else if (is_writable($filename)) {
            if (!$fh = fopen($filename, 'w')) {
                echo "Cannot open file ($filename)";
                exit;
            }

            if (fwrite($fh, $text) === FALSE) {
                echo "Cannot write to file ($filename)";
                exit;
            }

            fclose($fh);

        } else {
            die("The file {$filenamePath} is not writable");
            return;
        }
    }

    //==================================================================//
    function getUpdateAdminLangFiles() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        set_time_limit(5000);

        $langArray = $cpCfg['cp.availableLanguages'];

        foreach ($cpCfg['cp.adminInterfaceLangs'] as $langKey => $langText) {
            $this->updateAdminLangFilesActual($langKey);
        }
    }

    //==================================================================//
    function updateAdminLangFilesActual($lang) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $orderBy = 'key_text';
        $SQL = "
        SELECT * FROM admin_translation
        ORDER BY {$orderBy}
        ";
        $result = $db->sql_query($SQL);

        $text = "<? \n\n\$LANGARR = array();\n\n";

        $textLang = $lang != 'eng' ?  $lang . '_value' : 'value' ;

        while ($row = $db->sql_fetchrow($result)) {

            $textVal = $row[$textLang] != '' ? $row[$textLang] : $row['value'];
            $textVal = str_replace("'", "\'", $textVal);
            $textVal = str_replace("\"", "\\\"", $textVal);

            $key_text = $row['key_text'];
            $text .= "\$LANGARR['{$key_text}'] = \"{$textVal}\";\n" ;
        }

        //==================================================================//
        $filename = realpath("{$cpCfg['cp.adminLangFilesPath']}{$lang}.php");

        $filenamePath = "{$cpCfg['cp.adminLangFilesPath']}{$lang}.php";

        if (!file_exists($filenamePath)) {
            if ($fh = fopen($filenamePath, "w")) {
                fwrite($fh, $text);
                fclose($fh);
            }

        } else if (is_writable($filename)) {
            if (!$fh = fopen($filename, 'w')) {
                echo "Cannot open file ($filename)";
                exit;
            }

            if (fwrite($fh, $text) === FALSE) {
                echo "Cannot write to file ($filename)";
                exit;
            }

            fclose($fh);

        } else {
            die("The file {$filenamePath} is not writable");
            return;
        }
    }

    //========================================================//
    function getChangeLang() {
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');

        $text = "";

        $text .= "
        <html>
        <body>
        ";

        $ln        = $fn->getGetParam('lang');
        $returnUrl = $fn->getReqParam('returnUrl');

        if ($tv['dbSearhDone'] == 0) {
            $cpUtil->redirect($returnUrl);

        } else {
            $pagerCommon = new PagerCommon;

            $text .= $pagerCommon->getGoRecordText();
            $text .= "
            <script>
                document.forms['goRecord'].action = '{$returnUrl}';
                document.forms['goRecord'].submit();
            </script>\n
            ";
        }

        $text .= "
        </body>
        </html>
        ";

        return $text;
    }

    //========================================================//
    function getImportData() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $module = $fn->getReqParam('room_name');
        $importType = $fn->getReqParam('importType');
        $viewObj = getCPViewObj($module);

        $additionalFields = "";
        if (method_exists($viewObj, 'getAdditionalImportFields')){
            $additionalFields = $viewObj->getAdditionalImportFields();
        }

        $record_id = $fn->getReqParam('record_id');
        $recordIdText = "&record_id={$record_id}";


        $fieldset = "
        <div class='type-text editable'>
            <label for='fld_business_name'>Choose File</label>
            <input name='fileName' type='file' class='text ui-corner-all' />
        </div>
        {$additionalFields}
        <div class='type-button'>
            <input class='button' type='submit' value='Continue' name='fileUpload' />
        </div>
        ";

        /** if there are instructions available for the import display them **/
        $instructions = '';
        if (method_exists($viewObj, 'getImportInstructions')){
            $instructions = "
            <div class='importInstructions mt20'>
                {$viewObj->getImportInstructions()}
            </div>
            ";
        }

        $url = "index.php?_spAction=importData&module={$module}&importType={$importType}"
             . $recordIdText;
        $text = "
        <form class='yform columnar' method='post' action='{$url}'
              enctype='multipart/form-data' name='filename'>
            {$formObj->getFieldSetWrapped('Import Data', $fieldset)}
        </form>
        {$instructions}
        ";

        return $text;
    }
}