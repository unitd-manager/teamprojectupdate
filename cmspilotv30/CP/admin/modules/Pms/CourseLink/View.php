<?
class CP_Admin_Modules_Pms_CourseLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
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

		$sqlCourse    = $fn->getDDSql('pms_course');
        $sqlBatch     = '';
        $sqlSubsidy   = '';
        $sqlDiscount  = '';

        $id = $fn->getReqParam('id');
        
        /* For showing subsidy related fields only for citizens */
        $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $tv['srcRoomId']);
        $subsidy = '';
        $subsidyCode = '';
        $discount = '';
        if ($contactRec['is_citizen'] == 1) {
            $subsidy = $formObj->getDDRowBySQL('Subsidy', 'course_subsidy_history_id', $sqlSubsidy);
            $subsidyCode = $formObj->getTBRow('TG Ref No', 'subsidy_code');
        } else {
            $discount = $formObj->getDDRowBySQL('Discount', 'discount', $sqlDiscount);
        }
        
        /* Declare all the labels here to change the label names using this config variable */
        $course = 'Course';
        $batch = 'Batch';
        if($cpCfg['m.pms.courseLink.hasLabelChangeEnt']){
            $course = 'Class';
            $batch = 'Session';
        }
        
        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
            {$formObj->getDDRowBySQL($course, 'course_id', $sqlCourse)}
            {$formObj->getDDRowBySQL($batch, 'batch_id', $sqlBatch)}
            {$subsidy}
            {$discount}
            {$subsidyCode}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
            <input type='hidden' name='is_citizen' value='{$contactRec['is_citizen']}' />
            
            <table id='courseSummary' class='list'>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th class='txtRight'>Amount</th>
                    </tr>
                </thead>
                <tr id='courseAmount'>
                </tr>
    
                <tr id='subsidyAmount'>
                </tr>
    
                <tr id='discountAmount'>
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
    function getEdit(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $currentDate  = date("Y-m-d");

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $id);
        $subsidyTotal = '';
        $discTotal = '';
        
        $sqlCourse   = $fn->getDDSql('pms_course');
        $traineeCount = "
              (SELECT COUNT(*)
				FROM course_contact cc
				WHERE cc.batch_id = b.batch_id
                AND b.status = 'Open'
				) 
        ";
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
        SELECT s.course_subsidy_history_id
              ,sd.title 
        FROM course_subsidy_history s
        LEFT JOIN (subsidy_discount sd) ON (s.subsidy_discount_id = sd.subsidy_discount_id)
        WHERE s.course_id = {$row['course_id']}
        AND sd.category_type = 'Subsidy'
        AND valid_from_date <= '{$currentDate}'              
        AND valid_to_date >= '{$currentDate}'              
        ";

        $sqlDiscount  = "
        SELECT s.course_subsidy_history_id
              ,sd.title 
        FROM course_subsidy_history s
        LEFT JOIN (subsidy_discount sd) ON (s.subsidy_discount_id = sd.subsidy_discount_id)
        WHERE s.course_id = {$row['course_id']}
        AND sd.category_type = 'Discount'
        AND valid_from_date <= '{$currentDate}'              
        AND valid_to_date >= '{$currentDate}'              
        ";

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp  = array('sqlCourse' => 'TwoField');
        $exp1 = array('sqlBatch' => 'TwoField');
        $exp2 = array('sqlSubsidy' => 'TwoField');
        $exp3 = array('sqlDiscount' => 'TwoField');
        $expTextBox = array('isEditable' => 0);

        if ($row['order_id'] != '' && $row['course_id'] != ''){
            $cRec = $fn->getRecordRowByID('course', 'course_id', $row['course_id']);
            $course = $formObj->getTextBoxRow('Course', 'course_id', $cRec['title'], $expTextBox);

        } else {
            $course = $formObj->getDDRowBySQL('Course', 'course_id', $sqlCourse, $row['course_id'], $exp);
        }
        
        /* For showing subsidy only for citizens */
        $subsidy = '';
        $subsidyCode = '';
        $discount = '';
        $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $row['contact_id']);
        $subsidyCodeRec = $fn->getRecordRowByID('subsidy_paid_history', 'order_id ', $row['order_id']);
        if ($contactRec['is_citizen'] == 1) {
            $subsidy = $formObj->getDDRowBySQL('Subsidy', 'course_subsidy_history_id', $sqlSubsidy, $row['course_subsidy_history_id'], $exp2);
            $subsidyCode = $formObj->getTBRow('TG Ref No', 'subsidy_code', $subsidyCodeRec['subsidy_code']);
        } else {
            $discount = $formObj->getDDRowBySQL('Discount', 'discount', $sqlDiscount, $row['discount'], $exp3);
        }

        if($row['course_subsidy_history_id'] > 0){
            $sql1 = "
            SELECT sd.*
            FROM subsidy_discount sd
            LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
            WHERE csh.course_subsidy_history_id = {$row['course_subsidy_history_id']}
            ";
            $result1  = $db->sql_query($sql1);
            $row1 = $db->sql_fetchrow($result1);

            $total = 0;
            if ($cRec['price'] != ''){
                $total = $cRec['price'];
            }
            
            if ($row1['value'] != ''){
                if($row1['mode_of_calculation'] == 'Value'){
                    $subsidyTotal = $row1['value'];
                }
                else{
                    $subsidyTotal = ($cRec['price']*$row1['value'])/100;
                }
            }
        }
    
        if($row['discount'] > 0){
            $sql1 = "
            SELECT sd.*
            FROM subsidy_discount sd
            LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
            WHERE csh.course_subsidy_history_id = {$row['discount']}
            ";
            $result1  = $db->sql_query($sql1);
            $row1 = $db->sql_fetchrow($result1);

            $total = 0;
            if ($cRec['price'] != ''){
                $total = $cRec['price'];
            }
            
            if ($row1['value'] != ''){
                if($row1['mode_of_calculation'] == 'Value'){
                    $discTotal = $row1['value'];
                }
                else{
                    $discTotal = ($cRec['price']*$row1['value'])/100;
                    $total = $cRec['price'] - $discTotal;
                }
            }
        }
    
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$course}
                {$formObj->getDDRowBySQL('Batch', 'batch_id', $sqlBatch, $row['batch_id'], $exp1)}
                {$subsidy}
                {$discount}
                {$subsidyCode}
            </fieldset>
            <input type='hidden' id='course_contact_id' name='course_contact_id' value='{$id}' />

            <table id='courseSummary' class='list'>
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
    function getSubsidyData($course_subsidy_history_id, $course_contact_id, $course_id){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        if ($course_subsidy_history_id == '') {
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
        LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
        WHERE csh.course_subsidy_history_id = {$course_subsidy_history_id}
        ";
        $result  = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);

        if($row['mode_of_calculation'] == 'Value'){
            $total = $row['value'];
        }
        else{
            $total = ($courseRec['price']*$row['value'])/100;
        }
    
        
        $text = "
        <td>{$row['title']}</td>
        <td class='amount txtRight'>{$total}</td>
        ";
        
        return $text;
    }
    
    /**
    */
    function getNewCoursePvtLink(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        if (isset($_SESSION['selectedSubjectIds'])){
            unset($_SESSION['selectedSubjectIds']);
        }     

		$sqlCourse    = $fn->getDDSql('pms_course');
		$sqlCourse    = '';
        $sqlBatch     = '';
        $sqlSubsidy   = '';
        $sqlDiscount  = '';
        $sqlSubject   = '';
        
        $sqlSubject  = "
        SELECT s.subject_id
              ,s.title
        FROM subject s
        LEFT JOIN course_subject cs ON (s.subject_id = cs.subject_id)";
        
        $id = $fn->getReqParam('contact_id');
        
        /* For showing subsidy related fields only for citizens */
        $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $tv['srcRoomId']);
        $subsidy = '';
        $subsidyCode = '';
        $sqlCourseType = $fn->getValueListSQL('courseType');
        $expVl = array('sqlType' => 'OneField');
        $arr   = array(1 => "Only Registration", 2 => "Registration & Enrollment");
        
        $discount = '';
        if ($contactRec['is_citizen'] == 1) {
            $subsidy = $formObj->getDDRowBySQL('Subsidy', 'course_subsidy_history_id', $sqlSubsidy);
            $subsidyCode = $formObj->getTBRow('TG Ref No', 'subsidy_code');
        } else {
            $discount = $formObj->getDDRowBySQL('Discount', 'discount', $sqlDiscount);
        } 
        
        $formAction = "index.php?_spAction=addCoursePvtLink&lnkRoom={$tv['lnkRoom']}&showHTML=0&contact_id={$id}";
        $text = "
        <form id='portalPvtLinkForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
            {$formObj->getRadioArrRow('Registration Type ', "registration_type", '', $arr, '')}
            {$formObj->getDDRowBySQL('Course Type', 'course_type', $sqlCourseType,'', $expVl)}
            {$formObj->getDDRowBySQL('Course', 'course_id', $sqlCourse)}
            {$formObj->getYesNoRRow('Add Application Fee', 'add_registration_fee')}
            <div id='hideShortTermFlds'>
                {$formObj->getYesNoRRow('Full Time Student', 'full_time')}
                {$formObj->getTBRow('Number of Months', 'no_of_months')}
                {$formObj->getYesNoRRow('Medical Insurance Required', 'medical_insurance')}
                {$formObj->getTBRow('Contract No', 'contract_no')}
            </div>
            {$formObj->getDDRowBySQL('Batch', 'batch_id', $sqlBatch)}
            <div id='populate_subject_id'></div>
            {$formObj->getTBRow('Discount(%)', 'discount')}
            {$formObj->getTBRow('Number of Installments', 'installment')}
            <div id='updateTotal' class='button'>Update Total</div>
            </fieldset>
            <input type='hidden' name='contact_id' value='{$id}' />
            
            <table id='courseSummaryPvt' class='thinlist hideme'>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th class='txtRight'>Amount(INR)</th>
                    </tr>
                </thead>
                <tr id='courseAmount'>
                </tr>
    
                <tr id='subsidyAmount'>
                </tr>
    
                <tr id='subjectAmount' class=''>
                </tr>
    
                <tr id='insuranceAmount'>
                </tr>
                
                <tr id='discountAmount'>
                </tr>
                
                <tr id='registrationAmount'>
                    <td>Registration Amount</td>
                    <td class='regAmount txtRight'></td>
                </tr>

                <tr id='totalAmount'>
                    <td>Total</td>
                    <td class='amount txtRight'></td>
                </tr>

                <tr id='installmentAmount'>
                    <td>Installment Amount</td>
                    <td class='installAmount txtRight'></td>
                </tr>
            </table>
        </form>
        ";

        return $text;
    }
    
    /**
    */
    function getEditCoursePvtLink(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db'); 
        $dbUtil = Zend_Registry::get('dbUtil');

        if (isset($_SESSION['selectedSubjectIds'])){
            unset($_SESSION['selectedSubjectIds']);
        }        
        $_SESSION['selectedSubjectIds'] = array();

        $course_contact_id = $fn->getReqParam('course_contact_id');
        $SQLSubject      = '';
        $subjectArray    = '';
        $discount_amount = '';
        $subject_amount  = '';
        $inst_amount     = '';
        
        $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);
        $courseStatus = $row['course_status'];
        $full_time    = $row['full_time'];
        $orderRec    = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
        $installment = $orderRec['no_of_installment'];
        $add_registration_fee = $orderRec['add_registration_fee'];
        
        $courseRec  = $fn->getRecordRowByID('course', 'course_id', $row['course_id']);
        $courseType = $courseRec['course_type'];
        
        $expVl = array('sqlType' => 'OneField');
        $arr   = array(1 => "Only Registration", 2 => "Registration & Enrollment");

        $sqlCourseType   = $fn->getValueListSQL('courseType');
        $sqlCourseStatus = $fn->getValueListSQL('courseStatus');
        $sqlCourse       = $fn->getDDSql('pms_course');
        $sqlCourse  = "
        SELECT course_id
              ,title
        FROM course
        WHERE course_type = '{$courseType}'
        ";
        
        // TO get selected subject from the relevant table 
        if ($courseType == 'Long Term'){
            //To get all the subjects related to the course
            $SQLSubject  = "
            SELECT sub.subject_id
                  ,sub.title
            FROM course_subject cs
            LEFT JOIN (subject sub) ON (sub.subject_id = cs.subject_id)
            WHERE cs.course_id = {$row['course_id']}
            ";

            //To get selected subjects related to the course
            $SQLSelectedSubject  = "
            SELECT subject_id
            FROM course_contact_subject_history 
            WHERE course_contact_id = {$course_contact_id}
            ";
            $resultSubject  = $db->sql_query($SQLSelectedSubject);  
            
            $subjectArray   = $dbUtil->getResultsetAsArrayForForm($resultSubject);
            
            $resultSubject  = $db->sql_query($SQLSelectedSubject);  
            while ($rowSubject = $db->sql_fetchrow($resultSubject)) {
                $_SESSION['selectedSubjectIds'][] = $rowSubject['subject_id'];
            }
        }

        //print_r ($_SESSION['selectedSubjectIds']);
        
        if($row['medical_insurance'] == 1){
            $medical_insurance = $fn->getSettingsValueByKey("medicalInsuranceFeePvt");
        }        
        else{
            $medical_insurance = '';
        }
        
        if($row['discount']){
            $trDiscount = getCPModelObj('pms_courseLink')->getDiscountValueForPvt('',$row['discount'], $row['course_id'], $medical_insurance, $full_time);
            $discount_amount = getCPModelObj('pms_courseLink')->getDiscountValueForPvt(1, $row['discount'], $row['course_id'], $medical_insurance, $full_time);
        }        
        else{
            $trDiscount = '';
        }
        
        $count = count($_SESSION['selectedSubjectIds']);
        //$full_time = 1;
        // TO get the subject row and subject total
        if ($count != ''){
            //to get the subject tr
            if($full_time == 1){
                $subject_total     = getCPModelObj('pms_subjectLink')->getAddSubjectAmountToTotal('', 1);
                $trCourse = $subject_total ;
                
                //to get the subject total amount
                $subject_amount = getCPModelObj('pms_subjectLink')->getAddSubjectAmountToTotal(1,1);
            }
            else{
                $subject_total     = getCPModelObj('pms_subjectLink')->
                getAddSubjectAmountToTotal('','',$row['no_of_months']);
                $trCourse = $subject_total ;
                
                //to get the subject total amount
                $subject_amount = getCPModelObj('pms_subjectLink')->getAddSubjectAmountToTotal(1,'',$row['no_of_months']);
            }
            
            $total = $subject_amount  - $discount_amount + $medical_insurance;
            if($installment){
                $inst_amount = $total/$installment;
                $inst_amount = number_format($inst_amount, 2);
            }
        }
        else{
            $trCourse = $this->getCourseSummary($row['course_id']) ;
            $total    =  $courseRec['price']  - $discount_amount + $medical_insurance;
            if($installment){
                $inst_amount = $total/$installment;
                $inst_amount = number_format($inst_amount, 2);
            }
        }
        if ($courseType == 'Long Term'){
            $subjectRow = $formObj->getCheckBoxArrRowBySQL('Subject', 'subject_id', $SQLSubject, $subjectArray);
        }
        else{
            $subjectRow = '';
        }

        $formAction = "index.php?_spAction=saveCoursePvtLink&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp  = array('sqlCourse' => 'TwoField');
        $exp1 = array('sqlBatch' => 'TwoField');
        $expRegType = array('sqlBatch' => 'OneField');
        $expTextBox = array('isEditable' => 0);
        $longTermFields = '';
        
        if ($courseType == 'Long Term'){
            $longTermFields ="
                {$formObj->getYesNoRRow('Full Time Student', 'full_time', $row['full_time'])}
                {$formObj->getTBRow('Number of Months', 'no_of_months', $row['no_of_months'])}
                {$formObj->getYesNoRRow('Medical Insurance Required', 'medical_insurance', $row['medical_insurance'])}     
                {$formObj->getTBRow('Contract No', 'contract_no', $row['contract_no'])}
            ";
        }
        
        $text = "
        <form id='portalPvtLinkEditForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
            {$formObj->getRadioArrRow('Registration Type', "registration_type", $row['registration_type'], $arr, $expRegType)}
            {$formObj->getDDRowBySQL('Course Type', 'course_type', $sqlCourseType, $courseType, $expVl)}
            {$formObj->getDDRowBySQL('Course', 'course_id', $sqlCourse, $row['course_id'], $exp)}
            {$formObj->getYesNoRRow('Add Application Fee', 'add_registration_fee', $row['add_registration_fee'])}
            <div id='hideShortTermFlds'>
            {$longTermFields}
            </div>
            {$formObj->getDDRowBySQL('Course Status', 'course_status', $sqlCourseStatus, $courseStatus, $expVl)}
            {$formObj->getDateRow('Course Termination Date', 'course_termination_date', $row['course_termination_date'], array('rowCls' => 'hideme'))}
            {$formObj->getTARow('Remarks', 'remarks', $row['remarks'], array('rowCls' => 'hideme'))}
            <div id='populate_subject_id'>
                {$subjectRow}
            </div>
                {$formObj->getTBRow('Discount(%)', 'discount', $row['discount'])}
                {$formObj->getTBRow('Number of Installments', 'installment', $installment)}
                <div id='updateTotal' class='button'>Update Total</div>
            </fieldset>
            <input type='hidden' name='course_contact_id' value='{$course_contact_id}' />

            <table id='courseSummaryPvt' class='thinlist'>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th class='txtRight'>Amount(INR)</th>
                    </tr>
                </thead>
                <tr id='courseAmount'>
                {$trCourse}
                </tr>
    
                <tr id='subsidyAmount'>
                </tr>
    
                <tr id='subjectAmount' class=''>
                </tr>
    
                <tr id='discountAmount'>
                 {$trDiscount}
                </tr>

                <tr id='insuranceAmount'>
                    <td>Medical Insurance Amount</td>
                    <td class='amount txtRight'>{$medical_insurance}</td>
                </tr>
                
                <tr id='totalAmount'>
                    <td>Total</td>
                    <td class='amount txtRight'>{$total}</td>
                </tr>

                <tr id='registrationAmount'>
                    <td>Registration Amount</td>
                    <td class='regAmount txtRight'></td>
                </tr>

                <tr id='installmentAmount'>
                    <td>Installment Amount</td>
                    <td class='installAmount txtRight'>{$inst_amount}</td>
                </tr>
            </table>
        </form>
        ";

        return $text;
    }
}
