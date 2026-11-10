<?
class CP_Admin_Modules_ManPower_InvoiceItem_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getNew() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');

        $text  = '';

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <div id='errorDisplayBox'></div>
            <fieldset>
                {$formObj->getTARow('Description', 'description')}
                {$formObj->getTBRow('Amount', 'amount')}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $id = $fn->getReqParam('id');
        if ($id == ''){
            return;
        }

        $row = $fn->getRecordRowByID('invoice_items', 'invoice_items_id', $id);

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <div id='errorDisplayBox'></div>
            <fieldset>
                {$formObj->getTARow('Description', 'description', $row['description'])}
                {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
            </fieldset>
            <input type='hidden' name='invoice_items_id' value='{$id}'>
            <input type='hidden' name='invoice_id' value='{$row['invoice_id']}' />
        </form>
        ";

        return $text;
    }
}
