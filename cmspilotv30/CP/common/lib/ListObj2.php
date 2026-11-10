<?
class CP_Common_Lib_ListObj2
{
    private $config = array();
    private $fieldsArr = array();
    private $dataArr = array();
    private $rowId = 0;

    function __construct(){
        $this->setConfigArr();
    }

    function setConfigArr($arr = array()) {
        $defOptsArr = array(
             'showKeyColumn' => true
            ,'showRowNum' => true
            ,'noScrollableTable' => false
        );
        $arr = array_merge($defOptsArr, $arr);
        $this->config = $arr;
    }

    function setFieldsArr($arr) {
        $this->fieldsArr = $arr;
    }

    function setDataArr($arr) {
        $this->dataArr = &$arr;
    }

    function getFieldObj($name, $optsArr = array()) {
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $defOptsArr = array(
             'name' => $name
            ,'title' => $cpUtil->getLabelFromFieldName($name)
            ,'type' => 'text' //text/number/date/button/sortFld
            ,'hasDetailLink' => false
            ,'align' => 'left' //left/right/center
            ,'class' => 'fld-' . $name

            ,'header-class' => 'fld-' . $name
            ,'summaryVal' => ''
            //callback function to manipulate data. Array param
            //array[0] = object, array[1] = methodName
            ,'dataCb' => null

            //callback function to format data. Array param
            //array[0] = object, array[1] = methodName
            ,'formatCb' => null
            ,'sort' => ''
            ,'editable' => false
            ,'displayDecimalLength' => $cpCfg['cp.displayDecimalLength']
        );

        $arr = array_merge($defOptsArr, $optsArr);
        if ($arr['type'] == 'button' && !isset($optsArr['align'])) {
            $arr['align'] = 'center';
        }
        if ($arr['type'] == 'number' && !isset($optsArr['align'])) {
            $arr['align'] = 'right';
        }

        return $arr;
    }

    function addFld($name, $optsArr = array()) {
        $this->fieldsArr[$name] = $this->getFieldObj($name, $optsArr);
    }

    private function setRowId($row) {
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');

        $keyField = $modulesArr[$tv['module']]['keyField'];
        $this->rowId = $row[$keyField];
    }

    public function render() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $text = '';
        $header = '';
        $rows = '';
        $rowText = '';

        $modulesArr = Zend_Registry::get('modulesArr');

        $keyField = $modulesArr[$tv['module']]['keyField'];

        //get header row
        foreach ($this->fieldsArr as $fieldName => $fo) {
            switch ($fo['type']) {
                case 'sortFld':
                    $header .= $this->getHeaderCellSortOrder($fo);
                    break;

                default:
                    $header .= $this->getHeaderCell($fo);
                    break;
            }
        }

        $rowCounter = 0;
        //get data row
        foreach ($this->dataArr as $row){
            $this->setRowId($row);

            $rowText = '';
            foreach ($this->fieldsArr as $fieldName => $fo) {
                $formatCb = $fo['formatCb'];
                $hasFormatCb = $formatCb ? true : false;

                $dataCb = $fo['dataCb'];
                $hasDataCb = $dataCb ? true : false;

                $fieldValue = '';
                if ($hasDataCb) {
                    //the callback function must have $row & $fo params as reference
                    $arr = array($fieldName, &$row, &$fo);
                    $fieldValue = call_user_func_array($dataCb, $arr);
                } else {
                    $fieldValue = $row[$fieldName];
                }
                if ($hasFormatCb) {
                    //the callback function must have $fo params as reference
                    $arr = array($fieldName, $row, &$fo);
                    $fieldValue = call_user_func_array($formatCb, $arr);
                }

                switch ($fo['type']) {
                    case 'text':
                        $rowText .= $this->getDataCell($fieldValue, $fo, $rowCounter);
                        break;

                    case 'number':
                        if ($fieldValue);
                        if (!$hasFormatCb) {
                            $fieldValue = $fn->getFormatNumber($fieldValue, $fo['displayDecimalLength']);
                        }

                        $rowText .= $this->getDataCell($fieldValue, $fo, $rowCounter);
                        break;

                    case 'date':
                        $rowText .= $this->getDataCell($fieldValue, $fo, $rowCounter);
                        break;

                    case 'button':
                        $rowText .= $this->getDataCell($fieldValue, $fo, $rowCounter);
                        break;

                    case 'sortFld':
                        $rowText .= $this->getDataCellSortOrder($row, $keyField);
                        break;

                    default:
                        $rowText .= $this->getDataCell($fieldValue, $fo, $rowCounter);
                        break;
                }
            }

            $rows .= "
            {$this->getDataRowStart($row, $rowCounter)}
            {$rowText}
            {$this->getDataRowEnd($this->rowId)}
            ";
        	$rowCounter++;

        }//dataArr

        $text = "
        {$this->getTableHeader()}
        {$this->getHeaderRowStart()}
        {$header}
        {$this->getHeaderRowEnd()}
        {$this->getSummaryRow()}
        {$rows}
        {$this->getTableFooter()}
        ";
        return $text;
    }

    function getHeaderCell($fieldObj) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $text = '';

        $title = $fieldObj['title'];

        $classArr = array();
        $classArr[] = $fieldObj['header-class'];
        $classArr[] = $fieldObj['align'] != '' ? 'al-' . $fieldObj['align'] : '';
        $class = $fn->getClassTextFromArr($classArr);

        $sortText = $fieldObj['sort'];
        if ($sortText != '' && $tv['action'] != 'print') {
            $sortTextTemp = str_replace(' asc', '', $sortText);
            $sortTextTemp = str_replace(' desc', '', $sortText);

            $pos     = $cpUtil->stringPosition($tv['sortOrder'], $sortTextTemp);
            $posDesc = $cpUtil->stringPosition($tv['sortOrder'], 'desc');

            $arrow = ($posDesc >= 0) ?
            "<span style='font-size:15px;'>&nbsp;&darr;</span>" :
            "<span style='font-size:15px;'>&nbsp;&uarr;</span>";

            if ($pos >= 0) {
                $text = "
                <th{$class}>
                    <div class='headerSorted'>
                        {$fn->getSortText($sortText, $title)}
                        {$arrow}
                    </div>
                </th>
                ";
            } else {
                $text = "
                <th{$class}>
                    {$fn->getSortText($sortText, $title)}
                </th>
                ";
            }

        } else {
            $text = "
            <th{$class}>
                {$title}
            </th>
            ";
        }

        return $text;
    }

    function getDataCell($fieldValue, $fieldObj, $rowCounter) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $pager = Zend_Registry::get('pager');

        $hasDetailLink = $fieldObj['hasDetailLink'];
        $editable = $fieldObj['editable'];
        $fieldName = $fieldObj['name'];

        $classArr = array();
        $classArr[] = $fieldObj['class'];
        $classArr[] = $fieldObj['align'] != '' ? 'al-' . $fieldObj['align'] : '';
        $classArr[] = $fieldObj['editable'] ? 'cp-editable' : '';

        $fldId = '';
        if ($editable) {
            $fldId = $fieldName . '__row-' . $this->rowId;
            $fldId = "id='{$fldId}'";
        }

        if ($hasDetailLink) {
            $fieldValue = $fieldValue != '' ? $fieldValue : "---";
            $fieldValue = $pager->getGoToDetailText($fieldValue, $rowCounter , '');
            $classArr[] = 'detail';
        }
        $class = $fn->getClassTextFromArr($classArr);

        $text = "
        <td{$class} {$fldId}>{$fieldValue}</td>
        ";

        return $text;
    }

    function getTableHeader() {
        $tv = Zend_Registry::get('tv');
        $pager = Zend_Registry::get('pager');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $noScrollableTable = $this->config['noScrollableTable'];

        $scrollableTableText = "scrollabletable='1'";
        if ($noScrollableTable) {
            $scrollableTableText = '';
        }
        $searchQueryString = $pager->removeQueryString(array("_action"));
        $formAction = $searchQueryString . "&_spAction=saveList&showHTML=0";

        $text = "
        <form name='list' action='{$formAction}' method='post'>
            <table class='list' {$scrollableTableText} id='bodyList' cellspacing='1'>
            <thead>
            <tr>
        ";

        return $text;
    }

    function getHeaderRowStart($extraParam = array()) {
        $tv = Zend_Registry::get('tv');
        $pager = Zend_Registry::get('pager');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $hasFlagInList = $modulesArr[$tv['module']]['hasFlagInList'];

        $hasRowNumber  = isset($extraParam['hasRowNumber'])  ? $extraParam['hasRowNumber']  : true;
        $hasEditInList = isset($extraParam['hasEditInList']) ? $extraParam['hasEditInList'] : true;
        $hasFlagInList = isset($extraParam['hasFlagInList']) ? $extraParam['hasFlagInList'] : $hasFlagInList;
        $noScrollableTable = isset($extraParam['noScrollableTable']) ? $extraParam['noScrollableTable'] : false;

        $scrollableTableText = "scrollabletable='1'";
        if ($noScrollableTable) {
            $scrollableTableText = '';
        }

        $searchQueryString = $pager->removeQueryString(array("_action"));
        $formAction = $searchQueryString . "&_spAction=saveList&showHTML=0";

        $cbText        = '';
        $rowNumberText = '';
        $editRowText   = '';
        $flagCell      = '';

        if ($modulesArr[$tv['module']]['hasCheckboxInList'] == 1) {
            $cbText = "
            <td class='header' align='center' width='10'>
               <input class='check' type='checkbox' name='toggle' value='' />
            </td>
            ";
        }

        if ($hasRowNumber) {
            $rowNumberText = "
            <td width='5'> # </td>
            ";
        }

        if ($hasEditInList) {
            $editRowText = "
            <td align='center' width='10'></td>
            ";
        }

        if ($hasFlagInList) {
            $flagCell = "<td align='center' width='10'></td>";
        }

        $text = "
        <thead>
        <tr>
        {$cbText}
        {$rowNumberText}
        {$editRowText}
        {$flagCell}
        ";

        return $text;
    }

    function getHeaderRowEnd() {
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');

        $deleteBtn = "";

        $modName = ($tv['spAction'] == 'link') ? $tv['lnkRoom'] : $tv['module'];

        if ($tv['action'] != "print" && $modulesArr[$modName]['hasDeleteInList'] == 1
                                          && $cpCfg['cp.hasDeleteInList'] == 1)
        {
            $deleteBtn = $this->getHeaderCell("Delete", "", "headerCenter");
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

    function getDataRowStart($row, $rowCounter, $listRowClass = '', $extraParam = array()) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $modulesArr = Zend_Registry::get('modulesArr');

        $hasFlagInList     = $modulesArr[$tv['module']]['hasFlagInList'];
        $hasCheckboxInList = $modulesArr[$tv['module']]['hasCheckboxInList'];

        $hasFlagInList     = isset($extraParam['hasFlagInList'])     ? $extraParam['hasFlagInList']     : $hasFlagInList;
        $hasCheckboxInList = isset($extraParam['hasCheckboxInList']) ? $extraParam['hasCheckboxInList'] : $hasCheckboxInList;
        $keyFieldValue     = isset($extraParam['keyFieldValue'])     ? $extraParam['keyFieldValue']     : $row[$modulesArr[$tv['module']]['keyField']];

        $hasRowNumber  = isset($extraParam['hasRowNumber'])  ? $extraParam['hasRowNumber']  : true;
        $hasEditInList = isset($extraParam['hasEditInList']) ? $extraParam['hasEditInList'] : true;

        $text = '';
        $listRowClass  = $fn->getRowClass($rowCounter%2, $keyFieldValue, $listRowClass);

        //--------------------------------------------------------------//
        $rowNumberText = '';
        $exrtaCells = '';
        if ($tv['action'] != 'print') {
           if ($hasCheckboxInList == 1) {
              $exrtaCells .= $this->getListCheckBox($rowCounter, $keyFieldValue, $listRowClass);
           }

           if ($hasEditInList) {
               $exrtaCells .= $this->getEditIcon($rowCounter);
           }

           if ($hasFlagInList == 1) {
              $exrtaCells .= $this->getListFlagImage($row['flag'], $keyFieldValue);
           }

        }

        if ($hasRowNumber) {
            $class = "listRowIndex__" . $keyFieldValue;
            $rowNumberText = "
            <td class='{$class}'>" . ($rowCounter + 1) . "</td>
            ";
        }

        $text = "
        <tr class='{$listRowClass}' id='listRow__{$keyFieldValue}'>
            {$rowNumberText}
            {$exrtaCells}
        ";

        return $text;
    }

    function getDataRowEnd($id) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $showDelete = false;
        $deleteBtnText = "";

        $modName = ($tv['spAction'] == 'link') ? $tv['lnkRoom'] : $tv['module'];

        if ($tv['action'] != "print" && $modulesArr[$modName]['hasDeleteInList'] == 1
                                          && $cpCfg['cp.hasDeleteInList'] == 1)
        {
            $showDelete = true;
        }

        if ($showDelete) {
             $deleteBtnText = "
             <td>
                <div align='center'>
                    <a href=\"javascript:DBUtil.deleteRecordFromList('{$tv['module']}', '{$id}') \">
                        <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/delete_s.png' border='0'>
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

    /**
     *
     */
    function getEditIcon($rowCounter) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');

        $editIcon = "";
        $imgText = "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/edit_small.gif' border='0'>";

        //getGoToEditText();
        if ($modulesArr[$tv['module']]['hasEditInList'] == 1) {
            $editIcon = $pager->getGoToEditText($imgText, $rowCounter);
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

    function getTableFooter() {
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

    function getHeaderCellSortOrder($fieldObj) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sortText = $fieldObj['sort'];
        $text = "
        <td class='header' align='center' width='90'>
            {$fn->getSortText ("{$sortText}", "Order")}
        </td>
        ";

        return $text;
    }

    function getDataCellSortOrder($row, $keyFieldName) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $keyFieldValue = $row[$keyFieldName];

        $text = "
        <td align='center' width='85'>
            <a href=\"javascript:DBUtil.moveUp('{$tv['module']}',  {$keyFieldValue})\">
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/arrow_up.gif' border='0' />
            </a>

            <a href=\"javascript:DBUtil.moveDown('{$tv['module']}',{$keyFieldValue})\">
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/arrow_down.gif' border='0' />
            </a>

            <input class='sortOrder' name='sort_order[]' id='fld__sort_order__{$keyFieldValue}'
                type='text' value='{$row['sort_order']}' size='2'
                onchange=\"DBUtil.changeSortBoxValue('{$tv['module']}',  {$keyFieldValue})\";
            >
            <input type='hidden' name='{$keyFieldName}[]' value='{$keyFieldValue}'>
        </td>
        ";

        return $text;
    }

    //==================================================================//
    function getListPublishedImage($value, $id, $editable = 1) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $img            = ($value == 1) ? "published" : "not_published";
        $publishedIcons = "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/{$img}.png' title='upload' border='0'>";
        $publishedIcons = $this->getListPublishedImageIcon($tv['module'], $id, $value, $editable);

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
    function getListPublishedImageIcon($module, $id, $value, $editable = 1) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $imgReload = "";

        if ($value == 1) {
            $imgSrc = "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/published.png' title='upload' border='0'>";
        } else {
            $imgSrc = "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/not_published.png' border='0'>";
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

    function getSummaryRow() {
        $fn = Zend_Registry::get('fn');

        $text = '';

        $totalRows = count($this->fieldsArr);
        foreach ($this->fieldsArr as $fieldName => $fieldObj) {
            $classArr = array();
            $classArr[] = $fieldObj['align'] != '' ? 'al-' . $fieldObj['align'] : '';
            $class = $fn->getClassTextFromArr($classArr);
            if ($fieldObj['summaryVal']) {
                $text .= "<td{$class}>{$fieldObj['summaryVal']}</td>";
            } else {
                $text .= "<td></td>";
            }
        }
        $text = "
        <tr class='even'>
        <td></td>
        <td></td>
        {$text}
        <td></td>
        </tr>
        ";

        return $text;
    }

}

