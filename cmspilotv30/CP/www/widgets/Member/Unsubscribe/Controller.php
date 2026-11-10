<?
class CP_Www_Widgets_Member_Unsubscribe_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $hasPicture      = false;
    var $hasIntroRecord  = false; // intro text above the form (from content record)
    var $heading         = 'w.member.unsubscribe.heading'; //
    var $infoText        = 'w.member.unsubscribe.info'; // a short text above the fields
    var $formAction      = '/index.php?widget=member_unsubscribe&_spAction=save&showHTML=0';
    var $returnUrl       = '';
    var $sendNotifyEmailToUser = true;

    var $showCaptcha     = true;
}