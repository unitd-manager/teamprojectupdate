<?
class CP_Admin_Modules_Account_Reports_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSearch(){
        return $this->view->getSearch();
    }

    function getDisplayReport(){
        $fn = Zend_Registry::get('fn');
        
        set_time_limit(50000);
        $report = $fn->getReqParam('report');
        $fnName = 'get' . ucfirst($report);
        $text = $this->$fnName();
        return $this->view->getDisplayReport($text);
    }    

    function getTrialBalance($report = 'trialBalance') {
        $fn = Zend_Registry::get('fn');

        //goes to input screen for report
        $input = $fn->getReqParam('input');

        $wTrialBalance = getCPWidgetObj('account_trialBalance');
        $reportArr = $this->model->reportsArray[$report];

        $text = '';
        if ($input == 1) {
            $text = "
            <div id='reportInput'>
                {$wTrialBalance->view->getReportInput($reportArr)}
            </div>
            ";

        } else {
            $text = "
            {$wTrialBalance->getWidget()}
            ";
        }

        return $text;
    }
    
    function getTrialBalanceBankAccount() {
        return $this->getTrialBalance('trialBalanceBankAccount');
    }

    function getTrialBalanceSundryCreditorDebtor() {
        return $this->getTrialBalance('trialBalanceSundryCreditorDebtor');
    }

    function getTrialBalanceOutstandingReceivable() {
        return $this->getTrialBalance('trialBalanceOutstandingReceivable');
    }

    function getTrialBalanceOutstandingPayable() {
        return $this->getTrialBalance('trialBalanceOutstandingPayable');
    }

    function getNetworth() {
        return $this->view->getNetworth();
    }

    function getLiquiditySummary() {
        return $this->view->getLiquiditySummary();
    }

    function getCurrencyStock() {
        $modCurr = getCPModuleObj('account_currency');
        $text = "
        {$modCurr->view->getCurrencyStockReport()}
        ";
        return $text;
    }

    function getProfitMargin() {
        $modCurr = getCPModuleObj('account_currency');
        $text = "
        {$modCurr->view->getProfitMarginReport()}
        ";
        return $text;
    }

    function getBalanceSheet() {
        $text = "
        <h2>coming soon...</h2>
        ";
        return $text;
    }

    function getTradingProfitLoss() {
        $text = "
        <h2>coming soon...</h2>
        ";
        return $text;
    }

    function getDatesByQuickFilter() {
        return $this->model->getDatesByQuickFilter();
    }

    function getGeneralLedger() {
        $fn = Zend_Registry::get('fn');

        $wTrialBalance = getCPWidgetObj('account_trialBalance');

        $text = "
        ";

        return $text;
    }
}