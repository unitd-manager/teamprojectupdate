<?
class CP_Admin_Modules_Tradingsg_ExpenseLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingsg_expenseLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'expense'
           ,'keyField'  => 'expense_id'
        ));
    }
}
