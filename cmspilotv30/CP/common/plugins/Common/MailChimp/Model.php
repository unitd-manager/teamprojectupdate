<?
require_once 'mailchimp/1.3.1/MCAPI.class.php';

class CP_Common_Plugins_Common_MailChimp_Model extends CP_Common_Lib_PluginModelAbstract
{
    /**
     *
     */
    function listSubscribe($email, $first_name, $last_name, $exp = array()) {
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set("display_errors", $cpCfg[CP_ENV]['display_errors']);
        
        if (!isset($cpCfg[CP_ENV]['mailChimpApiKey']) || !isset($cpCfg[CP_ENV]['mailChimpNewsletterListId'])){
            exit("Plese set values for mailChimpApiKey & mailChimpNewsletterListId in conf.php");
        }
        
        $apiKey = $cpCfg[CP_ENV]['mailChimpApiKey'];
        $listId = $cpCfg[CP_ENV]['mailChimpNewsletterListId'];

        $api = new MCAPI($apiKey);

        $merge_vars = array('FNAME'=> $first_name, 'LNAME'=> $last_name);

        // By default this sends a confirmation email - you will not see new members
        // until the link contained in it is clicked!
        $retval = $api->listSubscribe($listId, $email, $merge_vars);

        return $retval;
        
        // if ($api->errorCode){
        //     echo "Unable to load listSubscribe()!\n";
        //     echo "\tCode=".$api->errorCode."\n";
        //     echo "\tMsg=".$api->errorMessage."\n";
        // } else {
        //     echo "Subscribed - look for the confirmation email!\n";
        // }        
    }
}