<?
class CP_Common_Lib_SqlMasterLink
{

    //========================================================//
    function getSQL($linkName) {
        $arrTemp   = explode("#", $linkName);
        $mainRoom  = $arrTemp[0];
        $linkRoom  = $arrTemp[1];

        $clsInst    = includeCPClass('ModuleFns', $linkRoom);
        $funcName   = "getSQL";
        
        if (method_exists($clsInst, $funcName)) {
            $searchText = $clsInst->$funcName();
            return $searchText;
        }
    }
    //==================================================================//
}
