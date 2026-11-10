<?
class CP_Admin_Modules_Account_CurrencyConvert_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj2 = Zend_Registry::get('listObj2');
        $cpCfg = Zend_Registry::get('cpCfg');

        $listObj2->addFld('from_currency', array('title' => 'From Currency'
                                                ,'sort' => 'from_currency'
                                                ,'hasDetailLink' => true));
        $listObj2->addFld('from_currency_code', array('title' => 'From Code'
                                                     ,'sort' => 'from_currency_code'
                                                     ,'hasDetailLink' => true));
        $listObj2->addFld('to_currency', array('title' => 'To Currency', 'sort' => 'to_currency'));
        $listObj2->addFld('to_currency_code', array('title' => 'To Code'
                                                   ,'sort' => 'to_currency_code'));
        $listObj2->addFld('exch_rate_buy', array('title' => 'Rate - V.Buy'
                                                ,'align' => 'right'
                                                ,'sort' => 'cc.exch_rate_buy'
                                                ,'class' => 'w100'
                                                ,'editable' => true));
        $listObj2->addFld('exch_rate_sell', array('title' => 'Rate - V.Sell'
                                                 ,'align' => 'right'
                                                 ,'sort' => 'cc.exch_rate_sell'
                                                 ,'class' => 'w100'
                                                 ,'editable' => true));
        $listObj2->addFld('exch_rate_buy_evening', array('title' => 'Rate - V.Buy (PM)'
                                                ,'align' => 'right'
                                                ,'sort' => 'cc.exch_rate_buy_evening'
                                                ,'class' => 'w100 evening-rate'
                                                ,'editable' => true));
        $listObj2->addFld('exch_rate_sell_evening', array('title' => 'Rate - V.Sell (PM)'
                                                ,'align' => 'right'
                                                ,'sort' => 'cc.exch_rate_sell_evening'
                                                ,'class' => 'w100 evening-rate'
                                                ,'editable' => true));

        $listObj2->addFld('exch_rate_cash', array('title' => 'Rate - Report (Cash)'
                                                ,'align' => 'right'
                                                ,'sort' => 'cc.exch_rate_cash'
                                                ,'class' => 'w100 report-rate'
                                                ,'editable' => true));
        $listObj2->addFld('exch_rate_bank', array('title' => 'Rate - Report (Bank)'
                                                ,'align' => 'right'
                                                ,'sort' => 'cc.exch_rate_bank'
                                                ,'class' => 'w100 report-rate'
                                                ,'editable' => true));

        $listObj2->addFld('sort_order', array('type' => 'sortFld'
                                             ,'title' => 'Sort Order'
                                             ,'align' => 'center'
                                             ,'sort' => 'cc.sort_order'));
        
        $listObj2->setDataArr($dataArray);
        $text = $listObj2->render();

        $eveningRateText = '';
        if ($cpCfg['m.account.currencyConvert.rateboardShowEveningRate'] == 1) {
            $eveningRateText = 'Turn evening rate off';
        } else {
            $eveningRateText = 'Turn evening rate on';
        }
        $eveningRateText = "
        <div class='updateEveningRateWrap'><a class='updateEveningRate' href='#'>{$eveningRateText}</a></div>
        ";
        $text = "
        {$eveningRateText}
        {$text}
        ";

        return $text;
    }    
    
    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $sqlCurrency = getCPModuleObj('account_currency')->model->getCurrencySQL();

        $fielset1  = "
        {$formObj->getDDRowBySQL('From Currency', 'from_currency_id', $sqlCurrency)}
        {$formObj->getDDRowBySQL('To Currency', 'to_currency_id', $sqlCurrency)}
        {$formObj->getTBRow('Exchange Rate', 'exch_rate_sell')}
		";
        $text = "
        {$formObj->getFieldSetWrapped('Currency Convert Details', $fielset1)}
        ";
        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');

        $expFromCurrency = array('detailValue' => $row['from_currency']);
        $expToCurrency = array('detailValue' => $row['to_currency']);
        $currencyObj = getCPModuleObj('account_currency');
        $sqlCurrency = $currencyObj->model->getCurrencySQL();

        $fielset1  = "
        {$formObj->getDDRowBySQL('From Currency', 'from_currency_id', $sqlCurrency, 
                                 $row['from_currency_id'], $expFromCurrency)}
        {$formObj->getDDRowBySQL('To Currency', 'to_currency_id', $sqlCurrency, 
                                 $row['to_currency_id'], $expToCurrency)}
        {$formObj->getTBRow('Rate V.Sell', 'exch_rate_sell', $row['exch_rate_sell'])}
        {$formObj->getTBRow('Rate V.Buy', 'exch_rate_buy', $row['exch_rate_buy'])}
        {$formObj->getTBRow('Rate V.Sell (PM)', 'exch_rate_sell_evening', $row['exch_rate_sell_evening'])}
        {$formObj->getTBRow('Rate V.Buy (PM)', 'exch_rate_buy_evening', $row['exch_rate_buy_evening'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Currency Convert Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {

        $text  = "
		";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
    }
}