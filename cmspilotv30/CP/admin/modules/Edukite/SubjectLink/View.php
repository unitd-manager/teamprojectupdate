<?
class CP_Admin_Modules_Edukite_SubjectLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{

    /**
     *
     */
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $db = Zend_Registry::get('db');

        $rows       = '';
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['title'] )}
            {$listObj->getListDataCell($row['group'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['subject_id'])}
            ";
            
            $rowCounter++;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Subject'  , 's.title'    )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Group' , 's.group'   )}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";
        
        return $text;
    }
}
