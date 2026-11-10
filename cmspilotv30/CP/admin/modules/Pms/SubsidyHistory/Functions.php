<?
class CP_Admin_Modules_Pms_SubsidyHistory_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_subsidyHistory');
        $modObj['tableName'] = 'subsidy_paid_history';
        $modObj['keyField']  = 'subsidy_history_id';
        $modules->registerModule($modObj, array(
            'title'         => 'Subsidy Paid History'
           ,'actBtnsList'   => array()
           ,'actBtnsDetail' => array()
           ,'hasEditInList' => false
        ));
    }

}