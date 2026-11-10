<?
class CP_Admin_Modules_EnggCrm_CompanyAddressLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{

   function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rows       = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['address_flat'])}
            {$listObj->getListDataCell($row['address_street'])}
            {$listObj->getListDataCell($row['address_town'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['company_address_id'])}
            ";

            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Flat/Apartment/House', 'ca.address_flat')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Street Address', 'ca.address_street')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Town/ Suburb', 'ca.address_town')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }
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
          		{$formObj->getTBRow('Flat/Apartment/House', 'address_flat')}
          		{$formObj->getTBRow('Street Address', 'address_street')}
          		{$formObj->getTBRow('Town/ Suburb', 'address_town')}
          		{$formObj->getTBRow('State', 'address_state')}
          		{$formObj->getTBRow('Country', 'address_country')}
          		{$formObj->getTBRow('PO Code', 'address_po_code')}
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
        $row = $fn->getRecordRowByID('company_address', 'company_address_id', $id);
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
          		{$formObj->getTBRow('Flat/Apartment/House', 'address_flat', $row['address_flat'])}
          		{$formObj->getTBRow('Street Address', 'address_street', $row['address_street'])}
          		{$formObj->getTBRow('Town/ Suburb', 'address_town', $row['address_town'])}
          		{$formObj->getTBRow('State', 'address_state', $row['address_state'])}
          		{$formObj->getTBRow('Country', 'address_country', $row['address_country'])}
          		{$formObj->getTBRow('PO Code', 'address_po_code', $row['address_po_code'])}
            </fieldset>
            <input type='hidden' name='company_address_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
}