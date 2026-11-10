<?
class CP_Admin_Modules_AgileIms_OrderLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

		$sqlCourse  = $fn->getDDSql('agileIms_course');
        $sqlBatch   = $fn->getDDSql('agileIms_batch');
        $sqlSubsidy = 'SELECT s.course_subsidy_id, s.title FROM course_subsidy s';
        $id         = $fn->getReqParam('id');

        $rows = '';
        for($i = 0; $i < 2; $i++){
            $rows .= $this->getCourseRow($tv['srcRoomId']);
        }

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $text = "
        <div><a id='addCourseRows' href='javascript:void(0)' company_id='{$tv['srcRoomId']}'>Add More Record</a></div>
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <table id='courseLinkList' class='thinlist orderClass'>
                {$formObj->getTextBoxRow('Discount', 'discount')}
                {$rows}
            </table>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
        </form>
        ";

        return $text;
    }

    /**
    */
    function getCourseRow($company_id){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

		$sqlCourse  = $fn->getDDSql('agileIms_course');
        $sqlBatch   = $fn->getDDSql('agileIms_batch');
        $sqlSubsidy = 'SELECT s.course_subsidy_id, s.title FROM course_subsidy s';
        $sqlTrainee = "
        SELECT c.contact_id
              ,(CONCAT_WS(' ', c.first_name, c.last_name)) as trainee_name
        FROM contact c
        WHERE c.company_id = {$company_id}
        ";

        $text = "
        <tr>
            <td>{$formObj->getDropDownBySQL('Trainee', 'contact_id[]', $sqlTrainee)}</td>
            <td>{$formObj->getDropDownBySQL('Course', 'course_id[]', $sqlCourse)}</td>
            <td>{$formObj->getDropDownBySQL('Session', 'batch_id[]', $sqlBatch)}</td>
            <td>
            {$formObj->getDropDownBySQL('Subsidy', 'course_subsidy_id[]', $sqlSubsidy)}
            <input type='hidden' name='course_contact_id[]' value='' />
            </td>
        </tr>
        ";

        return $text;
    }

    /**
    */
    function getCourseRowEdit($course_contact_id){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);

        $company_id = $row['company_id'];
		$sqlCourse  = $fn->getDDSql('agileIms_course');
        $sqlBatch   = $fn->getDDSql('agileIms_batch');

        $sqlSubsidy = "
        SELECT s.course_subsidy_id
              ,s.title
        FROM course_subsidy s
        ";

        $sqlTrainee = "
        SELECT c.contact_id
              ,c.first_name
        FROM contact c
        WHERE c.company_id = {$company_id}
        ";

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $text = "
        <tr>
            <td>{$formObj->getDropDownBySQL('Trainee', 'contact_id[]', $sqlTrainee, $row['contact_id'])}</td>
            <td>{$formObj->getDropDownBySQL('Course', 'course_id[]', $sqlCourse, $row['course_id'])}</td>
            <td>{$formObj->getDropDownBySQL('Session', 'batch_id[]', $sqlBatch, $row['batch_id'])}</td>
            <td>
                {$formObj->getDropDownBySQL('Subsidy', 'course_subsidy_id[]', $sqlSubsidy, $row['course_subsidy_id'])}
                <input type='hidden' name='course_contact_id[]' value='{$course_contact_id}' />
            </td>
        </tr>
        ";

        return $text;
    }

    /**
    */
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $order_id = $fn->getReqParam('id');
        $rows = '';

        $sqlCourseContact  = "
        SELECT cc.*
        FROM course_contact cc
        WHERE cc.order_id = {$order_id}
        ";
        $result  = $db->sql_query($sqlCourseContact);

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= $this->getCourseRowEdit($row['course_contact_id']);
            $company_id = $row['company_id'];
            $discount   = $row['discount'];
        }

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $text = "
        <div><a id='addCourseRows' href='javascript:void(0)' company_id='{$company_id}'>Add More Record</a></div>
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <table id='courseLinkList' class='thinlist orderClass'>
                {$formObj->getTextBoxRow('Discount', 'discount', $discount)}
                {$rows}
            </table>
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='company_id' value='{$company_id}' />
        </form>
        ";

        return $text;
    }

    /**
    * Main window of company enrollment (Has both left and right panels)
    */
    function getCourseTraineeSearch(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $traineeSelectedResult = '';
        $course_id = '';
        $expEdit   = array('isEditable' => 0);

        $_SESSION['selectedContactIds'] = array();
        $company_id = $fn->getReqParam('company_id');
        $order_id   = $fn->getReqParam('order_id');

        //This is to unset the course session value for a new programme
        if (isset($_SESSION['selectedCourse'])) {
            unset($_SESSION['selectedCourse']);
        }

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        //to show the header
        $traineeSelectedResult = $this->getSelectedTraineeResult();

        $auto_generation = '';
        if ($order_id != '') {
            $orderItemRec = $fn->getRecordRowByID('order_item', 'order_id', $order_id);

            $course_id = $orderItemRec['record_id'];
            $_SESSION['selectedCourse'] = $course_id ;
            $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        } else {
            
            $auto_generation = "
            <h2 class=''><b>AUTO GENERATION OF INVOICE/RECEIPT</b></h2>
            <div class='highlightBorder'>
                {$formObj->getYesNoRRow('Do you like to create invoice', 'auto_generate_invoice', 1)}
                {$formObj->getYesNoRRow('Do you like to create receipt', 'auto_generate_receipt')}
            </div>
            ";
        }

        $addUrl ='index.php?module=agileIms_contact&_spAction=contactNew&showHTML=0&company_id=';
        $addTraineeUrl = $addUrl . $company_id ;

        $text = "
        <div class='subcolumns companyCourseBulkLink'>
            <div class='c20l'>
                <a class='newContactDetails' href='{$addTraineeUrl}'><u>Click here to Add New Trainee</u></a>
                <h2>Trainees from company</h2>
                <div class='subcl leftCol'>
                    <div id='traineeSearchResult'>
                        {$this->getTraineeSearchResult()}
                    </div>
                </div>
            </div>
            <div class='c80r'>
                <form id='traineeSelectedForm' class='yform columnar' method='post' action='{$formAction}'>
                    {$auto_generation}
                    <div class=''>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
                    <strong>Selected Student/Trainee</strong>
                    <div class='subcr rightCol'>
                        <div id='traineeSelectedResult'>
                            {$traineeSelectedResult}
                        </div>
                    </div>
                    <input type='hidden' name='company_id' value='{$company_id}'>
                    <input type='hidden' name='order_id' value='{$order_id}'>
                </form>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     * Function in Edit company enrollment - Right panel
    */
    function getSelectedTraineeResult(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $batch_id = '';
        $course_subsidy_history_id = '';
        $discount_id = '';
        $rows = '';
        $sqlBatch = '';
        $sqlSubsidy = '';
        $sqlDiscount = '';
        $sqlAppendbatch    = '';
        $sqlAppendSubsidy  = '';
        $sqlAppendDiscount = '';
        $appendSQl = '';
        $course_id = '';
        $message = '';

        $contact_id = $fn->getReqParam('contact_id');
        $order_id   = $fn->getReqParam('order_id');
        
        if (isset($_SESSION['selectedCourse'])){
            $course_id =  $_SESSION['selectedCourse'];
        }

        //In editing a contact for new company - course link,we need the course id
        if ($course_id != '' ) {
            $sqlAppendbatch    = "WHERE course_id = $course_id";
            $sqlAppendSubsidy  = " AND s.course_id = $course_id";
            $sqlAppendDiscount = " AND s.course_id = $course_id";
        }

        //In edit mode when adding records show batch, subsidy, discount related to selected course
        if ($order_id != '') {
            $courseContactRec = $fn->getRecordRowByID('course_contact', 'order_id', $order_id);
            $course_id        = $courseContactRec['course_id'];

            $sqlAppendbatch    = "WHERE course_id = $course_id";
            $sqlAppendSubsidy  = " AND csh.course_id = $course_id";
            $sqlAppendDiscount = " AND csh.course_id = $course_id";
        }

        $sqlBatch = "
        SELECT batch_id
              ,title
        FROM batch
        {$sqlAppendbatch}
        ";

        $sqlSubsidy  = "
        SELECT sd.subsidy_discount_id
              ,sd.title
        FROM subsidy_discount sd
        LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id AND sd.category_type = 'Subsidy')
        WHERE sd.title != ''
        {$sqlAppendSubsidy}
        ";

        $sqlDiscount = "
        SELECT sd.subsidy_discount_id
              ,sd.title
        FROM subsidy_discount sd
        LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id AND sd.category_type = 'Discount')
        WHERE sd.title != ''
        {$sqlAppendDiscount}
        ";

        if ($contact_id != ''){
            $_SESSION['selectedContactIds'][] = $contact_id;
            //Below code is to avoid double entry of the existing contact_id in session id.
            $existingContactIds = join(',', $_SESSION['selectedContactIds']);
            $appendSQl = " AND contact_id NOT IN ($existingContactIds)";
        }

        if ($order_id != '') {
            $SQL = "
            SELECT contact_id
            FROM course_contact
            WHERE order_id = '{$order_id}'
            {$appendSQl}
            ";
            $result  = $db->sql_query($SQL);

            while ($row = $db->sql_fetchrow($result)) {
                $_SESSION['selectedContactIds'][] = $row['contact_id'];
            }
        }

        $selectContactIds = join(',', $_SESSION['selectedContactIds']);
        if($selectContactIds != ''){
            $sqlContact  = "
            SELECT c.*
            FROM contact c
            WHERE c.contact_id IN ({$selectContactIds})
            ";
            $result  = $db->sql_query($sqlContact);
            $numRows = $db->sql_numrows($result);

            $viewUrl ='index.php?module=agileIms_contact&_spAction=contactDetails&showHTML=0&contact_id=';
            $editUrl ='index.php?module=agileIms_contact&_spAction=contactEdit&showHTML=0&contact_id=';

            while ($row = $db->sql_fetchrow($result)) {
                $vUrl = $viewUrl . $row['contact_id'];
                $eUrl = $editUrl . $row['contact_id'];

                $receiptCount = '';
                if ($order_id != ''){
                    $expCourseContact = array('condn' => " AND contact_id = {$row['contact_id']}
                    ");
                    $courseContactRec = $fn->getRecordRowByID('course_contact', 'order_id', $order_id, $expCourseContact);
                    $cRec = $fn->getRecordRowByID('course', 'course_id', $courseContactRec['course_id']);
                    $batch_id = $courseContactRec['batch_id'];

                    if ($courseContactRec['subsidy_discount_type'] == 'Subsidy') {
                        $course_subsidy_history_id = $courseContactRec['subsidy_discount_id'];
                    } else {
                        $course_subsidy_history_id = '';
                    }

                    if ($courseContactRec['subsidy_discount_type'] == 'Discount') {
                        $discount_id = $courseContactRec['subsidy_discount_id'];
                    } else {
                        $discount_id = '';
                    }

                    $receiptCount = $fn->getRecordCount('receipt', "receipt_status = 'Paid' AND order_id = '{$order_id}'");
                }

                $discount_row = '';
                $subsidy_row  = '';
                $reg_fee_row  = '';
                if ($receiptCount) {
                    if ($courseContactRec['discount']) {
                        $discountRec  = $fn->getRecordByCondition('subsidy_discount', "category_type = 'Discount' AND subsidy_discount_id = {$courseContactRec['discount']}");
                        $discount_row = $discountRec['title'];
                    }

                    if ($courseContactRec['course_subsidy_history_id']) {
                        $subsidyRec  = $fn->getRecordByCondition('subsidy_discount', "category_type = 'Subsidy' AND subsidy_discount_id = {$courseContactRec['course_subsidy_history_id']}");
                        $subsidy_row  = $subsidyRec['title'];
                    }
                    
                    if ($courseContactRec['add_registration_fee']) {
                        $reg_fee_row = "Yes";
                    }
                    $remove_link_row = "";
                    $message = "<i>NOTE: To apply discount, please cancel the receipt, going to Finance.</i>";
                } else {
                    if ($row['is_citizen']) {
                        $subsidy_row = "
                        <select name='course_subsidy_history_id[]' id='fld_course_subsidy_history_id'>
                            <option value=''>Subsidy</option>
                            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSubsidy, $course_subsidy_history_id)}
                        </select>
                        ";
                        $discount_row = "
                        <select name='discount_id[]' id='fld_discount_id'>
                            <option value=''>Discount</option>
                        </select>
                        ";
                    } else {
                        $subsidy_row = "
                        <select name='course_subsidy_history_id[]' id='fld_course_subsidy_history_id'>
                            <option value=''>Subsidy</option>
                        </select>
                        ";

                        $discount_row = "
                        <select name='discount_id[]' id='fld_discount_id'>
                            <option value=''>Discount</option>
                            {$dbUtil->getDropDownFromSQLCols2($db, $sqlDiscount, $discount_id)}
                        </select>
                        ";
                    }

                    if ($courseContactRec['add_registration_fee']) {
                        $reg_fee_row = "Yes";
                    }

                    $remove_link_row = "
                    <a href='#' class='removeTrainee' contact_id='{$row['contact_id']}' company_id='{$row['company_id']}' order_id='{$order_id}'><u>Remove</u></a>
                    ";
                }

                $rows .= "
                <tr contact_id={$row['contact_id']} order_id={$order_id} isCitizen='{$row['is_citizen']}'>
                    <td class='first_name'>{$row['first_name']}</td>
                    <td class='id_card_no'>{$row['id_card_no']}</td>
                    <td>
                        {$cRec['title']}
                        <input type='hidden' name='course_id[]' value={$courseContactRec['course_id']}
                    </td>
                    <td>
                        <input type='hidden' name='contact_id[]' value='{$row['contact_id']}'>
                        <select name='batch_id[]' id='fld_batch_id'>
                            <option value=''>Batch</option>
                            {$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch, $batch_id)}
                        </select>
                    </td>
                    <td>{$subsidy_row}</td>
                    <td>{$discount_row}</td>
                    <td>
                        {$reg_fee_row}
                    </td>
                    <td><a class='showDetailPortalForm jqui-dialog' href='{$vUrl}'>view</a></td>
                    <td><a class='editContactDetails' href='{$eUrl}'>edit</a></td>
                    <td class='txtCenter'>{$remove_link_row}</td>
                </tr>
                ";
            }
        }

        $text = "
        <div class='mb10'>{$message}</div>
        <table class='traineesSelectedLinked thinlist'>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>NRIC No</th>
                    <th>Course</th>
                    <th>Batch</th>
                    <th>Subsidy</th>
                    <th>Discount</th>
                    <th>Reg Fee</th>
                    <th>View</th>
                    <th>Edit</th>
                    <th>Remove</th>
                </tr>
            </thead>
            {$rows}
        </table>
        ";

        if (isset($_SESSION['newTrainee'])){
            unset($_SESSION['newTrainee']);
        }

        return $text;
    }

    /**
    *
    */
    function getCourseTraineeSearchForm(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

		$sqlCourse  = $fn->getDDSql('agileIms_course');
        $company_id = $fn->getReqParam('company_id');
        $order_id   = $fn->getReqParam('order_id');

        if ($company_id) {
            $history_id = $company_id;
            $parent_link ='';
        } else if ($parent_id) {
            $history_id = $parent_id;
            $parent_link = 'yes';
        }

        $action ='index.php?module=agileIms_orderLink&_spAction=traineeSearchResult&showHTML=0';
        $text = "
        <form id='traineeSearchForm' class='yform columnar' method='post' action='{$action}'>
            {$formObj->getTextBoxRow('Student Name', 'student_name')}
        <!--
            <div><strong>Please select the course before adding the trainee</strong></div>
            -->
            <input type='hidden' name='history_id' value='{$history_id}' />
            <input type='hidden' name='order_id' value='{$order_id}'>
        </form>
        ";

        return $text;
    }

    /**
    */
    function getTraineeSearchResult(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $contact_name = $fn->getReqParam('student_name');
        $order_id     = $fn->getReqParam('order_id');
        $course_id    = $fn->getReqParam('course_id');

        $rows = '';

        $s = $_SESSION['selectedContactIds'];
        $history_id  = $fn->getPostParam('history_id');
        $company_id  = $fn->getReqParam('company_id');

        $appendSQL = '';
        if (count($s) > 0){
            $selectContactIds = join(',', $s);
            $appendSQL = "AND c.contact_id NOT IN ($selectContactIds) ";
        }

        $sqlContact  = "
        SELECT c.*
        FROM contact c
        WHERE c.company_id = {$company_id}
        {$appendSQL}
        ";

        $result  = $db->sql_query($sqlContact);

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td class='txtCenter'><a href='#' class='addTrainee' contact_id='{$row['contact_id']}' order_id='{$order_id}'><u>Add</u></a></td>
                <td>{$row['first_name']}</td>
                <td>{$row['id_card_no']}</td>
            </tr>
            ";
        }

        $text = "
        <table class='traineeSearchRow thinlist'>
            <thead>
                <tr>
                    <th></th>
                    <th>Full Name</th>
                    <th>NRIC No</th>
                </tr>
            </thead>
            {$rows}
        </table>
        ";

        return $text;

        $validate = Zend_Registry::get('validate');
        return $validate->getSuccessMessageXML('', $text);
    }

    /**
     * Student linked in right panel of company#enrollment window - In New enrollment
     */
    function getSelectedStudentListRow(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $modulesArr = Zend_Registry::get('modulesArr');

        $course_id  = '';

        $contact_id = $fn->getReqParam('contact_id');
        $order_id   = $fn->getReqParam('order_id');
        //below code will be used in case if a new trainee is added.
        if ($contact_id != ''){
            $_SESSION['selectedContactIds'][] = $contact_id;
        }

        if($contact_id == ''){
            $contact_id = $_SESSION['newTrainee'];
        }

        if (isset($_SESSION['selectedCourse'])){
            $course_id =  $_SESSION['selectedCourse'];
        }

        $text = '';
        $course_id = '';
        $batch_id = '';
        $course_subsidy_history_id = '';
        $discount_id = '';
        $rows = '';
        $sqlBatch = '';
        $sqlSubsidy = '';
        $sqlDiscount = '';
        $sqlAppendbatch    = '';
        $sqlAppendSubsidy  = '';
        $sqlAppendDiscount = '';
        $appendSQl = '';

        //In edit mode when adding records show batch, subsidy, discount related to //selected course
        if ($course_id != '' ){
            $sqlAppendbatch = "
                WHERE course_id = $course_id
            ";
            $sqlAppendSubsidy = "
                AND s.course_id = $course_id
            ";
            $sqlAppendDiscount = "
                AND s.course_id = $course_id
            ";
        }

        if ($order_id != ''){
            $courseContactRec   = $fn->getRecordRowByID('course_contact', 'order_id', $order_id);
            $course_id = $courseContactRec['course_id'];

            $sqlAppendbatch = "
                WHERE course_id = $course_id
            ";
            $sqlAppendSubsidy = "
                AND csh.course_id = $course_id
            ";
            $sqlAppendDiscount = "
                AND csh.course_id = $course_id
            ";
        }

        $sqlCourse    = "
        SELECT course_id
              ,title
        FROM course
        ";

        $sqlBatch = "
        SELECT batch_id
              ,title
        FROM batch
        {$sqlAppendbatch}
        ";

        $sqlSubsidy = "
        SELECT csh.subsidy_discount_id
              ,sd.title 
        FROM course_subsidy_history csh
        LEFT JOIN (subsidy_discount sd) ON (csh.subsidy_discount_id = sd.subsidy_discount_id AND sd.category_type = 'Subsidy')
        WHERE sd.title != ''
        {$sqlAppendSubsidy}
        ";

        $sqlDiscount = "
        SELECT csh.subsidy_discount_id
              ,sd.title 
        FROM course_subsidy_history csh
        LEFT JOIN (subsidy_discount sd) ON (csh.subsidy_discount_id = sd.subsidy_discount_id AND sd.category_type = 'Discount')
        WHERE sd.title != ''
        {$sqlAppendDiscount}
        ";

        $sqlContact = "
        SELECT c.*
        FROM contact c
        WHERE c.contact_id = {$contact_id}
        ";
        $result  = $db->sql_query($sqlContact);
        $numRows = $db->sql_numrows($result);

        $viewUrl ='index.php?module=agileIms_contact&_spAction=contactDetails&showHTML=0&contact_id=';
        $editUrl ='index.php?module=agileIms_contact&_spAction=contactEdit&showHTML=0&contact_id=';

        $row = $db->sql_fetchrow($result);
        $vUrl = $viewUrl . $row['contact_id'];
        $eUrl = $editUrl . $row['contact_id'];

        $batchOptions    = '';
        $subsidyOptions  = '';
        $discountOptions = '';

        if (!$row['is_citizen']) {
            $batchOptions = "
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch, $batch_id)}
            ";
        }

        if ($row['is_citizen']) {
            $subsidyOptions = "
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSubsidy, $course_subsidy_history_id)}
            ";
        } else {
            $discountOptions = "
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlDiscount, $discount_id)}
            ";
        }

        if ($course_id == '') {
            $batchOptions = "";
            $subsidyOptions = "";
            $discountOptions = "";
        }

        $arr = array('1' => 'Yes', '0' => 'No');

        if ($order_id != '' && $contact_id == $courseContactRec['contact_id']) {
            $cRec = $fn->getRecordRowByID('course', 'course_id', $courseContactRec['course_id']);
            $course_val = "
            {$cRec['title']}
            <input type='hidden' name='course_id[]' value={$courseContactRec['course_id']}
            ";
        } else {
            $course_val = "
            <select name='course_id[]' class='fld_course_id_row'>
                <option value=''>{$modulesArr['agileIms_course']['title']}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCourse, $course_id)}
            </select>
            ";
        }
        
        $rows = "
        <tr isCitizen='{$row['is_citizen']}' contact_id_row ='{$row['contact_id']}'>
            <td class='first_name'>{$row['first_name']}</td>
            <td class=''>{$row['id_card_no']}</td>
            <td>{$course_val}</td>
            <td>
                <select name='batch_id[]' class='fld_batch_id'>
                    <option value=''>{$modulesArr['agileIms_batch']['title']}</option>
                    {$batchOptions}
                </select>
            </td>
            <td>
                <select name='course_subsidy_history_id[]' class='fld_course_subsidy_history_id'>
                    <option value=''>Subsidy</option>
                    {$subsidyOptions}
                </select>
            </td>
            <td>
                <select name='discount_id[]' class='fld_discount_id'>
                    <option value=''>Discount</option>
                    {$discountOptions}
                </select>
            </td>
            <td>
                <input type='checkbox' name='add_reg_fee[]' value='{$row['contact_id']}' class='row_add_reg_fee'>
                <!--<div class='row_add_reg_fee'>
                    <div class='type-check'>
                        <input type='radio' value='1' name='add_reg_fee[]'>
                        <label for='add_reg_fee_1'>Yes</label>
                    </div>

                    <div class='type-check'>
                        <input type='radio' value='0' name='add_reg_fee[]'>
                        <label for='add_reg_fee_2'>No</label>
                    </div>
                </div>-->
            </td>
            <td>
            <a class='showDetailPortalForm jqui-dialog' href='{$vUrl}'>view</a>
            </td>
            <td><a class='editContactDetails' href='{$eUrl}'>edit</a></td>
            <td class='txtCenter'><a href='#' class='removeTrainee' contact_id='{$row['contact_id']}' company_id='{$row['company_id']}' order_id='{$order_id}'><u>Remove</u></a></td>
        </tr>
        ";

        return $rows;
    }

    /**
     * New contact added from Company#contact linking and getting automatically linked to company#contact linking
    */
    function getSelectedTraineeResultRow(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $modulesArr = Zend_Registry::get('modulesArr');

        $course_id  = '';

        $contact_id = $fn->getReqParam('contact_id');
        $order_id   = $fn->getReqParam('order_id');
        //below code will be used in case if a new trainee is added.
        if ($contact_id != ''){
            $_SESSION['selectedContactIds'][] = $contact_id;
        }

        if($contact_id == ''){
            $contact_id = $_SESSION['newTrainee'];
        }

        if (isset($_SESSION['selectedCourse'])){
            $course_id =  $_SESSION['selectedCourse'];
        }

        $text = '';
        $course_id = '';
        $batch_id = '';
        $course_subsidy_history_id = '';
        $discount_id = '';
        $rows = '';
        $sqlBatch = '';
        $sqlSubsidy = '';
        $sqlDiscount = '';
        $sqlAppendbatch    = '';
        $sqlAppendSubsidy  = '';
        $sqlAppendDiscount = '';
        $appendSQl = '';

        //In edit mode when adding records show batch, subsidy, discount related to //selected course
        if ($course_id != '' ){
            $sqlAppendbatch = "WHERE course_id = $course_id";
            $sqlAppendSubsidy = " AND s.course_id = $course_id";
            $sqlAppendDiscount = " AND s.course_id = $course_id";
        }

        if ($order_id != ''){
            $courseContactRec   = $fn->getRecordRowByID('course_contact', 'order_id', $order_id);
            $course_id = $courseContactRec['course_id'];

            $sqlAppendbatch = "WHERE course_id = $course_id";
            $sqlAppendSubsidy = " AND s.course_id = $course_id";
            $sqlAppendDiscount = " AND s.course_id = $course_id";
        }

        $sqlCourse = "
        SELECT course_id
              ,title
        FROM course
        ";

        $sqlBatch = "
        SELECT batch_id
              ,title
        FROM batch
        {$sqlAppendbatch}
        ";

        $sqlSubsidy = "
        SELECT sd.subsidy_discount_id
              ,sd.title
        FROM subsidy_discount sd
        WHERE sd.title != ''
          AND sd.category_type = 'Subsidy'
        {$sqlAppendSubsidy}
        ";

        $sqlDiscount = "
        SELECT sd.subsidy_discount_id
              ,sd.title
        FROM subsidy_discount sd
        WHERE sd.title != ''
          AND sd.category_type = 'Discount'
        {$sqlAppendDiscount}
        ";

        $sqlContact = "
        SELECT c.*
        FROM contact c
        WHERE c.contact_id = {$contact_id}
        ";
        $result  = $db->sql_query($sqlContact);
        $numRows = $db->sql_numrows($result);

        $viewUrl ='index.php?module=agileIms_contact&_spAction=contactDetails&showHTML=0&contact_id=';
        $editUrl ='index.php?module=agileIms_contact&_spAction=contactEdit&showHTML=0&contact_id=';

        $row = $db->sql_fetchrow($result);
        $vUrl = $viewUrl . $row['contact_id'];
        $eUrl = $editUrl . $row['contact_id'];

        $batchOptions    = '';
        $subsidyOptions  = '';
        $discountOptions = '';

        if ($row['is_citizen']) {
            $subsidyOptions = "
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSubsidy, $course_subsidy_history_id)}
            ";
        }

        if (!$row['is_citizen']) {
            $discountOptions = "
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlDiscount, $discount_id)}
            ";
        }

        if ($course_id == '') {
            $batchOptions = "";
            $subsidyOptions = "";
            $discountOptions = "";
        }

        $arr = array('1' => 'Yes', '0' => 'No');

        $rows = "
        <tr contact_id_row ='{$row['contact_id']}'>
            <td class='first_name'>{$row['first_name']}</td>
            <td class='id_card_no'>{$row['id_card_no']}</td>
            <td>
                <select name='course_id_row[]' class='fld_course_id_row'>
                    <option value=''>{$modulesArr['agileIms_course']['title']}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlCourse, $course_id)}
                </select>
            </td>
            <td>
                <select name='batch_id[]' class='fld_batch_id'>
                    <option value=''>{$modulesArr['agileIms_batch']['title']}</option>
                    {$batchOptions}
                </select>
            </td>
            <td>
                <select name='course_subsidy_history_id[]' class='fld_course_subsidy_history_id'>
                    <option value=''>Subsidy</option>
                    {$subsidyOptions}
                </select>
            </td>
            <td>
                <select name='discount_id[]' class='fld_discount_id'>
                    <option value=''>Discount</option>
                    {$discountOptions}
                </select>
            </td>
            <!--<td>
                <input type='checkbox' name='add_reg_fee[]' value='{$row['contact_id']}' class='row_add_reg_fee'>
            </td>-->

            <td>
                <div class='row_add_reg_fee'>
                    <div class='type-check'>
                        <input type='radio' value='1' name='add_reg_fee[]'>
                        <label for='add_reg_fee_1'>Yes</label>
                    </div>

                    <div class='type-check'>
                        <input type='radio' value='0' name='add_reg_fee[]'>
                        <label for='add_reg_fee_2'>No</label>
                    </div>
                </div>
            </td>
            <td><a class='showDetailPortalForm jqui-dialog' href='{$vUrl}'>view</a></td>
            <td><a class='editContactDetails' href='{$eUrl}'>edit</a></td>
            <td class='txtCenter'><a href='#' class='removeTrainee' contact_id='{$row['contact_id']}' company_id='{$row['company_id']}' order_id='{$order_id}'><u>Remove</u></a></td>
        </tr>
        ";

        return $rows;
    }
}
