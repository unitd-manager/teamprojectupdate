<?
class CP_Admin_Modules_Hms_Expense_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_expense');
        $modules->registerModule($modObj, array(
            'tableName' => 'expense'
            ,'hasFlagInList' => 0
           ,'keyField'         => 'expense_id'
           ,'actBtnsList'   => array('new')
           ,'actBtnsEdit'   => array('apply','save')
           ,'title'     => 'Expense'
        ));
    }
}