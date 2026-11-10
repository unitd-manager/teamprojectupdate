<?
class CP_Admin_Modules_Account_CounterSetup_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('account_counterSetup');
        $modObj['tableName'] = 'counter_setup';
        $modObj['keyField']  = 'counter_setup_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'title'         => 'Counter Setup'
        ));
    }
}