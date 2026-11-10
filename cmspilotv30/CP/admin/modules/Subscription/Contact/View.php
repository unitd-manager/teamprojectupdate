<?
class CP_Admin_Modules_Subscription_Contact_View extends CP_Common_Modules_Subscription_Contact_View
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $rows = '';

        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $email = $row['email'];
			$name = strtoupper($row['first_name']);	

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getGoToDetailText($rowCounter, $row['last_name'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['year_of_joining'])}
            {$listObj->getListDataCell($row['gender'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['mobile'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['contact_id'])}
            ";

            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('First Name', 'c.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'c.last_name')}
        {$listObj->getListHeaderCell('Email', 'c.email')}
        {$listObj->getListHeaderCell('Date of Joining', 'c.year_of_joining')}
        {$listObj->getListHeaderCell('Gender', 'c.gender')}
        {$listObj->getListHeaderCell('Phone', 'c.phone')}
        {$listObj->getListHeaderCell('Mobile', 'c.mobile')}
        {$listObj->getListHeaderCell('Status', 'c.status')}
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
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
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
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode  = $tv['action'];

        $sqlAcademicSchool = $fn->getValueListSQL('academicSchool');
        $sqlStatus = $fn->getValueListSQL('studentStatus');
        $expVL = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);

        $formActionStatus = "index.php?module=subscription_contact&_spAction=changeStatusForm&contact_id={$row['contact_id']}&showHTML=0";
        
        $sqlParent = "
        UPDATE student SET status = 'Active'
        WHERE status = 'Withdraw'
        ";

        if($row['status'] == 'Withdraw') {
            $statusBtn ="
            <div class='button'>
                <a id='actBtn_statusToActive' contact_id={$row['contact_id']} href='#'>Change to Active</a>
            </div>
            ";
        } else {
            $statusBtn ="
            <div class='button'>
                <a id='actBtn_status' href='{$formActionStatus}' contact_id={$row['contact_id']}>Change Status</a>
            </div>
            ";
        }

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);
        
		$spArrayGender = array (
			 "Male"
		    ,"Female"	
		);
        
        $fieldset1 = "
        {$formObj->getDDRowByArr('Gender', 'gender', $spArrayGender, $row['gender'])}
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Address', 'address_area', $row['address_area'])}
        {$formObj->getTBRow('City/Town', 'address_city', $row['address_city'])}
        {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
        {$formObj->getTBRow('Zip Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'], $expCountry)}
        {$formObj->getDateRow('Date of Joining', 'year_of_joining', $row['year_of_joining'])}
        {$formObj->getTBRow('Status', 'status', $row['status'], $expNoEdit)}
        {$statusBtn}
        ";

        $contact_id = $row['contact_id'];

        $sqlParent = "
        SELECT p.first_name AS parent_name
        FROM parent_contact pc
        LEFT JOIN (parent p) ON (pc.parent_id = p.parent_id)
        LEFT JOIN (contact c) ON (c.contact_id = pc.contact_id)
        WHERE c.contact_id = {$contact_id}
        ";
        $expPar = array('sqlType' => 'OneField');

        $fieldset2 = "
		{$formObj->getTARow('Reasons for Withdrawal', 'with_drawal', $row['with_drawal'])}
		{$formObj->getDDRowBySQL('Refund Payable To', 'refund_payable', $sqlParent, $row['refund_payable'], $expPar)}
		{$formObj->getTBRow('Bank Ac', 'refund_payable_bank_ac', $row['refund_payable_bank_ac'])}
		";
		
        $fieldset3 = "
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getYesNoRRow('Newsletter Subscribed', 'subscribe', $row['subscribe'])}
        ";
        
        $withdrawDetails = '';
        if($row['with_drawal'] != '' && $row['refund_payable'] != '' && $row['refund_payable_bank_ac'] != ''){
            $withdrawDetails = $formObj->getFieldSetWrapped('Withdraw Details', $fieldset2);
        }
        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$withdrawDetails}
        {$formObj->getFieldSetWrapped('Other Details', $fieldset3)}
        {$formObj->getCreationModificationText($row)}
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
        $comment = getCPPluginObj('common_comment');

        $record_id = $fn->getIssetParam($row, 'contact_id');

//        {$comment->getView(array(
    //         'roomName' => 'subscription_contact'
      //      ,'recordId' => $record_id
  //      ))}

        $formActionGroup = "index.php?_topRm=subscriptionr&module=subscription_contact&_spAction=addSubscriptionForm&contact_id={$row['contact_id']}&showHTML=0";

        $text = "
        <div class='button mb5'>
            <a href='{$formActionGroup}' id='addSubscriptionForm'>Add Subscription</a>
        </div>
		<div>
        <div class='mb5'>
		{$this->getAddSubscriptionListView($row['contact_id'])}
		</div>
        {$media->getRightPanelMediaDisplay('Picture', 'subscription_contact', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachment', 'subscription_contact', 'attachment', $row)}
        {$comment->getView(array(
             'roomName' => 'subscription_contact'
            ,'recordId' => $record_id
        ))}
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

        $subscribe       		  = $fn->getReqParam('subscribe');
        $special_search  		  = $fn->getReqParam('special_search');
        $category        		  = $fn->getReqParam('category');
        $status          		  = $fn->getReqParam('status');
        $continuing_to_next_year  = $fn->getReqParam('continuing_to_next_year');
        
        
        $previous_year = date('Y') - 1;
        $next_year = date('Y') + 1;
        $yearArray = array(
              $previous_year
             ,date('Y')
             ,$next_year
        );
        $sqlStudentStatus = $fn->getValueListSQL('studentStatus');


        //==================================================================//
        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
             ,"Batch-Not-Linked"
        );


        $spArrayContinuation = array(
              "Yes"
             ,"No"
        );

        $text = "

        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStudentStatus, $status)}
            </select>
        </td>
        <td>
            <select name='continuing_to_next_year'>
                <option value=''>Continuation Form Received</option>
                {$cpUtil->getDropDown1($spArrayContinuation, $continuing_to_next_year)}
            </select>
        </td>
        
        ";

        return $text;
    }

    /**
     *
     */
     function getAddSubscriptionForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $contact_id  = $fn->getReqParam('contact_id');

        $expNoEdit  = array('isEditable' => 0);
        $expStatus = array('sqlType' => 'OneField');

        $formAction = "index.php?_topRm=subscription&module=subscription_contact&_spAction=addsubscriptionFormSubmit&showHTML=0";

		$spArrayYear = array ('2001' ,'2002' ,'2003','2004','2005','2006','2007','2008', '2009', '2010', '2011', '2012', '2013', '2014', '2015'
							  ,'2016' ,'2017' ,'2018' ,'2019' ,'2020' ,'2021' ,'2022' ,'2023' ,'2024' ,'2025' );
        
        $checkedInvoice = "checked='checked'";
		$checked = "";

        $text = "
        <form id='portalForm' class='yform columnar addsubscriptionForm' method='post' action='{$formAction}'>
            {$formObj->getDDRowByArr('From Year', 'from_year', $spArrayYear)}
            {$formObj->getDDRowByArr('To Year', 'to_year', $spArrayYear)}
            {$formObj->getTBRow('Amount', 'amount', $cpCfg['cp.amount'], $expNoEdit)}
            Generate Invoice <input type='checkbox' name='invoice' value='' {$checkedInvoice} class='subscriptionInvoiceCheckBox' disabled ='false' /><br><br>
            Generate Receipt <input type='checkbox' name='receipt' value='1' class='subscriptionReceiptCheckBox' />
            <input type='hidden' name='contact_id' value='{$contact_id}' />
        </form>
        ";
        return $text;

    }

    /**
     *
     */
    function getAddSubscriptionListView($contact_id) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');


        $SQL = "
        SELECT o.order_id
              ,o.from_year
              ,o.to_year
              ,o.amount
        FROM `order` o
        WHERE o.contact_id = {$contact_id}
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';

	        while ($row = $db->sql_fetchrow($result)) {

		        $rows .= "
		            <tr>
		                <td>{$row['from_year']}</td>
		                <td>{$row['to_year']}</td>
		                <td>{$cpCfg['cp.amount']}</td>
		            </tr>
		        ";
			}
	
	        $text = '';	
	
			if ($numRows > 0)  {
	        $text = "	
	        <table class ='list'>
	            <thead>
	            <tr>
	                <th>From Year</th>
	                <th>To Year</th>
	                <th>Amount</th>
	            </tr>
	            </thead>
	            <tbody>
	                {$rows}
	            </tbody>
	        </table>
	        ";
	
	        return $text;
	
		}
    }


}
