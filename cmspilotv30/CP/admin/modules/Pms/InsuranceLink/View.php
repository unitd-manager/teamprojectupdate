<?
class CP_Admin_Modules_Pms_InsuranceLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $expEdit = array('isEditable' => 0);
        
        $order_id= $tv['srcRoomId'];

        $rows = '';
         
        $SQL = "
        SELECT i.*
            ,ir.invoice_receipt_history_id 	
            ,ir.amount as invoice_hist_amount
            ,ir.invoice_date as invoice_due_date
            ,ir.receipt_id as receipt_paid_id
            ,inst.title as invoice_hist_title
            ,IF(si.invoice_receipt_history_id > 0, 'Protected', '') AS protected_status
        FROM invoice i
        LEFT JOIN `invoice_receipt_history` ir ON (ir.invoice_id = i.invoice_id)
        LEFT JOIN `installment` inst ON (ir.installment_id = inst.installment_id)
        LEFT JOIN `student_insurance` si ON (ir.invoice_receipt_history_id = si.invoice_receipt_history_id)
        WHERE i.order_id = {$order_id}
          AND inst.title != 'Registration'
        ";

        $result = $db->sql_query($SQL);
        $count = 1;
        $invoice_hist_amount ='';
        while ($row = $db->sql_fetchrow($result)) {
            $status = '';
            if($row['protected_status'] == 'Protected'){
                $status = 'DISABLED';
            }
            $invoice_id = $row['invoice_id'];
            $invoice_hist_amount = round($row['invoice_hist_amount'], 3);
            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' name='invoiceHistId[]' value='{$row['invoice_receipt_history_id']}' class='invoiceCodePvt' $status>
                    </div>
                    <div class='float_left'>{$row['invoice_hist_title']} ({$invoice_hist_amount} SGD)</div>
                    <div class=''> : {$row['protected_status']}</div>
                </div>
            </div>
            ";
            $count++;
        }

        $sqlCourse = "
        SELECT DISTINCT c.course_id
              ,c.title 
        FROM course c
        LEFT JOIN course_contact cc ON (c.course_id = cc.course_id)
        WHERE cc.order_id = {$tv['srcRoomId']}
        ";
        $resultCourse   = $db->sql_query($sqlCourse);
        $rowCourse      = $db->sql_fetchrow($resultCourse); 
        $sqlInsurance   = $fn->getDDSql('pms_insurance');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                <h3>Please select Invoice</h3>
                {$rows}
                {$formObj->getTBRow('Course', '', $rowCourse['title'], $expEdit)}
                {$formObj->getDDRowBySQL('Insurance Company', 'insurance_id', $sqlInsurance)}
                {$formObj->getTBRow('Certificate of Insurance', 'code')}
                {$formObj->getDateRow('Insurance Start Date', 'insurance_start_date')}
                {$formObj->getDateRow('Insurance End Date', 'insurance_end_date')}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
            <input type='hidden' name='course_id' value='{$rowCourse['course_id']}' />
        </form>
        ";

        return $text;
    }
    /**
     *
     */
    function getNew1(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $expEdit = array('isEditable' => 0);
        
        $order_id= $tv['srcRoomId'];

        $rows = '';
         
        $SQL = "
        SELECT i.*
            ,ir.installment_id 	
            ,ir.amount as invoice_hist_amount
            ,ir.invoice_date as invoice_due_date
            ,ir.title as invoice_hist_title
            ,IF(si.installment_id > 0, 'Protected', '') AS protected_status
        FROM invoice i
        LEFT JOIN `installment` ir ON (ir.invoice_id = i.invoice_id)
        LEFT JOIN `invoice_receipt_history` irh ON (ir.installment_id = irh.installment_id)
        LEFT JOIN `student_insurance` si ON (ir.installment_id = si.installment_id)
        WHERE i.order_id = {$order_id}
          AND ir.title != 'Registration'
        ";

        $result = $db->sql_query($SQL);
        $count = 1;
        $invoice_hist_amount ='';
        while ($row = $db->sql_fetchrow($result)) {
            $status = '';
            if($row['protected_status'] == 'Protected'){
                $status = 'DISABLED';
            }
            $invoice_id = $row['invoice_id'];
            $invoice_hist_amount = round($row['invoice_hist_amount'], 3);
            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' name='invoiceHistId[]' value='{$row['installment_id']}' class='invoiceCodePvt' $status>
                    </div>
                    <div class='float_left'>{$row['invoice_hist_title']} ({$invoice_hist_amount} SGD)</div>
                    <div class=''> : {$row['protected_status']}</div>
                </div>
            </div>
            ";
            $count++;
        }

        $sqlCourse = "
        SELECT DISTINCT c.course_id
              ,c.title 
        FROM course c
        LEFT JOIN course_contact cc ON (c.course_id = cc.course_id)
        WHERE cc.order_id = {$tv['srcRoomId']}
        ";
        $resultCourse   = $db->sql_query($sqlCourse);
        $rowCourse      = $db->sql_fetchrow($resultCourse); 
        $sqlInsurance   = $fn->getDDSql('pms_insurance');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                <h3>Please select Invoice</h3>
                {$rows}
                {$formObj->getTBRow('Course', '', $rowCourse['title'], $expEdit)}
                {$formObj->getDDRowBySQL('Insurance Company', 'insurance_id', $sqlInsurance)}
                {$formObj->getTBRow('Certificate of Insurance', 'code')}
                {$formObj->getDateRow('Insurance Start Date', 'insurance_start_date')}
                {$formObj->getDateRow('Insurance End Date', 'insurance_end_date')}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
            <input type='hidden' name='course_id' value='{$rowCourse['course_id']}' />
        </form>
        ";

        return $text;
    }
    /**
     *
     */
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        
        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('student_insurance', 'student_insurance_id', $id);
        $rowCourse = $fn->getRecordRowByID('course', 'course_id', $row['course_id']);
        $exp = array('isEditable' => 0);

        $sqlInsurance = $fn->getDDSql('pms_insurance');

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL('Course', '', '', $rowCourse['title'], $exp)}
                {$formObj->getDDRowBySQL('Insurance Company', 'insurance_id', $sqlInsurance, $row['insurance_id'])}
                {$formObj->getTBRow('Certificate of Insurance', 'code', $row['code'])}
                {$formObj->getDateRow('Insurance Start Date', 'insurance_start_date', $row['insurance_start_date'])}
                {$formObj->getDateRow('Insurance End Date', 'insurance_end_date', $row['insurance_end_date'])}
            </fieldset>
            <input type='hidden' name='student_insurance_id' value='{$id}' />
            <input type='hidden' name='course_id' value='{$row['course_id']}' />
            <input type='hidden' name='order_id' value='{$row['order_id']}' />
        </form>
        ";

        return $text;
    }
}
