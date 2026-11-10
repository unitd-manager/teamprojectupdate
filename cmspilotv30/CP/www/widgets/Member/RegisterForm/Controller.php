<?
class CP_Www_Widgets_Member_RegisterForm_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $heading     = 'w.member.loginForm.heading';
    var $infoText    = 'w.member.loginForm.info';
    var $formAction  = '/index.php?module=membership_contact&_spAction=add&showHTML=0';
    var $returnUrl   = '';
    var $memberType  = 'membership_contact';

    var $hasSubcols  = false;
    var $col1Css     = 'c50l';
    var $col2Css     = 'c50r';

    var $quickForm   = false;
    var $showConfirmEmail = false;
    var $showPasswordNotes = false;
    
    var $hasFbRegister      = false;
    var $fbRegisterCaption  = 'w.member.registerForm.facebook.heading';
    var $fbRegisterInfoText = 'w.member.registerForm.facebook.info';

}