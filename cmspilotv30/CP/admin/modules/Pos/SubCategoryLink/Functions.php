<?
class CP_Admin_Modules_Pos_SubCategoryLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pos_subCategoryLink');
        $modObj['keyField'] = 'sub_category_id';
        $modules->registerModule($modObj, array(
            'tableName' => 'sub_category'
           ,'keyField'  => 'sub_category_id'
           ,'hasFlagInList' => 0
        ));
    }

}
