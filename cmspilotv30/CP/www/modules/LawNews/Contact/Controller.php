<?
class CP_Www_Modules_LawNews_Contact_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    //==================================================================//
    function getController() {
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUrl   = Zend_Registry::get('cpUrl');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');

        if ($tv['catType'] == 'Reset Password'){
            $wResetPassword = getCPWidgetObj('member_resetPassword');
            $text = $wResetPassword->getWidget(array(
            ));
            return $text;
        } else if ($tv['catType'] == 'Forgot Password'){
            $pForgotPassword = getCPPluginObj('member_forgotPassword');
            $text = $pForgotPassword->view->getView(array('showSubmitBtn' => true));
            return $text;

        } else if ($tv['secType'] == 'Login'){
            $referrerPageUrl = $fn->getReferrerPageUrl();
            $returnUrlAfterLogin = ($referrerPageUrl != '') ? $referrerPageUrl : '/';

            $wLogin = getCPWidgetObj('member_loginForm');
            $text = $wLogin->getWidget(array(
                 'registerUrl' => $cpUrl->getUrlBySecType('Subscribe')
                ,'returnUrlAfterLogin' => $returnUrlAfterLogin
            ));
            return $text;

        } else if ($tv['secType'] == 'Subscribe'){
            if(!isLoggedInWWW()){
                return $this->view->getNew();
            } else { //if logged in then go to My Accounts page
                $redirectUrl = $cpUrl->getUrlBySecType('My Account');
                return $cpUtil->redirect($redirectUrl);
            }

        } else if ($tv['secType'] == 'My Account') {
            checkLoggedIn();

            if ($tv['catType'] == 'My Clippings') {
                if($tv['action'] == 'delete'){
                    return $this->model->getDeleteClipping();
                } else {
                    return $this->view->getMyClippings();
                }

            } else if ($tv['catType'] == 'My Profile') {
                /*
                if ($tv['action'] == 'edit'){
                    $text = $this->getEdit();
                } else {
                    $tv['action'] = 'detail';
                    CP_Common_Lib_Registry::arrayMerge('tv', $tv);
                    $text = $this->getDetail();
                }
                */
                $text = $this->getEdit();
                return $text;

            } else if ($tv['catType'] == 'Change Password') {
                $wChngPwd = getCPWidgetObj('member_changePassword');
                $text = $wChngPwd->getWidget(array(
                         'showFormInModal'   => false
                        ,'validateCSRFToken' => true
                        ));
                return $text;

            } else if ($tv['catType'] == 'Logout') {
                $pLogin = getCPPluginObj('member_login');
                return $pLogin->model->getLogout();

            } else {
                return $this->view->getMyAccount();
            }
        }
    }

    //==================================================================//
    function getDetail(){
        $text = parent::getDetail(true);
        return $text;
    }

    //==================================================================//
    function getEdit() {
        $exp = array('hideKeyField' => true);
        $text = parent::getEdit($exp);
        return $text;
    }

    //==================================================================//
    function getSaveToMyClips() {
        return $this->view->getSaveToMyClips();
    }

    //==================================================================//
    function getDelete() {
    }

    //==================================================================//
    function getEmailToFriend() {
        $fn = Zend_Registry::get('fn');
        $wd = getCPWidgetObj('social_emailToFriend');

        $text = $wd->getWidget(array(
             'module' => 'webBasic_content'
            ,'record_id'  => $fn->getReqParam('content_id')
            ,'from_name'  => isset($_SESSION['cpUserFullNameWWW']) ? $_SESSION['cpUserFullNameWWW'] : ''
            ,'from_email' => isset($_SESSION['cpEmail']) ? $_SESSION['cpEmail'] : ''
        ));

        return $text;
    }

    //==================================================================//
    function getChangePassword() {
        $wd = getCPWidgetObj('member_changePassword');
        return $wd->getWidget();
    }
}