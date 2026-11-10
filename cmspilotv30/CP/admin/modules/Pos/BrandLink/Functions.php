<?
class CP_Admin_Modules_Pos_BrandLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pos_brandLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'valuelist'
           ,'keyField'  => 'valuelist_id'
           ,'hasFlagInList' => 0
           ,'titleField' => 'value'
        ));
    }

}
