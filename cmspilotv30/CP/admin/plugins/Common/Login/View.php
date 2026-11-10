<?
class CP_Admin_Plugins_Common_Login_View extends CP_Common_Lib_PluginViewAbstract
{

    /**
     *
     */
    function getLoginForm(){
        $hook = getCPPluginHook('common_login', 'loginForm', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUtil = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');
        $formObj->mode = 'edit';

        if (!isset($_SESSION['returnUrlAfterLogin'])){
            $_SESSION['returnUrlAfterLogin'] =  @$_SERVER['HTTP_REFERER'];
        }

        $formAction = 'index.php?plugin=common_login&_spAction=loginSubmit&showHTML=0';
        $expPass['password'] = 1;
        $expPass['disableAutoComplete'] = $cpCfg['p.common.login.disableAutoCompleteTextFld'];

        $expEmail = array('disableAutoComplete' => $cpCfg['p.common.login.disableAutoCompleteTextFld']);

        $saveLoginText = '';
        if ($cpCfg['cp.adminHasRememberLogin']) {
            $saveLoginText = "
            <div class='type-check'>
                <input type='checkbox' id='fld_save_login' class='checkBox' name='saveLogin' value='1' />
                <label for='fld_save_login'>Save Login</label>
            </div>
            ";
        }

        $text = "
        <div id='loginOuter'>
            <div class='subcolumns'>
                <div class='c38l'>
                    <div class='subcl pr0'>
                        {$ln->gd('loginIntro')}
                    </div>
                </div>
                <div class='c62r'>
                    <div class='subcr'>
                        <form name='loginForm' id='loginForm' class='yform login cpJqForm' method='post' action='{$formAction}'>
                            <fieldset>
                                <h1>Login</h1>
                                <div id='errorDisplayBox'></div>
                                {$formObj->getTextBoxRow('Email', 'email', '', $expEmail)}
                                {$formObj->getTextBoxRow('Password', 'pass_word', '', $expPass)}
                                {$saveLoginText}
                                <input type='submit' name='submit' class='button' value='Login' />
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getChooseUsergroupForm($staffRow){
        $hook = getCPPluginHook('common_login', 'chooseUsergroupForm', $this, $staffRow);
        if($hook['status']){
            return $hook['html'];
        }

        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUtil = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');
        $formObj->mode = 'edit';

        $formAction = 'index.php?plugin=common_login&_spAction=chooseUsergroupSubmit&showHTML=0';
        $saveLogin = $fn->getPostParam('saveLogin', '', true);

        $sqlGroup = "
        SELECT DISTINCT a.user_group_id
              ,b.title
        FROM mod_acc_shop_user_group a
        LEFT JOIN user_group b ON (a.user_group_id = b.user_group_id)
        WHERE a.staff_id = '{$staffRow['staff_id']}'
        ORDER BY b.title
        ";

        $text = "
        <form name='chooseUsergroupForm' id='chooseUsergroupForm' class='yform login cpJqForm' method='post' action='{$formAction}'>
            <fieldset>
                <h1>Choose Usergroup</h1>
                {$formObj->getDDRowBySQL('Usergroup', 'user_group_id', $sqlGroup)}
                <input type='submit' name='submit' class='button' value='Submit' />
                <input type='hidden' name='staff_id' value='{$staffRow['staff_id']}' />
                <input type='hidden' name='saveLogin' value='{$saveLogin}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getMobileOTPValidationForm() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        $email_id = $fn->getReqParam('email_id');
        $formAction = "index.php?plugin=common_login&_spAction=mobileOTPValidationFormSubmit&showHTML=0";

        $text = "
        <div class='col-md-12 txtCenter'>
            <br>
            <img src='/cmspilotv30/CP/admin/images/login/SMS-512.png' alt='otp_mobile_image' class='img-responsive OTPVerifyImage'><br>
            <p></p><br>
            <p class='OTPVerifyContent'>An OTP has been sent to your Mobile Number.<br/>Please enter the 6 digit OTP below for Successful Login</p> 
            <p></p><br>

            <form role='form' class='clearfix cpJqForm' method='post' id='veryfyotp' action='{$formAction}'>
                <div class='row'>                    
                    <div class='form-group col-md-8'>
                        <div class='form-group otp_verify_wrapper'>
                            <input id='fld_otp_verify' name='otp_verify' class='form-control' placeholder='Enter your OTP number' value='' maxlength='6' required='' type='text'>
                        </div>
                    </div>
                    <input type='hidden' name='email_id' value='{$email_id}'/>
                    <input type='hidden' name='callbackAfterSuccess' value='cpt.materialTim.afterOTPVerfied'>
                    <button type='submit' class='btn btn-primary otpVerifyButton'>Verify OTP</button>
                </div>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
     function getPaymentReminder2() {
        $fn      = Zend_Registry::get('fn');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $text = "
        <h3 class='h3'>{$cpCfg['paymentReminderMessage']}</h3>
        ";
        
        return $text;
    }
}
