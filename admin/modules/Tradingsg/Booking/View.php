<?
class CPL_Admin_Modules_Tradingsg_Booking_View extends CP_Common_Lib_ModuleViewAbstract
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
            $booking_date = $fn->getCPDate($row['booking_date'], 'd-m-Y');

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($booking_date)}
            {$listObj->getListDataCell($row['assign_time'])}
            {$listObj->getListDataCell($row['c_company_name'])}
            {$listObj->getListDataCell($row['employee_name'])}
            {$listObj->getListDataCell($row['c_address_flat'].', '.$row['c_address_street'].', '.$row['c_address_town'].', '.$row['c_address_state'])}
            {$listObj->getListRowEnd($row['booking_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Date', 'b.booking_date')}
        {$listObj->getListHeaderCell('Assign Time', 'b.assign_time')}
        {$listObj->getListHeaderCell('Customer Name', 'c.company_name')}
        {$listObj->getListHeaderCell('Employee Name', 'e.first_name')}
        {$listObj->getListHeaderCell('Address', 'c.address_flat')}
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $newCustomerUrl = 'index.php?_spAction=newCustomer&lnkRoom=tradingsg_booking&showHTML=0';

        $newCustomerUrl = "<a class='jqui-dialog-form' formId='portalForm' title='New Customer' 
            w=600 h=560 href='' link='{$newCustomerUrl}' callback='cpm.tradingsg.booking.afterNewCustomer'>Add New Customer</a>";

        $expCustomer = array('notesRight'  => $newCustomerUrl, 'placeholder' => 'Please type and select');
        $fieldset = "
        {$formObj->getTBRow('Customer Name', 'c_company_name', '', $expCustomer)}
        <input type='hidden' name='customer_id' value='' />
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
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
        $expNoEdit = array('isEditable' => 0);

        $statusArr = array(
              "Scheduled"
             ,"In progress"
             ,"Completed"
        );

        $expEmpName = array('placeholder' => 'Please type and select');

        $fielset1 = "
        {$formObj->getDateRow('Booking Date', 'booking_date', $row['booking_date'])}
        <div id='clientFields'>{$this->getClientFields($row['customer_id'])}</div>
        {$formObj->getTBRow('Employee Name', 'employee_name', $row['employee_name'], $expEmpName)}
        {$formObj->getTimeRow('Assign Time', 'assign_time', $row['assign_time'])}
        {$formObj->getDDRowByArr('Status', 'status', $statusArr, $row['status'])}
        {$formObj->getTBRow('GPS Parameter', 'gps_parameter', $row['gps_parameter'], $expNoEdit)}
        <input type='hidden' name='customer_id' value='{$row['customer_id']}' />
        <input type='hidden' name='employee_id' value='{$row['employee_id']}' />
        ";     
        
        $text = "
        {$formObj->getFieldSetWrapped('Booking Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getClientFields($customer_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        if($customer_id == ''){
            $customer_id = $fn->getReqParam('customer_id');
        }

        $comRec = $fn->getRecordRowByID('company', 'company_id', $customer_id);

        $formObj->mode  = $tv['action'];
        $expNoEdit = array('isEditable' => 0);

        $address = $comRec['address_flat'].', '.$comRec['address_street'].', '.$comRec['address_town'].', '.$comRec['address_state'];

        $editCustomerUrl = "index.php?_spAction=editCustomer&lnkRoom=tradingsg_booking&customer_id={$customer_id}&showHTML=0";

        $editCustomerUrl = "<a class='jqui-dialog-form' formId='portalForm' title='Edit Customer' 
            w=600 h=560 href='' link='{$editCustomerUrl}' callback='cpm.tradingsg.booking.afterEditCustomer'>Edit Customer</a>";
        $expCustomer = array('notesRight'  => $editCustomerUrl);
        
        $text = "
        {$formObj->getTBRow('Customer Name', 'c_company_name', $comRec['company_name'], $expCustomer)}
        {$formObj->getTBRow('Address', 'c_address_flat', $address, $expNoEdit)}
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
        $formObj = Zend_Registry::get('formObj');
   
        $special_search     = $fn->getReqParam('special_search');
        $booking_date1 = $fn->getReqParam('booking_date_1');
        $booking_date2 = $fn->getReqParam('booking_date_2');
        $employee_id = $fn->getReqParam('employee_id');

        $SQLemployee = "
        SELECT employee_id
              ,CONCAT_WS(' ', first_name, last_name) AS employee_name
        FROM employee
        ORDER BY employee_name
        ";
        
        $text = "
        <td>
            {$formObj->getDateRangeRow('Booking Date:', 'booking_date', $booking_date1, $booking_date2)}
        </td>
        <td class='fieldValue'>
            <select name='employee_id'>
                <option value=''>Employee</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLemployee, $employee_id)}
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
        $comment = getCPPluginObj('common_comment');
        
        $rows = "";
        $record_id = $fn->getIssetParam($row, 'booking_id');

        $text = "
        <div id='bookingServiceLinkPortal'>
            {$this->getServiceLinkPortal($row['booking_id'])}
        </div>
        {$media->getRightPanelMediaDisplay("Customer Acknowledgement", "tradingsg_booking", "picture", $row)}
        {$comment->getView(array(
             'roomName' => 'tradingsg_booking'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getNewCustomer(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=addCustomer&lnkRoom=tradingsg_booking&showHTML=0";
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Name', 'company_name')}
                {$formObj->getTBRow('Phone', 'phone')}
                {$formObj->getTBRow('Website', 'website')}
                {$formObj->getTBRow('Address 1', 'address_flat')}
                {$formObj->getTBRow('Address 2', 'address_street')}
                {$formObj->getTBRow('Area', 'address_town')}
                {$formObj->getTBRow('Zip Code', 'address_state')}
                {$formObj->getTBRow('Latitude', 'latitude')}
                {$formObj->getTBRow('Longitude', 'longitude')}
            </fieldset>
            
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditCustomer(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $customer_id = $fn->getReqParam('customer_id');
        $comRec = $fn->getRecordRowByID('company', 'company_id', $customer_id);

        $formAction = "index.php?_spAction=editCustomerSubmit&lnkRoom=tradingsg_booking&showHTML=0";
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Name', 'company_name', $comRec['company_name'])}
                {$formObj->getTBRow('Phone', 'phone', $comRec['phone'])}
                {$formObj->getTBRow('Website', 'website', $comRec['website'])}
                {$formObj->getTBRow('Address 1', 'address_flat', $comRec['address_flat'])}
                {$formObj->getTBRow('Address 2', 'address_street', $comRec['address_street'])}
                {$formObj->getTBRow('Area', 'address_town', $comRec['address_town'])}
                {$formObj->getTBRow('Zip Code', 'address_state', $comRec['address_state'])}
                {$formObj->getTBRow('Latitude', 'latitude', $comRec['latitude'])}
                {$formObj->getTBRow('Longitude', 'longitude', $comRec['longitude'])}
                <input type='hidden' name='customer_id' value='{$customer_id}' />
            </fieldset>            
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getServiceLinkPortal($booking_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($booking_id == ''){
            $booking_id = $fn->getReqParam('booking_id');
        }

        $rows = '';

        $recCount = $fn->getRecordCount('booking_service', "booking_id = '{$booking_id}'");

        $SQL="
        SELECT *
        FROM booking_service
        WHERE booking_id = '{$booking_id}'
        ORDER BY booking_service_id
        ";
        $result   = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>{$row['service']}</td>
            </tr>
            ";
        }

        $text = "
        <div class='linkPortalWrapper tradingsg_booking_serviceLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Services Linked</div>
                    <div class='txtRight'>
                        <span class='count' id='AddProductPricePortalCount'>({$recCount})</span>
                        <div class='toggle'></div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='productPricelist'>
                        <tbody id='AddProductPricePortal'>
                            {$rows}
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        ";

        return $text;
    }
}