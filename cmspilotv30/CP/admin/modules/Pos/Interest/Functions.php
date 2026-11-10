<?
class CP_Admin_Modules_Pos_Interest_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pos_interest');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('interest_contact')
           ,'hasMultiLang'  => 1
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        $linkObj = $inst->getLinksArrayObj('pos_interest', 'common_contactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'interest_contact'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
        ));
    }
}