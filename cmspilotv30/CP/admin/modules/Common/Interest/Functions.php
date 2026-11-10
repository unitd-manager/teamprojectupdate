<?
class CP_Admin_Modules_Common_Interest_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $actBtnsList = array('new', 'export');
        if($cpCfg['cp.showDeleteActionBtnInList']){
            $actBtnsList[] = 'deleteList';
        } 
        
        $modObj = $modules->getModuleObj('common_interest');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('interest_contact')
           ,'hasMultiLang'  => 1
           ,'actBtnsList' => $actBtnsList
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        $linkObj = $inst->getLinksArrayObj('common_interest', 'common_contactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'interest_contact'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
        ));
    }
}