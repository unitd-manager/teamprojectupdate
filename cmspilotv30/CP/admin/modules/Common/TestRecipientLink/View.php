<?
include_once(CP_MODULES_PATH . 'Common/ContactLink/View.php');
class CP_Admin_Modules_Common_testRecipientLink_View extends CP_Admin_Modules_Common_ContactLink_View
{
    function getQuickSearch() {
        $modObj = getCPModuleObj('common_contact');
        return $modObj->view->getQuickSearch();
    }
}
