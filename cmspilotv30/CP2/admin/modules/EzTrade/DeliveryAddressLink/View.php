<?
class CP_Admin_Modules_EzTrade_DeliveryAddressLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Address Line 1', 'address_flat')}
                {$formObj->getTBRow('Address Line 2', 'address_street')}
                {$formObj->getTBRow('City', 'address_town')}
                {$formObj->getTBRow('State', 'address_state')}
                {$formObj->getTBRow('Post Code/Zip', 'address_po_code')}
                {$formObj->getTBRow('Country', 'address_country')}
                {$formObj->getTBRow('Phone', 'phone')}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
        </form>
        ";

        return $text;
    }

    //==================================================================//
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');


        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $id = $fn->getReqParam('id');
        $rowDA = $fn->getRecordRowByID('delivery_address', 'delivery_address_id', $id);
        
        $row = $fn->getRecordRowByID('delivery_address', 'delivery_address_id', $id);
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Address Line 1', 'address_flat', $row['address_flat'])}
                {$formObj->getTBRow('Address Line 2', 'address_street', $row['address_street'])}
                {$formObj->getTBRow('City', 'address_town', $row['address_town'])}
                {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
                {$formObj->getTBRow('Post Code/Zip', 'address_po_code', $row['address_po_code'])}
                {$formObj->getTBRow('Country', 'address_country', $row['address_country'])}
                {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
            </fieldset>
            <input type='hidden' name='delivery_address_id' value='{$id}' />
        </form>
        ";

        return $text;
    }

}