<?
class CPL_Admin_Modules_EnggCrm_Vehicle_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['vehicle_no'])}           
            {$listObj->getListDataCell($row['year_of_purchase'])}		   
            {$listObj->getListDataCell($row['model'])}           
            ";
            $count++;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
		
        {$listObj->getListHeaderCell('Vehicle No', 'v.vehicle_no')} 
        {$listObj->getListHeaderCell('Year Of Purchase', 'v.year_of_purchase')}
        {$listObj->getListHeaderCell('Model', 'v.model')}
        
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
        {$formObj->getTBRow('Vehicle No', 'vehicle_no')}
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
        $db = Zend_Registry::get('db');

        $expNoEdit = array('isEditable' => 0);

         $formObj->mode = $tv['action'];

          $vehicle_id = $fn->getReqParam('vehicle_id');
           

        
        $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y-H-i-s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y-H-i-s');
        
        $colorTM = 'green';
        $colorTC = 'green';
        $colorTLC = 'green';
        $colorSC = 'green';
        $colorFC = 'green';
        $colorOO = 'green';
        $colorOC = 'green';
        $colorTA = 'green';

        $arrowTM = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowTC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowTLC= "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowSC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowFC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowOO = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowOC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
        $arrowTA = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='22'/>";
       
        $expCode = array('isEditable' => 0);

        $sqlfuel = "
        SELECT SUM(vl.amount) AS amount
        FROM vehicle_fuel vl
        WHERE 
          vl.vehicle_id = {$row['vehicle_id']}
          
        ";
        $resultfuel = $db->sql_query($sqlfuel);  
        $rowfuel = $db->sql_fetchrow($resultfuel);

        $amount = number_format($rowfuel['amount'],2);

        $sqlinsurance = "
        SELECT vi.*
        FROM vehicle_insurance vi
        WHERE 
          vi.vehicle_id = {$row['vehicle_id']}
          ORDER BY vi.vehicle_insurance_id DESC          
        ";
        $resultinsurance = $db->sql_query($sqlinsurance);  
        $rowinsurance = $db->sql_fetchrow($resultinsurance);
           
        $sqlservice = "
        SELECT SUM(vs.amount) AS amount
        FROM vehicle_service vs
        WHERE 
          vs.vehicle_id = {$row['vehicle_id']}
          
        ";
        $resultservice = $db->sql_query($sqlservice);  
        $rowservice = $db->sql_fetchrow($resultservice);

        $amount = number_format($rowservice['amount'],2);
           
        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Vehicle Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$modification_date}</div>
                    
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
        
		
        <td> {$formObj->getTBRow('Vehicle No', 'vehicle_no',  $row['vehicle_no'])}</td>
		<td>{$formObj->getTBRow('Year Of Purchase', 'year_of_purchase', $row['year_of_purchase'])}</td>
		<td>{$formObj->getTBRow('Model', 'model', $row['model'])}</td>
		</tr>

        
        <tr>
        
        <td>
            <div class='float_left mr20'>
                {$formObj->getTBRow('Fuel', 'amount', $rowfuel['amount'], $expNoEdit)}
            </div>
            <div class='float_left mr20 mt5'>
                <a class='addActualCharge' vehicle_id={$row['vehicle_id']}>Add</a>
            </div>
        </td>
        
        <td>
            <div class='float_left mr20'>
                {$formObj->getTBRow('Insurance', 'renewal_date', $rowinsurance['renewal_date'], $expNoEdit)}
            </div>
            <div class='float_left mr20 mt5'>
                <a class='addRenewalDate' vehicle_id={$row['vehicle_id']}>Add</a>
            </div>
        </td>

        <td>
            <div class='float_left mr20'>
                {$formObj->getTBRow('Service', 'amount', $rowservice['amount'], $expNoEdit)}
            </div>
            <div class='float_left mr20 mt5'>
                <a class='addService' vehicle_id={$row['vehicle_id']}>Add</a>
            </div>
        </td>
        </tr>

        </tbody>
      </table>
        </div>
        </div>
        </div>
        ";
		
        return $text;
    }

    function getAddActualCharge() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $vehicle_id     = $fn->getReqParam('vehicle_id');
        $amount = $fn->getReqParam('amount');

        $today = date("Y-m-d");
        
        $rowVehicle     = $fn->getRecordRowByID('vehicle', 'vehicle_id', $vehicle_id);

        $liters = "<input type='text' value='' id='liters' class='text lineItemLiters' name='liters'>";
        $amount      = "<input type='text' value='' id='amount' class='text lineItemAmount' name='amount'>";
        /*$date = "<input type='text' allowEdit='1' name='date' class='fld_date'
                    id='fld_date' value='' />";*/
        $date = $formObj->getDateRow('', 'date', $today);

        $rows = "
        <tr>
            <td>{$date}</td>
            <td>{$amount}</td>
            <td>{$liters}</td>
        </tr>
        ";          

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th class='txtCenter'>Date</th>
            <th class='txtCenter'>Amount</th>
            <th>Liters</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=main&module=enggCrm_vehicle&_spAction=actualchargeSubmit&showHTML=0";
        $expNoEdit = array('isEditable' => 0);

        $SQL = "
        SELECT vl.*
        FROM `vehicle_fuel` vl
        WHERE vl.vehicle_id = {$vehicle_id}
         
        ORDER BY vl.vehicle_fuel_id DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $rowActual = '';
        while ($row = $db->sql_fetchrow($result)) {
            $date = $fn->getCPDate($row['date'], 'd-m-Y');
            $rowActual .= "
            <tr>
                <td>{$date}</td>
                <td>{$row['amount']}</td>
                <td>{$row['liters']}</td>
            </tr>
            ";
        }
        
        $text = "
        <form id='actualChargeForm' class='actualChargeForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expNoEdit)}
            <table class='thinlist' id='actualChargesTable'>
                <tr><th colspan='3'>Fuel</th></tr>
                {$header}
                {$rows}
                {$rowActual}
            </table>
            <input type='hidden' name='vehicle_id' value='{$vehicle_id}' />
           
        </form>
         ";


        return $text;
    }

    function getAddRenewalDate() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $vehicle_id     = $fn->getReqParam('vehicle_id');
        $renewal_date = $fn->getReqParam('renewal_date');
         $today = date("Y-m-d");

        $rowVehicle     = $fn->getRecordRowByID('vehicle', 'vehicle_id', $vehicle_id);

       $insurance_date = $formObj->getDateRow('', 'insurance_date', $today);
        $insurance_amount      = "<input type='text' value='' id='insurance_amount' class='text lineItemInsuranceAmount' name='insurance_amount'>";
        /*$date = "<input type='text' allowEdit='1' name='date' class='fld_date'
                    id='fld_date' value='' />";*/
        $renewal_date = $formObj->getDateRow('', 'renewal_date', $today);
        $rows = "
        <tr>
            <td>{$insurance_date}</td>
            <td>{$insurance_amount}</td>
            <td>{$renewal_date}</td>
        </tr>
        ";          

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th class='txtCenter'>Insurance_Date</th>
            <th class='txtCenter'>Amount</th>
            <th>Renewal_Date</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=main&module=enggCrm_vehicle&_spAction=renewaldateSubmit&showHTML=0";
        $expNoEdit = array('isEditable' => 0);

        $SQL = "
        SELECT vi.*
        FROM `vehicle_insurance` vi
        WHERE vi.vehicle_id = {$vehicle_id}
         
        ORDER BY vi.vehicle_insurance_id DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $rowActual = '';
        while ($row = $db->sql_fetchrow($result)) {
            $insurance_date = $fn->getCPDate($row['insurance_date'], 'd-m-Y');
            $renewal_date = $fn->getCPDate($row['renewal_date'], 'd-m-Y');
           
            $rowActual .= "
            <tr>
                <td>{$insurance_date}</td>
                <td>{$row['insurance_amount']}</td>
                <td>{$renewal_date}</td>
            </tr>
            ";
        }
        
        $text = "
        <form id='actualChargeForm' class='actualChargeForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expNoEdit)}
            <table class='thinlist' id='actualChargesTable'>
                <tr><th colspan='3'>Insurance</th></tr>
                {$header}
                {$rows}
                {$rowActual}
            </table>
            <input type='hidden' name='vehicle_id' value='{$vehicle_id}' />
           
        </form>
         ";


        return $text;
    }

    function getAddService() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $vehicle_id     = $fn->getReqParam('vehicle_id');
        $amount = $fn->getReqParam('amount');

        $today = date("Y-m-d");
        $rowVehicle     = $fn->getRecordRowByID('vehicle', 'vehicle_id', $vehicle_id);

         $description = "<textarea value='' id='description' class='text lineItemDescription' name='description'></textarea>";
        $amount      = "<input type='text' value='' id='amount' class='text lineItemAmount' name='amount'>";
        /*$date = "<input type='text' allowEdit='1' name='date' class='fld_date'
                    id='fld_date' value='' />";*/
        $date = $formObj->getDateRow('', 'date', $today);

        $rows = "
        <tr>
            <td>{$date}</td>
            <td>{$amount}</td>
            <td>{$description}</td>
        </tr>
        ";          

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th class='txtCenter'>Date</th>
            <th class='txtCenter'>Amount</th>
            <th>description</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=main&module=enggCrm_vehicle&_spAction=serviceSubmit&showHTML=0";
        $expNoEdit = array('isEditable' => 0);

        $SQL = "
        SELECT vs.*
        FROM `vehicle_service` vs
        WHERE vs.vehicle_id = {$vehicle_id}
         
        ORDER BY vs.vehicle_service_id DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $rowActual = '';
        while ($row = $db->sql_fetchrow($result)) {
            $date = $fn->getCPDate($row['date'], 'd-m-Y');
            $rowActual .= "
            <tr>
                <td>{$date}</td>
                <td>{$row['amount']}</td>
                <td>{$row['description']}</td>
            </tr>
            ";
        }
        
        $text = "
        <form id='actualChargeForm' class='actualChargeForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expNoEdit)}
            <table class='thinlist' id='actualChargesTable'>
                <tr><th colspan='3'>Service</th></tr>
                {$header}
                {$rows}
                {$rowActual}
            </table>
            <input type='hidden' name='vehicle_id' value='{$vehicle_id}' />
           
        </form>
         ";


        return $text;
    }
    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
       $db = Zend_Registry::get('db');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        

        $text = "
       
        
        {$media->getRightPanelMediaDisplay('Attachments', 'enggCrm_vehicle', 'attachment', $row)}
       
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

       
        $model     = $fn->getReqParam('model');

        //==================================================================//
        

        $sqltask = "

        SELECT v.model
        FROM `vehicle` v
		
        ";

        $text = "
        <td>
            <select name='model'>
                <option value=''>model</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqltask, $model)}
            </select>
        </td>
       
        ";

        return $text;
    }

}