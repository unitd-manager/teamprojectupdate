<?
class CP_Admin_Modules_Account_AccCompany_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('account_accCompany');
        $modObj['tableName'] = 'acc_company';
        $modObj['keyField']  = 'acc_company_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title'         => 'A/C Company'
        ));
    }
}