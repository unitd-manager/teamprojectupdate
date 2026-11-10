<?
class CP_Admin_Modules_Payroll_LoanRepaymentLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('payroll_loanRepaymentLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'loan'
           ,'keyField'  => 'loan_id'
        ));
    }
}
