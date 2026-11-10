<?
class CP_Admin_Modules_Directory_BusinessLink_View extends CP_Common_Modules_Directory_BusinessLink_View
{
    /**
     *
     */
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $ln = Zend_Registry::get('ln');

        $rows       = '';
        $rowCounter = 0;
        
        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['business_name'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['business_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,$ln->gd('m.directory.businessLink.lbl.name'), 'business_name')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";
        
        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($tv['srcRoom'] == 'directory_businessContact') {
            $formAction = "index.php?_spAction=save&srcRoom={$tv['srcRoom']}&lnkRoom={$tv['lnkRoom']}&showHTML=0";
            $exp = array('sqlType' => 'OneField');
    
            $text = "
            <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
                <fieldset>
                    {$formObj->getTBRow($ln->gd('m.directory.businessLink.lbl.position'), 'position', $row['position'])}
                </fieldset>
                <input type='hidden' name='business_contact_link_id' value='{$row['business_contact_link_id']}' />
            </form>
            ";
    
            return $text;
        }
    }
}
