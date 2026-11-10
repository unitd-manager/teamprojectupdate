<?
class CP_Admin_Modules_Edukite_TaskLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
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
            {$listObj->getListDataCell($row['last_name'] )}
            {$listObj->getListDataCell($row['first_name'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['student_id'])}
            ";
            
            $rowCounter++;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Last Name'  , 'a.last_name'    )}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'First Name' , 'a.first_name'   )}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";
        
        return $text;
    }
}
