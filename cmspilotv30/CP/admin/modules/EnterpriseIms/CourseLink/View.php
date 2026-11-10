<?
class CP_Admin_Modules_EnterpriseIms_CourseLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
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

		$sqlCourse    = $fn->getDDSql('enterpriseIms_course');
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
        if($cpCfg['m.enterpriseIms.courseLink.hasLabelChangeEnt']){
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
        
        $sqlCourse   = $fn->getDDSql('enterpriseIms_course');
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
}
