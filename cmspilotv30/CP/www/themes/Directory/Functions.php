<?
class CP_Www_Themes_Directory_Functions
{

    /**
     *
     * @param <type> $redirectURL
     */
    function checkLoggedIn($loginSecType) {
        $db = Zend_Registry::get('cpUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpUtil = Zend_Registry::get('cpUtil');
    
        if (!isLoggedInWWW()){
            $_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
            $loginUrl = $cpUrl->getUrlBySecType($loginSecType);
            $cpUtil->redirect($loginUrl);
        }
    }
        
    /**
     *
     */
    function checkLoggedInUser() {
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        
        $this->checkLoggedIn('Login');
        
        $userType = $fn->getSessionParam('cpLoginTypeWWW');
        
        if ($userType != 'directory_contact'){
            $_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
            $loginUrl = $cpUrl->getUrlBySecType('Login');
            $cpUtil->redirect($loginUrl);
        }
    }
    
    /**
     *
     */
    function checkLoggedInBusiness() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
    
        $this->checkLoggedIn('Business Login');
        
        $userType = $fn->getSessionParam('cpLoginTypeWWW');
        
        if ($userType != 'directory_businessContact'){
            $_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
            $loginUrl = $cpUrl->getUrlBySecType('Business Login');
            $cpUtil->redirect($loginUrl);
        }
    }

    /**
     *
     */
    function isLoggedInUser() {
        $fn = Zend_Registry::get('fn');
    
        $userType = $fn->getSessionParam('cpLoginTypeWWW');
        
        if ($userType == 'directory_contact'){
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function isLoggedInBusiness() {
        $fn = Zend_Registry::get('fn');
    
        $userType = $fn->getSessionParam('cpLoginTypeWWW');

        if ($userType == 'directory_businessContact'){
            return true;
        } else {
            return false;
        }        
    }

    /**
     *
     */
    function getContactName($name) {
        $nameArr = explode(' ', $name);
        
        if(is_array($nameArr)){
            return $nameArr[0];
        }
    }
}