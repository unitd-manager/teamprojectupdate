<?
include_once(CP_MODULES_PATH . 'Common/ContactLink/Model.php');

class CP_Admin_Modules_Common_testRecipientLink_Model extends CP_Admin_Modules_Common_ContactLink_Model
{
    /**
     *
     */
    function getSQL($linkRecType) {
        $modObj = getCPModuleObj('common_contact');
        return $modObj->model->getSQL($linkRecType);
    }

    /**
     *
     */
    function getSQLForPager() {
        $modObj = getCPModuleObj('common_contact');
        return $modObj->model->getSQLForPager();
    }

    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');

        $modObj = getCPModuleObj('common_contact');
        $modObj->model->setSearchVar($linkRecType);

        $searchVar->sqlSearchVar[] = "c.subscribe = 1";
    }
}
