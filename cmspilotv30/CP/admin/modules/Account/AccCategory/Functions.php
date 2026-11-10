<?
class CP_Admin_Modules_Account_AccCategory_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('account_accCategory');
        $modObj['tableName'] = 'acc_category';
        $modObj['keyField']  = 'acc_category_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title' => 'Category'
           ,'actBtnsList' => array()

        ));
    }
}