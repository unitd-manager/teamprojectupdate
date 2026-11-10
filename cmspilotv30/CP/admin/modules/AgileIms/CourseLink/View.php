<?
class CP_Admin_Modules_AgileIms_CourseLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rows       = '';
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['title'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['course_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Title", "a.title")}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }

    /**
    */
    function getNew(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

		//$sqlCourse    = $fn->getDDSql('agileIms_course');
        $sqlCourse = "SELECT course_id, title FROM course WHERE published = 1 ORDER BY title";
        $sqlBatch     = '';
        $sqlSubsidy   = '';
        $sqlDiscount  = '';

        $id = $fn->getReqParam('contact_id');
        $today = date('Y-m-d');

        /* For showing subsidy related fields only for citizens */
        $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $id);
        $subsidy = '';
        $subsidyCode = '';
        $discount = '';

        if ($contactRec['is_citizen'] == 1) {
            $subsidy = $formObj->getDDRowBySQL('Subsidy', 'subsidy_discount_id', $sqlSubsidy);
            $subsidyCode = $formObj->getTBRow('TG Ref No', 'subsidy_code');
        } else {
            $discount = $formObj->getDDRowBySQL('Discount', 'discount', $sqlDiscount);
        }

        $reg_fee_lbl = 'Add Registration Fee <br/>[SGD ' . $fn->getSettingsValueByKey("registrationFee") . ']';

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $text = "
        <form id='portalForm' class='yform columnar studentEnrollment' method='post' action='{$formAction}'>
            <fieldset>
            {$formObj->getYesNoRRow($reg_fee_lbl, 'add_registration_fee')}
            {$formObj->getDateRow('Enrollment Date', 'order_date', $today)}
            {$formObj->getDDRowBySQL('Course', 'course_id', $sqlCourse)}
            {$formObj->getDDRowBySQL('Batch', 'batch_id', $sqlBatch)}
            {$subsidy}
            {$discount}
            {$subsidyCode}
            <h2 class='mt20'><b>AUTO GENERATION OF INVOICE/RECEIPT</b></h2>
            <div class='highlightBorder'>
                {$formObj->getYesNoRRow('Do you like to create invoice', 'auto_generate_invoice', 1)}
                {$formObj->getYesNoRRow('Do you like to create receipt', 'auto_generate_receipt')}
            </div>
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$id}' />
            <input type='hidden' name='is_citizen' value='{$contactRec['is_citizen']}' />
            <input type='hidden' name='registration_fee' value='{$cpCfg['registrationFee']}' />

            <div class='mb10'><b><u>ENROLLMENT SUMMARY</u></b></div>
            <table id='courseSummary' class='thinlist'>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th class='txtRight'>Amount</th>
                    </tr>
                </thead>

                <tr id='courseAmount'></tr>
                <tr id='subsidyAmount'></tr>
                <tr id='discountAmount'></tr>

                <tr id='registrationAmount'>
                    <td>Registration Amount</td>
                    <td class='regAmount txtRight'></td>
                </tr>

                <tr id='totalAmount'>
                    <td>Total</td>
                    <td class='amount txtRight'></td>
                </tr>
            </table>
        </form>
        ";

        return $text;
    }

    /**
    */
    function getEditStudentEnrollment(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $id       = $fn->getReqParam('course_contact_id');
        $row      = $fn->getRecordRowByID('course_contact', 'course_contact_id', $id);
        $rowOrder = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
        
        $sqlOi = "
        SELECT SUM(unit_price) AS total_order_amount
        FROM order_item
        WHERE order_id = {$row['order_id']}
        ";
        $resultOi  = $db->sql_query($sqlOi);
        $rowOi     = $db->sql_fetchrow($resultOi);
        $total = $rowOi['total_order_amount'] ;

        $currentDate  = date("Y-m-d");
        $subsidyTotal = '';
        $discTotal    = '';

        $sqlBatch  = "
        SELECT b.batch_id
             ,CONCAT(
             b.title
             ,' : '
             , b.start_date
             ,' : '
             , b.status
             ,' :Engaged-'
             , (SELECT COUNT(*)
                FROM course_contact cc
                WHERE cc.batch_id = b.batch_id
                ))
             as batch_title
        FROM batch b
        WHERE b.course_id = {$row['course_id']}
          AND (status='Open' || status='Closed')
        ";

        $sqlSubsidy  = "
        SELECT sd.subsidy_discount_id
              ,sd.title
        FROM subsidy_discount sd
        LEFT JOIN (course_subsidy_history s) ON (sd.subsidy_discount_id = s.subsidy_discount_id)
        WHERE s.course_id = {$row['course_id']}
        AND sd.category_type = 'Subsidy'
        AND valid_from_date <= '{$currentDate}'
        AND valid_to_date >= '{$currentDate}'
        ";

        $sqlDiscount  = "
        SELECT sd.subsidy_discount_id
              ,sd.title
        FROM subsidy_discount sd
        LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
        WHERE csh.course_id = {$row['course_id']}
        AND sd.category_type = 'Discount'
        AND valid_from_date <= '{$currentDate}'
        AND valid_to_date >= '{$currentDate}'
        ";

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $exp  = array('sqlCourse' => 'TwoField');
        $exp1 = array('sqlBatch' => 'TwoField');
        $exp2 = array('sqlSubsidy' => 'TwoField');
        $exp3 = array('sqlDiscount' => 'TwoField');
        $expEdit = array('isEditable' => 0);

        $cRec = $fn->getRecordRowByID('course', 'course_id', $row['course_id']);
        $course = "
        <div class='type-text ym-fbox-text row_course_id non-editable'>
            <label for='fld_course_id'>Course</label>
            <div>{$cRec['title']}</div>
        </div>
        ";

        /* For showing subsidy only for citizens */
        $autoReceipt = '';
        $subsidy = '';
        $subsidyCode = '';
        $discount = '';
        $contactRec     = $fn->getRecordRowByID('contact', 'contact_id', $row['contact_id']);
        $subsidyCodeRec = $fn->getRecordRowByID('subsidy_paid_history', 'order_id ', $row['order_id']);

        if ($contactRec['is_citizen'] == 1) {
            $subsidy = $formObj->getDDRowBySQL('Subsidy', 'subsidy_discount_id', $sqlSubsidy, $row['subsidy_discount_id'], $exp2);
            $subsidyCode = $formObj->getTBRow('TG Ref No', 'subsidy_code', $subsidyCodeRec['subsidy_code']);

            /* Showing discount drop-down only if no receipt is generated for the enrollment */
            $sqlRec = "
            SELECT r.receipt_id
            FROM receipt r
            LEFT JOIN (`order` o) ON (r.order_id = o.order_id)
            LEFT JOIN (course_contact cc) ON (o.order_id = cc.order_id)
            WHERE cc.course_contact_id = {$id}
              AND r.receipt_status = 'Paid'
            ";
            $resultRec  = $db->sql_query($sqlRec);
            $numRowsRec = $db->sql_numrows($resultRec);
            
            if ($numRowsRec == 0) {
                $autoReceipt = $formObj->getYesNoRRow('Do you like to create receipt', 'auto_generate_receipt');
                $enrollment_date = $formObj->getDateRow('Enrollment Date', 'order_date', $rowOrder['order_date']);
            } else {
                $enrollment_date = "
                {$formObj->getDateRow('Enrollment Date', 'order_date', $rowOrder['order_date'], $expEdit)}
                <input type='hidden' id='order_date' name='order_date' value='{$rowOrder['order_date']}' />
                ";
            }
        } else {
            /* Showing discount drop-down only if no receipt is generated for the enrollment */
            $sqlRec = "
            SELECT r.receipt_id
            FROM receipt r
            LEFT JOIN (`order` o) ON (r.order_id = o.order_id)
            LEFT JOIN (course_contact cc) ON (o.order_id = cc.order_id)
            WHERE cc.course_contact_id = {$id}
              AND r.receipt_status = 'Paid'
            ";
            $resultRec  = $db->sql_query($sqlRec);
            $numRowsRec = $db->sql_numrows($resultRec);
            
            if ($numRowsRec == 0) {
                $autoReceipt = $formObj->getYesNoRRow('Do you like to create receipt', 'auto_generate_receipt');
                $discount = $formObj->getDDRowBySQL('Discount', 'discount', $sqlDiscount, $row['subsidy_discount_id'], $exp3);
                $enrollment_date = $formObj->getDateRow('Enrollment Date', 'order_date', $rowOrder['order_date']);
            } else {
                $enrollment_date = "
                {$formObj->getDateRow('Enrollment Date', 'order_date', $rowOrder['order_date'], $expEdit)}
                <input type='hidden' id='order_date' name='order_date' value='{$rowOrder['order_date']}' />
                ";
                $subsidyDiscRec = $fn->getRecordRowByID('subsidy_discount', 'subsidy_discount_id', $row['subsidy_discount_id']);
                $discount = "
                <div class='type-text ym-fbox-text row_course_id non-editable'>
                    <label for='fld_course_id'>Discount</label>
                    <div>{$subsidyDiscRec['title']}</div>
                </div>
                <input type='hidden' id='discount' name='discount' value='{$row['discount']}' />
                <i>NOTE: To apply discount, please cancel the receipt, going to Finance.</i>
                ";
            }
        }

        $reg_amt = 0;
        if ($row['add_registration_fee'] == 1) {
            $reg_amt = $fn->getSettingsValueByKey("registrationFee");
        }
        
        if($row['subsidy_discount_type'] == 'Subsidy' && $row['subsidy_discount_id'] != ''){
            $sql1 = "
            SELECT sd.*
            FROM subsidy_discount sd
            WHERE sd.subsidy_discount_id = {$row['subsidy_discount_id']}
            ";
            $result1  = $db->sql_query($sql1);
            $row1 = $db->sql_fetchrow($result1);

            $total = 0;
            if ($cRec['price'] != '') {
                $total = $cRec['price'] + $reg_amt;
            }

            if ($row1['value'] != '') {
                if($row1['mode_of_calculation'] == 'Value'){
                    $subsidyTotal = $row1['value'];
                }
                else{
                    $subsidyTotal = ($cRec['price']*$row1['value'])/100;
                    $total = $cRec['price'] - $subsidyTotal + $reg_amt;
                }
            }
        }

        if($row['subsidy_discount_type'] == 'Discount' && $row['subsidy_discount_id'] != ''){
            $sql1 = "
            SELECT sd.*
            FROM subsidy_discount sd
            WHERE sd.subsidy_discount_id = {$row['subsidy_discount_id']}
            ";
            $result1  = $db->sql_query($sql1);
            $row1 = $db->sql_fetchrow($result1);

            $total = 0;
            if ($cRec['price'] != ''){
                $total = $cRec['price'] + $reg_amt;
            }

            if ($row1['value'] != ''){
                if($row1['mode_of_calculation'] == 'Value'){
                    $discTotal = $row1['value'];
                }
                else{
                    $discTotal = ($cRec['price']*$row1['value'])/100;
                    $total = $cRec['price'] - $discTotal + $reg_amt;
                }
            }
        }
        
        $text = "
        <form id='portalFormEdit' class='yform columnar studentEnrollmentEdit' method='post' action='{$formAction}'>
            <fieldset>
                <div class='highlightBorder'>
                    {$autoReceipt}
                </div>
                {$enrollment_date}
                {$course}
                {$formObj->getDDRowBySQL('Batch', 'batch_id', $sqlBatch, $row['batch_id'], $exp1)}
                {$subsidy}
                {$subsidyCode}
                {$discount}
            </fieldset>
            <input type='hidden' id='course_contact_id' name='course_contact_id' value='{$id}' />
            <input type='hidden' id='course_id' name='course_id' value='{$row['course_id']}' />
            <input type='hidden' id='reg_fee' name='reg_fee' value='{$reg_amt}' />

            <div class='mb10'><b><u>ENROLLMENT SUMMARY</u></b></div>
            <table id='courseSummary' class='thinlist'>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th class='txtRight'>Amount</th>
                    </tr>
                </thead>
                <tr id='courseAmount'>
                    <td>{$cRec['title']}</td>
                    <td class='amount txtRight'>{$cRec['price']}</td>
                </tr>

                <tr id='registrationAmount'>
                    <td>Registration Amount</td>
                    <td class='amount txtRight'>{$reg_amt}</td>
                </tr>

                <tr id='subsidyAmount'>
                    <td>Subsidy</td>
                    <td class='amount txtRight'>{$subsidyTotal}</td>
                </tr>

                <tr id='discountAmount'>
                    <td>Discount</td>
                    <td class='amount txtRight'>{$discTotal}</td>
                </tr>

                <tr id='totalAmount'>
                    <td>Total</td>
                    <td class='amount txtRight'>{$total}</td>
                </tr>
            </table>
        </form>
        ";

        return $text;
    }

    /**
    */
    function getCourseSummary($course_id){
        $fn = Zend_Registry::get('fn');

        if ($course_id == '') {
            return $text = '';
        }

        $row = $fn->getRecordRowByID('course', 'course_id', $course_id);

        $text = "
        <td>{$row['title']}</td>
        <td class='amount txtRight'>{$row['price']}</td>
        ";

        return $text;
    }

    /**
    */
    function getCourseCompanySummary($course_id){
        $fn = Zend_Registry::get('fn');

        $num_rows = $fn->getReqParam('num_rows');

        if ($course_id == '') {
            return $text = '';
        }

        $row = $fn->getRecordRowByID('course', 'course_id', $course_id);

        $price = $num_rows * $row['price'];

        $text = "
        <td>{$row['title']}</td>
        <td class='txtCenter'>{$row['price']}</td>
        <td class='amount txtRight'>{$price}</td>
        ";

        return $text;
    }

    /**
    */
    function getSubsidyData($subsidy_discount_id, $course_contact_id, $course_id){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        if ($subsidy_discount_id == '') {
            return $text = '';
        }
        $total = '';
        if($course_contact_id){
            $CCRec  = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);
            $courseRec  = $fn->getRecordRowByID('course', 'course_id', $CCRec['course_id']);
        }
        else{
            $courseRec  = $fn->getRecordRowByID('course', 'course_id', $course_id);
        }

        $sql = "
        SELECT sd.*
        FROM subsidy_discount sd
        WHERE sd.subsidy_discount_id = {$subsidy_discount_id}
        ";
        $result  = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);

        if ($row['mode_of_calculation'] == 'Value') {
            $total = $row['value'];
        } else {
            $total = ($courseRec['price']*$row['value'])/100;
        }

        $text = "
        <td>{$row['title']}</td>
        <td class='amount txtRight'>{$total}</td>
        ";

        return $text;
    }
}
