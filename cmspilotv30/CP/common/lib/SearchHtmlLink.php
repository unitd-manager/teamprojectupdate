<?
class CP_Common_Lib_SearchHTMLLink
{

    //========================================================//
    function getSearchHTML($moduleName, $linkRecType, $linkName = '', $exp = array()) {
        $db = Zend_Registry::get('db');
        $linksArray = Zend_Registry::get('linksArray');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        
        $linkName = ($linkName != '') ? $linkName : $tv['linkName'];
        $linkNameArr = explode("#", $linkName);

        //--------------------------------------------------------//
        $srcRoom           = $linkNameArr[0];
        $lnkRoom           = $linkNameArr[1];
        $linkMasterTableID = $fn->getIssetParam($exp, 'linkMasterTableID', $fn->getReqParam('linkMasterTableID'));
        $spAction          = $fn->getIssetParam($exp, '_spAction', $fn->getReqParam('_spAction', 'link'));
        $fldName           = $fn->getIssetParam($exp, 'fldName', $fn->getReqParam('fldName'));
        //--------------------------------------------------------//
        $isLinkMultiple = $linksArray[$linkName]["linkMultiple"] ;
        $modObj = Zend_Registry::get('currentModule');

        $funcName = "getQuickSearch";

        $searchText = '';
        if (method_exists($modObj->view, $funcName)) {
            $searchText = $modObj->view->$funcName($linkRecType);
        }

        $append = '';
        if ($srcRoom != '') {
            $append .= "<input type='hidden' name='srcRoom' value='{$srcRoom}'>";
        }

        if ($lnkRoom != '') {
            $append .= "<input type='hidden' name='lnkRoom' value='{$lnkRoom}'>";
        }

        if ($spAction != '') {
            $append .= "<input type='hidden' name='_spAction' value='{$spAction}'>";
        }

        if ($linkMasterTableID != '') {
            $append .= "<input type='hidden' name='linkMasterTableID' value='{$linkMasterTableID}'>";
        }

        if ($tv['linkSingle'] != '') {
            $append .= "<input type='hidden' name='linkSingle' value='{$tv['linkSingle']}'>";
        }

        if ($fldName != '') {
            $append .= "<input type='hidden' name='fldName' value='{$fldName}'>";
        }

        $mainUrl = "index.php?srcRoom={$tv['module']}&lnkRoom={$lnkRoom}" .
                    "&_action=list&_spAction=link&_linkMultiple={$isLinkMultiple}".
                    "&linkMasterTableID={$linkMasterTableID}";
        $showSelUrl = $mainUrl . '&openLink=1';
        $showAllUrl = $mainUrl . '&searchDone=1';

        $formId = ($linkRecType == 'linked') ? 'searchLinked' : 'searchNotLinked';

        $text = "
        <div class='floatbox'>
            <div class='float_right'>
                <form name='keywordSearch' id='{$formId}' action='index.php' method='get'>
                    <table class='cpSearch'>
                        <tr>
                            {$searchText}
                            <td>
                                <input type='text' class='keyword' name='keyword'>
                            </td>
                            <td class='vertMiddle'>
                                <input type='submit' value='GO' class='go'>
                            </td>
                        </tr>
                    </table>
                    <input type='hidden' name='module' value='{$tv['module']}'>
                    <input type='hidden' name='_topRm' value='{$tv['topRm']}'>
                    <input type='hidden' name='_action' value='list'>
                    <input type='hidden' name='searchDone' value='1'>
                    <input type='hidden' name='linkRecType' value='{$linkRecType}'>
                    <input type='hidden' name='showHTML' value='0'>
                    {$append}
                </form>
            </div>
        </div>
        ";

        return $text;
    }

    //==================================================================//
}