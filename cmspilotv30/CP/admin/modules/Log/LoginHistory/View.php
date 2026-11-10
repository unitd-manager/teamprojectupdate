<?
class CP_Admin_Modules_Common_LoginHistory_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows       = "";

        foreach ($dataArray as $row){
            $rows .="
    		{$listObj->getListRowHeader($row, $rowCounter)}
    		{$listObj->getListDataCell($row['contact_name'])}
    		{$listObj->getListDataCell($row['login_date'])}
    		{$listObj->getListDataCell($row['login_time'])}
    		{$listObj->getListRowEnd($row['login_history_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
    	{$listObj->getListHeaderCell('Contact Name', 'contact_name')}
    	{$listObj->getListHeaderCell('Login Date', 'login_date')}
    	{$listObj->getListHeaderCell('Login Time', 'login_time')}
    	{$listObj->getListHeaderEnd()}
        {$rows}
	    {$listObj->getListFooter()}
		";
		
        return $text;
    }


    /**
     *
     */
    function getQuickSearch() {
    }
}
