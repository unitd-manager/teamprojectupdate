<?
class CP_Common_Lib_Smtp
{
    /**
     *
     * @var <type>
     */
    var $emailInfo = array();
    var $mailError;
    var $smtpError;

    var $host;
    var $port;
    var $user;
    var $pass;
    var $ssl; //'ssl' or 'tls'

    var $useSMTPAuth;
    var $useLocalMailForSending;

    var $toName;
    var $toEmail;
    var $fromName;
    var $fromEmail;
    var $replyToName;
    var $replyToEmail;
    var $message;
    var $subject;
    var $ccEmail;
    var $bccEmail;
    var $attachmentArray;
    var $displayErrorOutput = true;

    /**
     *
     * @param <type> $toName
     * @param <type> $toEmail
     * @param <type> $fromName
     * @param <type> $fromEmail
     * @param <type> $subject
     * @param <type> $message
     * @param <type> $ccEmail
     * @param <type> $bccEmail
     * @param <type> $attachmentArray
     * @return <type>
     */
    function sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $message,
                       $ccEmail = '', $bccEmail = '', $attachmentArray = array(), $exp = array()) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $this->useSMTPAuth     = isset($cpCfg['useSMTPAuth'])     ? $cpCfg['useSMTPAuth']     : true;
        $this->useLocalMailForSending = isset($cpCfg['useLocalMailForSending']) ? $cpCfg['useLocalMailForSending'] : false;

        $host = $fn->getIssetParam($cpCfg[CP_ENV], 'SMTPServer'  , $fn->getIssetParam($cpCfg, 'SMTPServer'));
        $port = $fn->getIssetParam($cpCfg[CP_ENV], 'SMTPPort'    , $fn->getIssetParam($cpCfg, 'SMTPPort', '25'));
        $user = $fn->getIssetParam($cpCfg[CP_ENV], 'SMTPUsername', $fn->getIssetParam($cpCfg, 'SMTPUsername'));
        $pass = $fn->getIssetParam($cpCfg[CP_ENV], 'SMTPPassword', $fn->getIssetParam($cpCfg, 'SMTPPassword'));
        $ssl  = $fn->getIssetParam($cpCfg[CP_ENV], 'SMTPSSL');

        $replyToAddress = isset($exp['replyTo']) ? $exp['replyTo'] : '';
        $opts = array();
        if ($replyToAddress != '') {
            $opts['ReplyToAddresses'] = $replyToAddress;
        } else {
            $opts['ReplyToAddresses'] = $fromEmail;
        }

        if ($host == ''){
            $confPath = CP_LIBRARY_PATH . 'cp_conf/cp_conf.php';

            if (file_exists($confPath)){
                include($confPath);
                $host = $cpCfgCommon['SMTPServer'];
                $port = $cpCfgCommon['SMTPPort'];
                $user = $cpCfgCommon['SMTPUsername'];
                $pass = $cpCfgCommon['SMTPPassword'];
            }
        }

        if ($this->useSMTPAuth && $host == ''){
            die('No SMTP Server configurations found..');
        }

        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->ssl  = $ssl;

        $this->toName          = $toName;
        $this->toEmail         = $toEmail;
        $this->fromName        = $fromName;
        $this->fromEmail       = $fromEmail;
        $this->replyToName     = $fromName;
        $this->replyToEmail    = $opts['ReplyToAddresses'];
        $this->message         = $message;
        $this->subject         = $subject;
        $this->ccEmail         = $ccEmail;
        $this->bccEmail        = $bccEmail;
        $this->attachmentArray = $attachmentArray;

        try {
            $this->sendEmailZend();
        } catch (Exception $e){

            /*** if localhost was used and failed throw the error and stop executing ***/
            if ($this->useLocalMailForSending == 1){
                $this->mailError = $e->getMessage();

                if ($this->displayErrorOutput){
                    echo $this->mailError;
                    return;
                } else {
                    return $this->mailError;
                }
            }

            /*** if smtp was used and failed try to send through localhost ***/
            if ($this->useLocalMailForSending == 0){
                $this->smtpError = $e->getMessage();
                $this->useLocalMailForSending = 1;

                try {
                    $this->sendEmailZend();
                } catch (Exception $e){
                    $this->mailError = $e->getMessage();

                    $error = "SMTP Server Failed: {$this->smtpError} <hr> Trying localhost.... Error: {$this->mailError}<br>";

                    if ($this->displayErrorOutput){
                        echo $error;
                    } else {
                        return $error;
                    }
                }
            }
        }
    }

    /**
     *
     */
    function sendEmailZend(){
        $cpCfg = Zend_Registry::get('cpCfg');

        include_once 'Zend/Loader/Autoloader.php';
        Zend_Loader_Autoloader::getInstance();

        $mail = new Zend_Mail('utf-8');

        $transport = null;

        if (is_array($this->attachmentArray)){
            foreach ($this->attachmentArray as $fileName => $filePath) {
                //if file name is specified in array
                $fileName2 = '';
                $filePath2 = '';

                if (is_int($fileName)) {
                    $filePath2 = $filePath;
                    $fileName2 = basename($filePath);
                } else {
                    $filePath2 = $filePath;
                    $fileName2 = $fileName;
                }

                $att    = file_get_contents($filePath2);
                $attObj = new Zend_Mime_Part($att);
                $attObj->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                $attObj->encoding    = Zend_Mime::ENCODING_BASE64;
                $attObj->filename = $fileName2;
                $mail->addAttachment($attObj);
            }
        }

        $toEmailArr = explode(',', $this->toEmail);

        if (count($toEmailArr) > 1){
            $toEmail = array();
            foreach($toEmailArr AS $email){
                $toEmail[] = trim($email);
            }
        } else {
            $toEmail = $this->toEmail;
        }

        // if from address (supposed to be admin email) has comma separated value then just use the first one
        $fromEmailArr = explode(',', $this->fromEmail); 
        $fromEmail = $fromEmailArr[0];

        $mail->setBodyHtml($this->message)
             ->setFrom($fromEmail, $this->fromName)
             ->setReplyTo($this->replyToEmail, $this->replyToName)
             ->addTo($toEmail, $this->toName)
             ->setSubject($this->subject);


        if ($this->ccEmail != '') {
            $mail->addCc($this->ccEmail);
        }

        if ($this->bccEmail != "") {
            $mail->addBcc($this->bccEmail);
        }

        if ($this->useLocalMailForSending == 0){
            $config = array();

            if ($this->useSMTPAuth){
                $config = array(
                     'auth'     => 'login'
                    ,'username' => $this->user
                    ,'password' => $this->pass
                    ,'port'     => $this->port
                );
            }

            if($this->ssl != ''){
                $config['ssl'] = $this->ssl;
            }

            $transport = new Zend_Mail_Transport_Smtp($this->host, $config);
        }

        try {
            $mail->send($transport);

        } catch (Exception $e){
if($_SERVER['REMOTE_ADDR'] == '1.36.9.202' ){print $transport->getConnection()->getLog();}            
            throw $e;
        }
    }
}
