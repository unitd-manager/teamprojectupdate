<?
class CP_Admin_Modules_Edukite_ClassLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('edukite_classLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'class'
           ,'keyField'  => 'class_id'
        ));
    }
}
