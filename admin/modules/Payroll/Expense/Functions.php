<?
class CPL_Admin_Modules_Payroll_Expense_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('payroll_expense');
        $modules->registerModule($modObj, array(
            'tableName' => 'expense'
            ,'hasFlagInList' => 0
           ,'keyField'         => 'expense_id'
           ,'actBtnsList'   => array()
           ,'actBtnsEdit'   => array('apply','save', 'delete')
           ,'title'     => 'Accounts'
        ));
    }
}