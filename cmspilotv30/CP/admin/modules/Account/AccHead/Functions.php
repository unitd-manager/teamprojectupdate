<?
class CP_Admin_Modules_Account_AccHead_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('account_accHead');
        $modObj['tableName'] = 'acc_head';
        $modObj['keyField']  = 'acc_head_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title'         => 'Chart of A/C'
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        //-------------------------- extra ---------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('account_accHead', 'common_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'project'
           ,'displayTitleFieldName' => 'a.company_name'
           ,'linkMultiple'          => 0
        ));
    }
}