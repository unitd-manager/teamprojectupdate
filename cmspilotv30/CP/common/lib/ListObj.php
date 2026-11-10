<?
/**
 *
 */
class CP_Common_Lib_ListObj
{

    function __construct(){
    }

    //==================================================================//
    function getListCheckBox($rowCounter, $id, $rowClassName) {
        $tv = Zend_Registry::get('tv');

        $text = "
        <td align='center'>
            <input class='check' type='checkbox' id='cb{$rowCounter}'
                  name='cid[]' value='{$id}'
                  onclick=\"Actions.isChecked(this.checked);
                            Actions.highlightRow(this, {$id}, '{$rowClassName}');
                  \"
            />
        </td>
        ";

        return $text;
    }

    //==================================================================//
    function getListRowHeader($row, $rowCounter, $listRowClass = '', $extraParam = array()) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $pager = Zend_Registry::get('pager');

        $hasFlagInList     = $modulesArr[$tv['module']]['hasFlagInList'];
        $hasCheckboxInList = ($modulesArr[$tv['module']]['hasCheckboxInList'] && $cpCfg['cp.hasCheckboxInList']) ? true : 0;

        $hasCheckboxInList = isset($extraParam['hasCheckboxInList']) ? $extraParam['hasCheckboxInList'] : $hasCheckboxInList;
        $keyFieldValue     = isset($extraParam['keyFieldValue'])     ? $extraParam['keyFieldValue']     : $row[$modulesArr[$tv['module']]['keyField']];

        $hasFlagInList     = isset($extraParam['hasFlagInList'])     ? $extraParam['hasFlagInList']     : $hasFlagInList;
        $hasFlagInListBlue = isset($extraParam['hasFlagInListBlue']) ? $extraParam['hasFlagInListBlue'] : 0;
        $hasFlagInListGreen = isset($extraParam['hasFlagInListGreen']) ? $extraParam['hasFlagInListGreen'] : 0;

        $hasRowNumber  = isset($extraParam['hasRowNumber'])  ? $extraParam['hasRowNumber']  : true;
        $hasEditInList = isset($extraParam['hasEditInList']) ? $extraParam['hasEditInList'] : true;

        $text = '';
        if($listRowClass == "") {
            $listRowClass = $fn->getRowClass($rowCounter%2, $keyFieldValue, $listRowClass);
        }

        //--------------------------------------------------------------//
        $rowNumberText = '';
        $exrtaCells = '';
        if ($tv['action'] != 'print') {
            if ($hasCheckboxInList == 1) {
                $exrtaCells .= $this->getListCheckBox($rowCounter, $keyFieldValue, $listRowClass);
            }

            $hasEditAccess = true;
            if (CP_SCOPE == 'admin' && $cpCfg['cp.hasAccessModule']){
                $hasEditAccess = getCPModuleObj('core_userGroup')->model->hasAccessToAction($tv['module'], 'edit');
            }

            if ($hasEditInList && $hasEditAccess) {
                $exrtaCells .= $this->getEditIcon($rowCounter, $row);
            }

            $color = $fn->getReqParam('color', 'red');
            $flag_fld = 'flag';
            if ($color != 'red') {
                $flag_fld = 'flag_' . $color;
            }
            if ($hasFlagInList == 1) {
                $exrtaCells .= $this->getListFlagImage($row['flag'], $keyFieldValue, 'red');
            }
            if ($hasFlagInListBlue == 1) {
                $exrtaCells .= $this->getListFlagImage($row['flag_blue'], $keyFieldValue, 'blue');
            }
            if ($hasFlagInListGreen == 1) {
                $exrtaCells .= $this->getListFlagImage($row['flag_green'], $keyFieldValue, 'green');
            }

        }

        if ($hasRowNumber) {
            $rowSerial = $pager->startRecordNo + $rowCounter;
            $rowNumberText = "
            {$this->getListDataCell($rowSerial, "left", "listRowIndex__" . $keyFieldValue, 5)}
            ";
        }

        $text = "
        <tr class='{$listRowClass}' id='listRow__{$keyFieldValue}'>
            {$rowNumberText}
            {$exrtaCells}
        ";

        return $text;
    }

    //==================================================================//
    function getListHeaderCell($title = '', $sortText = '', $class='', $colspan = 1) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $text = '';

        $colspanText = '';
        if ($colspan > 1) {
            $colspanText = " colspan='{$colspan}'";
        }

        $class = ($class != '') ? " class='{$class}'": "";

        if ($sortText != "" && $tv['action'] != "print") {
            $sortTextTemp = str_replace(" asc", "", $sortText);
            $sortTextTemp = str_replace(" desc", "", $sortText);

            $pos     = $cpUtil->stringPosition($tv['sortOrder'], $sortTextTemp);
            $posDesc = $cpUtil->stringPosition($tv['sortOrder'], "desc");

            $arrow = ($posDesc >= 0) ?
            "<span style='font-size:15px;'>&nbsp;&darr;</span>" :
            "<span style='font-size:15px;'>&nbsp;&uarr;</span>";

            if ($pos >= 0) {
                $text = "
                <th{$class}>
                    <div class='headerSorted'>
                        {$fn->getSortText($sortText, $title, '', $arrow)}
                    </div>
                </th>
                ";
            } else {
                $text = "
                <th{$colspanText}{$class}>
                    {$fn->getSortText($sortText, $title)}
                </th>
                ";
            }

        } else {
            $text = "
            <th{$colspanText}{$class}>
                {$title}
            </th>
            ";
        }

        return $text;
    }


    //==================================================================//
    function getListDataCell($title, $align='', $cellID = '', $width ='', $exp = array()) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $class = isset($exp['class']) ? " class='{$exp['class']}'" : '';

        $width = ($width != '') ? " width={$width}"   : '';
        $align = ($align != '') ? " align='{$align}'" : '';
        $text = "
        <td{$width}{$class}>
            <div{$align} id='{$cellID}'>{$title}</div>
        </td>
        ";

        return $text;
    }

    //==================================================================//
    function getListDateCell($fieldValue , $align="left", $cellID = "", $width ="", $format = "DD MMM YYYY") {
        $dateUtil = Zend_Registry::get('dateUtil');


        $fieldValue = $dateUtil->formatDate($fieldValue, $format);

        $text = "";

        $width = $width != "" ? " width='{$width}'" : "";

        $text = "
        <td{$width}>
            <div align='{$align}' id='{$cellID}'>{$fieldValue}</div>
        </td>
        ";

        return $text;
    }

    //==================================================================//
    function getListPublishedImage($value, $id) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $publishFromList   = $modulesArr[$tv['module']]['publishFromList'];

        $img            = ($value == 1) ? "published" : "not_published";
        $publishedIcons = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/{$img}.png' title='upload' border='0'>";
        $publishedIcons = $this->getListPublishedImageIcon($tv['module'], $id, $value, $publishFromList);

        $text = "
        <td width='60'>
            <div align='center' id='txt__published__{$id}'>
               {$publishedIcons}
            </div>
        </td>
        ";

        return $text;
    }

    //==================================================================//
    function getListYesNo($value) {
        $yesNo  = ($value == 1) ? "Yes" : "No";

        $text = "
        <td width='60'>
            <div align='center'>
               {$yesNo}
            </div>
        </td>
        ";

        return $text;
    }

    //==================================================================//
    function getListPublishedImageIcon($module, $id, $value, $editable = 1) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $imgReload = "";

        if ($value == 1) {
            $imgSrc = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/published.png' title='upload' border='0'>";
        } else {
            $imgSrc = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/not_published.png' border='0'>";
        }

        if ($editable == 1) {
            $text = "
            <a style='text-decoration:none;'
                href=\"javascript:DBUtil.publishRecordFromList('{$module}', '{$id}', '{$value}') \">{$imgSrc}
            </a>
            {$imgReload}
            ";
        } else {
            $text =  $imgSrc;
        }

        return $text;
    }

    //==================================================================//
    function getListPublishedImageTest($value, $id) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $publishedIcons = $this->getListPublishedImageTestIcon($tv['module'], $id, $value);

        $text = "
        <td width='60'>
            <div align='center' id='txt__published_test__{$id}'>
                {$publishedIcons}
            </div>
        </td>
        ";

        return $text;
    }

    //==================================================================//
    function getListPublishedImageTestIcon($module, $id, $value) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($value == 1) {
            $imgSrc = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/published.png' border='0'>";
        } else {
            $imgSrc = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/not_published.png' border='0'>";
        }

        $text = "
        <a href=\"javascript:DBUtil.publishTestRecordFromList('{$module}', '{$id}', '{$value}') \">
            {$imgSrc}
        </a>
        ";

        return $text;
    }

    //==================================================================//
    function getListFlagImage($currentValue, $id, $color = 'red') {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($currentValue == 1) {
            $imgSrc = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/flag_on_{$color}.png' border='0'>";
        } else {
            $imgSrc = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/flag_off.png' border='0'>";
        }

        $text = "
        <td width='10'>
            <div align='center'>
                <a href='#'
                   class='list-flag'
                   module='{$tv['module']}'
                   record_id='{$id}'
                   currentValue='{$currentValue}'
                   color='{$color}'>
                    {$imgSrc}
                </a>
            </div>
        </td>
        ";
        return $text;
    }


    //==================================================================//
    function getListThumbMediaImage($module = '', $recordType = '', $id = '', $extraParam = array()) {
        $db = Zend_Registry::get('db');
        $mediaArray = Zend_Registry::get('mediaArray');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');

        $cssClass = isset($extraParam['cssClass']) ? $extraParam['cssClass'] : '';

        $SQL = "
        SELECT *
        FROM media
        WHERE record_id = '{$id}'
          AND record_type = '{$recordType}'
          AND room_name   = '{$module}'
          AND lang = '{$tv['lang']}'
        LIMIT 1
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $mediaArr1 = &$mediaArray[$module][$recordType];

        $picture = '';
        while ($row = $db->sql_fetchrow($result)){
            if ($row['media_type'] == 'image'){
                $folder = $mediaArr1['thumbFolder'];

                $filePath = $folder . $row['file_name'];
                $picture  = "<img src='{$filePath}' style='height:50px;margin:auto; display:block' />";
            }
        }

        $text = "
        <td>
            <div class='{$cssClass}'>
               {$picture}
            </div>
        </td>
        ";

        return $text;
    }

    //==================================================================//
    function getListHeader($extraParam = array()) {
        $tv = Zend_Registry::get('tv');
        $pager = Zend_Registry::get('pager');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $hasFlagInList = $modulesArr[$tv['module']]['hasFlagInList'];

        $hasRowNumber  = isset($extraParam['hasRowNumber'])  ? $extraParam['hasRowNumber']  : true;
        $hasEditInList = isset($extraParam['hasEditInList']) ? $extraParam['hasEditInList'] : true;
        $noScrollableTable = isset($extraParam['noScrollableTable']) ? $extraParam['noScrollableTable'] : false;

        $hasFlagInList = isset($extraParam['hasFlagInList']) ? $extraParam['hasFlagInList'] : $hasFlagInList;
        $hasFlagInListBlue = isset($extraParam['hasFlagInListBlue']) ? $extraParam['hasFlagInListBlue'] : 0;
        $hasFlagInListGreen = isset($extraParam['hasFlagInListGreen']) ? $extraParam['hasFlagInListGreen'] : 0;

        $scrollableTableText = "scrollabletable='1'";
        if ($noScrollableTable) {
            $scrollableTableText = '';
        }

        if ($tv['action'] == "list") {
            $searchQueryString = $pager->removeQueryString(array("_action"));
            $formAction = $searchQueryString . "&_spAction=saveList&showHTML=0";

            $cbText        = '';
            $rowNumberText = '';
            $editRowText   = '';
            $flagCell      = '';

            if ($modulesArr[$tv['module']]['hasCheckboxInList'] == 1 && $cpCfg['cp.hasCheckboxInList']) {
                $cbText = "
                <th class='header' align='center' width='10'>
                   <input class='check' type='checkbox' name='toggle' value='' />
                </th>
                ";
            }

            if ($hasRowNumber) {
                $rowNumberText = "
                <th width='5'> # </th>
                ";
            }

            $hasEditAccess = true;
            if (CP_SCOPE == 'admin' && $cpCfg['cp.hasAccessModule']){
                $hasEditAccess = getCPModuleObj('core_userGroup')->model->hasAccessToAction($tv['module'], 'edit');
            }

            if ($hasEditInList && $hasEditAccess) {
                $editRowText = "
                <th align='center' width='10'></th>
                ";
            }

            if ($hasFlagInList) {
                $color = 'red';
                $flagCell .= "
                <th align='center' width='25'>
                <a href='#' class='list-flag-all' color='{$color}'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/flag_on_{$color}.png' border='0'>
                </a>
                <a href='#' class='list-unflag-all' color='{$color}'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/flag_off.png' border='0'>
                </a>
                </th>
                ";
            }
            if ($hasFlagInListBlue) {
                $color = 'blue';
                $flagCell .= "
                <th align='center' width='25'>
                <a href='#' class='list-flag-all' color='{$color}'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/flag_on_{$color}.png' border='0'>
                </a>
                <a href='#' class='list-unflag-all' color='{$color}'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/flag_off.png' border='0'>
                </a>
                </th>
                ";
            }
            if ($hasFlagInListGreen) {
                $color = 'green';
                $flagCell .= "
                <th align='center' width='25'>
                <a href='#' class='list-flag-all' color='{$color}'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/flag_on_{$color}.png' border='0'>
                </a>
                <a href='#' class='list-unflag-all' color='{$color}'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/flag_off.png' border='0'>
                </a>
                </th>
                ";
            }

            $tableClass = 'list';
            if($cpCfg['cp.theme'] == "MaterialTim") {
                $tableClass = 'thinlist';
            }

            $text = "
            <form name='list' class='cpJqForm' id='listForm' action='{$formAction}' method='post' >
            <table class='{$tableClass}' {$scrollableTableText} id='bodyList' cellspacing='1'>
                <thead>
                <tr>
                {$rowNumberText}
                {$cbText}
                {$editRowText}
                {$flagCell}
            ";

        } else {

            $rowNumberText = '';

            if ($hasRowNumber) {
                $rowNumberText = "
                <th width='5'> # </th>
                ";
            }
            $text = "
            <table class='list' {$scrollableTableText} id='bodyList' cellspacing='1'>
                <thead>
                <tr>
                {$rowNumberText}
            ";
        }

        return $text;
    }

    //==================================================================//
    function getListHeaderEnd() {
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');

        $deleteBtn = "";

        $modName = ($tv['spAction'] == 'link') ? $tv['lnkRoom'] : $tv['module'];

        if ($tv['action'] != "print" && $modulesArr[$modName]['hasDeleteInList'] == 1
                                          && $cpCfg['cp.hasDeleteInList'] == 1)
        {
            $deleteBtn = $this->getListHeaderCell("Delete", "", "headerCenter");
        }

        $text = "
        {$deleteBtn}
        <th width='5'></th>
        </tr>
        </thead>
        <tbody>
        ";

        return $text;
    }

    //==================================================================//
    function getListRowEnd($id) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $showDelete    = 0;
        $deleteBtnText = "";

        $modName = ($tv['spAction'] == 'link') ? $tv['lnkRoom'] : $tv['module'];

        if ($tv['action'] != "print" && $modulesArr[$modName]['hasDeleteInList'] == 1
                                          && $cpCfg['cp.hasDeleteInList'] == 1)
        {
            $showDelete = 1;
        }

        if ($showDelete == 1) {
             $deleteBtnText = "
             <td>
                <div align='center'>
                    <a href=\"javascript:DBUtil.deleteRecordFromList('{$tv['module']}', '{$id}') \">
                        <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/delete_s.png' border='0'>
                    </a>
                </div>
             </td>
             ";
        }

        $text = "
        {$deleteBtnText}
        <td width='5'></td>
        </tr>
        ";

        return $text;
    }

    //==================================================================//
    function getListFooter() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "
        </tbody>
        <tfoot>
            <tr>
               <td class='header' colspan='100'>&nbsp;</td>
            </tr>
            <input type='hidden' name='boxChecked' value='0' />
            <input type='hidden' name='task' value='' />
        </tfoot>
        </table>
        </form>
       ";

        return $text;
    }

    //==================================================================//
    function getListSortOrderImage($prefix = 'c') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $text = "
        <td class='header' align='center' width='90'>
            {$fn->getSortText ("{$prefix}.sort_order", "{$ln->gd('cp.header.lbl.order', 'Order')}")}
        </td>
        ";

        return $text;
    }

    //==================================================================//
    function getListSortOrderField($row, $keyFieldName) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $keyFieldValue = $row[$keyFieldName];

        if($modulesArr[$tv['module']]['changeSortOrderFromList']){
            $text = "
            <td align='center' width='85'>
                <a href=\"javascript:DBUtil.moveUp('{$tv['module']}',  {$keyFieldValue})\">
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/arrow_up.gif' border='0' />
                </a>

                <a href=\"javascript:DBUtil.moveDown('{$tv['module']}',{$keyFieldValue})\">
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/arrow_down.gif' border='0' />
                </a>

                <input class='sortOrder' name='sort_order[]' id='fld__sort_order__{$keyFieldValue}'
                    type='text' value='{$row['sort_order']}' size='2'
                    onchange=\"DBUtil.changeSortBoxValue('{$tv['module']}',  {$keyFieldValue})\";
                >
                <!--<input type='hidden' name='{$keyFieldName}[]' value='{$keyFieldValue}'>-->
            </td>
            ";
        } else {
            $text = "
            <td align='center' width='85'>
                {$keyFieldValue}
            </td>";
        }

        return $text;
    }

    //==================================================================//
    /**
     *
     * @global <type> $cpCfg
     * @global <type> $pager
     * @global <type> $tv
     * @global <type> $modulesArrAccess
     * @global <type> $modulesArr
     * @param <type> $rowCounter
     * @return <type>
     */
    function getEditIcon($rowCounter, $row = array()) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');

        $editIcon = "";
        $imgText = "<img src='{$cpCfg['cp.commonImagesPathAlias']}icons/edit_small.gif' border='0'>";
        
        //getGoToEditText();
        if ($modulesArr[$tv['module']]['hasEditInList'] == 1) {
            $editIcon = $pager->getGoToEditText($imgText, $rowCounter, '', $row);
        } else {
            $editIcon = "";
        }

        $text = "
        <td width='10' class='edit'>
            {$editIcon}
        </td>
        ";

        return $text;
    }

    //==================================================================//
    function getGoToDetailText($rowCounter, $title, $cellID = "", $width ="", $row = array()) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');

        $titleText = "";

        $width  = $width  != "" ? "width={$width}"   : "";
        $cellID = $cellID != "" ? "cellID={$cellID}" : "";

        $title = $title != "" ? $title : "---";

        $hasDetailAccess = true;
        if (CP_SCOPE == 'admin' && $cpCfg['cp.hasAccessModule']){
            $hasDetailAccess = getCPModuleObj('core_userGroup')
                               ->model->hasAccessToAction($tv['module'], 'detail');
        }

        if ($tv['action'] != "print" && $hasDetailAccess) {
            $titleText = $pager->getGoToDetailText($title, $rowCounter , "", $row);
        } else {
            $titleText = $title;
        }

        $text = "
        <td {$width} {$cellID}>
            {$titleText}
        </td>
        ";

        return $text;
    }

    //==================================================================//
    function getListImageDisplay($module, $recordType, $id, $ln = "") {
        $db = Zend_Registry::get('db');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');
        $tv = Zend_Registry::get('tv');

        $ln = $ln != "" ? $ln : $tv['lang'];

        $keyFieldName = $modulesArr[$module]['keyField'];

        $SQL = "
        SELECT * FROM media
        WHERE record_id = {$id}
          AND record_type = '{$recordType}'
          AND lang = '{$ln}'
        ";

        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $imgText = "";
        while ($row = $db->sql_fetchrow($result)) {
            $fileName = $mediaArray[$module][$recordType]["thumbFolder"] . $row['file_name'];
            if (file_exists($fileName)) {
                $imageDetails = getimagesize($fileName);
                $imageSizeX   = $imageDetails[0];
                $imageWidth   = $imageSizeX > 150 ? "width='150'" : ""  ;
                $imgText     .= "<img src='{$fileName}' {$imageWidth} ALT='{$row['alt_tag_data']}'/>";
            }
        }

        $text = "
        <td align='center'>
            {$imgText}
        </td>
        ";

        return $text;
    }

    function getPhoneFieldValue($fieldValue1, $fieldValue2, $fieldValue3) {
        $arr = array();
        if ($fieldValue1) {
            $arr[] = $fieldValue1;
        }
        if ($fieldValue2) {
            $arr[] = $fieldValue2;
        }
        if ($fieldValue3) {
            $arr[] = $fieldValue3;
        }
        $fieldValue = join(' - ', $arr);

        return $this->getListDataCell($fieldValue);
    }

    function getDisplayListRows($rows, $message = '') {
        if ($message == '') {
            $message = 'No records found for your search criteria.';
        }

        $rowsText = $rows;
        if (trim($rows) == '') {
            $rowsText = "
            <tr>
            <td colspan='100' class='noRecords'>{$message}</td>
            </tr>
            ";

        }

        return $rowsText;
    }

    function getSummaryRow($valArr, $totalRows) {
        $text = '';
        list($colspan) = array_keys($valArr); //get the first key
        for ($i = 1; $i <= $totalRows; $i++) {
            if (isset($valArr[$i])) {
                $value = $valArr[$i];
                $text .= "<td>{$value}</td>";
            } else {
                $text .= "<td></td>";
            }
        }
        $text = "<tr class='even'>{$text}</tr>";

        return $text;
    }
}

