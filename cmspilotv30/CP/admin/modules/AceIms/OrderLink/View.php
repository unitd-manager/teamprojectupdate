<?
class CP_Admin_Modules_AceIms_OrderLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

		$sqlCourse  = $fn->getDDSql('aceIms_course');
        $sqlBatch   = $fn->getDDSql('aceIms_batch');
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

		$sqlCourse  = $fn->getDDSql('aceIms_course');
        $sqlBatch   = $fn->getDDSql('aceIms_batch');
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
		$sqlCourse  = $fn->getDDSql('aceIms_course');
        $sqlBatch   = $fn->getDDSql('aceIms_batch');

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

        $company_id = $fn->getReqParam('company_id');
        $order_id   = $fn->getReqParam('order_id');

        // unsetting of subjects when subjects are chosen in previous session
        if ($order_id == '') {
            if (isset($_SESSION['selectedSubjectIds'])){
                unset($_SESSION['selectedSubjectIds']);
            }
            $_SESSION['selectedSubjectIds'] = array();

            if (isset($_SESSION['selectedBatchIds'])){
                unset($_SESSION['selectedBatchIds']);
            }
            $_SESSION['selectedBatchIds'] = array();
        }

        $traineeSelectedResult = '';
        $course_type = '';
        $course_id   = '';
        $expEdit     = array('isEditable' => 0);

        $_SESSION['selectedContactIds'] = array();

        //This is to unset the course session value for a new programme
        if (isset($_SESSION['selectedCourseType'])) {
            unset($_SESSION['selectedCourseType']);
        }

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

        $addUrl ='index.php?module=aceIms_contact&_spAction=contactNew&showHTML=0&company_id=';
        $addTraineeUrl = $addUrl . $company_id ;

        if ($order_id == '') {
            $expYearArr = array(date('Y')-1, date('Y'), date('Y')+1);
            $yearExp = array('hideFirstOption' => 1);
            $enrollment_year = $formObj->getDropDownRowByArray('Enrollment Year', 'enrollment_year', $expYearArr, date('Y'), $yearExp);
        } else {
            $course_contact_rec = $fn->getRecordRowByID('course_contact', 'order_id', $order_id);
            $enrollment_year = "
            {$formObj->getDropDownRowByArray('Enrollment Year', 'enrollment_year', '', $course_contact_rec['year_of_enrollment'], $expEdit)}
            <input type='hidden' name='enrollment_year' value='{$course_contact_rec['year_of_enrollment']}'>
            ";
        }

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
                    {$enrollment_year}
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
        $sqlAppendSubsidy  = '';
        $appendSQl = '';
        $course_type = '';
        $course_id = '';
        $message = '';
        $courseType = '';

        $contact_id = $fn->getReqParam('contact_id');
        $order_id   = $fn->getReqParam('order_id');

        if (isset($_SESSION['selectedCourseType'])){
            $course_type = $_SESSION['selectedCourseType'];
        }

        if (isset($_SESSION['selectedCourse'])){
            $course_id = $_SESSION['selectedCourse'];
        }

        //In editing a contact for new company - course link,we need the course id
        if ($course_id != '') {
            $sqlAppendSubsidy  = " AND s.course_id = $course_id";
            $sqlAppendDiscount = " AND s.course_id = $course_id";
        }

        //In edit mode when adding records show batch, subsidy, discount related to selected course
        if ($order_id != '') {
            $courseContactRec = $fn->getRecordRowByID('course_contact', 'order_id', $order_id);
            $course_id        = $courseContactRec['course_id'];

            $sqlAppendSubsidy  = " AND csh.course_id = $course_id";
        }

        $sqlBatch = "
        SELECT batch_id
              ,title
        FROM batch
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

            $viewUrl ='index.php?module=aceIms_contact&_spAction=contactDetails&showHTML=0&contact_id=';
            $editUrl ='index.php?module=aceIms_contact&_spAction=contactEdit&showHTML=0&contact_id=';

            while ($row = $db->sql_fetchrow($result)) {
                $vUrl = $viewUrl . $row['contact_id'];
                $eUrl = $editUrl . $row['contact_id'];

                $receiptCount = '';
                if ($order_id != ''){
                    $expCourseContact = array('condn' => " AND contact_id = {$row['contact_id']}");
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
                $fees_by_module_row = '';
                if ($receiptCount) {
                    if ($courseContactRec['subsidy_discount_type'] == 'Discount' && $courseContactRec['subsidy_discount_id']) {
                        $discountRec  = $fn->getRecordByCondition('subsidy_discount', "category_type = 'Discount' AND subsidy_discount_id = {$courseContactRec['subsidy_discount_id']}");
                        $discount_row = $discountRec['title'];
                    }

                    if ($courseContactRec['subsidy_discount_type'] == 'Subsidy' && $courseContactRec['subsidy_discount_id']) {
                        $subsidyRec  = $fn->getRecordByCondition('subsidy_discount', "category_type = 'Subsidy' AND subsidy_discount_id = {$courseContactRec['subsidy_discount_id']}");
                        $subsidy_row  = $subsidyRec['title'];
                    }

                    if ($courseContactRec['fees_by_module']) {
                        $fees_by_module_row = "Yes";
                    }

                    if ($courseContactRec['add_registration_fee']) {
                        $reg_fee_row = "Yes";
                    }
                    $remove_link_row = "";
                    $message = "<i>NOTE: To apply discount, change subjects, please cancel the receipt, going to Finance.</i>";
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

                        $sqlDiscount = "
                        SELECT sd.subsidy_discount_id
                              ,sd.title
                        FROM subsidy_discount sd
                        LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id AND sd.category_type = 'Discount')
                        WHERE sd.title != ''
                          AND csh.course_id = {$courseContactRec['course_id']}
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

                    if ($cRec['course_type'] == 'Long Term' && $courseContactRec['fees_by_module']) {
                        $checked = "checked='checked'";
                        $fees_by_module_row = "<input type='checkbox' {$checked} name='fees_by_module[]' value='{$row['contact_id']}'>";
                    } else if ($cRec['course_type'] == 'Long Term') {
                        $fees_by_module_row = "<input type='checkbox' name='fees_by_module[]' value='{$row['contact_id']}'>";
                    }

                    $remove_link_row = "
                    <a href='#' class='removeTrainee' contact_id='{$row['contact_id']}' company_id='{$row['company_id']}' order_id='{$order_id}'><u>Remove</u></a>
                    ";
                }

                $sqlBatch = "
                SELECT b.batch_id
                      ,b.title
                FROM batch b
                LEFT JOIN (course_contact cc) ON (b.batch_id = cc.batch_id)
                WHERE b.course_id = cc.course_id
                  AND cc.order_id = {$order_id}
                  AND cc.contact_id = {$row['contact_id']}
                ";

                $subject_link = '';
                if ($cRec['course_type'] == 'Long Term') {
                    $subject_link = "<a class='editSubjectsForCourse' href='#'>choose<br/>subjects</a>";
                }

                $batchDisplay = '';
                $disabled = '';
                $courseType = $cRec['course_type'];
                if ($cRec['course_type'] == 'Long Term') {
                    $disabled = "disabled='1'";
                }
                    $batchDisplay = "
                    <td id='hideLongTermFlds'>
                        <input type='hidden' name='contact_id[]' value='{$row['contact_id']}'>
                        <select name='batch_id[]' id='fld_batch_id' {$disabled}>
                            <option value=''>Batch</option>
                            {$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch, $batch_id)}
                        </select>
                    </td>
                    ";

                $rows .= "
                <tr contact_id={$row['contact_id']} order_id={$order_id} isCitizen='{$row['is_citizen']}'>
                    <td class='first_name'>{$row['first_name']}</td>
                    <td class='id_card_no'>{$row['id_card_no']}</td>
                    <td>
                        {$cRec['course_type']}
                        <input type='hidden' name='course_type[]' value={$cRec['course_type']}
                    </td>
                    <td>
                        {$cRec['title']}
                        <input type='hidden' name='course_id[]' value={$courseContactRec['course_id']}
                    </td>
                    {$batchDisplay}
                    <td>{$subsidy_row}</td>
                    <td>{$discount_row}</td>
                    <td>{$reg_fee_row}</td>
                    <td>{$fees_by_module_row}</td>
                    <td>{$subject_link}</td>
                    <td><a class='showDetailPortalForm jqui-dialog' href='{$vUrl}'>view</a></td>
                    <td><a class='editContactDetails' href='{$eUrl}'>edit</a></td>
                    <td class='txtCenter'>{$remove_link_row}</td>
                </tr>
                ";
            }
        }
        $batchLabel ='';

        //if ($courseType != 'Long Term') {
            $batchLabel = "<th id='hideLongTermFlds'>Batch</th>";
        //}

        $text = "
        <div class='mb10'>{$message}</div>
        <table class='traineesSelectedLinked thinlist'>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>NRIC No</th>
                    <th>Course Type</th>
                    <th>Course</th>
                    {$batchLabel}
                    <th>Subsidy</th>
                    <th>Discount</th>
                    <th>Reg Fee</th>
                    <th>Fees by<br/>Module</th>
                    <th>Subjects</th>
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
    */
    function getTraineeSearchResult(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $contact_name = $fn->getReqParam('student_name');
        $order_id     = $fn->getReqParam('order_id');
        $course_id    = $fn->getReqParam('course_id');

        $receiptRec = '';
        if($order_id){
            $receiptRec = $fn->getRecordByCondition('receipt', "order_id = {$order_id} AND receipt_status = 'Paid'");
        }

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
            $addTrainee = '';

            if($receiptRec){
                $addTrainee = "<td></td>";
            } else {
                $addTrainee = "<td class='txtCenter'><a href='#' class='addTrainee' contact_id='{$row['contact_id']}' order_id='{$order_id}'><u>Add</u></a></td>";
            }

            $rows .= "
            <tr>
                {$addTrainee}
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
                    <th>Name</th>
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

        $course_id    = '';
        $course_type  = '';

        $contact_id = $fn->getReqParam('contact_id');
        $order_id   = $fn->getReqParam('order_id');
        //below code will be used in case if a new trainee is added.
        if ($contact_id != ''){
            $_SESSION['selectedContactIds'][] = $contact_id;
        }

        if($contact_id == ''){
            $contact_id = $_SESSION['newTrainee'];
        }

        if (isset($_SESSION['selectedCourseType'])){
            $course_type = $_SESSION['selectedCourseType'];
        }

        if (isset($_SESSION['selectedCourse'])){
            $course_id = $_SESSION['selectedCourse'];
        }

        $text = '';
        $course_type = '';
        $course_id = '';
        $batch_id = '';
        $course_subsidy_history_id = '';
        $discount_id = '';
        $rows = '';
        $sqlAppendcourse = '';
        $sqlBatch = '';
        $sqlSubsidy = '';
        $sqlDiscount = '';
        $sqlAppendbatch    = '';
        $sqlAppendSubsidy  = '';
        $sqlAppendDiscount = '';
        $appendSQl = '';

        if ($course_type != ''){
            $sqlAppendcourse = "
                WHERE course_type = '$course_type'
            ";
        }
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

        $sqlCourseType = $fn->getValueListSQL('courseType');

        $sqlCourse = "
        SELECT course_id
              ,title
        FROM course
        {$sqlAppendcourse}
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

        $subjectUrl ='index.php?module=aceIms_subject&_spAction=subjectList&showHTML=0';
        $viewUrl    ='index.php?module=aceIms_contact&_spAction=contactDetails&showHTML=0&contact_id=';
        $editUrl    ='index.php?module=aceIms_contact&_spAction=contactEdit&showHTML=0&contact_id=';

        $row = $db->sql_fetchrow($result);
        $vUrl = $viewUrl . $row['contact_id'];
        $eUrl = $editUrl . $row['contact_id'];

        $courseOptions   = '';
        $batchOptions    = '';
        $subsidyOptions  = '';
        $discountOptions = '';

        if (!$row['is_citizen']) {
            $courseOptions = "{$dbUtil->getDropDownFromSQLCols2($db, $sqlCourse, $course_id)}";
            $batchOptions  = "{$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch, $batch_id)}";
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

        if ($course_type == '') {
            $courseOptions = "";
        }

        if ($course_id == '') {
            $batchOptions = "";
            $subsidyOptions = "";
            $discountOptions = "";
        }

        $arr = array('1' => 'Yes', '0' => 'No');

        if ($order_id != '' && $contact_id == $courseContactRec['contact_id']) {
            $cRec = $fn->getRecordRowByID('course', 'course_id', $courseContactRec['course_id']);
            $course_type_val = "
            {$cRec['course_type']}
            <input type='hidden' name='course_type[]' value={$courseContactRec['course_id']}
            ";
            $course_val = "
            {$cRec['title']}
            <input type='hidden' name='course_id[]' value={$courseContactRec['course_id']}
            ";
        } else {
            $course_type_val = "
            <select name='course_type[]' class='fld_course_type_row'>
                <option value=''>Course Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCourseType, $course_type)}
            </select>
            ";
            $course_val = "
            <select name='course_id[]' class='fld_course_id_row'>
                <option value=''>{$modulesArr['aceIms_course']['title']}</option>
                {$courseOptions}
            </select>
            ";
        }

        $batchDisplay ='';

        if($course_type !='Long Term'){
            $batchDisplay ="
            <td id='hideLongTermFlds'>
                <select name='batch_id[]' class='fld_batch_id'>
                    <option value=''>{$modulesArr['aceIms_batch']['title']}</option>
                    {$batchOptions}
                </select>
            </td>
            ";
        }

        $rows = "
        <tr isCitizen='{$row['is_citizen']}' contact_id_row ='{$row['contact_id']}'>
            <td class='first_name'>{$row['first_name']}</td>
            <td class=''>{$row['id_card_no']}</td>
            <td>{$course_type_val}</td>
            <td>{$course_val}</td>
            {$batchDisplay}
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
            </td>
            <td class='feesByModuleCell'>
                <input type='checkbox' name='fees_by_module[]' value='{$row['contact_id']}' class='hideme row_fees_by_module'>
            </td>
            <td class='subjectsCell'>
                <a class='hideme subjectsForCourse' href='#'>choose<br/>subjects</a>
            </td>
            <td><a class='showDetailPortalForm jqui-dialog' href='{$vUrl}'>view</a></td>
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

        $course_type = '';
        $course_id   = '';

        $contact_id = $fn->getReqParam('contact_id');
        $order_id   = $fn->getReqParam('order_id');
        //below code will be used in case if a new trainee is added.
        if ($contact_id != ''){
            $_SESSION['selectedContactIds'][] = $contact_id;
        }

        if($contact_id == ''){
            $contact_id = $_SESSION['newTrainee'];
        }

        if (isset($_SESSION['selectedCourseType'])){
            $course_type = $_SESSION['selectedCourseType'];
        }

        if (isset($_SESSION['selectedCourse'])){
            $course_id = $_SESSION['selectedCourse'];
        }

        $text = '';
        $course_type = '';
        $course_id = '';
        $batch_id = '';
        $course_subsidy_history_id = '';
        $discount_id = '';
        $rows = '';
        $sqlAppendcourse = '';
        $sqlBatch = '';
        $sqlSubsidy = '';
        $sqlDiscount = '';
        $sqlAppendbatch    = '';
        $sqlAppendSubsidy  = '';
        $sqlAppendDiscount = '';
        $appendSQl = '';

        if ($course_type != ''){
            $sqlAppendcourse = "
                WHERE course_type = '$course_type'
            ";
        }

        //In edit mode when adding records show batch, subsidy, discount related to //selected course
        if ($course_id != ''){
            $sqlAppendbatch = "WHERE course_id = $course_id";
            $sqlAppendSubsidy = " AND s.course_id = $course_id";
            $sqlAppendDiscount = " AND s.course_id = $course_id";
        }

        if ($order_id != ''){
            $courseContactRec   = $fn->getRecordRowByID('course_contact', 'order_id', $order_id);
            $course_id = $courseContactRec['course_id'];

            $sqlAppendbatch = "WHERE course_id = $course_id";
            $sqlAppendSubsidy = " AND csh.course_id = $course_id";
            $sqlAppendDiscount = " AND csh.course_id = $course_id";
        }

        $sqlCourseType = $fn->getValueListSQL('courseType');

        $sqlCourse = "
        SELECT course_id
              ,title
        FROM course
        {$sqlAppendcourse}
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

        $subjectUrl ='index.php?module=aceIms_subject&_spAction=subjectList&showHTML=0';
        $viewUrl    ='index.php?module=aceIms_contact&_spAction=contactDetails&showHTML=0&contact_id=';
        $editUrl    ='index.php?module=aceIms_contact&_spAction=contactEdit&showHTML=0&contact_id=';

        $row = $db->sql_fetchrow($result);
        $vUrl = $viewUrl . $row['contact_id'];
        $eUrl = $editUrl . $row['contact_id'];

        $courseOptions   = '';
        $batchOptions    = '';
        $subsidyOptions  = '';
        $discountOptions = '';

        if (!$row['is_citizen']) {
            $courseOptions = "{$dbUtil->getDropDownFromSQLCols2($db, $sqlCourse, $course_id)}";
            $batchOptions  = "{$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch, $batch_id)}";
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

        if ($course_type == '') {
            $courseOptions = "";
        }

        if ($course_id == '') {
            $batchOptions = "";
            $subsidyOptions = "";
            $discountOptions = "";
        }

        $arr = array('1' => 'Yes', '0' => 'No');

        if ($order_id != '' && $contact_id == $courseContactRec['contact_id']) {
            $cRec = $fn->getRecordRowByID('course', 'course_id', $courseContactRec['course_id']);
            $course_type_val = "
            {$cRec['course_type']}
            <input type='hidden' name='course_type[]' value={$courseContactRec['course_id']}
            ";
            $course_val = "
            {$cRec['title']}
            <input type='hidden' name='course_id[]' value={$courseContactRec['course_id']}
            ";
        } else {
            $course_type_val = "
            <select name='course_type[]' class='fld_course_type_row'>
                <option value=''>Course Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCourseType, $course_type)}
            </select>
            ";
            $course_val = "
            <select name='course_id[]' class='fld_course_id_row'>
                <option value=''>{$modulesArr['aceIms_course']['title']}</option>
                {$courseOptions}
            </select>
            ";
        }

        $rows = "
        <tr isCitizen='{$row['is_citizen']}' contact_id_row ='{$row['contact_id']}'>
            <td class='first_name'>{$row['first_name']}</td>
            <td class='id_card_no'>{$row['id_card_no']}</td>
            <td>{$course_type_val}</td>
            <td>{$course_val}</td>
            <td>
                <select name='batch_id[]' class='fld_batch_id'>
                    <option value=''>{$modulesArr['aceIms_batch']['title']}</option>
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
            </td>
            <td class='feesByModuleCell'>
                <input type='checkbox' name='fees_by_module[]' value='{$row['contact_id']}' class='hideme row_fees_by_module'>
            </td>
            <td class='subjectsCell'>
                <a class='hideme subjectsForCourse' href='#'>choose<br/>subjects</a>
            </td>
            <!--<td>
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
            </td>-->
            <td><a class='showDetailPortalForm jqui-dialog' href='{$vUrl}'>view</a></td>
            <td><a class='editContactDetails' href='{$eUrl}'>edit</a></td>
            <td class='txtCenter'><a href='#' class='removeTrainee' contact_id='{$row['contact_id']}' company_id='{$row['company_id']}' order_id='{$order_id}'><u>Remove</u></a></td>
        </tr>
        ";
        return $rows;
    }
}
