<?
class CP_Admin_Modules_Account_Currency_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows       = "";

        foreach ($dataArray as $row){
            $rows .="
    		{$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['code'])}
            {$listObj->getListDataCell($row['currency_id'], 'center')}
            {$listObj->getListRowEnd($row['currency_id'])}
			";
        	$rowCounter++;
		}

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Currency Name', 'c.title')}
        {$listObj->getListHeaderCell('Code', 'c.code')}
        {$listObj->getListHeaderCell('ID', 'c.currency_id', 'headerCenter')}
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

        $fieldset = "
        {$formObj->getTBRow('Currency Name', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');

        $base_currency = getCPModelObj('account_accCompany')->getBaseCurrencyCode();

        $fielset1  = "
        {$formObj->getTBRow('Currency Name', 'title', $row['title'])}
        {$formObj->getTBRow('Currency Code', 'code', $row['code'])}
        {$formObj->getTBRow('Stock - ' . $base_currency , 'stock', $row['stock'])}
        {$formObj->getTBRow('Stock Rate', 'avg_stock_rate', $row['avg_stock_rate'])}
        {$formObj->getYesNoRRow('Is this Main Currency?', 'main_currency', $row['main_currency'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Currency Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        $media = Zend_Registry::get('media');

        $text  = "
        {$media->getRightPanelMediaDisplay('Picture', 'account_currency', 'picture', $row)}
		";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
    }

    function getCurrencyStockReport() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rowsHTML = '';

        $SQL = "
        SELECT *
        FROM currency
        ORDER BY code
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $stock = $fn->getFormatNumber($row['stock']);

            $rowsHTML .= "
            <tr>
                <td>{$row['code']}</td>
                <td class='txtRight'>{$stock}</td>
                <td class='txtRight'>{$row['avg_buy_rate']}</td>
                <td class='txtRight'>{$row['avg_stock_rate']}</td>
            </tr>
            ";
        }


        $text = "
        <table class='thinlist w650'>
            <thead>
            <tr>
                <th class=''>Currency</th>
                <th class='txtRight'>Stock</th>
                <th class='txtRight'>Avg Buy Rate</th>
                <th class='txtRight'>Stock Rate</th>
            </tr>
            </thead>
            {$rowsHTML}
        </table>
        ";
        return $text;
    }

    function getProfitMarginReport() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rowsHTML = '';

        $SQL = "
        SELECT *
        FROM currency
        ORDER BY code
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $stock = $fn->getFormatNumber($row['stock']);

            $rowsHTML .= "
            <tr>
                <td>{$row['code']}</td>
                <td class='txtRight'>{$stock}</td>
                <td class='txtRight'>{$row['avg_buy_rate']}</td>
                <td class='txtRight'>{$row['avg_stock_rate']}</td>
            </tr>
            ";
        }


        $text = "
        <table class='thinlist w650'>
            <thead>
            <tr>
                <th class=''>Currency</th>
                <th class='txtRight'>Stock</th>
                <th class='txtRight'>Avg Buy Rate</th>
                <th class='txtRight'>Stock Rate</th>
            </tr>
            </thead>
            {$rowsHTML}
        </table>
        ";
        return $text;
    }
}