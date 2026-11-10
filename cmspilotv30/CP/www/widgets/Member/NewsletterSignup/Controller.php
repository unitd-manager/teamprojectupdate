<?
class CP_Www_Widgets_Member_NewsletterSignup_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $hasPicture      = false;
    var $hasIntroRecord  = false; // intro text above the form (from content record)
    var $heading         = 'w.member.newsletterSignup.heading'; //
    var $infoText        = 'w.member.newsletterSignup.info'; // a short text above the fields
    var $formAction      = '/index.php?widget=member_newsletterSignup&_spAction=add&showHTML=0';
    var $returnUrl       = '';
    var $formStyleCls    = 'yform';
    var $showEmailOnly   = false;
    var $showResetBtn    = false;

    var $hasSubcols      = false;
    var $col1Css         = 'c50l';
    var $col2Css         = 'c50r';
    var $showLangPref    = false;
    var $showCaptcha     = true;
    var $subscribeToMailChimp = false;
}