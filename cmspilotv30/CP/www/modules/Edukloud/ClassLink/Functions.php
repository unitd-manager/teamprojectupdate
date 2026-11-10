<?
class CP_Www_Modules_Edukloud_ClassLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_classLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'class'
           ,'keyField'      => 'class_id'
        ));
    }
}
