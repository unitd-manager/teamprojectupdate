<?
class CPL_Admin_Modules_Payroll_IncomeHead_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('payroll_incomeHead');
        $modules->registerModule($modObj, array(
            'tableName'     => 'income_group'
           ,'hasFlagInList' => 0
           ,'keyField'      => 'income_group_id'
           ,'actBtnsList'   => array()
           ,'actBtnsEdit'   => array('apply','save')
           ,'title'         => 'Income Head'
        ));
    }
}