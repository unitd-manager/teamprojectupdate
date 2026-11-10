<?
class CP_Admin_Modules_Trading_DeliveryAddressLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $countryArr = $dbUtil->getArrayFromSQLForVL($sqlCountry);
        $expCountry = array('useKey' => 0);

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Address Line 1', 'address_flat')}
                {$formObj->getTBRow('Address Line 2', 'address_street')}
                {$formObj->getTBRow('Town/City', 'address_town')}
                {$formObj->getTBRow('State', 'address_state')}
                {$formObj->getTBRow('Post Code/Zip', 'address_po_code')}
                {$formObj->getDropDownRowByArray('Country', 'address_country',
                           $countryArr, '', $expCountry)}
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
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('delivery_address', 'delivery_address_id', $id);
        
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $countryArr = $dbUtil->getArrayFromSQLForVL($sqlCountry);
        $expCountry = array('detailValue' => $row['address_country'], 'useKey' => 0);

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Address Line 1', 'address_flat', $row['address_flat'])}
                {$formObj->getTBRow('Address Line 2', 'address_street', $row['address_street'])}
                {$formObj->getTBRow('Town/City', 'address_town', $row['address_town'])}
                {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
                {$formObj->getTBRow('Post Code/Zip', 'address_po_code', $row['address_po_code'])}
                {$formObj->getDropDownRowByArray('Country', 'address_country',
                           $countryArr, $row['address_country'], $expCountry)}                
                {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
            </fieldset>
            <input type='hidden' name='delivery_address_id' value='{$id}' />
        </form>
        ";

        return $text;
    }

}