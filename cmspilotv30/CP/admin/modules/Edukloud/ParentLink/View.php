<?
class CP_Admin_Modules_Edukloud_ParentLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rows       = '';
        $rowCounter = 0;
        $company = '';

        foreach ($dataArray as $row){

            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['first_name'])}
            {$listObj->getListDataCell($row['email'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['parent_id'])}
            ";
            $rowCounter++ ;
        }


        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Name', 'first_name')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Email', 'c.email')}
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

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('First Name', 'first_name')}
                {$formObj->getTBRow('Last Name', 'last_name')}
                {$formObj->getTBRow('NRIC no', 'id_card_no')}
                {$formObj->getTBRow('Telephone', 'phone')}
                {$formObj->getTBRow('Mobile', 'mobile')}
                {$formObj->getTBRow('Email', 'email')}
                {$formObj->getTBRow('Relationship to student', 'relationship_to_student')}
                {$formObj->getTBRow('Occupation', 'occupation')}
                {$formObj->getTBRow('Address 1', 'address_flat')}
                {$formObj->getTBRow('Address 2', 'address_street')}
                {$formObj->getTBRow('Postal Code', 'address_po_code')}
                {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry)}
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

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('parent', 'parent_id', $id);
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
                {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
                {$formObj->getTBRow('NRIC no', 'id_card_no', $row['id_card_no'])}
                {$formObj->getTBRow('Telephone', 'phone', $row['phone'])}
                {$formObj->getTBRow('Mobile', 'Mobile', $row['mobile'])}
                {$formObj->getTBRow('Email', 'email', $row['email'])}
                {$formObj->getTBRow('Relationship to student', 'relationship_to_student', $row['relationship_to_student'])}
                {$formObj->getTBRow('Occupation', 'occupation', $row['occupation'])}
                {$formObj->getTBRow('Address 1', 'address_flat', $row['address_flat'])}
                {$formObj->getTBRow('Address 2', 'address_street', $row['address_street'])}
                {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
                {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'])}
            </fieldset>
            <input type='hidden' name='parent_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');


        //==================================================================//
        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";


        return $text;
    }
}
