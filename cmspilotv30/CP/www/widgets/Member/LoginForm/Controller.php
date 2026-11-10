<?
class CP_Www_Widgets_Member_LoginForm_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $hasPicture      = false;
    var $hasIntroRecord  = false; // intro text above the form (from content record)
    var $heading         = 'w.member.loginForm.heading'; // a short text above the fields in the login form
    var $infoText        = 'w.member.loginForm.form.info'; // a short text above the fields in the login form
    var $hasForgotPass   = true; // whether to show the forgot password link or not
    var $formAction      = '/index.php?plugin=member_login&_spAction=loginSubmit&showHTML=0';
    var $returnUrl       = '';
    var $saveLogin       = true;
    var $modal           = false;

    var $hasSubcols      = false;
    var $col1Css         = 'c50l';
    var $col2Css         = 'c50r';

    var $hasRegiserInfo  = true;
    var $loginType       = 'membership_contact';
    var $registerUrl     = '';
    var $returnUrlAfterLogin  = '';
    var $registerCaption  = 'w.member.loginForm.register.heading';
    var $registerInfoText = 'w.member.loginForm.register.info';
    var $registerBtnText  = 'w.member.loginForm.btn.register';
    var $loginTypeArr     = '';

    var $hasFbLogin       = false;
    var $fbLoginCaption   = 'w.member.loginForm.facebook.heading';
    var $fbLoginInfoText  = 'w.member.loginForm.facebook.info';

}