<?
class CP_Admin_Modules_Ek_Class_Functions extends CP_Common_Modules_Ek_Class_Functions
{
    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ek_class', 'core_staffLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'staff_class'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showAnchorInLinkPortal' => 0
        ));
        
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ek_class', 'ek_subjectLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'class_subject'
           ,'showAnchorInLinkPortal' => 0
        ));

    }
}