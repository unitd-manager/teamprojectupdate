<?
class CP_Admin_Modules_Project_QuoteTemplate_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows  = '';

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['template_title'])}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListSortOrderField($row, 'quote_id')}
            {$listObj->getListRowEnd($row['quote_id'])}
            ";

            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Template Label', 'q.template_title')}
        {$listObj->getListHeaderCell('Creation Date', 'q.creation_date')}
        {$listObj->getListSortOrderImage('q')}
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
        {$formObj->getTBRow('Template Title', 'template_title')}
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
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlType    = $fn->getValueListSQL('quoteType');
        $sqlCurr    = $fn->getValueListSQL('quoteCurrency');
        $sqlStat    = $fn->getValueListSQL('quoteStatus');

        $sqlStaff = $fn->getDDSql('core_staff', array('condn' => "status = 'Current'"));

        $expVl   = array('sqlType' => 'OneField');
        $expSign = array('detailValue' => $row['staff_name']);
        
        $fielset1  = "
        {$formObj->getTBRow('Template Label', 'template_title', $row['template_title'])}
        {$formObj->getDDRowBySQL('Quote Type', 'quote_type', $sqlType, $row['quote_type'], $expVl)}
        {$formObj->getDDRowBySQL('Currency', 'currency_item', $sqlCurr, $row['currency_item'], $expVl)}
        {$formObj->getDDRowBySQL('Use Signature of', 'sign_staff_id', $sqlStaff, $row['sign_staff_id'], $expSign)}
        {$formObj->getTARow('Notes', 'note', $row['note'])}
        {$formObj->getTARow('Conditions', 'condition', $row['condition'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Template Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        
        $quote = getCPModuleObj('project_quote', true);

        $text = "
        <div id='quotesOuter'>
            {$quote->view->getQuotesPortal($row['quote_id'], 'quoteTemplate')}
        </div>
        ";

        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
           cpm.project.quote.init();
        "));

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
    }
}