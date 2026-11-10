<?
class CP_Www_Themes_Manager_Functions
{

    /**
     *
     * @param <type> $redirectURL
     */
    function checkLoggedIn($loginSecType) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('cpUtil');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpUtil = Zend_Registry::get('cpUtil');

        $userType = $fn->getSessionParam('cpLoginTypeWWW');
        $loginUrl = $cpUrl->getUrlBySecType('Login');

        if ($loginSecType == 'edukite_student' && $userType != 'edukite_student'){
            $_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
            $cpUtil->redirect($loginUrl);
        }

        if ($loginSecType == 'edukite_parent' && $userType != 'edukite_parent'){
            $_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
            $cpUtil->redirect($loginUrl);
        }

        if ($loginSecType == 'edukite_teacher' && $userType != 'edukite_teacher'){
            $_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
            $cpUtil->redirect($loginUrl);
        }
    }

    /**
     *
     */
    function isLoggedInStudent() {
        $fn = Zend_Registry::get('fn');

        $userType = $fn->getSessionParam('cpLoginTypeWWW');

        if ($userType == 'edukite_student'){
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function isLoggedInTeacher() {
        $fn = Zend_Registry::get('fn');

        $userType = $fn->getSessionParam('cpLoginTypeWWW');

        if ($userType == 'edukite_teacher'){
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function isLoggedInParent() {
        $fn = Zend_Registry::get('fn');

        $userType = $fn->getSessionParam('cpLoginTypeWWW');

        if ($userType == 'edukite_parent'){
            return true;
        } else {
            return false;
        }
    }
}