<?
class CP_Www_Modules_Membership_Contact_Controller extends CP_Common_Modules_Membership_Contact_Controller
{
    //==================================================================//
    function getController() {
        $hook = getCPModuleHook2('membership_contact', 'controller', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        if (!isLoggedInWWW() && ($tv['secType'] == 'Login'
            || $tv['catType'] == 'Login'
            || $tv['subCatType'] == 'Login')                
            ){
            $wLogin = getCPWidgetObj('member_loginForm');
            return $wLogin->getWidget(array(
                'hasRegiserInfo' => $cpCfg['m.membership.allowRegistration']
            ));
        } else if ($tv['secType'] == 'Logout' 
                || $tv['catType'] == 'Logout'
                || $tv['subCatType'] == 'Logout'
                ) {
            $pLogin = getCPPluginObj('member_login');
            return $pLogin->model->getLogout();
            
        } else if ($tv['secType'] == 'Register' || $tv['catType'] == 'Register'){
            $wRegister = getCPWidgetObj('member_registerForm');
            return $wRegister->getWidget(array(
                'showConfirmEmail' => $cpCfg['m.membership.contact.showConfirmEmailInRegForm']
            ));

        } else if ($tv['secType'] == 'Newsletter Signup' 
                || $tv['catType'] == 'Newsletter Signup'
                || $tv['subCatType'] == 'Newsletter Signup'
                ){
            $wNewsletter = getCPWidgetObj('member_newsletterSignup');
            return $wNewsletter->getWidget(array(
                 'showLangPref' => $cpCfg['m.membership.newsletterSignup.showLangPref']
                ,'subscribeToMailChimp' => $cpCfg['m.membership.newsletterSignup.subscribeToMailChimp']
            ));

        } else if ($tv['secType'] == 'Unsubscribe' 
                || $tv['catType'] == 'Unsubscribe'
                || $tv['subCatType'] == 'Unsubscribe'
                ){
            $wUnsubscribe= getCPWidgetObj('member_unsubscribe');
            return $wUnsubscribe->getWidget(array(
            ));

        } else {
            checkLoggedIn();

            if ($tv['secType'] == 'My Profile' || $tv['catType'] == 'My Profile'){
                if ($tv['action'] == 'edit'){
                    $text = $this->getEdit();
                } else {
                    $tv['action'] = 'detail';
                    CP_Common_Lib_Registry::arrayMerge('tv', $tv);
                    $text = $this->getDetail();
                }
                return $text;
            } else if ($tv['secType'] == 'My Orders' || $tv['catType'] == 'My Orders'){
                $wOrders = getCPWidgetObj('ecommerce_orders');
                return $wOrders->getWidget(array(
                ));
            }
        }
    }

    //==================================================================//
    function getDetail(){
        $text = parent::getDetailWithForm();
        return $text;
    }

    //==================================================================//
    function getEdit() {
        $exp = array('hideKeyField' => true);
        $text = parent::getEdit($exp);
        return $text;
    }

    //==================================================================//
    function getChangePassword() {
        $wd = getCPWidgetObj('member_changePassword');
        return $wd->getWidget();
    }

    //==================================================================//
    function getAddPasswordForNonMember() {
        return $this->model->getAddPasswordForNonMember();
    }
}