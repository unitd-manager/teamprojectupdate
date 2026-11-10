<?
class CP_Www_Themes_Kite_Model extends CP_Www_Lib_ThemeModelAbstract
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