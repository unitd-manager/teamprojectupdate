<?
class CP_Www_Widgets_Member_ResetPassword_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $hasPicture      = false;
    var $hasIntroRecord  = false; // intro text above the form (from content record)
    var $heading         = 'w.member.resetPassword.heading'; //
    var $infoText        = 'w.member.resetPassword.info'; // a short text above the fields
    var $showSuccessMsgInDialog = true;
    var $formAction      = '/index.php?widget=member_resetPassword&_spAction=save&showHTML=0';
    var $returnUrl       = '';

    var $hasSubcols      = false;
    var $col1Css         = 'c50l';
    var $col2Css         = 'c50r';
    var $showCaptcha     = true;
}