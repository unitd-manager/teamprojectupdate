<?
class CP_Www_Themes_LawNews_Controller extends CP_Www_Lib_ThemeControllerAbstract
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
}