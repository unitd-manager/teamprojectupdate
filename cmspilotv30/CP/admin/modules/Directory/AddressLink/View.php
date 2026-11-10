<?
class CP_Admin_Modules_Directory_AddressLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['country_title'])}
            {$listObj->getListDataCell($row['state_title'])}
            {$listObj->getListDataCell($row['city_title'])}
            {$listObj->getListDataCell($row['borough_title'])}
            {$listObj->getListDataCell($row['area_title'])}
            {$listObj->getListDataCell($row['street_title'])}
            {$listObj->getListDataCell($row['shop_center_title'])}
            {$listObj->getListDataCell($row['address_street_no_from'])}
            {$listObj->getListDataCell($row['address_street_no_to'])}
            {$listObj->getListDataCell($row['address_building_name'])}
            {$listObj->getListDataCell($row['address_block'])}
            {$listObj->getListDataCell($row['address_floor_from'])}
            {$listObj->getListDataCell($row['address_unit_from'])}
            {$listObj->getListDataCell($row['address_po_code'])}
            {$listObj->getListDataCell($row['address_id'], 'center')}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['address_id'])}
            
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listLinkObj->getListHeaderLink()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.country'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.state'), 's.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.city'), 'ci.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.borough'), 'b.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.area'), 'a.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.street'), 's.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.shopCenter'), 'sc.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.st.#(from)'), 'ad.address_street_no_from')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.st.#(to)'), 'ad.address_street_no_from')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.buildingName'), 'ad.address_building_name')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.block'), 'ad.address_block')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.floor'), 'ad.address_floor_from')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.unit'), 'ad.address_unit_from')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.zipCode'), 'ad.address_po_code')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.addressLink.lbl.id'), 'ad.address_id', 'headerCenter')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
		";
        return $text;
    }
}
