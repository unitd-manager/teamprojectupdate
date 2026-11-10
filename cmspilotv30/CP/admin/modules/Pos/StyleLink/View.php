<?
class CP_Admin_Modules_Pos_StyleLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $modObj = getCPModuleObj('pos_valuelistLink');
        return $modObj->view->getList($dataArray, $linkRecType);
    }

    function getQuickSearch() {
    }
}
