<?
class CP_Www_Themes_Party_Functions
{
    function getModuleWebBasicContentControllerHook($contObj) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($tv['catType'] == 'Faq') {
            $text = $contObj->getList('faqList');
            return $text;
        } else if ($tv['currentViewRecType'] == 'How It Works') {
            $text = getCPThemeObj($cpCfg['cp.theme'])->view->getHowItWorks();
            return $text;

        } else if ($tv['currentViewRecType'] == 'Charity') {
            $text = getCPModuleObj('party_charity')->view->getDetail();
            return $text;

        } else {
            return false;
        }
    }

    /**
     *
     */
    function getModuleMembershipContactControllerHook($contObj) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

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
            ));

        } else if ($tv['secType'] == 'Newsletter Signup'
                || $tv['catType'] == 'Newsletter Signup'
                || $tv['subCatType'] == 'Newsletter Signup'
                ){
            $wNewsletter = getCPWidgetObj('member_newsletterSignup');
            return $wNewsletter->getWidget(array(
                'showLangPref' => $cpCfg['m.membership.newsletterSignup.showLangPref']
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

            if ($tv['catType'] == 'My Profile' || $tv['secType'] == 'My Profile'){
                if ($tv['action'] == 'edit'){
                    $text = $contObj->getEdit();
                } else {
                    $tv['action'] = 'detail';
                    CP_Common_Lib_Registry::arrayMerge('tv', $tv);
                    $text = $contObj->getDetail();
                }
                return $text;
            } else if ($tv['secType'] == 'My Orders' || $tv['catType'] == 'My Orders'){
                $wOrders = getCPWidgetObj('ecommerce_orders');
                return $wOrders->getWidget(array(
                ));
            }
        }
    }

    function getModuleWebBasicHomeListHook($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $fn = Zend_Registry::get('fn');

        $paymentThanks = $fn->getReqParam('paymentThanks');
        $remindMePopup = $fn->getReqParam('remindMePopup');
        $logout = $fn->getReqParam('logout');

        //party remider from reminder email
        $party_reminder_id = $fn->getReqParam('prid'); // party_reminder_id
        if ($party_reminder_id != '') {
            $fn->setSessionParam('party_reminder_id', $party_reminder_id);
        } else {
            $fn->setSessionParam('party_reminder_id', '');
        }
        
        $paymentThanksFld = "<input type='hidden' id='paymentThanks' value='{$paymentThanks}'>";
        $remindMePopupFld = "<input type='hidden' id='remindMePopup' value='{$remindMePopup}'>";

        $wRecord = getCPWidgetObj('content_record');
        $twopresent = $wRecord->getWidget(array(
             'contentType' => 'What Is Twopresents'
            ,'recordTitleHeadTag' => 'h1'
        ));

        $wRecord = getCPWidgetObj('content_record');
        $howItWork = $wRecord->getWidget(array(
             'contentType' => 'How It Works'
            ,'recordTitleHeadTag' => 'h1'
        ));

        $wRecord = getCPWidgetObj('content_record');
        $getStarted = $wRecord->getWidget(array(
             'contentType' => 'Get Started'
            ,'recordTitleHeadTag' => 'h1'
        ));

        /** create an instance of the widget **/
        $exp = array(
             'speed' => $cpCfg['w.media.anythingSlider.speed']
            ,'controlNav' => false
            ,'effect' => 'fade'
        );
        if ($paymentThanks == 1) {
            $dataArr = array();
            $dataArr[0]['pic']     = $fn->getVersionUrl('/www/images/payment-thanks-banner.jpg');
            $dataArr[0]['link']    = '';
            $dataArr[0]['caption'] = '';

            $exp['dataArray'] = $dataArr;
            $exp['directionNav'] = false;

        } else if ($logout == 1) {
            $dataArr = array();
            $dataArr[0]['pic']     = $fn->getVersionUrl('/www/images/logout-thanks-banner.jpg');
            $dataArr[0]['link']    = '';
            $dataArr[0]['caption'] = '';

            $exp['dataArray'] = $dataArr;
            $exp['directionNav'] = false;
        }

        $wSlideshow = getCPWidgetObj('media_nivoSlider');
        $slideshow = $wSlideshow->getWidget($exp);

        $urlGetStarted = $cpUrl->getUrlBySubCatType('Content', 'Select Card', 'Party');
        
        //charity & partners
        $template = "
        <[[rowTag]]>
            <div class='inner'>
                [[titleAbovePic]]
                [[desc]]
                [[pic]]
            </div>
        </[[rowTag]]>        
        ";                
        $wRecord = getCPWidgetObj('content_record');
        $charity = $wRecord->getWidget(array(
            'contentType' => 'Homepage Charity',
            'recordTitleHeadTag' => 'h2',
            'showPicInDesc' => false,
            'template' => $template,
            'mediaExp' => array('folder' => 'large'),
        ));
        
        $wRecord = getCPWidgetObj('content_record');
        $partner = $wRecord->getWidget(array(
            'contentType' => 'Homepage Partner',
            'recordTitleHeadTag' => 'h2',
            'showPicInDesc' => false,
            'template' => $template,
            'mediaExp' => array('folder' => 'large'),
        ));
        
        $text = "
        {$slideshow}
        <div class='home-content subcolumns'>
            <div class='c33l col1'>
                <div class='subcl'>
                    {$twopresent}
                </div>
            </div>
            <div class='c33l col2 howItWork'>
                <div class='subcl'>
                    {$howItWork}
                </div>
            </div>
            <div class='c33r col3 getStarted'>
                <div class='subcr'>
                    {$getStarted}
                    <a href='{$urlGetStarted}'
                       class='button-www'>
                        <span>{$ln->gd('cp.btn.getStarted')}</span>
                    </a>
                </div>
            </div>
        </div>
        {$paymentThanksFld}
        {$remindMePopupFld}
            
        <div class='charity-partner-container'>
            <div id='our-charity'>
                {$charity}
            </div>
            <div id='our-partner'>
                {$partner}
            </div>
        </div>
        ";

        return $text;
    }

    function getLoginLink() {
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $loginUrl = $cpUrl->getUrlBySecType('Login');
        $registerUrl = $cpUrl->getUrlBySecType('Register');
        $text = "
        {$this->getSocialMediaLinks()}
        <a class='btnLogin' href='{$loginUrl}'>
            <span>{$ln->gd('w.member.loginForm.form.lbl.login')}</span>
        </a>
        ";
        return $text;
    }

    function getSocialMediaLinks() {
        $ln = Zend_Registry::get('ln');

        $text = "
        {$ln->gd('cp.socialMediaLinks')}
        ";
        return $text;
    }

    function getWidgetMemberLoginFormRowsHTMLHook($viewObj) {
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');

        $c = &$viewObj->controller;

        $expPass['password'] = 1;

        $infoText = '';
        $titleText = '';
        $regiserInfo = '';

        $subTitle = '';
        if ($tv['secType'] != 'Login') {
            $titleText = '<h1>' . $ln->gd2('w.member.loginForm.form.heading') . '</h1>';
            $infoText = $ln->gd2($c->infoText);

            if ($c->hasRegiserInfo){
                $regiserInfo = $this->getWidgetMemberLoginFormRegisterInfoHook($viewObj);
            }
            $subTitle = "<h3>{$ln->gd2('w.member.loginForm.heading')}</h3>";

        } else { // if secType = Login ie. general login (not through Get Started process)
            $titleText = '<h1>' . $ln->gd2('w.member.loginForm.heading') . '</h1>';
            $infoText = $ln->gd2('w.member.loginForm.infoText');
        }

        if ($infoText != ''){
            $infoText = "<div class='infoText'>{$infoText}</div>";
        }

        if ($c->returnUrlAfterLogin != ''){
            $_SESSION['cpReturnUrlAfterLogin'] = $c->returnUrlAfterLogin;
        }

        $forgotText = '';
        if ($c->hasForgotPass){
            $url = '/index.php?plugin=member_forgotPassword&_spAction=view&showHTML=0';
            $forgotText = "
            <div class='forgotPasswordLink mt20'>
                <a href='javascript:void(0)' link='{$url}' class='jqui-dialog-form' formId='forgotPasswordForm'
                    w='400' h='250' title='{$ln->gd('p.member.forgotPassword.form.heading')}'>
                    <label>{$ln->gd('w.member.loginForm.form.lbl.forgotPassword')}</label>
                </a>
            </div>
            ";
        }

        $retUrl = $fn->getSessionParam('cpReturnUrlAfterLogin');
        if ($retUrl == '') {
            if ($tv['secType'] == 'Party') { //ie. Get Started
                $retUrl = $cpUrl->getUrlByCatType('Party Detail', 'Party');
            } else {
                $retUrl = $cpUrl->getUrlByCatType('Party List', 'Dashboard');
            }
        }


        $retUrlText = "<input type='hidden' name='returnUrl' value='{$retUrl}'>";


        $loginType = '';
        if (is_array($c->loginTypeArr)){
            $exp = array('useKey' => true, 'hideFirstOption' => true);
            $loginType = $formObj->getDDRowByArr($ln->gd('cp.form.fld.loginType'), 'loginType', $c->loginTypeArr, $c->loginType, $exp);
        } else {
            $loginType = "<input type='hidden' name='loginType' value='{$c->loginType}' />";
        }

        $text = "
        {$titleText}
        <div class='mt20'>{$infoText}</div>
        {$regiserInfo}
        <form name='loginForm' id='loginForm' class='yform cpJqForm' method='post'
              action='{$c->formAction}'>
            {$subTitle}
            <fieldset>
                {$formObj->getTextBoxRow($ln->gd('cp.form.fld.emailAddress.lbl'), 'email')}
                {$formObj->getTextBoxRow($ln->gd('cp.form.fld.password.lbl'), 'pass_word', '', $expPass)}
                {$loginType}
                {$forgotText}

                <a class='button-www mt20 submit' href='javascript:void(0)'
                   onclick=\"$('#loginForm').submit();\">
                    <span>{$ln->gd('cp.form.btn.submit')}</span>
                </a>
                <input type='submit' name='x_submit' class='submithidden' />
                {$retUrlText}
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getWidgetMemberLoginFormRegisterInfoHook($viewObj) {
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $c = &$viewObj->controller;

        $url = ($c->registerUrl != '') ? $c->registerUrl : $cpUrl->getUrlBySecType('Register');

        $text = "
        <div class='registerInfo'>
            <form class='yform'>
            <fieldset>
                <h3>{$ln->gd($c->registerCaption)}</h3>
                <a class='button-www mt10 submit' href='{$url}'>
                    <span>{$ln->gd('m.membership.contact.form.new.heading')}</span>
                </a>
            </fieldset>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getLogoutLinkHook() {
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');

        $logoutUrl = '/index.php?plugin=member_login&_spAction=logout';
        $dashboardUrl = $cpUrl->getUrlByCatType('Party List', 'Dashboard');

        $clsDashboard = '';
        if ($tv['secType'] == 'Dashboard') {
            $clsDashboard = 'dashboard';
        }

        $text = "
        {$this->getSocialMediaLinks()}
        {$ln->gd('p.member.login.lbl.welcome')} {$_SESSION['cpUserFullNameWWW']} |
        <a href='{$dashboardUrl}' class='{$clsDashboard}'><span>{$ln->gd('p.member.login.lbl.dashboard')}</span></a> |
        <a class='btnLogout' href='{$logoutUrl}'>
            <span>{$ln->gd('p.member.login.lbl.logout')}</span>
        </a>
        ";
        return $text;
    }

    function getCssTextForEmails(){
        $text = "
    	.mainTable {
    	    padding:10px;
    	    width:685px;
    	}

        #emailContent {
            padding:10px 0 30px 0;
            border-top: 0px dotted #ccc;
        }

    	#emailContent table td,
    	#emailContent table th {padding:5px 5px 5px 0;}

        #emailContent table.thinlist {
            width:100%;
            border-collapse: collapse;
        }

        #emailContent table.thinlist th, #emailContent table.thinlist td {
            padding:5px;
            border:1px solid #ccc;
            vertical-align: top;
        }

        #emailContent thead th {
            background: #EFEFEF;
            text-align: left;
        }

        #emailContent .emailFooter {
            border-top: 1px dotted #ccc;
        }

        #emailContent .txtRight {text-align:right;}
        #emailContent .txtCenter {text-align:center;}
        #emailContent .bold {
            font-weight: bold;
        }
        ";

        return $text;
    }

}