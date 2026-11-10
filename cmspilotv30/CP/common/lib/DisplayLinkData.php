<?
class CP_Common_Lib_DisplayLinkData
{

    var $linkObj;
    /**
     *
     */
    function getSQL($srcRoom, $lnkRoom, $srcRoomId) {

        $modObj = getCPModuleObj($srcRoom);

        $srcRmArr = explode('_', $srcRoom);
        $lnkRmArr = explode('_', $lnkRoom);

        //ex: getTradingCompanyTradingContactLinkSQL
        $funcName = "get" . ucfirst($srcRmArr[0]) . ucfirst($srcRmArr[1]) .
                    ucfirst($lnkRmArr[0]) . ucfirst($lnkRmArr[1]) . "SQL";

        //print $funcName;
        //die();

        if (method_exists($modObj->model, $funcName)) {
            $SQL = $modObj->model->$funcName($srcRoomId);
        } else {
            $SQL = $this->getDefaultSQL($srcRoom, $lnkRoom, $srcRoomId);
        }
        //print $SQL . '<hr>';
        //fb::log($SQL);
        return $SQL;
    }

    /**
     *
     */
    function getDefaultSQL($mainRoomName, $linkRoomName, $id) {
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $linksArray = Zend_Registry::get('linksArray');
        $formObj = Zend_Registry::get('formObj');

        $linkName              = $mainRoomName . "#" . $linkRoomName;
        $mainRoomKeyField      = $modulesArr[$mainRoomName]["keyField"];
        $linkRoomKeyField      = $modulesArr[$linkRoomName]["keyField"];
        $historyTableName      = $linksArray[$linkName]["historyTableName"];
        $historyTableKeyField  = $linksArray[$linkName]["historyTableKeyField"];
        $linkRoomTableName     = $modulesArr[$linkRoomName]["tableName"];
        $displayTitleFieldName = $linksArray[$linkName]["displayTitleFieldName"];
        $recordTypeForHistory  = $linksArray[$linkName]["recordTypeForHistory"];
        $hasListBoxSort        = $linksArray[$linkName]["hasListBoxSort"];
        $sortFieldName         = $hasListBoxSort == 1 ? "b.sort_order" : "title";
        $recordTypeQuery       = $recordTypeForHistory != "" ? " AND b.record_type = '{$recordTypeForHistory}'" : "";
        $additionalFieldsArray = $linksArray[$linkName]["additionalFieldsArray"];
        $moduleForHistory      = $linksArray[$linkName]["moduleForHistory"];
        $moduleNameQuery       = $moduleForHistory != "" ? " AND b.module = '{$moduleForHistory}'" : "";
        $additionalFieldsArrayDetail = $linksArray[$linkName]["additionalFieldsArrayDetail"];

        $mainRoomKeyFldNameInHistTbl = $linksArray[$linkName]["mainRoomKeyFldNameInHistTbl"];
        if ($mainRoomKeyFldNameInHistTbl != ""){
            $mainRoomKeyField  = $mainRoomKeyFldNameInHistTbl;
        }

        $linkingType = $linksArray[$linkName]["linkingType"];

        if ($formObj->mode == 'detail' && count($additionalFieldsArrayDetail) > 0){
            $additionalFields = ',' . join(',', $additionalFieldsArrayDetail);
        } else {
            $additionalFields = count($additionalFieldsArray) > 0 ? ',' . join(',', $additionalFieldsArray) : '';
        }

        $additionalFields = str_replace('[[id]]', $id, $additionalFields);

        if ($linkingType == 'grid'){
            $linkRoomTitleField  = $modulesArr[$linkRoomName]['titleField'];
            
            $sortGridRecordsBy = $linksArray[$linkName]["sortGridRecordsBy"];
            $sortGridRecordsBy = ($sortGridRecordsBy == '') ? "b.{$historyTableKeyField}" : $sortGridRecordsBy;
            
            if ($historyTableName == $linkRoomTableName){ //** if all the data is stroed in history table ex: order#order_item
                $titleFld = ($formObj->mode == 'edit') ? "b.{$linkRoomKeyField}" : "b.{$linkRoomTitleField} AS title";

                $SQL = "
                SELECT b.{$historyTableKeyField}
                      {$additionalFields}
                FROM `{$historyTableName}` b
                WHERE b.{$mainRoomKeyField} = {$id}
                  {$recordTypeQuery}
                  {$moduleNameQuery}
                ORDER BY {$sortGridRecordsBy}
                ";

            } else { //** if 3 tables invloved (ex: contact#interest)
                $titleFld = ($formObj->mode == 'edit') ? "a.{$linkRoomKeyField}" : "a.{$linkRoomTitleField} AS title";
                $SQL = "
                SELECT b.{$historyTableKeyField}
                      ,{$titleFld}
                      {$additionalFields}
                FROM `{$historyTableName}` b
                LEFT JOIN `{$linkRoomTableName}` a ON (a.{$linkRoomKeyField} = b.{$linkRoomKeyField})
                WHERE b.{$mainRoomKeyField} = {$id}
                  {$recordTypeQuery}
                  {$moduleNameQuery}
                ORDER BY {$sortGridRecordsBy}
                ";
            }

        } else {
            $linkRoomKeyFieldForEqCondn  = $linkRoomKeyField;

            $linkRoomKeyFldNameInHistTbl = $linksArray[$linkName]["linkRoomKeyFldNameInHistTbl"];
            if ($linkRoomKeyFldNameInHistTbl != ""){
                $linkRoomKeyFieldForEqCondn  = $linkRoomKeyFldNameInHistTbl;
            }

            $SQL = "
            SELECT a.{$linkRoomKeyField}
                  ,{$displayTitleFieldName} AS title
                  {$additionalFields}
            FROM `{$linkRoomTableName}` a
                 ,`{$historyTableName}` b
            WHERE a.{$linkRoomKeyField} = b.{$linkRoomKeyFieldForEqCondn}
              AND b.{$mainRoomKeyField} = {$id}
              {$recordTypeQuery}
              {$moduleNameQuery}
            ORDER BY {$sortFieldName}
            ";
        }

        //print "<pre class='sql'>" . $SQL . '</pre>';
        return $SQL;
    }

    function setLinkArray($srcRoom, $lnkRoom){
        $linksArray = Zend_Registry::get('linksArray');
        $arrayMasterLink = Zend_Registry::get('arrayMasterLink');
        $linkName   = $srcRoom . '#' . $lnkRoom;

        //--------------------------------------------------------------------//
        if (!isset($linksArray[$linkName])){
            $modObj = getCPModuleObj($srcRoom);
            $funcName = "setLinksArray";
            if (method_exists($modObj->fns, $funcName)) {
                $modObj->fns->$funcName($arrayMasterLink);
                Zend_Registry::set('linksArray', $arrayMasterLink->linksArray);
            }

            /** re-get the linksArray since it would have more values now **/
            $linksArray = Zend_Registry::get('linksArray');
        }

        /** if still the media type does not exist, throw an error **/
        if (!isset($linksArray[$linkName])){
            $cpUtil = Zend_Registry::get('cpUtil');
            print trace();
            exit("the link object is not properly set for {$linkName}. Please check {$srcRoom} -> Functions.php");
        }
    }

    /**
     *
     */
    function getLinkPortalMain($srcRoom, $lnkRoom, $displayTitle = '', $row = '', $exp = array(), $rightLink = '') {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $modulesArr = Zend_Registry::get('modulesArr');
        $linksArray = Zend_Registry::get('linksArray');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $this->setLinkArray($srcRoom, $lnkRoom);
        $imAChild = $fn->getIssetParam($exp, 'imAChild', false);

        if ($tv['action'] == 'search'){
            return;
        }

        $lra = &$linksArray;
        $text = '';
        $linkName              = $srcRoom . '#' . $lnkRoom;
        $linkName2             = $srcRoom . '__' . $lnkRoom;
        $hasPortalNew          = $lra[$linkName]['hasPortalNew'];
        $hasPortalEdit         = $lra[$linkName]['hasPortalEdit'];
        $hasPortalDelete       = $lra[$linkName]['hasPortalDelete'];
        $showLinkPanelInEdit   = $lra[$linkName]['showLinkPanelInEdit'];
        $showLinkPanelInDetail = $lra[$linkName]['showLinkPanelInDetail'];
        $fieldLabel            = $lra[$linkName]['fieldlabel'];
        $fieldClassArray       = $lra[$linkName]['fieldClassArray'];
        $addButtonText         = $fn->getIssetParam($exp, 'addButtonText', $lra[$linkName]['addButtonText']);

        $mainRoomKeyField      = $modulesArr[$srcRoom]['keyField'];
        $mainRoomTitle         = isset($modulesArr[$lnkRoom]) ? $modulesArr[$lnkRoom]['title'] : '';
        $linkRoomKeyField      = $lra[$linkName]['keyField'];
        $hasToggle             = $lra[$linkName]['hasToggle'];
        $openExpanded          = $lra[$linkName]['openExpanded'];
        $linkingType           = $lra[$linkName]['linkingType'];
        $linkHeaderHyperLink   = $lra[$linkName]['linkHeaderHyperLink'];
        $portalListLimit       = $lra[$linkName]['portalListLimit'];

        if ($showLinkPanelInDetail == 0 && $formObj->mode == 'detail'){
            return;
        }

        if ($showLinkPanelInEdit == 0 && $formObj->mode == 'edit'){
            return;
        }

        $pager = includeCPClass('Lib', 'Pager', 'Pager');

        $SQL = $this->getSQL($srcRoom, $lnkRoom, $row[$mainRoomKeyField]);
        $pager->setPagerData($pager->getTotalRecords($SQL), $portalListLimit, true, 1);
        $SQL = $pager->addLimitToSql($SQL, '', $portalListLimit);
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $pagerObjTitle = 'pager_' . $linkName2; //
        Zend_Registry::set($pagerObjTitle, $pager);

        $srcRmArr = explode('_', $srcRoom);
        $lnkRmArr = explode('_', $lnkRoom);

        //ex: getPmsCoursePmsBatchLinkPortalSearch
        $portalSearchFunction = "get" . ucfirst($srcRmArr[0]) . ucfirst($srcRmArr[1]) .
                    ucfirst($lnkRmArr[0]) . ucfirst($lnkRmArr[1]) . "PortalSearch";
        $textTemp = "";

        //----------------------------------------------------//
        //search form within link portal
        $portalSearchText = "";
        $fnsMod = includeCPClass('ModuleFns', $srcRoom);
        if ($portalSearchFunction != "" && method_exists($fnsMod, $portalSearchFunction)) {
            $portalSearchText = "
            <div class='portalSearch floatbox' linkName='{$linkName}'>
                <form>
                    {$fnsMod->$portalSearchFunction()}
                </form>
            </div>
            ";
        }

        //-----------------------------//
        //add action button
        $portalAddText = "";
        if ($linkingType == "portal" && $formObj->mode == 'edit') {
            $dialogWidth  = $lra[$linkName]['portalDialogWidth'];
            $dialogHeight = $lra[$linkName]['portalDialogHeight'];

            if ($hasPortalNew == 1) {
                $newPortalUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?_spAction=new&srcRoom={$srcRoom}&lnkRoom={$lnkRoom}" .
                                "&srcRoomId={$row[$mainRoomKeyField]}&showHTML=0";
                $portalAddText = "
                <div class='actBtns'>
                    <a href='javascript:void(0);' link='{$newPortalUrl}' class='newPortalRecord'
                    dialogTitle='New {$mainRoomTitle}'
                    w='{$dialogWidth}'
                    h='{$dialogHeight}'>
                    Add</a>
                </div>
                ";
            }
        }

        if ($linkingType == "grid"  && $formObj->mode == 'edit') {
            if ($hasPortalNew == 1) {
                $newGridItemUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?_spAction=addNewGridItem&srcRoom={$srcRoom}&lnkRoom={$lnkRoom}" .
                                  "&srcRoomId={$row[$mainRoomKeyField]}&showHTML=0";

                //$blkAdd = "<input type='text' name='add_record_count' value='1'>";
                //{$blkAdd}
                $portalAddText = "
                <div class='actBtns'>
                    <a href='javascript:void(0);' link='{$newGridItemUrl}' recId='{$row[$mainRoomKeyField]}' class='newGridRecord'>{$addButtonText}</a>
                </div>
                ";
            }
        }

        $toggleText = "";
        $hasToggle = 1;
        $countText = "";
        $pageLinks = '';
        if ($hasToggle && ($formObj->mode == 'detail' || $formObj->mode == 'edit')) {
           $toggleText = "<div class='toggle'>&nbsp;</div>";
        }

        if ($pager->totalPages <= 1) {
           $countText = "<span class='count'>({$numRows})</span>";
        }

        $expandedVal = 0;
        if ($openExpanded) {
           $expandedVal = 1;
        }

        $selectBtn = '';
        $hasModalChoose = $lra[$linkName]['hasModalChoose'];

        if ($formObj->mode == 'edit' && ($linkingType != 'portal' && $linkingType != 'grid') && $hasModalChoose) {
            $chooseLinkValidateJsMethod = $lra[$linkName]['chooseLinkValidateJsMethod'];
            $beforeCloseFnName = $lra[$linkName]['beforeCloseFnName'];

            $chooseLinkValidateJsMethodText = '';
            if ($chooseLinkValidateJsMethod) {
                $chooseLinkValidateJsMethodText = "chooseLinkValidateJsMethod='{$chooseLinkValidateJsMethod}'";
            }

            $beforeCloseFnNameText = '';
            if ($beforeCloseFnName) {
                $beforeCloseFnNameText = "beforeCloseFnName='{$beforeCloseFnName}'";
            }

            $editLinkUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?srcRoom={$srcRoom}&lnkRoom={$lnkRoom}&_action=list" .
                           "&_spAction=link&linkMasterTableID={$row[$mainRoomKeyField]}&showHTML=0";

            $selectBtn = "
            <div class='float_left'>
                <a href='javascript:void(0);' class='editLink ul' link='{$editLinkUrl}'
                   {$chooseLinkValidateJsMethodText}
                   {$beforeCloseFnNameText}
                   dialogTitle='Edit {$mainRoomTitle}'>{$ln->gd('cp.lbl.choose', 'Choose')}</a>
            </div>
            ";
        }

        $linkHeaderHyperLinkText = '';
        if (is_array($linkHeaderHyperLink)) {
            $headerLinkTitle = $linkHeaderHyperLink['title'];
            $headerLinkUrl = $linkHeaderHyperLink['url'];
            $headerLinkClass = $linkHeaderHyperLink['class'];
            $linkHeaderHyperLinkText = "
            <div class='float_right mr10 {$headerLinkClass}'>
                <a href='{$headerLinkUrl}'>{$headerLinkTitle}</a>
            </div>
            ";
        }

        $pagerUrl = $cpCfg['cp.scopeRootAlias'] . "index.php?_spAction=linkPortalRecordsByFilter&showHTML=0".
                    "&_action={$tv['action']}&srcRoom={$srcRoom}&lnkRoom={$lnkRoom}&record_id={$row[$mainRoomKeyField]}";

        $text = "
        <div class='linkPortalWrapper {$linkName2}' id='{$linkName}' pagerUrl='{$pagerUrl}'>
            <div expanded='{$expandedVal}' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>
                        {$displayTitle}
                    </div>
                    {$selectBtn}
                    {$toggleText}
                    <div style='float:right;'>{$rightLink}{$countText}</div>
                    {$linkHeaderHyperLinkText}
                </div>
            </div>
            <div>
                {$portalSearchText}
                <div class='linkPortalDataWrapper'>
                    {$this->getLinkPortal($result, $numRows, $srcRoom, $lnkRoom, $row[$mainRoomKeyField], $exp)}
                </div>
                {$portalAddText}
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getLinkPortal($result, $numRows, $srcRoom, $lnkRoom, $srcRoomId = '', $exp = array()) {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $modulesArr = Zend_Registry::get('modulesArr');
        $dbUtil = Zend_Registry::get('dbUtil');
        $searchHTMLLink = Zend_Registry::get('searchHTMLLink');
        $linksArray = Zend_Registry::get('linksArray');
        $arrayMasterLink = Zend_Registry::get('arrayMasterLink');
        $cpUtil = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');

        $imAChild = $fn->getIssetParam($exp, 'imAChild', false);
        $parent_id = $fn->getIssetParam($exp, 'parent_id');
        $child_fieldLabel = $fn->getIssetParam($exp, 'fieldLabel', array());
        $child_fieldClassArray = $fn->getIssetParam($exp, 'fieldClassArray', array());

        $lra = &$linksArray;

        $linkName                = $srcRoom . '#' . $lnkRoom;
        $linkName2               = $srcRoom . '__' . $lnkRoom;

        $this->linkObj = $lra[$linkName];
        $hasPortalEdit           = $lra[$linkName]['hasPortalEdit'];
        $hasPortalDelete         = $lra[$linkName]['hasPortalDelete'];
        $hasPortalDetail         = $lra[$linkName]['hasPortalDetail'];
        $hasGridEdit             = $lra[$linkName]['hasGridEdit'];
        $fieldLabel              = $lra[$linkName]['fieldlabel'];
        $fieldClassArray         = $lra[$linkName]['fieldClassArray'];

        if($imAChild){
            if(count($child_fieldLabel) > 0){
               $fieldLabel = $child_fieldLabel;
            }

            if(count($child_fieldClassArray) > 0){
               $fieldClassArray = $child_fieldClassArray;
            }
        }

        $mainRoomKeyField        = $modulesArr[$srcRoom]['keyField'];
        $mainRoomTitle           = isset($modulesArr[$lnkRoom]) ? $modulesArr[$lnkRoom]['title'] : '';
        $linkRoomKeyField        = $lra[$linkName]['keyField'];
        $historyTableKeyField    = $lra[$linkName]["historyTableKeyField"];
        $showAnchorInLinkPortal  = $lra[$linkName]['showAnchorInLinkPortal'];
        $linkingType             = $lra[$linkName]['linkingType'];
        $hasChildren             = $lra[$linkName]['hasChildren'];
        $summaryFieldsArray      = $lra[$linkName]['summaryFieldsArray'];
        $anchorFieldsArr         = $lra[$linkName]['anchorFieldsArr'];
        $hideFieldsArray         = $lra[$linkName]['hideFieldsArray'];
        $hasHistoryCallback      = $lra[$linkName]['hasHistoryCallback'];
        $showAlternativeRowColor = $lra[$linkName]['showAlternativeRowColor'];
        $gridFieldTypeArray      = $lra[$linkName]['gridFieldTypeArray'];
        $showRowSerialNo         = $lra[$linkName]['showRowSerialNo'];

        $fnsMod = includeCPClass('ModuleFns', $srcRoom);

        /********* if the link has a child link (eg: question & answers) ***************/
        if($hasChildren){
            $childLinkKey    = $lra[$linkName]['childLinkKey'];
            $childFieldLabel = $lra[$linkName]['childFieldLabel'];
            $childAddButtonText   = $lra[$linkName]['childAddButtonText'];
            $childFieldClassArray = $lra[$linkName]['childFieldClassArray'];
            $childLinkKeyArr = explode('#', $childLinkKey);
            $srcRoomChild    = $childLinkKeyArr[0];
            $lnkRoomChild    = $childLinkKeyArr[1];
            $childRoomTitle  = $modulesArr[$lnkRoomChild]['title'];
        }

        $fldLabelText   = '';
        $fldLabelText2  = '';
        $noRecText      = '';
        $summaryRowText = '';
        $records        = '';

        $pagerObjTitle = 'pager_' . $linkName2; //
        $pager = Zend_Registry::get($pagerObjTitle);

        if ($numRows == 0) { //if 1
            $noRecText = "
            <tr>
                <td height='50' align='center'>{$ln->gd('cp.lbl.noRecordsLinked', 'No Records Linked')}</td>
            </tr>
            ";
        } else { //if 1
            $tmpCount = 0;

            if (count($fieldLabel) > 0){
                if($showRowSerialNo){
                    $fldLabelText .= "<th width='5'>#</th>";
                }
            }

            foreach ($fieldLabel as $value) {
                $clsName = $cpUrl->getUrlText($value);
                $fieldCSSClass = isset($fieldClassArray[$tmpCount]) ? " {$fieldClassArray[$tmpCount]}" : '';
                $fldLabelText .= "<th class='{$clsName}{$fieldCSSClass}'>{$value}</th>";
                $tmpCount++;
            }

            if (($hasPortalEdit == 1 || $linkingType == 'grid') && $formObj->mode == 'edit' && count($fieldLabel) > 0) {
                $fldLabelText2 = "<th{$fieldCSSClass}>&nbsp;</th>";
            }
            if ($hasPortalEdit == 1 && $formObj->mode == 'detail' && count($fieldLabel) > 0) {
                $fldLabelText2 = "<th{$fieldCSSClass}>&nbsp;</th>";
            }

            $fldLabelText = "
            <tr>
                {$fldLabelText}
                {$fldLabelText2}
            </tr>
            ";

            //extract first row for examining field position etc for use in summary row and other places
            $row = $db->sql_fetchrow($result, MYSQL_ASSOC);
            //$db->sql_rowseek(0);
            $db->sql_rowseek(0, $result);
            $record_id_name = $lra[$linkName]['recordIdFldName'];
            $counter = 0;
            $fieldsPosArray   = array();
            $fieldsNamesArray = array();
            foreach ($row as $fieldName => $value) {
                //get fieldsArr (fieldObj)
                if (!isset($this->linkObj['fieldsArr'][$fieldName])) {
                    $this->linkObj['fieldsArr'][$fieldName] = $arrayMasterLink->getFieldObj($fieldName);
                }

                if ($counter == 0 && $record_id_name == '') {
                    //get the first column name as record id field name
                    $record_id_name = $fieldName;
                }
                $fieldsNamesArray[] = $fieldName;

                $fieldName = 'pos_' . $fieldName; //ex: pos_first_name
                $$fieldName = $counter; //sames as $pos_first_name = $counter
                $fieldsPosArray[$fieldName] = $$fieldName;

                $counter++;
            }

            if ($showAnchorInLinkPortal) {
                //get the first two columns in the result. for ex: it can be like
                // product_id, product_code
                //when having product as link in an order record
                $fieldArr = array_slice($fieldsNamesArray, 0, 2);
                $idFieldName = isset($fieldArr[0]) ? $fieldArr[0] : '';
                $anchorFieldName = isset($fieldArr[1]) ? $fieldArr[1] : '';
                //if the field (ex: product_code) is not set in anchorFieldsArr then set it as default
                if (!isset($anchorFieldsArr[$anchorFieldName])) {
                    $anchorFieldsArr[$anchorFieldName] = $arrayMasterLink->getLinkAnchorObj($anchorFieldName, $idFieldName);
                }
            }

            //edit link item validation
            $editLinkItemValidateJsMethod = $lra[$linkName]['editLinkItemValidateJsMethod'];

            //add anchor field's id field to hideFieldsArray
            foreach ($anchorFieldsArr as $fieldName => $anchorFieldArr) {
                if (!$anchorFieldArr['showIdField']) {
                    $hideFieldsArray[] = $anchorFieldArr['idFieldName'];
                }
            }

            if ($record_id_name != '') {
                $hideFieldsArray[] = $record_id_name;
            }

            //if we have any summary fields to show then let's build it
            if (count($summaryFieldsArray) > 0) {
                foreach ($summaryFieldsArray as $fieldName) {
                    $hideFieldsArray[] = $fieldName . '_sum';
                }

                $summaryRowText = $this->getLinkPortalSummaryFields($row, $summaryFieldsArray, $hideFieldsArray, $srcRoom, $lnkRoom);
            }

            $topRm = '';
            $module  = '';
            $rowCounter = 0;

            while ($row = $db->sql_fetchrow($result, MYSQL_ASSOC)) { //while 1
                $editBtns  = '';
                $editBtn   = '';
                $deleteBtn = '';
                $viewBtn   = '';
                $addChildBtn = '';
                $record_id = '';

                $bgClass = 'portal-row1';
                if ($showAlternativeRowColor) {
                    $bgClass = ($rowCounter%2) != 0 ? 'portal-row1' : 'portal-row2';
                }

                $fldValues = '';

                $tmpCount = 0;

                foreach ($row as $fieldName => $value) { //for loop 1
                    $fieldCSSClass = isset($fieldClassArray[$tmpCount]) ? " {$fieldClassArray[$tmpCount]}" : '';
                    $clsName = $cpUrl->getUrlText($fieldName);

                    if (in_array($fieldName, $hideFieldsArray)) {
                        continue;
                    }

                    //edit link item validate javascript
                    $editLinkItemValidateJsMethodText = '';
                    if ($editLinkItemValidateJsMethod) {
                        $record_id = $row[$record_id_name];
                        $editLinkItemValidateJsMethodText =
                        "editLinkItemValidateJsMethod='{$editLinkItemValidateJsMethod}'";
                    }
                    if ($linkingType == 'grid' && $hasGridEdit == 1){
                        $fieldTypeArr = isset($gridFieldTypeArray[$tmpCount]) ? $gridFieldTypeArray[$tmpCount] : array();
                        $fieldType = count($fieldTypeArr) > 0 ? $fieldTypeArr['type'] : 'textbox';
                        $editable  = $fn->getIssetParam($fieldTypeArr, 'editable', 1);
                        $validationType = $fn->getIssetParam($fieldTypeArr, 'validationType');
                        $maxLength = $fn->getIssetParam($fieldTypeArr, 'maxLength');

                        // associative array for non editable fields ex: days (1 => 'Sunday', 2 => 'Monday')
                        $associtaiveArr = $fn->getIssetParam($fieldTypeArr, 'associtaiveArr');

                        if ($formObj->mode == 'edit'){
                            if ($fieldType == 'dropdown'){
                                $ddArr = $fn->getIssetParam($fieldTypeArr, 'ddArr');
                                $sql = $fn->getIssetParam($fieldTypeArr, 'sql');
                                $useKey = $fn->getIssetParam($fieldTypeArr, 'useKey', true);
                                $hideFirstOption = $fn->getIssetParam($fieldTypeArr, 'hideFirstOption', false);
                                $firstOptionLabel = $fn->getIssetParam($fieldTypeArr, 'firstOptionLabel', 'Please Select');

                                if ($ddArr == '' && $sql == ''){
                                    exit("if you use a dropdown field type, you must pass the ddArr in {$linkName} / gridFieldTypeArray");
                                }

                                if ($sql != ''){
                                    /** ahmad: to be fine tuned **/
                                    //if($imAChild){
                                    //    $sql = str_replace('[[parent_id]]', $parent_id, $sql);
                                    //}
                                    //$ddText = $dbUtil->getDropDownFromSQLCols2($db, $sql, $value);
                                } else {
                                    $ddText = $cpUtil->getDropDown1($ddArr, $value, $useKey);
                                }

                                $disabled = ($editable == true) ? '' : " disabled='disabled";
                                $firstOption = '';
                                if (!$hideFirstOption){
                                    $firstOption = "<option value=''>{$firstOptionLabel}</option>";
                                }

                                $value = "
                                <select name='{$fieldName}'{$disabled}>
                                    {$firstOption}
                                    {$ddText}
                                </select>
                                ";
                            } else if ($fieldType == 'singleCheckbox'){
                                $checked = ($value == 1)  ? " checked='checked'" : '';
                                $value = "
                                <input type='checkbox' name='{$fieldName}' value='1' {$checked} />
                                ";
                            } else if ($fieldType == 'singleRadio'){// used to for publish to publish one one record
                                $checked = ($value == 1)  ? " checked='checked'" : '';
                                $value = "
                                <input type='radio' name='{$fieldName}' value='1' {$checked} />
                                ";

                            } else if ($fieldType == 'date'){
                                $value = $cpUtil->replaceForFormField($value);
                                $value = "<input name='{$fieldName}' type='text' value=\"{$value}\" class='fld_date' />";

                            } else if ($fieldType == 'time'){
                                CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jqUITimePickerAddon-0.9.3'));
                                $value = $cpUtil->replaceForFormField($value);
                                $value = "<input name='{$fieldName}' type='text' value=\"{$value}\" class='fld_time' />";

                            } else if ($fieldType == 'textarea'){
                                $value = $cpUtil->replaceForFormField($value);
                                $value = "<textarea name='{$fieldName}'>{$value}</textarea>";

                            } else if ($editable == 1){
                                $validationTypeTxt = '';
                                if ($validationType != ''){
                                    $validationTypeTxt = " validation='{$validationType}'";
                                }

                                $maxLengthTxt = '';
                                if ($maxLength != ''){
                                    $maxLengthTxt = " maxlength='{$maxLength}'";
                                }
                                // this is added due to some error in wine_product (product country link) -- backer//
                                if (!isset($this->linkObj['fieldsArr'][$fieldName])) {
                                    $this->linkObj['fieldsArr'][$fieldName] = $arrayMasterLink->getFieldObj($fieldName);
                                }
                                $isGridFldEditable = $this->linkObj['fieldsArr'][$fieldName]['isGridFldEditable'];
                                if ($isGridFldEditable) {
                                    $value = $cpUtil->replaceForFormField($value);
                                    $value = "<input name='{$fieldName}'{$validationTypeTxt}{$maxLengthTxt} type='text' value=\"{$value}\" />";
                                }
                            } else if (is_array($associtaiveArr)){
                                $value = isset($associtaiveArr[$value]) ? $associtaiveArr[$value] : $value;
                            }


                        } else {
                            if ($fieldType == 'singleCheckbox'){
                                $value = $fn->getYesNo($value);
                            }
                        }

                        if ($fieldType == 'media'){
                            $mediaRecType  = $fn->getIssetParam($fieldTypeArr, 'mediaRecType', 'picture');
                            $media = Zend_Registry::get('media');
                            $url = "{$cpCfg['cp.scopeRootAlias']}index.php?plugin=common_media" .
                                    "&_spAction=selectMedia&room={$lnkRoom}&recordType={$mediaRecType}&id={$row[$historyTableKeyField]}" .
                                    "&showHTML=0&lang={$tv['lang']}";

                            $dispUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?plugin=common_media" .
                                       "&_spAction=singleFileForLinkGrid&room={$lnkRoom}&recordType={$mediaRecType}" .
                                       "&id={$row[$historyTableKeyField]}&showHTML=0&lang={$tv['lang']}";

                            $actionButtons = '';
                            $mediaExp = array('folder' => 'thumb');

                            if ($formObj->mode == 'edit') {
                                $actionButtons = "
                                <div class='txtCenter mt10'>
                                    <a class='btnSelectMedia' href='{$url}'>Browse</a>
                                </div>
                                ";
                                $mediaExp['showDeleteIcon'] = true;
                            }

                            $value = "
                            <div id='media__{$lnkRoom}__{$mediaRecType}__{$row[$historyTableKeyField]}'>
                                <div class='mediaFileWrap' url='{$dispUrl}'>
                                    {$media->getMediaPicture($lnkRoom, 'picture', $row[$historyTableKeyField], $mediaExp)}
                                </div>
                                {$actionButtons}
                            </div>
                            ";
                        }

                    }

                    // this is added due to some error in wine_product (country city link) -- ahm//
                    if (!isset($this->linkObj['fieldsArr'][$fieldName])) {
                        $this->linkObj['fieldsArr'][$fieldName] = $arrayMasterLink->getFieldObj($fieldName);
                    }

                    if ($cpCfg['cp.showAnchorInLinkPortal'] == 1 && isset($anchorFieldsArr[$fieldName])
                       && ($formObj->mode != 'edit' || $anchorFieldsArr[$fieldName]['showLinkInEdit'])) {
                        //--------------------------------------------------//
                        $anchorFieldArr = $anchorFieldsArr[$fieldName];
                        $idFieldName = $anchorFieldArr['idFieldName'];
                        $idValue = $row[$idFieldName];

                        if ($anchorFieldArr['room']) {
                            $topRm = $fn->getTopRoomName($anchorFieldArr['room']);
                            $module = $anchorFieldArr['room'];
                        } else {
                            $mainModuleName = $modulesArr[$lnkRoom]['mainModuleName'];
                            $topRm = $fn->getTopRoomName($mainModuleName);
                            $module = $mainModuleName;
                        }

                        $align = $this->linkObj['fieldsArr'][$fieldName]['align'];
                        $align = $this->getCellAlign($value, $align);

                        $portalRecordUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?_topRm={$topRm}"
                                         . "&module={$module}"
                                         . "&_action=detail"
                                         . "&record_id={$idValue}";

                        $fldValues .= "
                        <td class='{$clsName} {$align} {$fieldCSSClass}'>
                            <a href='{$portalRecordUrl}'>{$value}</a>
                        </td>
                        ";
                    } else {
                        $align = $this->linkObj['fieldsArr'][$fieldName]['align'];
                        $align = $this->getCellAlign($value, $align);
                        $fldValues .= "<td class='{$clsName} {$align} {$fieldCSSClass}'>{$value}</td>";
                    }

                    $tmpCount++;
                } //for loop 1


                $dialogWidth  = $lra[$linkName]['portalDialogWidth'];
                $dialogHeight = $lra[$linkName]['portalDialogHeight'];

                //------------------ view button --------------------//
                if ($hasPortalDetail == 1 && $formObj->mode == 'detail') {
                    $record_id = $row[$record_id_name];

                    $viewPortalUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?_spAction=detailPortal&srcRoom={$srcRoom}&lnkRoom={$lnkRoom}" .
                                     "&id={$record_id}&showHTML=0";

                    $viewBtn = "
                    <a href='javascript:void(0);' link='{$viewPortalUrl}' class='viewPortalRecord'
                       dialogTitle='View {$mainRoomTitle}'
                       w='{$dialogWidth}'
                       h='{$dialogHeight}'>Detail
                    </a>
                    ";
                }

                //------------------ edit button --------------------//
                if ($hasPortalEdit == 1 && $formObj->mode == 'edit') {
                    $record_id = $row[$record_id_name];
                    if ($hasChildren){
                        $addChildUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?_spAction=new&srcRoom={$srcRoomChild}&lnkRoom={$lnkRoomChild}" .
                                       "&srcRoomId={$record_id}&showHTML=0";
                        $addChildBtn = "
                        <a href='javascript:void(0);' link='{$addChildUrl}'
                           class='newPortalRecord'
                           dialogTitle='New {$childRoomTitle}'
                           w='{$dialogWidth}'
                           h='{$dialogHeight}'>
                            <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/btn_add.png' title='Add Child Record' border='0'>
                        </a>
                        ";
                    }

                    $editPortalUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?_spAction=edit&srcRoom={$srcRoom}&lnkRoom={$lnkRoom}" .
                                     "&id={$record_id}&showHTML=0";

                    $editBtn = "
                    <a href='javascript:void(0);' link='{$editPortalUrl}' class='editPortalRecord'
                       {$editLinkItemValidateJsMethodText}
                       dialogTitle='Edit {$mainRoomTitle}'
                       recId='{$record_id}'
                       w='{$dialogWidth}'
                       h='{$dialogHeight}'>
                        <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/btn_edit.png' title='Edit Record' border='0'>
                    </a>
                    ";
                }

                //------------------ delete button --------------------//
                if ($hasPortalDelete == 1 && $formObj->mode == 'edit') {
                    $record_id = $row[$record_id_name];
                    $deletePortalUrl = '';

                    if ($lra[$linkName]['historyTableName'] != '') {
                        $deletePortalUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?_spAction=deletePortalRecordByID&srcRoom={$srcRoom}" .
                                           "&lnkRoom={$lnkRoom}&id={$record_id}&showHTML=0";
                    } else {
                        $deletePortalUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?_spAction=deleteRecordByID&record_id={$record_id}&room={$lnkRoom}&showHTML=0";
                    }

                    $deleteBtn = "
                    <a href='javascript:void(0);' class='deletePortalRecord' link='{$deletePortalUrl}' srcRoomId='{$srcRoomId}'>
                        <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/btn_remove.png' title='Delete Record' border='0'>
                    </a>
                    ";
                }

                if ($addChildBtn != '' || $editBtn != '' || $deleteBtn != '' || $viewBtn != '') {
                    $editBtns = "
                    <td class='portalActBtns'>
                        <div style='float:right'>{$addChildBtn}{$editBtn}{$deleteBtn}{$viewBtn}</div>
                    </td>
                    ";
                }

                $rowCounter++;

                $childRecs = '';

                if ($hasChildren){
                    $parentKeyField = $modulesArr[$srcRoomChild]['keyField'];
                    $parent_id = $row[$parentKeyField];
                    $expChild = array(
                         'imAChild' => true
                        ,'parent_id' => $parent_id
                        ,'fieldLabel' => $childFieldLabel
                        ,'fieldClassArray' => $childFieldClassArray
                        ,'addButtonText'   => $childAddButtonText
                    );

                    $childRecs = "
                    <tr>
                    <td colspan='20'>
                        <div class='childWrapper' parent_id='{$parent_id}'>
                            {$this->getLinkPortalMain($srcRoomChild, $lnkRoomChild, 'Child Records', $row, $expChild)}
                        </div>
                    </td>
                    </tr>
                    ";
                }

                $historyCallbackText = '';
                if ($hasHistoryCallback){
                    $srcRmArr = explode('_', $srcRoom);
                    $lnkRmArr = explode('_', $lnkRoom);

                    //ex: getTradingCompanyTradingContactLinkSQL
                    $funcName = "get" . ucfirst($srcRmArr[0]) . ucfirst($srcRmArr[1]) .
                                        ucfirst($lnkRmArr[0]) . ucfirst($lnkRmArr[1]) .
                                        "HistoryCallback";

                    $tempText = $fnsMod->$funcName($row);
                    if (trim($tempText) != '') {
                        $historyCallbackText = "
                        <tr>
                        <td colspan='20' class='callbackWrapperTD'>
                            <div class='callbackWrapper'>
                                {$tempText}
                            </div>
                        </td>
                        </tr>
                        ";
                    }
                }

                $record_id = $row[$record_id_name];
                $trClass = ($childRecs != '') ? ' hasChildren' : $bgClass;

                $rowSerialTxt = '';
                if($showRowSerialNo){
                    $rowSerial = ($pager->page == 1) ? $rowCounter : ($pager->page -1) * $pager->numRecordsPerPage + $rowCounter;
                    $rowSerialTxt = "<td width='5'>{$rowSerial}</td>";
                }
                $records .= "
                <tr class='{$trClass} row-{$linkName2}' recId='{$record_id}'>
                    {$rowSerialTxt}
                    {$fldValues}
                    {$editBtns}
                </tr>
                {$childRecs}
                {$historyCallbackText}
                ";

                if ($rowCounter > 500){
                    break;
                }

            } //while 1
        } //if 1

        $tblProperties = "";

        if ($linkingType == 'grid'){
            $autoSaveGrid = $lra[$linkName]['autoSaveGrid'];

            $tblProperties = "
            class='grid' autoSaveGrid='{$autoSaveGrid}' saveUrl='{$cpCfg['cp.scopeRootAlias']}index.php?_spAction=saveGridItem&srcRoom={$srcRoom}&lnkRoom={$lnkRoom}&lang={$tv['lang']}&showHTML=0'
            keyFld={$historyTableKeyField}
            ";
        }

        $pageLinks = '';
        if ($pager->totalPages > 1){
            $pageLinks = $pager->getNavButtons(5, 'list', '', array('linkHasOnlyPageNo' => true));
        }

        $tblText = "
        <table{$tblProperties}>
            <thead>
                {$fldLabelText}
            </thead>
            <tbody>
                {$noRecText}
                {$summaryRowText}
                {$records}
            </tbody>
        </table>
        ";

        $surroundLinkPortalTblWithForm = $lra[$linkName]['surroundLinkPortalTblWithForm'];
        if ($surroundLinkPortalTblWithForm){
            $tblText = "
            <form>
                {$tblText}
            </form>
            ";
        }
        
        $text = "
        {$pageLinks}
        {$tblText}
        ";

        return $text;
    }

    /**
     * build summary row for portal
     */
    function getLinkPortalSummaryFields($row, $summaryFieldsArray, $hideFieldsArray, $srcRoom, $lnkRoom) {
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $linksArray = Zend_Registry::get('linksArray');
        $linkName          = $srcRoom . '#' . $lnkRoom;
        $showRowSerialNo   = $linksArray[$linkName]['showRowSerialNo'];

        $linkObj = $this->linkObj;
        $fieldClassArray = $linkObj['fieldClassArray'];

        $summaryRowText = '';

        if($showRowSerialNo){
            $summaryRowText .= "<th></th>";
        }

        $tmpCount = 0;
        foreach ($row as $fieldName => $value) {
            $fieldCSSClass = isset($fieldClassArray[$tmpCount]) ? " {$fieldClassArray[$tmpCount]}" : '';

            if (in_array($fieldName, $hideFieldsArray)) {
                continue;
            }
            if (in_array($fieldName, $summaryFieldsArray)) {
                $align = $this->linkObj['fieldsArr'][$fieldName]['align'];
                $align = $this->getCellAlign($value, $align);

                $summaryValue = $row[$fieldName . '_sum'];
                $summaryRowText .= "
                <th class='{$align}{$fieldCSSClass}'>{$summaryValue}</th>
                ";
            } else {
                $summaryRowText .= "
                <th></th>
                ";
            }
            $tmpCount++;
        }

        if ($linkObj['hasPortalDetail'] && $formObj->mode == 'detail') {
            $summaryRowText .= "
            <th></th>
            ";
        }
        if ($linkObj['hasPortalEdit'] && $formObj->mode == 'edit') {
            $summaryRowText .= "
            <th></th>
            ";
        }

        $summaryRowText = "
        <tr class='summary-row'>{$summaryRowText}</tr>
        ";

        return $summaryRowText;
    }

    /**
     *
     */
    function getRightPanelMediaDisplay($displayTitle = '', $module = '', $recordType = '', $row = '') {
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        if ($tv['action'] == 'search') {
            return;
        }

        $keyField  = $modulesArr[$module]['keyField'];

        $rows = '';

        //-----------------------------------------------------------//
        if ($formObj->mode == 'detail' || $formObj->mode == 'edit' || $formObj->mode == 'print') {
            $rows = $media->getMediaFilesDisplay($module, $recordType, $row[$keyField]);
        }

        $toggleText = '';

        if ($formObj->mode == 'detail' || $formObj->mode == 'edit') {
            $toggleText = "<div class='toggle'>&nbsp;</div>";
        }

        $expandedVal = 1;

        $url     = "{$cpCfg['cp.scopeRootAlias']}index.php?module=media&_spAction=selectMedia&room={$module}&recordType={$recordType}&id={$row[$keyField]}&showHTML=0";
        $dispUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?module=media&_spAction=mediaFilesDisplay&room={$module}&recordType={$recordType}&id={$row[$keyField]}&showHTML=0";


        $actionButtons = '';
        if ($formObj->mode == 'edit') {
            $actionButtons = "<a class='btnSelectMedia' href='{$url}'>Select</a>";
        }

        $text = "
        <div class='linkPortalWrapper' id='media__{$module}__{$recordType}'>
            <div expanded='{$expandedVal}' class='header floatbox'>
                <div class='float_left'>{$displayTitle}</div>
                {$toggleText}
            </div>
            <div class='mediaFilesDisplayWrap' url='{$dispUrl}'>
                {$rows}
    		</div>
    		<div class='actBtns'>{$actionButtons}</div>
        </div>
        ";

        return $text;
    }

    function getCellAlign($value, $align) {
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($align == '' && $cpCfg['cp.autoAlignPortalCells']) {
            $strType = $cpUtil->getStringType($value);
            if ($strType == 'numeric') {
                $align = 'right';
            } else {
                $align = 'left';
            }
        }
        $align = $align != '' ? 'al-' . $align : '';

        return $align;
    }
}
