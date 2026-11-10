<?
class CP_Admin_Modules_Project_QuoteItem_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getNew() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $text  = '';

        $category_id = $fn->getGetParam('category_id');
        $quote_id    = $fn->getGetParam('quote_id');

        if ($category_id == ''){
            return;
        }

        $quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        
        $sqlItemType = $fn->getValueListSQL('quoteItemType');

        $formAction = "index.php?module=project_quoteItem&_spAction=add&showHTML=0";

        if($quoteRec['quote_type'] != 'other $'){
            $hideOtherAmountText = "
            <script>
                $(function(){
                    $('#fld_amount_other').parent().hide();
                });
            </script>
            ";
        }
        
        $sortOrder = $fn->getNextSortOrder('quote_items', "quote_category_id={$category_id}");
        
        $text = "
        <form id='quoteForm' class='yform columnar' method='post' action='{$formAction}'>
            <div id='errorDisplayBox'></div>
            <fieldset>
                {$formObj->getTBRow('Item Name', 'title')}
                {$formObj->getDDRowBySQL('Type', 'item_type', $sqlItemType, 'in-house', array('sqlType' => 'OneField'))}
                {$formObj->getTBRow('HK$', 'amount')}
                {$formObj->getTBRow('Other $', 'amount_other')}
                {$formObj->getTBRow('Sort Order', 'sort_order', $sortOrder)}
                {$formObj->getTARow('Description (if any)', 'description')}
            </fieldset>
            <input type='hidden' name='category_id' value='{$category_id}'>
            <input type='hidden' name='quote_id' value='{$quote_id}'>
        </form>
        {$hideOtherAmountText}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $quote_items_id = $fn->getGetParam('item_id');

        if ($quote_items_id == ''){
            return;
        }

        $SQL     = "
        SELECT * 
        FROM quote_items 
        WHERE quote_items_id = 
        {$quote_items_id}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0){
            return;
        }
        $row     = $db->sql_fetchrow($result);

        $quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $row['quote_id']);
        $hideOtherAmountText = '';

        if($quoteRec['quote_type'] != 'other $'){
            $hideOtherAmountText = "
            <script>
                $(function(){
                    $('#fld_amount_other').parent().hide();
                });
            </script>
            ";
        }

        $sqlItemType    = $fn->getValueListSQL('quoteItemType');

        $formAction = "index.php?module=project_quoteItem&_spAction=save&showHTML=0";

        $exp = array('sqlType' => 'OneField');

        $actual = '';

        if ($row['item_type'] == '3rd party'){
            $actual ="
            {$formObj->getTBRow('Supplier Cost HK$'   , 'supplier_amount'  , $row['supplier_amount']  )}
            {$formObj->getTBRow('Supplier Invoice HK$', 'actual_amount'    , $row['actual_amount']    )}
            ";
        }
        
        $lblAmt      = ($row['item_type'] == '3rd party') ? 'Client Price HK$'     : 'HK$';
        $lblAmtOther = ($row['item_type'] == '3rd party') ? 'Client Price Other$' : 'Other$';
        
        $text = "
        <form id='quoteForm' class='yform columnar' method='post' action='{$formAction}'>
            <div id='errorDisplayBox'></div>
            <fieldset>
                {$formObj->getTBRow('Item Name', 'title', $row['title'])}
                {$formObj->getTBRow('Type', 'item_type', $row['item_type'], array('isEditable' => 0))}
                {$formObj->getTBRow($lblAmt, 'amount', $row['amount'])}
                {$formObj->getTBRow($lblAmtOther, 'amount_other', $row['amount_other'])}
                {$actual}
                {$formObj->getTBRow('Sort Order', 'sort_order', $row['sort_order'])}
                {$formObj->getTARow('Description (if any)', 'description', $row['description'])}
            </fieldset>
            <input type='hidden' name='quote_items_id' value='{$quote_items_id}'>
        </form>
        {$hideOtherAmountText}
        ";

        return $text;
    }
}
