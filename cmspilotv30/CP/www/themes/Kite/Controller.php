<?
class CP_Www_Themes_Kite_Controller extends CP_Www_Lib_ThemeControllerAbstract
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

}