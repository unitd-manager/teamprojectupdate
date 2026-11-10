<?
class CP_Admin_Modules_ManPower_ExpenseLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
    */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        
        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDateRow('Date', 'date')}
                {$formObj->getTBRow('Amount', 'amount')}
                {$formObj->getTARow('Description', 'description')}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
        </form>
        ";

        return $text;
    }
    
    /**
     *
    */
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('expense', 'expense_id', $id);
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDateRow('Date', 'date', $row['date'])}
                {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
                {$formObj->getTARow('Description', 'description', $row['description'])}
            </fieldset>
            <input type='hidden' name='expense_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
}
