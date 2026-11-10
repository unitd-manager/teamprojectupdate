<?
class CP_Www_Themes_Directory_Controller extends CP_Www_Lib_ThemeControllerAbstract
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
    function checkLoggedInUser() {
        $this->fns->checkLoggedInUser();
    }

    /**
     *
     */
    function checkLoggedInBusiness() {
        $this->fns->checkLoggedInBusiness();
    }

    /**
     *
     */
    function isLoggedInUser() {
        return $this->fns->isLoggedInUser();
    }

    /**
     *
     */
    function isLoggedInBusiness() {
        return $this->fns->isLoggedInBusiness();
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
    function getLoginRegisterText() {
        $cpUrl = Zend_Registry::get('cpUrl');

        $text = '';
        if (!$this->isLoggedInUser()){
            $loginUrl = $cpUrl->getUrlBySecType('Login') . '?ret=1';
            $registerUrl = $cpUrl->getUrlBySecType('Register') . '?ret=1';
            $text = "
            Please <a href='{$loginUrl}'>login</a> or <a href='{$registerUrl}'>signup</a> to proceed
            ";
        }

        return $text;
    }
}