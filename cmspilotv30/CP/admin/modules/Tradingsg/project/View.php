<?
class CP_Admin_Modules_Tradingsg_project_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['project_id'])}
            {$listObj->getGoToDetailText($count, $row['title'])}
           
           {$listObj->getListDataCell($row['start_date'])}
		   {$listObj->getListDataCell($row['end_date'])}
            {$listObj->getListDataCell($row['amount'])}
            {$listObj->getListDataCell($row['description'])}
            {$listObj->getListRowEnd($row['project_id'])}
            ";
            $count++;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
		{$listObj->getListHeaderCell('project_id', 'p.project_id')}
        {$listObj->getListHeaderCell('title', 'p.title')}
       
         
        {$listObj->getListHeaderCell('start_date', 'p.start_date')}
		{$listObj->getListHeaderCell('end_date', 'p.end_date')}
        {$listObj->getListHeaderCell('amount', 'p.amount')}
        
        {$listObj->getListHeaderCell('description', 'p.description')}
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

        $fielset = "
        {$formObj->getTBRow('title', 'title')}
       
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

        $expNoEdit = array('isEditable' => 0);

        $fielset1 = "
		 {$formObj->getTBRow('project_id', 'project_id', $row['project_id'], $expNoEdit)}
        {$formObj->getTBRow('title', 'title',  $row['title'])}
        
       
		{$formObj->getTextBoxRow('description', 'description', $row['description'])}
		{$formObj->getTBRow('amount', 'amount', $row['amount'])}
        {$formObj->getDateRow('start_date', 'start_date', $row['start_date'])}
		{$formObj->getDateRow('end_date', 'end_date', $row['end_date'])}
        ";


       

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        
        
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

       
        $project_id     = $fn->getReqParam('project_id');

        //==================================================================//
        

        $sqlproject = "

        SELECT p.project_id
        FROM `project` p
		
        ";

        $text = "
        <td>
            <select name='project_id'>
                <option value=''>id</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlproject, $project_id)}
            </select>
        </td>
       
        ";

        return $text;
    }

   /**
     *
     */
	  function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        

       
       
       
     //  $project_id = $fn->getIssetParam($row, 'project_id');

        $text = "
        <div id='projectPriceLinkPortal'>
            {$this->getProjectPriceDetail($row['project_id'])}
        </div>
        
        {$media->getRightPanelMediaDisplay('Attachments', 'tradingsg_project', 'attachment', $row)}
       
        ";

        return $text;
    }

    /**
     *
     */
	 
	 
	  function getProjectPriceDetail($project_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        $Project = $this->getProjectPriceDetailList($project_id);

        $recCount = $fn->getRecordCount('project_price', "project_id = '{$project_id}'");

       $header ="
        <thead>
            <tr>
                <th>start_date</th>
                <th>amount</th>
                <th>title</th>
                <th>description</th>
            </tr>
        </thead>
        ";
		
        $formActionProjectPrice = "index.php?module=tradingsg_project&_spAction=AddProjectPrice&project_id={$project_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddProjectPrice' href='{$formActionProjectPrice}' project_id={$project_id}>Add</a>
                </div>";

   
	  $text = "
	   

       <div class='linkPortalWrapper tradingsg_project_projectPriceLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Project Price Linked</div>
                    <div class='txtRight'>
					
                      
					
                        <div class='toggle'></div>
	
                    </div>
                </div>
            </div>
			  
            <div class='linkPortalDataWrapper'>
			
                <form>
                    <table class='projectPricelist'>
                        {$header}
                        <tbody id='AddProjectPricePortal'>
                            {$Project}
                        </tbody>
                    </table>
                    <input type='hidden' name='project_id' value='{$project_id}' />
                </form>
				
            </div>
            {$add}
       </div>
		 
        
        ";

        return $text;

    }

    /**
     *  <span class='count' id='AddProjectPricePortalCount'>({$fn->getRecordCount('project_price', "project_id = '{$project_id}'")})</span>
     */
    function getProjectPriceDetailList($project_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        $rows  = "";

        $SQL="
        SELECT pp.amount
              ,pp.agent_commission
              
              ,pp.description
              ,pp.start_date
              ,pp.title
              ,pp.project_price_id
              ,pp.project_id
        FROM project_price pp
        WHERE project_id = '{$project_id}'
        ORDER BY pp.start_date DESC
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if($numRows == 0){
            $SQL="
            SELECT p.*
                 
                  
            FROM project p
            WHERE project_id = '{$project_id}'
            ";
            $result   = $db->sql_query($SQL);
        }

        $count = 1;
        $qty_balance = '';
        while ($row = $db->sql_fetchrow($result)) {
            
            $rows .= "
            <tr>
                <td>{$row['start_date']}</td>
                <td>{$row['amount']}</td>
                <td>{$row['title']}</td>
                <td>{$row['description']}</td>
            </tr>
            ";
            $count++;
        }

        $text="{$rows}";

        return $text;
    }

        /**
     *
     */
    function getAddProjectPrice() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $project_id = $fn->getReqParam('project_id');

        $projectRec = $fn->getRecordRowByID('project', 'project_id', $project_id);

        $formAction = "index.php?_topRm=inventory&module=tradingsg_project&_spAction=AddProjectPriceSubmit&showHTML=0";
       // $expNoEdit = array('isEditable' => 0);

      
            $text = "
            <form id='AddProjectPriceForm' class='AddProjectPriceForm yform columnar' method='post' action='{$formAction}'>
                {$formObj->getTBRow('amount', 'amount', $projectRec['amount'])}
                {$formObj->getTBRow('title', 'title', $projectRec['title'])}
                {$formObj->getTEXTBOXRow('description', 'description', $projectRec['description'])}
                <input type='hidden' name='project_id' value='{$project_id}' />
				
				 
            </form>
            ";
       // } else{
           /* $text = "
            <form id='AddProductPriceForm' class='AddProductPriceForm yform columnar' method='post' action='{$formAction}'>
                {$formObj->getTBRow('Price', 'price', $productRec['price'])}
                {$formObj->getTBRow('Product Weight(kg)', 'product_weight', $productRec['product_weight'])}
                {$formObj->getTBRow('GST %', 'gst', $productRec['gst'])}
                <input type='hidden' name='product_id' value='{$product_id}' />
            </form>
            ";
        }*/
            //{$formObj->getTBRow('TP Commission(%)', 'tp_commission', $productRec['tp_commission'])}

        return $text;
    }

    /**
     *
     */

}
   


		
		
		


    

