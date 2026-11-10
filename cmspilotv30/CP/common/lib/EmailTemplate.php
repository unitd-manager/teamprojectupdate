<?
/**
 *
 */
class CP_Common_Lib_EmailTemplate
{
    // Store the single instance of the class
    private static $instance;
    public $fromName;
    public $fromEmail;
    public $toName;
    public $toEmail;
    public $subject;
    public $message;
    public $footer;
    public $css;
    public $showMsgSettingsNotes = 1;
    public $attachmentArray;
    public $ccEmail;
    public $bccEmail;
    public $replyTo;

    public function __construct($params = '') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $this->css      = $fn->getIssetParam($params, 'css', $this->getCssTextForEmails());

        if(!is_array($params)){
            return;
        }

        $this->fromName        = $fn->getIssetParam($params, 'fromName', $cpCfg['cp.companyName']);
        $this->fromEmail       = $fn->getIssetParam($params, 'fromEmail', $cpCfg['cp.adminEmail']);
        $this->toName          = $fn->getIssetParam($params, 'toName');
        $this->toEmail         = $fn->getIssetParam($params, 'toEmail');
        $this->subject         = $fn->getIssetParam($params, 'subject');
        $this->message         = $fn->getIssetParam($params, 'message');
        $this->footer          = $fn->getIssetParam($params, 'footer');
        $this->ccEmail         = $fn->getIssetParam($params, 'ccEmail');
        $this->bccEmail        = $fn->getIssetParam($params, 'bccEmail');
        $this->replyTo         = $fn->getIssetParam($params, 'replyTo');
        $this->attachmentArray = $fn->getIssetParam($params, 'attachmentArray');
    }

    // Getter method for creating/returning the single instance of this class
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new EmailContent();
        }

        return self::$instance;
    }

    function getCssTextForEmails(){
        $text = "
    	.mainTable {
    	    padding:10px;
    	    width:680px;
    	}

        #emailContent {
            padding:10px 0 30px 0;
        }

        #headerContent {
            border-bottom: 1px dotted #ccc;
        }

    	#emailContent table td,
    	#emailContent table th {padding:5px 5px 5px 0;vertical-align: top;}

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

    public function sendEmail($exp = array()){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $localPath = CP_LOCAL_PATH_ALIAS;

        $this->message = $this->getFormattedEmail($this->message, $exp);

        $useAWSForEmail = $fn->getIssetParam($cpCfg[CP_ENV], 'useAWSForEmail', false);

        $opts = array('replyTo' => $this->replyTo);

        $smtp = null;
        //$useAWSForEmail is use Amazon Web service
        if ($useAWSForEmail) {
            $smtp = includeCPClass('Lib', 'SmtpAWS');
        } else {
            $smtp = includeCPClass('Lib', 'Smtp');
        }
        $result = $smtp->sendEmail($this->toName, $this->toEmail, $this->fromName,
                                   $this->fromEmail, $this->subject, $this->message,
                                   $this->ccEmail, $this->bccEmail, $this->attachmentArray, $opts);

        return $result;
    }

    public function getFormattedEmail($message, $exp = array()){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $localPath = CP_LOCAL_PATH_ALIAS;

        $showHeader = $fn->getIssetParam($exp, 'showHeader', true);

        $textHeader = '';
        if ($showHeader) {
            $textHeader = "
            <tr>
            <td id='headerContent'>
                <a href='{$cpCfg['cp.siteUrl']}'><img src='{$cpCfg['cp.siteUrlNoSlash']}{$localPath}images/logo.png' border='0'></a>
            </td>
            </tr>
            ";
        }

        $message = "
        <html>
        <body>
            <table class='mainTable' cellpadding='0' cellspacing='0'>
                {$textHeader}
                <tr>
                <td>
                    <div id='emailContent'>
                        {$message}
                    </div>
                </td>
                </tr>

                <tr>
                <td>
                    <div class='emailFooter'>
                        {$this->footer}
                    </div>
                </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        require_once('emogrifier.php');
        $message = str_replace('&', '&amp;', $message);
        $message = str_replace('[[siteUrl]]', $cpCfg['cp.siteUrl'], $message);
        $emogrifier  = new Emogrifier($message, $this->css);
        $message = $emogrifier->emogrify();

        return $message;
    }

}//end class
