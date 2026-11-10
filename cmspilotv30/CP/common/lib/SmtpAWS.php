<?
require_once(CP_LIBRARY_PATH . 'lib_php/aws/sdk.class.php');

class CP_Common_Lib_SmtpAWS
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

        $ses = new AmazonSES();

        $recip = array(
            'ToAddresses' => array($toEmail)
        );

        $replyToAddress = isset($exp['replyTo']) ? $exp['replyTo'] : '';
        $opts = array();
        if ($replyToAddress != '') {
            $opts['ReplyToAddresses'] = $replyToAddress;
            //$opts['ReturnPath']       = $replyToAddress;
        }

        $message = array(
            'Subject.Data' => $subject
           ,'Body.Html.Data'=> $message
        );

        $from = "\"{$fromName}\" <{$fromEmail}>";
        $res = $ses->send_email($from, $recip, $message, $opts);
        //print_r($res);

        // print '<pre>';
        // print $res->body->SendEmailResult->MessageId;
        // print_r($res);
        // print '</pre>';

        //return $res;

    }

}
