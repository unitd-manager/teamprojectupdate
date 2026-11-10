<?
class CP_Common_Modules_Edukite_Class_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_class');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'import')
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukite_class', 'edukite_teacherLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'class_teacher'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showAnchorInLinkPortal' => 0
        ));
    }
}