<?
class CP_Admin_Modules_Ek_Subject_Functions extends CP_Common_Modules_Ek_Subject_Functions
{
    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ek_subject', 'core_staffLink', array(
            'historyTableName'       => 'staff_subject'
           ,'showAnchorInLinkPortal' => 0
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', first_name, last_name)"
        ));
        $inst->registerLinksArray($linkObj);

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ek_subject', 'ek_classLink', array(
            'historyTableName'       => 'class_subject'
           ,'showAnchorInLinkPortal' => 0
        ));
        $inst->registerLinksArray($linkObj);
    }
}