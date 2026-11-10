<?
class CP_Www_Themes_Edukloud_Controller extends CP_Www_Lib_ThemeControllerAbstract
{
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
    function checkLoggedIn($loginSecType) {
        $this->fns->checkLoggedIn($loginSecType);
    }


}