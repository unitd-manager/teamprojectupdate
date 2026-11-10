<?
class CP_Common_Modules_WebBasic_Career_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('webBasic_career');
        $modules->registerModule($modObj, array(
             'tableName' => 'career'
            ,'keyField' => 'career_id'
        ));
    }
}