<?
class CP_Admin_Modules_Accountsg_AccHead_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('accountsg_accHead');
        $modObj['tableName'] = 'acc_head';
        $modObj['keyField']  = 'acc_head_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title'         => 'Chart of A/C'
           ,'actBtnsEdit'   => array('save', 'apply')
           ,'actBtnsDetail' => array('edit')
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        //-------------------------- extra ---------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('accountsg_accHead', 'common_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'project'
           ,'displayTitleFieldName' => 'a.company_name'
           ,'linkMultiple'          => 0
        ));
    }
}