<?
class CP_Admin_Modules_ManPower_ExpenseLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('manPower_expenseLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'expense'
           ,'keyField'  => 'expense_id'
        ));
    }
}
