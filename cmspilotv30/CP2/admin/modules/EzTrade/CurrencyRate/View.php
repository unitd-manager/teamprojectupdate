<?
class CP_Admin_Modules_EzTrade_CurrencyRate_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $fieldsArray = array();

    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');
        $dateUtil = Zend_Registry::get('dateUtil');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['currency_from'])}
            {$listObj->getListDataCell($row['currency_to'])}
            {$listObj->getListDataCell($row['exchange_rate'])}
            {$listObj->getListDataCell($row['currency_rate_id'], 'center')}
            {$listObj->getListRowEnd($row['currency_rate_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Currency From', 'cr.currency_from', 'cr.currency_to')}
        {$listObj->getListHeaderCell('Currency To', 'cr.currency_to', 'cr.currency_from')}
        {$listObj->getListHeaderCell('Exchange Rate', 'cr.exchange_rate')}
        {$listObj->getListHeaderCell('ID', 'cr.currency_rate_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $modCurrencyRate = getCPModuleObj('ezTrade_currencyRate');
        $sqlCurrency = $fn->getValueListSQL('currency');

        $expVl = array('sqlType' => 'OneField');

        $fieldset = "
        {$formObj->getDDRowBySQL('Currency From', 'currency_from', $sqlCurrency, '', $expVl)}
        {$formObj->getDDRowBySQL('Currency To', 'currency_to', $sqlCurrency, '', $expVl)}
        {$formObj->getTBRow('Exchange Rate', 'exchange_rate')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Currency Rate Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $modCurrencyRate = getCPModuleObj('ezTrade_currencyRate');
        $sqlCurrency = $fn->getValueListSQL('currency');

        $expVl = array('sqlType' => 'OneField');
        $fieldset = "
        {$formObj->getDDRowBySQL('Currency From', 'currency_from', $sqlCurrency, $row['currency_from'], $expVl)}
        {$formObj->getDDRowBySQL('Currency To', 'currency_to', $sqlCurrency, $row['currency_to'], $expVl)}
        {$formObj->getTBRow('Exchange Rate', 'exchange_rate', $row['exchange_rate'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Currency Rate Details', $fieldset)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    /**
     *
     */
    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $fnLink = Zend_Registry::get('fnLink');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');

        return '';
        
        $rows = "";
        $rows .= $displayLinkData->getLinkPortalMain("ezTrade_enquiry", "ezTrade_productLink", "Items", $row);

        $record_id = $fn->getIssetParam($row, 'enquiry_id');

        $text = "
        {$rows}
        {$comment->getView(array(
             'roomName' => 'ezTrade_currencyRate'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getCurrencyExchageRate($currency_from = '', $currency_to = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        if ($currency_from == $currency_to) {
            return 1;
        }
        if ($currency_from == '' || $currency_to == '') {
            return 0;
        }

        $SQL = "
        SELECT exchange_rate
        FROM currency_rate
        WHERE currency_from = '{$currency_from}'
          AND currency_to   = '{$currency_to}'
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        if ($numRows == 0) {
            return 1;
        }

        $row = $db->sql_fetchrow($result);
        if ($row['exchange_rate'] <= 0) {
            return 1;
        }

        return $row['exchange_rate'];
    }

    /**
     *
     */
    function getConvertedCurrencyValue($currency_from, $currency_to, $amount) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $exchange_rate = $this->getCurrencyExchageRate($currency_from, $currency_to);
        $converted_amount = $amount * $exchange_rate;

        return $converted_amount;
    }

    /**
     *
     */
    function getCurrencyExchageRateFromWeb() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $currency_from = $fn->getReqParam('currency_from');
        $currency_to   = $fn->getReqParam('currency_to');

        $exchRate = $this->getCurrencyExchageRateFromWeb($currency_from, $currency_to);
        return $cpUtil->getJsonFromArray(array('exchange_rate' => $exchRate));
    }

}