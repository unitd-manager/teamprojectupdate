<?
class CP_Www_Themes_Manager_Controller extends CP_Www_Lib_ThemeControllerAbstract
{
    /**
     *
     */
    function checkLoggedIn($loginSecType) {
        $this->fns->checkLoggedIn($loginSecType);
    }

    /**
     *
     */
    function isLoggedInStudent() {
        return $this->fns->isLoggedInStudent();
    }

    /**
     *
     */
    function isLoggedInTeacher() {
        return $this->fns->isLoggedInTeacher();
    }

    /**
     *
     */
    function isLoggedInParent() {
        return $this->fns->isLoggedInParent();
    }

    /**
     *
     */
    function getActionButtons() {
        return $this->view->getActionButtons();
    }

    /**
     *
     */
    /*function getAchievementPanel(){
        $modObj = getCPModuleObj('edukite_notice');
        return $modObj->view->getAchievementPanel();
    }*/

}