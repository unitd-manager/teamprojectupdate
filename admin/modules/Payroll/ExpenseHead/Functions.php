<?
class CPL_Admin_Modules_Payroll_ExpenseHead_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('payroll_expenseHead');
        $modules->registerModule($modObj, array(
            'tableName' => 'expense_group'
            ,'hasFlagInList' => 0
           ,'keyField'         => 'expense_group_id'
           ,'actBtnsList'   => array()
           ,'actBtnsEdit'   => array('apply','save')
           ,'title'     => 'Expense Head'
        ));
    }
}