<?
class CP_Www_Plugins_Member_ResetPassword_View extends CP_Common_Lib_PluginViewAbstract
{

    /**
     *
     */
    function getView($exp = array()) {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $showSubmitBtn = $fn->getIssetParam($exp, 'showSubmitBtn', false);
        $submitButton  = '';
        if($showSubmitBtn){
            $submitButton = "
            <div class='type-button'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                        <input type='reset' value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
                    </div>
                </div>
            </div>
            ";
        }

        $formAction = '/index.php?plugin=member_resetPassword&_spAction=submit&showHTML=0';
        $text = "
        <form name='resetPasswordForm' id='resetPasswordForm' class='resetPasswordFormEmail yform columnar cpJqForm' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTextBoxRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
                <input type='hidden' name='dialogMessage' value='Please check your email and click the reset password link in the email to change your password' />
                <input type='submit' name='x_submit' class='submithidden' />
                {$submitButton}
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getResetPasswordForm() {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $ran_no    = $fn->getReqParam('ran_no');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        $submitButton = "
        <div class='type-button'>
            <div class='floatbox'>
                <div class='float_left'>
                    <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                </div>
            </div>
        </div>
        ";

        $expPass = array(
             'password' => 1
            ,'required' => true
            ,'disableAutoComplete' => true
        );

        $expConfPass = array(
             'password' => 1
            ,'required' => true
            ,'disableAutoComplete'  => true
        );

        $siteUrl = $cpCfg['cp.siteUrl'];
        $click = "<a href='{$siteUrl}'>click here</a>";
        $returnUrl = "/login/";

        $SQL = "
        SELECT *
        FROM parent
        WHERE reset_password = '{$ran_no}'
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        if ($numRows == 0) {
            $fieldset = "
            <fieldset>
                <div class='resetPasswordText'>
                    <p>Please note that the reset password link you have clicked <br/>is expired.</p>
                </div>
            </fieldset>
            ";
        } else {
            $fieldset = "
            <fieldset>
                <div class='resetPasswordText'>
                    <p>Please enter the new password below. Please make sure that the new password and
                    confirm password are same.</p>
                    <p>After submitting please use the password created now to login in Edukite system.</p>
                </div>

                {$formObj->getTBRow('New Password', 'pass_word', '', $expPass)}
                {$formObj->getTBRow('Confirm Password', 'cpass_word', '', $expConfPass)}
                <input type='hidden' name='ran_no' value='{$ran_no}' />
                <input type='submit' name='x_submit' class='submithidden' />
                <input type='hidden' name='returnUrl' value='{$returnUrl}' />
                {$submitButton}
            </fieldset>
            ";
        }

        $formAction = '/index.php?plugin=member_resetPassword&_spAction=resetSubmit&showHTML=0';
        $text = "
        <form name='resetPasswordForm' id='resetPasswordForm' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
            {$fieldset}
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getSendMessageToAdmin() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $email    = $fn->getReqParam('email');

        $formAction = '/index.php?plugin=member_resetPassword&_spAction=sendMessageToAdminSubmit&showHTML=0';
        $returnUrl = "/eng/login/";

        $text = "
        <form name='messageToAdminForm' id='messageToAdminForm' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTextBoxRow('Parent Name', 'first_name')}
                {$formObj->getTextBoxRow('Student Name', 'student_name')}
                {$formObj->getTextBoxRow($ln->gd('cp.form.fld.email.lbl'), 'email', $email)}
                {$formObj->getTextBoxRow('School Name', 'school_name')}
                {$formObj->getTextAreaRow($ln->gd('cp.form.fld.message.lbl'), 'comments', 'Please add my email in the system and send me the login details.')}
                <input type='hidden' name='dialogMessage' value='{$ln->gd('p.member.emailToAdmin.form.message.success')}' />
                <input type='submit' name='x_submit' class='submithidden' />
                <input type='hidden' name='returnUrl' value='{$returnUrl}' />
            </fieldset>
        </form>
        ";

        return $text;
    }
}