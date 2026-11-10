<?
/**
 *
 */
class CP_Common_Lib_ListLinkObj
{

    function __construct(){
    }

    //==================================================================//
    function getListHeaderCellLink($linkRecType, $title, $sortText = '') {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $text = "";

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
                <td>
                    <div class='headerSorted'>
                        {$fn->getSortText($sortText, $title, $linkRecType)}
                        {$arrow}
                    </div>
                </td>
                ";
            } else {
                $text = "
                <td>
                    {$fn->getSortText($sortText, $title, $linkRecType)}
                </td>
                ";
            }

        } else {
            $text = "
            <td>
                {$title}
            </td>
            ";
        }

        return $text;
    }

    //==================================================================//
    function getListRowEndLink($linkRecType, $id) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        
        if ($tv['linkSingle'] == 0){
            $curValue = ($linkRecType == 'linked') ? 0 : 1;
            $class = ($linkRecType == 'linked') ? 'removeFromLinked' : 'addToLinked';
            $label = ($linkRecType == 'linked') ? 'Remove' : 'Add';
            
            $url = "{$cpCfg['cp.scopeRootAlias']}index.php?_spAction=createDeleteLinkRecord&linkMasterTableID={$tv['linkMasterTableID']}" .
                   "&record_id={$id}&srcRoom={$tv['srcRoom']}&lnkRoom={$tv['lnkRoom']}&currentValue={$curValue}&showHTML=0";

            $addCell = "
            <td class='txtCenter'>
                <a href='javascript:void(0);' link='{$url}' id='{$id}' class='{$class}'>{$label}</a>
            </td>
            ";
        } else {
            $addCell = "
            <td class='txtCenter'>
                <input type='button' value='Set' recId='{$id}' class='setSingleValue {$tv['lnkRoom']} w50'/>
            </td>
            ";
        }
    
        $text = "
            {$addCell}
            <td width='5'></td>
        </tr>
        ";
        return $text;
    }

    //==================================================================//
    function getListHeaderEndLink($linkRecType) {
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $url = $_SERVER['REQUEST_URI'];
        
        if ($tv['linkSingle'] == 0){
            $curValue = ($linkRecType == 'linked') ? 0 : 1;
            $label = ($linkRecType == 'linked') ? 'Remove All' : 'Add All';

            $url = str_replace("_spAction=link", "_spAction=createDeleteLinkAllRecords", $url);
            $url .= "&currentValue={$curValue}";
            
            $addAllCell = "
            <th class='txtCenter'><a href='javascript:void(0);' link='{$url}' id='addRemoveAll'>{$label}<a/></th>
            ";
        } else {
            $addAllCell = "
            <th></th>
            ";
        }
    
        $text = "
        {$addAllCell}
        <th width='5'></th>
        </tr>
        </thead>
        <tbody>
        ";

        return $text;
    }


    //==================================================================//
    function getListRowHeaderLink($row, $rowCounter, $exp = array()) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $linksArray = Zend_Registry::get('linksArray');
        $pager = Zend_Registry::get('pager');
        $modulesArr = Zend_Registry::get('modulesArr');
        $listObj = Zend_Registry::get('listObj');

        $text = '';
        $keyFieldName = $modulesArr[$tv['lnkRoom']]['keyField'];
        $keyFieldForLinking = $linksArray[$tv['linkName']]['keyFieldForLinking'];
        if ($keyFieldForLinking != '') {
            $keyFieldName = $keyFieldForLinking;
        }
        
        $keyFieldValue = $row[$keyFieldName];
        $hasFlagInList = $modulesArr[$tv['lnkRoom']]['hasFlagInList'];
        $hasFlagInListBlue = isset($exp['hasFlagInListBlue']) ? $exp['hasFlagInListBlue'] : 0;
        $hasFlagInListGreen = isset($exp['hasFlagInListGreen']) ? $exp['hasFlagInListGreen'] : 0;
        
        $listRowClass = $fn->getRowClass($rowCounter%2, $keyFieldValue);

        $exrtaCells = '';
         
        if ($hasFlagInList == 1) {
            $exrtaCells .= $this->getListFlagImage($row['flag'], $keyFieldValue, 'red');
        }
        if ($hasFlagInListBlue == 1) {
            $exrtaCells .= $this->getListFlagImage($row['flag_blue'], $keyFieldValue, 'blue');
        }
        if ($hasFlagInListGreen == 1) {
            $exrtaCells .= $this->getListFlagImage($row['flag_green'], $keyFieldValue, 'green');
        }        
        $text = "
        <tr class='{$listRowClass}' id='listRow__{$keyFieldValue}' recId='{$keyFieldValue}'>
        {$listObj->getListDataCell($rowCounter + 1)}
        {$exrtaCells}
        ";

        return $text;
    }

    //==================================================================//
    function getListHeaderLink($exp = array()) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $hasFlagInList     = $modulesArr[$tv['lnkRoom']]['hasFlagInList'];
        $hasFlagInList     = isset($exp['hasFlagInList'])     ? $exp['hasFlagInList']     : $hasFlagInList;
        $hasFlagInListBlue = isset($exp['hasFlagInListBlue']) ? $exp['hasFlagInListBlue'] : 0;
        $hasFlagInListGreen = isset($exp['hasFlagInListGreen']) ? $exp['hasFlagInListGreen'] : 0;

        $exrtaCells = '';
        if ($hasFlagInList) {
            $exrtaCells .= "<th align='center' width='10'></th>";
        }
        if ($hasFlagInListBlue) {
            $exrtaCells .= "<th align='center' width='10'></th>";
        }
        if ($hasFlagInListGreen) {
            $exrtaCells .= "<th align='center' width='10'></th>";
        } 
            
        $text = "
        <table class='list' cellspacing='1'>
           <thead>
           <tr class='header'>
              <td width='5'> # </td>
              {$exrtaCells}
           ";

        return $text;
    }

    //==================================================================//
    function getListFooterLink() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $fldName = $fn->getReqParam('fldName');
        
        $linkSingleTxt = '';
        
        if ($tv['linkSingle'] == 1){
            $linkSingleTxt = "
            <input type='hidden' id='linkSingleFldId' value='{$fldName}'>
            ";
        }
        
        $text = "
        </tbody>
        <tfoot>
            <tr>
               <td class='header' colspan='100'>&nbsp;</td>
            </tr>
        </tfoot>
        </table>
        {$linkSingleTxt}
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
                   module='{$tv['lnkRoom']}' 
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
    
    function getListFlagImage11($value, $id) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($value == 1) {
            $imgSrc = "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/flag_on.png' border='0'>";
        } else {
            $imgSrc = "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/flag_off.png' border='0'>";
        }

        $text = "
        <td width='10'>
            <div align='center' id='txt__flag__{$id}'>
                <a href=\"javascript:DBUtil.flagRecordFromList('{$tv['lnkRoom']}', '{$id}', '{$value}') \">
                    {$imgSrc}
                </a>
            </div>
        </td>
        ";
        return $text;
    }    
}

