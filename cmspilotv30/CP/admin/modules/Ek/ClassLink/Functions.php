<?
class CP_Admin_Modules_Ek_ClassLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('ek_classLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'class'
           ,'keyField'  => 'class_id'
        ));
    }
}
