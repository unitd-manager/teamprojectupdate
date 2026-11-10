<?
class CP_Www_Widgets_Social_EmailToFriend_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $hasPicture      = false;
    var $hasIntroRecord  = false; // intro text above the form (from content record)

    var $module          = '';
    var $record_id       = '';

    var $heading         = 'w.social.emailToFriend.heading';
    var $infoText        = 'w.social.emailToFriend.info'; // a short text above the fields
    var $formAction      = '/index.php?widget=social_emailToFriend&_spAction=add&showHTML=0';
    var $from_name       = '';
    var $from_email      = '';
    var $pageToSend      = ''; //url to be sent through email
    var $returnUrl       = '';

    var $hasSubcols      = false;
    var $col1Css         = 'c50l';
    var $col2Css         = 'c50r';
    var $showCaptcha     = true;
}