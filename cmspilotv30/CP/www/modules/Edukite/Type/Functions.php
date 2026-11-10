<?
class CP_Www_Modules_Edukite_Type_Functions extends CP_Common_Modules_Edukite_Type_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_type');
        $modObj['tableName'] = 'notice_type';
        $modObj['keyField']  = 'notice_type_id';
        $modObj['listLimit'] = 50;
        $modules->registerModule($modObj, array(
             'actBtnsList'   => array('new')
        ));
    }
    
}