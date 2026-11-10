<?
class CP_Admin_Modules_AceIms_AssessorLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getList($dataArray, $linkRecType) {
        $modObj = getCPModuleObj('aceIms_teacherLink');
        return $modObj->view->getList($dataArray, $linkRecType);
    }

    /**
     *
     */
    function getQuickSearch() {
    }
}
