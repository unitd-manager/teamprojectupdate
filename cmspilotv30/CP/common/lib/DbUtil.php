<?
/**
 *
 */
class CP_Common_Lib_DbUtil
{
    /**
     *
     * @var <type>
     */
    var $db;

    /**
     *
     * @var <type>
     */
    var $replaceForDbValue;

    /**
     *
     */
    function __construct() {
        $numArgs = func_num_args();
        $argList = func_get_args();
        if ($numArgs == 1) {//*** if $db is passed as param
            $this->db = $argList[0];
        }
        $replaceForDbValue = 0;
    }

    /**
     *
     */
    function getDropDownFromSQLCols1($dbInstance, $SQL, $selectedValue = "") {

        if ($SQL == ''){
            return;
        }

        $resultText = "";
        $result  = $dbInstance->sql_query($SQL);

        while ($row = $dbInstance->sql_fetchrow($result)) {
            if ($row[0] == $selectedValue && $selectedValue != "") {
               $resultText .= "<option selected=\"selected\" value=\"" . $row[0] . "\">" . $row[0] . "</option>\n";
            }
            else {
               $resultText .= "<option value=\"" . $row[0] . "\">" . $row[0] . "</option>\n";
            }
        }
        return $resultText;

    }

    /**
     *
     */
    function getDropDownFromSQLCols2($dbInstance, $SQL, $selectedValue = "") {
        $resultText = "";
        if ($SQL == ""){
           return;
        }
        $result  = $dbInstance->sql_query($SQL);

        while ($row = $dbInstance->sql_fetchrow($result)) {

            if ($row[0] == $selectedValue && $selectedValue != "") {
               $resultText .= "<option selected=\"selected\" value=\"" . $row[0] . "\">" . $row[1] . "</option>\n";
            }
            else {
               $resultText .= "<option value=\"" . $row[0] . "\">" . $row[1] . "</option>\n";
            }
        }

        return $resultText;
    }

    /**
     *
     */
    function getDropDownWithSeperator($dbInstance, $SQL, $selectedValue = "") {
        $resultText = "";
        $result  = $dbInstance->sql_query($SQL);
        $numRows = $dbInstance->sql_numrows($result);
        $rowCounter = 0;

        $seperatorValueTemp = "";

        while ($row = $dbInstance->sql_fetchrow($result)) {
            $seperatorValue  = $row[2];

            if ($seperatorValue  != $seperatorValueTemp){

                if ($rowCounter != 0){
                   // $resultText .= "</optgroup>\n";
                }
                $resultText .= "\n<optgroup label=\"{$row[2]}\"></optgroup>\n";
                $seperatorValueTemp = $seperatorValue;
            }

            if ($row[0] == $selectedValue && $selectedValue != "") {
                $resultText .= "<option selected=\"selected\" value=\"" . $row[0] . "\">" . $row[1] . "</option>\n";
            } else {
                $resultText .= "<option value=\"" . $row[0] . "\">" . $row[1] . "</option>\n";
            }

            $rowCounter++;

            if ($rowCounter == $numRows){
                $resultText .= "</optgroup>\n";
            }
        }

        return $resultText;
    }



    /**
     *
     */
    function getCheckBoxFromSQLCols($dbInstance, $SQL, $fieldName, $selectedValuesArr = array(), $extraParam = array()) {
        $text    = "";
        $counter = 1;

        $selectedValuesArr = ($selectedValuesArr == "") ? array(): $selectedValuesArr;

        $exp = &$extraParam;

        $rowCls            = isset($exp['rowCls'])             ? " {$exp['rowCls']}"                : "";
        $fieldLabelCls     = isset($exp['fieldLabelCls'])      ? " class='{$exp['fieldLabelCls']}'" : "";
        $fieldCls          = isset($exp['fieldCls'])           ? " class='{$exp['fieldCls']}'"      : "";
        $isEditable        = isset($exp['isEditable'])         ? $exp['isEditable']                 : 1;
        $disabled          = isset($exp['disabled'])           ? $exp['disabled']                   : false;
        $sqlType           = isset($extraParam['sqlType'])     ? $extraParam['sqlType']             : "TwoFields";
        $jsFunction        = isset($extraParam['jsFunction'])  ? $extraParam['jsFunction']       : "";
        $singleValue       = isset($exp['singleValue'])        ? $exp['singleValue']                : 0;

        $jsText            = ($jsFunction != "") ? " onchange='{$jsFunction}'"  : "";
        $disabled          = ($disabled == true) ? " disabled='disabled'"     : "";


        $result  = $dbInstance->sql_query($SQL);

        $fldNameTmp = (substr($fieldName, -2) == '[]') ? substr($fieldName, 0, strlen($fieldName)-2) : $fieldName;

        while ($row = $dbInstance->sql_fetchrow($result)) {

            $checked = (in_array($row['0'], $selectedValuesArr ) == true) ? " checked=\"checked\"" : "";
            $key     = $row['0'];
            $value   = ($sqlType == "TwoFields") ? $row['1']: $row['0'];

            $id = "{$fldNameTmp}_{$counter}";
            $text .= "
            <div class='type-check'>
                <input type=\"checkbox\" name=\"{$fieldName}\" value=\"{$key}\" id=\"{$id}\" {$checked}{$disabled}{$jsText} />
                <label for=\"{$id}\">{$value}</label>
            </div>
            ";

            $counter++;
        }
        return $text;
    }

    /**
     *
     */
    function getCheckBoxFromArray($valuesArr, $fieldNamesArr, $selectedValuesArr = array(), $seperator = "", $jsFunction = "") {

        $resultText = "";

        $jsText = $jsFunction != "" ? "onClick=\"{$jsFunction}\"" : "";

        foreach ($valuesArr as $index => $value) {
            if (is_array($fieldNamesArr)) {
                $fieldName = $fieldNamesArr[$index];
            } else {
                $fieldName = $fieldNamesArr;
            }

            if (in_array($value, $selectedValuesArr ) == true) {
                $resultText .= "<input name=\"{$fieldName}\" type=\"checkbox\" {$jsText} checked value=\"" . $value . "\">" . $value;
            } else {
                $resultText .= "<input name=\"{$fieldName}\" type=\"checkbox\" {$jsText}  value=\"" . $value . "\">" . $value;
            }

            if ($seperator != ""){
                $resultText .= $seperator . "\n";
            }

        }

        return $resultText;
    }

    /**
     *
     * @param <type> $value
     * @return <type>
     */
    function replaceForDB($value){
        $replacedValue = $value;

        //*** if the value is a number then no need to escape it
        if (!is_numeric($value) && trim($value) != "") {
            $replacedValue = $this->db->db_connect_id->real_escape_string($value);
        }
        return $replacedValue;
    }


    /**
     * returns the field value as quoted / unquoted according to its type
     * @param <type> $value
     * @param <type> $fieldType
     * @return <type>
     */
    function getFieldValueQuoted($value, $fieldType) {

        if ($this->replaceForDbValue == 1) {
           $value = $this->replaceForDB($value);
        }

        $returnValue = "";
        if (substr($fieldType, 0, 3) == "int") {
            //print $fieldType . ":" . $value . "\n";
            if (is_numeric($value) || $value === 0) {
               //print $fieldType . ":" . $value . "\n\n";
               $returnValue = $value;
            } else {
               $returnValue = "NULL";
            }

        } else if (substr($fieldType, 0, 7) == "varchar") {
           $returnValue = "'" . $value . "'";

        } else if (substr($fieldType, 0, 4) == "date" || substr($fieldType, 0, 8) == "datetime" ) {
            if ($value == "") {
                $returnValue = "NULL";
            } else if ($value == "NOW()") {
                $returnValue = "NOW()";
            } else {
                $returnValue = "'" . $value . "'";
            }
        } else if (substr($fieldType, 0, 5) == "float" ) {
            if ($value !== '' && (is_float($value) || is_numeric($value) || $value === 0) ) {
                $returnValue = "'" . $value . "'";
            } else {
                $returnValue = "NULL";
            }
        } else {
            $returnValue = "'" . $value . "'";
        }

        return $returnValue;
    }

    /**
     */
    function recordExists($tableName, $idFieldName, $idFieldValue) {
        $db = $this->db;
        $SQL = "SELECT count(*) AS count FROM `{$tableName}`
               WHERE {$idFieldName} = {$idFieldValue}";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);


        if ($row['count'] > 0) {
            return true;
        } else {
           return false;
        }
    } //*** end function

    /**
     *
     */
    function getInsertSQLStringFromArray($arr1, $tableName, $excludeFldsArr = array()) {
        $fieldTypeArr = $this->getTableColumns($tableName, "type");
        $fieldsList = "";
        $valuesList = "";
        foreach ($arr1 as $fieldName => $fieldValue) {
            if (in_array($fieldName, $excludeFldsArr)){
                continue;
            }
            $fieldType   = $fieldTypeArr[$fieldName];
            $fieldsList .= "`" . $fieldName . "`, ";
            $valuesList .= $this->getFieldValueQuoted($fieldValue, $fieldType) . ", ";
        }
        $fieldsList = substr($fieldsList, 0, -2);
        $valuesList = substr($valuesList, 0, -2);

        $tableNameArr = explode(".", $tableName);

        if (count($tableNameArr) == 1){
            $insertSQL = "INSERT INTO `{$tableName}` ({$fieldsList}) VALUES ({$valuesList})";
        } else {
            $insertSQL = "INSERT INTO {$tableNameArr[0]}.`{$tableNameArr[1]}` ({$fieldsList}) " .
                         "VALUES ({$valuesList})";
        }

        return $insertSQL;
    }


    /**
     *
     */
    function getUpdateSQLStringFromArray($arr1, $tableName, $whereCondition, $excludeFldsArr = array()) {
        $fieldTypeArr = $this->getTableColumns($tableName, "type");
        $updateSQL = "UPDATE `{$tableName}` SET ";
        foreach ($arr1 as $fieldName => $fieldValue) {
            if (in_array($fieldName, $excludeFldsArr)){
                continue;
            }
            $fieldType  = $fieldTypeArr[$fieldName];
            $updateSQL .= "`" . $fieldName . "` = " . $this->getFieldValueQuoted($fieldValue, $fieldType) . ", ";
        }

        $updateSQL = substr($updateSQL, 0, -2);
        $updateSQL .= " " . $whereCondition;

        return $updateSQL;
    }

    /**
     *
     */
    function getTableColumns($tableName, $option) {
        $db = $this->db;

        $tableNameArr = explode(".", $tableName);

        if (count($tableNameArr) == 1){
            $SQL = "SHOW COLUMNS FROM `{$tableName}`";
        } else {
            $SQL = "SHOW COLUMNS FROM {$tableNameArr[0]}.`{$tableNameArr[1]}`";
        }

        $result  = $db->sql_query($SQL);
        $returnArr = array();
        while ($row = $db->sql_fetchrow($result)) {
            if ($option == "all") {
               $returnArr[] = $row;
            } else if ($option == "field") {
               $returnArr[$row['Field']] = $row['Field'];
            } else if ($option == "type") {
               $returnArr[$row['Field']] = $row['Type'];
            } else if ($option == "null") {
               $returnArr[$row['Field']] = $row['Null'];
            } else if ($option == "key") {
               $returnArr[$row['Field']] = $row['Key'];
            } else if ($option == "default") {
               $returnArr[$row['Field']] = $row['Default'];
            } else if ($option == "extra") {
               $returnArr[$row['Field']] = $row['Extra'];
            }
        }
        return $returnArr;

    }

    /**
     * Inserts one record from any table to another record in any other table
     * @global <type> $utilCommon
     * @param <type> $exp
     * @return <type>
     */
    function copyRecordByInsert($exp) {
        $db                 = $this->db;
        $sourceTableName    = $exp['sourceTableName'];
        $destTableName      = $exp['destTableName'];
        $sourceTableIdName  = $exp['sourceTableIdName'];
        $sourceTableIdValue = $exp['sourceTableIdValue'];

        //omits fields from being added to destination table from source table
        $omitFieldsArr = isset($exp['omitFieldsArr']) ? $exp['omitFieldsArr'] : array();

        //preset values for dest record fields instead from source row
        $fieldValuesArr = isset($exp['fieldValuesArr']) ? $exp['fieldValuesArr'] : array();

        //*** get fields source array
        $fieldsArrSource = array();

        $sourceTableNameArr = explode(".", $sourceTableName);

        if (count($sourceTableNameArr) == 1){ //*** does not have a dot
            $SQL = "SHOW COLUMNS FROM `{$sourceTableName}`";
        } else {
            $SQL = "SHOW COLUMNS FROM {$sourceTableNameArr[0]}.`{$sourceTableNameArr}`";
        }

        $result  = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $fieldsArrSource[] = $row;
            $fieldsArrSourceNames[] = $row['Field'];
        }

        //*** get fields destination array
        $fieldsArrDest = array();
        $SQL = "SHOW COLUMNS FROM `{$destTableName}`";
        $result  = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $fieldsArrDest[]      = $row;
            $fieldsArrDestNames[] = $row['Field'];
        }

        //*** get source record
        $SQL = "SELECT * FROM `{$sourceTableName}`
               WHERE {$sourceTableIdName} = {$sourceTableIdValue}";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        //*** create destination record
        $fieldsList = "";
        $valuesList = "";
        foreach ($fieldsArrSource as $key => $tempFieldArr) {
            $fieldName = $tempFieldArr['Field'];
            $fieldType = $tempFieldArr['Type'];
            //*** if not in $omitFieldsArr then add the field to field list
            if (in_array($fieldName, $omitFieldsArr) == false &&
                in_array($fieldName, $fieldsArrDestNames) == true) {
                //*** create fields list for INSERT
                $fieldsList .= $fieldName . ",";

                $fieldValue = "";
                //*** if value for this field is preset from $fieldValuesArr
                if (array_key_exists($fieldName, $fieldValuesArr)) {
                   $fieldValue = $fieldValuesArr[$fieldName];
                }
                else {
                   $fieldValue = $row[$fieldName];
                }

                $valuesList .= $this->getFieldValueQuoted($fieldValue, $fieldType) . ",";
            } //*** if not in $omitFieldsArr
        } //*** end for $fieldsArrSource

        //*** if fieldNames in $fieldValuesArr does not exist in $fieldsArrSource then just add it
        foreach ($fieldValuesArr as $fieldName => $fieldValue) {
            if (in_array($fieldName, $fieldsArrSourceNames) == false) {
                $fieldsList .= $fieldName . ",";
                $valuesList .= $fieldValue . ",";
            }
        }

        $fieldsList = substr($fieldsList, 0, -1); //*** remove the final comma
        $valuesList = substr($valuesList, 0, -1); //*** remove the final comma
        $SQL = "INSERT INTO `{$destTableName}` ({$fieldsList})
                VALUES ({$valuesList})";

        //print $SQL . "<hr>";
        $result = $db->sql_query($SQL);
        return $db->sql_nextid();
    }


    /**
     *
     */
    function getStringAsCommaSeperated($str, $seperator) {

        if(substr($str, 0, 1) == "#") { // if the first char is # then remove it
            $str = substr($str, 1);
        }

        $str = str_replace($seperator, ",", $str);
        $str = trim($str);

        if(substr($str, -1, 1) == ",") { // if the last char is "," then remove it
            $str = substr($str, 0, strlen($str) - 1);
        }

        if(substr($str, -1, 1) == ",") { // if the last char is "," then remove it
            $str = substr($str, 0, strlen($str) - 1);
        }

        return $str;
    }


    /**
     *
     */
    function getArrayAsCommaSeperated($arr, $useQuotes = false, $returnKey = false, $returnCharIfEmpty = '') {
        $text = "";
        foreach ($arr as $key => $value) {
           $arrVal = $returnKey ? $key : $value;
           $text .= $useQuotes ? "'{$arrVal}'," : $arrVal . ",";
        }

        $text = trim($text, ',');

        if ($text == '' && $returnCharIfEmpty != '') {
            $text = $returnCharIfEmpty;
        }

        return $text;
    }


    /**
     *
     * @param <type> $result
     * @return <type>
     */
    function getResultsetAsArrayForForm($resultOrSQL) {
        $db = Zend_Registry::get('db');

        $result = $resultOrSQL;
        if (is_string($resultOrSQL)) {
            $result = $db->sql_query($resultOrSQL);
        }
        $array1 = array();
        while ($row = $db->sql_fetchrow($result, MYSQL_NUM)) {
            if (isset($row[1])){
                $array1[$row[0]] = $row[1];
            }  else {
                $array1[] = $row[0];
            }
        }
        return $array1;
    }

    /**
     *
     * @param <type> $result
     * @return <type>
     */
    function getResultsetAsArray($resultOrSQL, $fetchType = MYSQL_ASSOC) {
        $db = Zend_Registry::get('db');

        $result = $resultOrSQL;
        if (is_string($resultOrSQL)) {
            $result = $db->sql_query($resultOrSQL);
        }

        $dataArray = array();
        while ($row = $db->sql_fetchrow($result, $fetchType)) {
            $dataArray[] = $row;
        }

        return $dataArray;
    }

    /**
     *
     */
    function getSQLResultAsArray($SQL, $fetchType = MYSQL_ASSOC) {
        $db = Zend_Registry::get('db');
        $result = $db->sql_query($SQL);

        return $this->getResultsetAsArray($result, $fetchType);
    }

    function getArrayFromSQLForVL($SQL, $fetchType = MYSQL_NUM) {
        $db = Zend_Registry::get('db');

        $result = $db->sql_query($SQL);

        $dataArray = array();
        while ($row = $db->sql_fetchrow($result, $fetchType)) {
            if (isset($row[1])) {
                $dataArray[$row[0]] = $row[1];
            } else {
                $dataArray[$row[0]] = $row[0];
            }
        }
        return $dataArray;
    }

    /**
     *
     */
    function setReplaceForDbValue($value) {
        $this->replaceForDbValue = $value;
    }

    /**
     *
     * @param type $tableName
     * @param type $column
     * @return boolean
     */
    function getColumnExists($tableName, $column) {
        $db = $this->db;

        $tableNameArr = explode('.', $tableName);

        if (count($tableNameArr) == 1){
            $SQL = "SHOW COLUMNS FROM `{$tableName}` LIKE '{$column}'";
        } else {
            $SQL = "SHOW COLUMNS FROM {$tableNameArr[0]}.`{$tableNameArr[1]}` LIKE '{$column}'";
        }

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0){
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param type $tableName
     * @return boolean
     */
    function isTableExists($tableName) {
        $db = $this->db;

        $tableNameArr = explode('.', $tableName);

        if (count($tableNameArr) == 1){
            $SQL = "SHOW TABLES LIKE '{$tableName}'";
        } else {
            $SQL = "SHOW TABLES LIKE {$tableNameArr[0]}.`{$tableNameArr[1]}`";
        }

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0){
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param type $tableName
     * @param type $column
     * @return type
     */
    function isColumnExists($tableName, $column) {
        return $this->getColumnExists($tableName, $column);
    }

    /**
     *
     * @param <type> $SQL
     * @param <type> $selectedValue
     * @return <type>
     */
    function getValuelistDropDown($SQL, $selectedValue = "") {
        $db = Zend_Registry::get('db');

        $text = "";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $rowCounter = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $isGroupHead = isset($row['is_group_heading']) ? $row['is_group_heading'] : 0;

            if ($isGroupHead  == 1){
                $text .= "\n<optgroup Label=\"{$row[1]}\"></optgroup>\n";
            } else {

               if ($row[0] == $selectedValue && $selectedValue != '') {
                    $text .= "<option selected=\"selected\" value=\"" . $row[0] . "\">" . $row[1] . "</option>\n";
               } else {
                  $text .= "<option value=\"" . $row[0] . "\">" . $row[1] . "</option>\n";
               }

               $rowCounter++;
            }

        }

        return $text;
    }

    /**
     *
     */
    function getSQL($SQL, $condnArr = array(), $exp = array()) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $orderBy = $fn->getIssetParam($exp, 'orderBy');
        $groupBy = $fn->getIssetParam($exp, 'groupBy');

        if ($cpCfg['cp.hasMultiUniqueSites']){
            $module = $fn->getIssetParam($exp, 'module');
            $tblPrefix = $fn->getIssetParam($exp, 'tblPrefix');
            $tblPrefix = ($tblPrefix != '') ? ".{$tblPrefix}" : '';

            if ($module == '' ||
               ($module != '' & in_array($module, $cpCfg['w.common_multiUniqueSite.ignoreModules']))
               ){
                if (CP_SCOPE == 'admin'){
                    $site_id = @$_SESSION['cp_site_id'];
                } else {
                    $site_id = $cpCfg['cp.site_id'];
                }

                if ($site_id != ''){
                    $condnArr[] = "{$tblPrefix}site_id = '{$site_id}'";
                }
            }
        }

        $sqlSearchVar = join("\nAND ", $condnArr);


        if ($sqlSearchVar != '') {
            $sqlSearchVar = " WHERE {$sqlSearchVar}";
        }

        if ($groupBy != '') {
            $sqlSearchVar .= "\n GROUP BY {$groupBy}";
        }

        if ($orderBy != '') {
            $sqlSearchVar .= "\n ORDER BY {$orderBy}";
        }

        $SQL = "
        {$SQL}
        {$sqlSearchVar}
        ";

        return $SQL;
    }
}