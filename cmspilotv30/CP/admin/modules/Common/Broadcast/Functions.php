<?
class CP_Admin_Modules_Common_Broadcast_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $actBtnsList = array('new', 'export');
        if($cpCfg['cp.showDeleteActionBtnInList']){
            $actBtnsList[] = 'deleteList';
        } 
        $modObj = $modules->getModuleObj('common_broadcast');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'actBtnsList' => $actBtnsList
           ,'actBtnsDetail' => array('edit', 'sendTest', 'send', 'delete', 'duplicate')
           ,'relatedTables' => array('media', 'broadcast_contact')
        ));
    }

    /**
     *
     */
    function setArrays() {

        $SQL = "
        SELECT a.* 
        FROM broadcast a
        ";

        return $SQL;
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('common_broadcast', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('common_broadcast', 'common_contactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'broadcast_contact'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
        ));
        
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('common_broadcast', 'common_testRecipientLink');

        $inst->registerLinksArray($linkObj, array(
            'linkRoomTableName'      => 'contact'
           ,'historyTableName'       => 'broadcast_test_recipient'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
        ));

        //------------------------------------------------------------------------------//
        if ($tv['module'] == "broadcast" && $tv['lnkRoom'] == "contact"){
            $tv['listLimitOverride'] = 500;
        }
    }

    /**
     *
     */
    function getBroadcastCount($broadcast_id, $item) {
        $db = Zend_Registry::get('db');

        if ($item == "total") {
            $SQL = "
            SELECT count(DISTINCT contact_id) 
            FROM broadcast_contact 
            WHERE broadcast_id = {$broadcast_id}
            ";
        } else if ($item == "success") {
            $SQL = "
            SELECT count(DISTINCT contact_id) 
            FROM broadcast_contact 
            WHERE broadcast_id = {$broadcast_id} 
            AND status = 'Success'
            ";
        } else if ($item == "failed") {
            $SQL = "
            SELECT count(DISTINCT contact_id) 
            FROM broadcast_contact 
            WHERE broadcast_id = {$broadcast_id} 
              AND status = 'Failed'
            ";
        } else if ($item == "remaining") {
            $SQL = "
            SELECT count(DISTINCT contact_id) 
            FROM broadcast_contact 
            WHERE broadcast_id = {$broadcast_id} 
              AND send_tag = 0
            ";
        }

        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        return $row[0];
    }
    
    /**
     *
     */
    function getRecordTypeArray(){
        $langArray = array(
                     'sms'   => 'SMS'
                    ,'email' => 'Email'
        );

        return $langArray;
    }
}