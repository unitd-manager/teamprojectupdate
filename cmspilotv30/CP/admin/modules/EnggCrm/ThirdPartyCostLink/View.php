<?
class CP_Admin_Modules_EnggCrm_ThirdPartyCostLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Item Title', 'item_title')}
                {$formObj->getTBRow('Budget Amount', 'budget_amount')}
                {$formObj->getTBRow('Actual Amount', 'actual_amount')}
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('third_party_cost', 'third_party_cost_id', $id);

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Item Title', 'item_title', $row['item_title'])}
                {$formObj->getTBRow('Budget Amount', 'budget_amount', $row['budget_amount'])}
                {$formObj->getTBRow('Actual Amount', 'actual_amount', $row['actual_amount'])}
            </fieldset>
            <input type='hidden' name='third_party_cost_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
}
