<?
class CP_Admin_Modules_Directory_CityLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $city_code = '';
            if ($cpCfg['m.directory.city.showCodeFld']){
                $city_code = $listObj->getListDataCell($row['city_code']);
            }

            $state = '';
            if ($cpCfg['m.directory.city.hasState']){
                $state = $listObj->getListDataCell($row['state_title']);
            }
            
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['title'])}
            {$city_code}
            {$state}
            {$listObj->getListDataCell($row['country_name'])}
            {$listObj->getListDataCell($row['city_id'], 'center')}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['city_id'])}
            ";

            $rowCounter++;
        }

        $city_code = '';
        if ($cpCfg['m.directory.city.showCodeFld']){
            $city_code = $listLinkObj->getListHeaderCellLink($linkRecType, 'City Code', 'c.city_code');
        }
         
        $state = '';
        if ($cpCfg['m.directory.city.hasState']){
            $state = $listLinkObj->getListHeaderCellLink($linkRecType, 'State', 's.title');
        }
        $text = "
    	{$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'City', 'c.title')}
        {$city_code}
        {$state}
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'Country', 'co.title')}
        {$listLinkObj->getListHeaderCellLink($linkRecType, 'ID', 'c.city_id', 'headerCenter')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";
		
        return $text;
    }

}