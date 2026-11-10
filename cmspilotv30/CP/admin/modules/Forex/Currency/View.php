<?
class CP_Admin_Modules_Forex_Currency_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rowCounter = 0;
        $rows       = "";


        foreach ($dataArray as $row){
            $rows .="
    		{$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['currency_name'])}                                  
            {$listObj->getListSortOrderField($row, 'currency_id')}
            {$listObj->getListDataCell($row['country'])}
            {$listObj->getListDataCell($row['we_buy'])}
            {$listObj->getListDataCell($row['we_sell'])}
            {$listObj->getListDataCell($row['currency_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['currency_id'])}
            {$listObj->getListRowEnd($row['currency_id'])}            
			";
        	$rowCounter++;
		}

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Currency Name', 'c.currency_name')}
        {$listObj->getListSortOrderImage()}
        {$listObj->getListHeaderCell('Country', 'c.country')}
        {$listObj->getListHeaderCell('We Buy', 'c.we_buy')}
        {$listObj->getListHeaderCell('We Sell', 'c.we_sell')}
        {$listObj->getListHeaderCell('ID', 'c.currency_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
    	{$listObj->getListHeaderEnd()}
        {$rows}
	    {$listObj->getListFooter()}
		";
        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Currency (code)', 'currency_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');

        $fielset1  = "
        {$formObj->getTBRow('Currency (code)', 'currency_name', $row['currency_name'])}
        {$formObj->getTBRow('Country (for display)', 'country', $row['country'])}
        {$formObj->getTBRow('Buying Rate', 'we_buy', $row['we_buy'])}
        {$formObj->getTBRow('Selling Rate', 'we_sell', $row['we_sell'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Currency Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row) {
        $media = Zend_Registry::get('media');

        $text  = "
        {$media->getRightPanelMediaDisplay('Picture', 'forex_currency', 'picture', $row)}
		";
        return $text;
    }

    //==================================================================//

}