<?
class CPL_Admin_Modules_Core_UserGroup_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');

        $modObj = $modules->getModuleObj('core_userGroup');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('media')
           ,'tableName'     => $cpCfg['cp.modAccessUserGroupTable']
           ,'keyField'      => 'user_group_id'
           ,'hasFlagInList' => 0
           ,'title'         => 'User Group'
        ));
    }
}