<?
class CP_Admin_Modules_Tradingsg_tasks_View extends CP_Common_Lib_ModuleViewAbstract

{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
			 
           
			
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['tasks_id'])}
            {$listObj->getGoToDetailText($count, $row['task1'])}
           
           {$listObj->getListDataCell($row['date'])}
		   
            {$listObj->getListDataCell($row['status_filter'])}
            {$listObj->getListDataCell($row['description'])}
            {$listObj->getListRowEnd($row['tasks_id'])}
            ";
            $count++;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
		{$listObj->getListHeaderCell('tasks_id', 't.tasks_id')}
        {$listObj->getListHeaderCell('task1', 't.task1')}
       
         
        {$listObj->getListHeaderCell('date', 't.date')}
		
        {$listObj->getListHeaderCell('status_filter', 't.status_filter')}
        
        {$listObj->getListHeaderCell('description', 't.description')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
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
		
		
		
		

        
       $fielset="
        {$formObj->getTBRow('task1', 'task1')}
         ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";
        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
		$tv = Zend_Registry::get('tv');

        $expNoEdit = array('isEditable' => 0);

        
       $fielset1 ="
        
		 {$formObj->getTBRow('tasks_id', 'tasks_id', $row['tasks_id'], $expNoEdit)}
        {$formObj->getTBRow('task1', 'task1',  $row['task1'])}
		{$formObj->getDateRow('date', 'date', $row['date'])}
		{$formObj->getTBRow('status_filter', 'status_filter', $row['status_filter'])}
		{$formObj->getTextBoxRow('description', 'description', $row['description'])}
		
       
        ";
		 $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        ";
        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
      
        $media = Zend_Registry::get('media');
        

       
       
       
      

        $text = "
       
        
        {$media->getRightPanelMediaDisplay('Attachments', 'tradingsg_tasks', 'attachment', $row)}
       
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

       
        $status_filter     = $fn->getReqParam('status_filter');

        //==================================================================//
        

        $sqltask = "

        SELECT t.status_filter
        FROM `tasks` t
		
        ";

        $text = "
        <td>
            <select name='status'>
                <option value=''>status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqltask, $status_filter)}
            </select>
        </td>
       
        ";

        return $text;
    }

}