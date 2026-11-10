<?
class CP_Admin_Modules_EnterpriseIms_OrderLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
    Student search form in Parent Student enrollment - Left side of Modal Window.
    */
    function getCourseTraineeSearchForm(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

		$sqlCourse = $fn->getDDSql('enterpriseIms_course');
        $company_id = $fn->getReqParam('company_id');
        $parent_id  = $fn->getReqParam('parent_id');
        $order_id   = $fn->getReqParam('order_id');

        if ($company_id) {
            $history_id = $company_id;
            $parent_link ='';
        } else if ($parent_id) {
            $history_id = $parent_id;
            $parent_link = 'yes';
        }

        $action ='index.php?module=enterpriseIms_orderLink&_spAction=traineeSearchResult&showHTML=0';
        $text = "
        <form id='traineeSearchForm' class='yform columnar' method='post' action='{$action}'>
        <!--
            <div><strong>Please select the course before adding the trainee</strong></div>
            -->
            <input type='hidden' name='history_id' value='{$history_id}' />
            <input type='hidden' name='parent_link' value='{$parent_link}' />
            <input type='hidden' name='order_id' value='{$order_id}'>
        </form>
        ";

        return $text;
    }

    /**
     Student search row (each row is of student record) for student parent enrollment form - Left side of Modal Window.
    */
    function getTraineeSearchResult(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $contact_name = $fn->getReqParam('student_name');
        $order_id     = $fn->getReqParam('order_id');
        $course_id    = $fn->getReqParam('course_id');

        $rows = '';

        $s = $_SESSION['selectedContactIds'];
        //$company_id = $fn->getReqParam('company_id');
        $history_id  = $fn->getPostParam('history_id');
        $parent_link = $fn->getPostParam('parent_link');

        $appendSQL = '';
        if (count($s) > 0){
            $selectContactIds = join(',', $s);
            $appendSQL = "AND c.contact_id NOT IN ($selectContactIds) ";
        }

        if ($parent_link == 'yes') {
            $sqlContact  = "
            SELECT c.*
            FROM contact c
            LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
            WHERE (
                c.first_name LIKE '%{$contact_name}%'
                OR c.last_name LIKE '%{$contact_name}%'
                OR c.id_card_no LIKE '%{$contact_name}%'
               )
            {$appendSQL}
            AND pc.parent_id = {$history_id}
            ";
        } else {
            $sqlContact  = "
            SELECT c.*
            FROM contact c
            WHERE (
                c.first_name LIKE '%{$contact_name}%'
                OR c.last_name LIKE '%{$contact_name}%'
                OR c.id_card_no LIKE '%{$contact_name}%'
               )
            {$appendSQL}
            AND c.company_id = {$history_id}
            ";;
        }
        //print $sqlContact;
        $result  = $db->sql_query($sqlContact);

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>{$row['first_name']}</td>
                ";
            if ($parent_link == 'yes') {
                $rows .= "
                <td>{$row['age']}</td>
                ";
            }
            else{
                $rows .= "
                <td>{$row['last_name']}</td>
                <td>{$row['id_card_no']}</td>
                ";
            }

            $rows .= "
                <td class='txtCenter'><a href='#' class='addTrainee' contact_id='{$row['contact_id']}' order_id='{$order_id}'>Add</a></td>
            </tr>
            ";
        }
        if ($parent_link == 'yes') {
            $thAddtnl = "
            <th>Name</th>
            <th>Age</th>
            ";
        }
        else{
            $thAddtnl = "
            <th>First Name</th>
            <th>Last Name</th>
            <th>NRIC No</th>
            ";
        }


        $text = "
        <table class='traineeSearchRow thinlist'>
            <thead>
                <tr>
                    {$thAddtnl}
                    <th></th>
                </tr>
            </thead>
            {$rows}
        </table>
        ";

        $validate = Zend_Registry::get('validate');
        return $validate->getSuccessMessageXML('', $text);
    }

    /**
    Linked student detail in right panel (function called when view link is clicked in enrolled student)
    */
    function getContactDetails(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $text = '';

        $contact_id = $fn->getReqParam('contact_id');
        $sqlContact  = "
        SELECT c.*
        FROM contact c
        WHERE c.contact_id = '{$contact_id}'
        ";
        $result  = $db->sql_query($sqlContact);

        $exp = array('isEditable' => 0);
        while ($row = $db->sql_fetchrow($result)) {

            $is_citizen = ($row['is_citizen'] == 0) ? "No" : "Yes";

            $text = "
            <form class='yform columnar' method='post' action=''>
                <h2>Contact Details</h2>
                {$formObj->getTBRow('First Name', 'first_name', $row['first_name'], $exp)}
                {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'], $exp)}
                {$formObj->getDDRowBySQL('Gender', 'gender', '', $row['gender'], $exp)}
                {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'], $exp)}
                {$formObj->getDateRow('Age', 'age', $row['age'], $exp)}
                {$formObj->getTBRow('Academic School & Level', 'academic_school_level', $row['academic_school_level'], $exp)}
                {$formObj->getTBRow('NRIC No', 'id_card_no', $row['id_card_no'], $exp)}
                {$formObj->getTBRow('Singapore Citizen / PR', 'is_citizen', $is_citizen, $exp)}
                {$formObj->getDDRowBySQL('Nationality', 'nationality', '', $row['nationality'], $exp)}
            </form>
            ";
        }

        return $text;

     }

    /**
    Linked student edit in right panel (function called when edit link is clicked in enrolled student).
    This opens in a modal window where student data is edited and saved
    */
    function getContactEdit(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $contact_id     = $fn->getReqParam('contact_id');
        $sqlNationality = $fn->getValueListSQL('nationality');
        $sqlGender      = $fn->getValueListSQL('gender');
        $expVL          = array('sqlType' => 'OneField');

        $sqlContact  = "
        SELECT c.*
        FROM contact c
        WHERE c.contact_id = '{$contact_id}'
        ";
        $result  = $db->sql_query($sqlContact);
        $row = $db->sql_fetchrow($result);

        $formAction ='index.php?module=enterpriseIms_orderLink&_spAction=contactSave&showHTML=0';
        $text = "
        <form name='portalForm' id='contactEditForm' class='yform columnar'
              method='post' action='{$formAction}'>
            {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
            {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
            {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'], $expVL)}
            {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'])}
            {$formObj->getTBRow('Age', 'age', $row['age'])}
            {$formObj->getTBRow('Academic School & Level', 'academic_school_level', $row['academic_school_level'])}
            {$formObj->getTBRow('NRIC No', 'id_card_no', $row['id_card_no'])}
            {$formObj->getYesNoRRow('Singapore Citizen / PR', 'is_citizen', $row['is_citizen'])}
            {$formObj->getDDRowBySQL('Nationality', 'nationality', $sqlNationality, $row['nationality'], $expVL)}
            <input type='hidden' name='contact_id' value='{$contact_id}' />
            <input type='submit' name='x_submit' class='submithidden' />
        </form>
        ";

        return $text;
     }

    /**
    Provision to add a new student in enrollment form. This opens in a modal window where a new student is saved
    */
    function getContactNew(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $company_id     = $fn->getReqParam('company_id');
        $parent_id      = $fn->getReqParam('parent_id');
        $sqlNationality = $fn->getValueListSQL('nationality');
        $sqlGender      = $fn->getValueListSQL('gender');
        $sqlAcademicSchool = $fn->getValueListSQL('academicSchool');
        $expVL          = array('sqlType' => 'OneField');

        if ($company_id) {
            $history_id = $company_id;
            $parent_link ='';
        } else if ($parent_id) {
            $history_id = $parent_id;
            $parent_link = 'yes';
        }

        $expNoEdit = array('isEditable' => 0);

        $formAction ='index.php?module=enterpriseIms_orderLink&_spAction=contactAddSubmit&showHTML=0';
        $text = "
        <form name='portalForm' id='contactAddForm' class='yform columnar'
              method='post' action='{$formAction}'>
            {$formObj->getTBRow('First Name', 'first_name')}
            {$formObj->getTBRow('Last Name', 'last_name')}
            {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, '', $expVL)}
            {$formObj->getDateRow('Date of Birth', 'date_of_birth', '', array('yearStart' => 1920, 'yearEnd' => date('Y') + 10))}
            {$formObj->getTBRow('Age', 'age')}
            {$formObj->getTBRow('Academic School & Level', 'academic_school_level')}
            {$formObj->getTBRow('NRIC No', 'id_card_no')}
            {$formObj->getYesNoRRow('Singapore Citizen / PR', 'is_citizen')}
            {$formObj->getDDRowBySQL('Nationality', 'nationality', $sqlNationality, '', $expVL)}
            <input type='hidden' name='history_id' value='{$history_id}' />
            <input type='hidden' name='parent_link' value='{$parent_link}' />
            <input type='submit' name='x_submit' class='submithidden' />
        </form>
        ";

        return $text;
     }
    //=================================== PARENT ENROLLMENT RELATED FUNCTIONS - MODAL FORM RIGHT SIDE DISPLAY ============
     function getSelectedStudentList(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $modulesArr = Zend_Registry::get('modulesArr');

        $course_id = '';
        $level_id  = '';
        $batch_id  = '';
        $course_subsidy_history_id = '';
        $discount_id = '';
        $rows = '';
        $sqlLevel = '';
        $sqlBatch = '';
        $sqlSubsidy = '';
        $sqlDiscount = '';
        $sqlAppendLevel    = '';
        $sqlAppendbatch    = '';
        $sqlAppendSubsidy  = '';
        $sqlAppendDiscount = '';
        $sqlAppendCourse   = '';
        $appendSQl = '';
        $course_id = '';

        $contact_id = $fn->getReqParam('contact_id');
        $order_id   = $fn->getReqParam('order_id');

        if ($contact_id != ''){
            $_SESSION['selectedContactIds'][] = $contact_id;
            //Below code is to avoid double entry of the existing contact_id in session id.
            $existingContactIds = join(',', $_SESSION['selectedContactIds']);
            $appendSQl = " AND contact_id NOT IN ($existingContactIds)";
        }

        if ($order_id != ''){
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

            $viewUrl ='index.php?module=enterpriseIms_orderLink&_spAction=contactDetails&showHTML=0&contact_id=';
            $editUrl ='index.php?module=enterpriseIms_orderLink&_spAction=contactEdit&showHTML=0&contact_id=';

            while ($row = $db->sql_fetchrow($result)) {
                $vUrl = $viewUrl . $row['contact_id'];
                $eUrl = $editUrl . $row['contact_id'];
                $add_reg_fee_value = '';
                //in edit mode set related records for each filter
                if ($order_id != '' ){
                    $expCourseContact = array('condn' => " AND contact_id = {$row['contact_id']}
                    ");

                    $courseContactRec = $fn->getRecordRowByID('course_contact', 'order_id', $order_id, $expCourseContact);
                    $course_id = $courseContactRec['course_id'];

                    $sqlAppendLevel = "
                        WHERE cl.course_id = $course_id
                    ";

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

                $sqlCourse    = "
                SELECT course_id
                      ,title
                FROM course
                WHERE published = 1
                ORDER BY sort_order ASC
                ";

                $sqlLevel    = "
                SELECT l.level_id
                      ,l.title
                FROM level l
                LEFT JOIN (course_level cl) ON (l.level_id = cl.level_id)
                {$sqlAppendLevel}
                ";

                $sqlBatch    = "
                SELECT batch_id
                      ,title
                FROM batch
                {$sqlAppendbatch}
                ";

                $sqlSubsidy  = "
                SELECT s.course_subsidy_history_id
                      ,sd.title
                FROM course_subsidy_history s
                LEFT JOIN (subsidy_discount sd) ON (s.subsidy_discount_id = sd.subsidy_discount_id AND sd.category_type = 'Subsidy')
                WHERE sd.title != ''
                {$sqlAppendSubsidy}
                ";

                $sqlDiscount = "
                SELECT s.course_subsidy_history_id
                      ,sd.title
                FROM course_subsidy_history s
                LEFT JOIN (subsidy_discount sd) ON (s.subsidy_discount_id = sd.subsidy_discount_id AND sd.category_type = 'Discount')
                WHERE sd.title != ''
                {$sqlAppendDiscount}
                ";
                if ($order_id != ''){
                    $expCourseContact = array('condn' => " AND contact_id = {$row['contact_id']}
                    ");
                    $courseContactRec = $fn->getRecordRowByID('course_contact', 'order_id', $order_id, $expCourseContact);
                    $level_id = $courseContactRec['level_id'];
                    $batch_id = $courseContactRec['batch_id'];
                    $course_subsidy_history_id = $courseContactRec['course_subsidy_history_id'];
                    $discount_id = $courseContactRec['discount'];
                    $course_id   = $courseContactRec['course_id'];
                    $add_reg_fee_value = $courseContactRec['add_registration_fee'];
                }

                $arr = array('1' => 'Yes', '0' => 'No');
                if($add_reg_fee_value == 1){
                    $add_reg_fee_val = 'Yes';
                } else {
                    $add_reg_fee_val = 'No';
                }

                $rows .= "
                <tr>
                    <td class='first_name'>{$row['first_name']}</td>
                    <td class='age'>{$row['age']}</td>
                    <td>
                    <select name='course_id_row[]' class='fld_course_id_row'>
                            <option value=''>{$modulesArr['enterpriseIms_course']['title']}</option>
                            {$dbUtil->getDropDownFromSQLCols2($db, $sqlCourse, $course_id)}
                        </select>
                    </td>
                    <td>
                        <input type='hidden' name='contact_id[]' value='{$row['contact_id']}'>
                        <select name='level_id[]' class='fld_level_id'>
                            <option value=''>{$modulesArr['enterpriseIms_level']['title']}</option>
                            {$dbUtil->getDropDownFromSQLCols2($db, $sqlLevel, $level_id)}
                        </select>
                    </td>
                    <td>
                        <input type='hidden' name='contact_id[]' value='{$row['contact_id']}'>
                        <select name='batch_id[]' class='fld_batch_id'>
                            <option value=''>{$modulesArr['enterpriseIms_batch']['title']}</option>
                            {$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch, $batch_id)}
                        </select>
                    </td>
                    <td>
                        <select name='course_subsidy_history_id[]' class='fld_course_subsidy_history_id'>
                            <option value=''>Subsidy</option>
                            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSubsidy, $course_subsidy_history_id)}
                        </select>
                    </td>
                    <td class='reg_fee'>{$formObj->getRadioArrRow(' ', "{$row['contact_id']}_add_reg_fee", $add_reg_fee_val, $arr, '')}
                    </td>
                    <td><a class='showDetailPortalForm jqui-dialog' href='{$vUrl}'>view</a></td>
                    <td><a class='editContactDetails' href='{$eUrl}'>edit</a></td>
                    <td class='txtCenter'><a href='#' class='removeTrainee' contact_id='{$row['contact_id']}' order_id='{$order_id}'>Remove</a></td>
                </tr>
                ";
            }
        }

        $text = "
        <table class='traineesSelectedLinked thinlist'>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th>{$modulesArr['enterpriseIms_course']['title']}</th>
                    <th>{$modulesArr['enterpriseIms_level']['title']}</th>
                    <th>{$modulesArr['enterpriseIms_batch']['title']}</th>
                    <th>Subsidy</th>
                    <!--<th>Discount</th>-->
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
    This function is called from javascript to update the row( in right panel - enrollment form)
    when we add new contact/trainee from left panel to right panel.
    This funtion is not used in ipms.
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
        $sqlLevel = '';
        $sqlBatch = '';
        $sqlSubsidy = '';
        $sqlDiscount = '';
        $sqlAppendLevel    = '';
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

            $sqlAppendLevel = "
                WHERE course_id = $course_id
            ";
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

        $sqlCourse    = "
        SELECT course_id
              ,title
        FROM course
        ";

        $sqlLevel = "
        SELECT level_id
              ,title
        FROM level
        {$sqlAppendLevel}
        ";

        $sqlBatch = "
        SELECT batch_id
              ,title
        FROM batch
        {$sqlAppendbatch}
        ";

        $sqlSubsidy = "
        SELECT s.course_subsidy_history_id
              ,sd.title
        FROM course_subsidy_history s
        LEFT JOIN (subsidy_discount sd) ON (s.subsidy_discount_id = sd.subsidy_discount_id AND sd.category_type = 'Subsidy')
        WHERE sd.title != ''
        {$sqlAppendSubsidy}
        ";

        $sqlDiscount = "
        SELECT s.course_subsidy_history_id
              ,sd.title
        FROM course_subsidy_history s
        LEFT JOIN (subsidy_discount sd) ON (s.subsidy_discount_id = sd.subsidy_discount_id AND sd.category_type = 'Discount')
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

        $viewUrl ='index.php?module=enterpriseIms_orderLink&_spAction=contactDetails&showHTML=0&contact_id=';
        $editUrl ='index.php?module=enterpriseIms_orderLink&_spAction=contactEdit&showHTML=0&contact_id=';

        $row = $db->sql_fetchrow($result);
        $vUrl = $viewUrl . $row['contact_id'];
        $eUrl = $editUrl . $row['contact_id'];

        $levelOptions    = '';
        $batchOptions    = '';
        $subsidyOptions  = '';
        $discountOptions = '';

        if (!$row['is_citizen']) {
            $batchOptions = "
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch, $batch_id)}
            ";
        }

        if (!$row['is_citizen']) {
            $subsidyOptions = "
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSubsidy, $course_subsidy_history_id)}
            ";
        }

        if ($course_id == '') {
            $batchOptions = "";
            $subsidyOptions = "";
        }

        if (!$row['is_citizen']) {
            $discountOptions = "
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlDiscount, $discount_id)}
            ";
        }

        $arr = array('1' => 'Yes', '0' => 'No');

        $rows = "
        <tr contact_id_row ='{$row['contact_id']}'>
            <td class='first_name'>{$row['first_name']}</td>
            <td class='age'>{$row['age']}</td>
            <td>
                <select name='course_id_row[]' class='fld_course_id_row'>
                    <option value=''>{$modulesArr['enterpriseIms_course']['title']}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlCourse, $course_id)}
                </select>
            </td>
            <td>
                <select name='level_id[]' class='fld_level_id'>
                    <option value=''>{$modulesArr['enterpriseIms_level']['title']}</option>
                    {$levelOptions}
                </select>
            </td>
            <td>
                <select name='batch_id[]' class='fld_batch_id'>
                    <option value=''>{$modulesArr['enterpriseIms_batch']['title']}</option>
                    {$batchOptions}
                </select>
            </td>
            <td>
                <select name='course_subsidy_history_id[]' class='fld_course_subsidy_history_id'>
                    <option value=''>Subsidy</option>
                    {$subsidyOptions}
                </select>
            </td>
            <!--<td>
                <select name='discount_id[]' class='fld_discount_id'>
                    <option value=''>Discount</option>
                    {$discountOptions}
                </select>
            </td>-->
            <td>
                <div class='row_add_reg_fee'>
                {$formObj->getRadioArrRow(' ', "{$row['contact_id']}_add_reg_fee", '', $arr, '')}
                <!--<div class='type-check'>
                        <input type='radio' value='1' name='add_reg_fee[]'>
                        <label for='add_reg_fee_1'>Yes</label>
                    </div>

                    <div class='type-check'>
                        <input type='radio' value='0' name='add_reg_fee[]'>
                        <label for='add_reg_fee_2'>No</label>
                    </div>-->
                </div>
            </td>
            <td>
            <a class='showDetailPortalForm jqui-dialog' href='{$vUrl}'>view</a>
            </td>
            <td><a class='editContactDetails' href='{$eUrl}'>edit</a></td>
            <td class='txtCenter'><a href='#' class='removeTrainee' contact_id='{$row['contact_id']}' order_id='{$order_id}'>Remove</a></td>
        </tr>
        ";

        return $rows;
    }

    /**
     * Parent Enrollment form
    */
    function getBulkParentStudentEnrollment(){
        /********************************* PROCESS ************************************/
        /*
        ACTION: PARENT RIGHT PANEL: DISPLAY FOR PARENT # STUDENT ENROLLMENT WINDOW, WHEN WE 'CLICK HERE FOR ENROLLMENT' BUTTON IS CLICKED 
        STEP 1: SELECT THE STUDENTS FOR THE PARENT AND SAVE THEM IN A SESSION VARIABLE
        STEP 2: DISPLAY ENROLLMENT YEAR DROP DOWN BOX
        STEP 3: DISPLAY OF OVERALL FORM OF ENROLLMENT
        */
        /******************************* END PROCESS **********************************/

        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

		$sqlCourse   = $fn->getDDSql('enterpriseIms_course');
        $traineeSelectedResult = '';
        $course_id = '';
        $enrollmentYearFld = '';
        #$_SESSION['selectedContactIds'] = array();
        $parent_id  = $fn->getReqParam('parent_id');
        $order_id   = $fn->getReqParam('order_id');
        $monthList = '';

        /********************************** STEP 1 **************************************/
        $sqlContact = "
        SELECT c.*
        FROM contact c
        LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
        WHERE pc.parent_id = {$parent_id}
        ";
        $resultContact  = $db->sql_query($sqlContact);
        $numRows = $db->sql_numrows($resultContact);
        $contact_id = '';
        $contactCount = 1;

        $_SESSION['selectedContactIds'] = array();
        if ($order_id == '') {
            while ($rowContact = $db->sql_fetchrow($resultContact)) {
                $_SESSION['selectedContactIds'][] = $rowContact['contact_id'];
            }
        }
        /********************************** STEP 1 ENDS HERE ****************************/

        $formAction = "index.php?_spAction=addbulkParentStudentSubmit&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        //to show the header
        $traineeSelectedResult = $this->getSelectedStudentList();

        /********************************** STEP 2 **************************************/
        if($order_id != ''){
            //$formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
            $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);
            $formAction = "index.php?_spAction=saveBulkParentStudentSubmit&lnkRoom={$tv['lnkRoom']}&showHTML=0";
            $enrollmentYearFld = 'Enrollment Year : ' . $orderRec['year_of_enrollment'];
        }
        else{
            $enrollmentYearFld = $formObj->getDDRowByArr('Enrollment Year', 'year_of_enrollment', $cpCfg['m.enterpriseIms.parent.enrollmentYear'], $cpCfg['m.enterpriseIms.currentYear']);
            $monthList = $this->getMonthList();
        }

        /********************************** STEP 2 ENDS HERE ****************************/

        $addUrl ='index.php?module=enterpriseIms_orderLink&_spAction=contactNew&showHTML=0&parent_id=';
        $addTraineeUrl = $addUrl . $parent_id ;
        $expEdit = array('isEditable' => 0);

        /********************************** STEP 3 **************************************/
        $text = "
        <div class='subcolumns companyCourseBulkLink'>
            <!-- LEFT PANEL OF ENROLLMENT FORM-->
            <div class='c20l'>
                <!--
                <a class='newContactDetails button' href='{$addTraineeUrl}'>Click here to Add Student</a>
                -->
                <h2>Students</h2>
                <div class='subcl leftCol'>
                    {$this->getCourseTraineeSearchForm()}
                    <div id='traineeSearchResult'>
                    </div>
                </div>
            </div>
            <!-- RIGHT PANEL OF ENROLLMENT FORM-->
            <div class='c80r'>
                <form id='traineeSelectedForm' class='yform columnar' method='post' action='{$formAction}'>
                    <div class='mb10'>
                        <div class=''>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
                        {$enrollmentYearFld}
                        {$formObj->getTBRow('Discount Amount', 'discount_amount')}
                        <div id='monthListWrapper'>{$monthList}</div>
                    </div>
                    <h2>Selected Trainee</h2>
                    <div class='subcr rightCol'>
                        <div id='traineeSelectedResult'>
                            {$traineeSelectedResult}
                        </div>
                    </div>
                    <input type='hidden' name='parent_id' value='{$parent_id}'>
                    <input type='hidden' name='order_id' value='{$order_id}'>
                </form>
            </div>
        </div>
        ";
        /********************************** STEP 3 ENDS HERE ****************************/

        return $text;
    }

    /**
    */
    function getMonthList(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $selected_year = $fn->getReqParam('selected_year');

        $rows = "";
        $rowsHTML = "";

        $current_month = date('m');
        $current_year  = date('Y');

        for($i=1;$i<=12;$i++){
            $fldName = "month[]";

            $checked = "";
            if ($i >= $current_month || $selected_year > $current_year) {
                $checked = "checked='checked'";
            }

            $status = "<input type='checkbox' name={$fldName} value='{$i}' class='orderItem' {$checked}>";
            $rowsHTML .="
            <td>{$status}</td>
            ";
        }

        $rows .= "
        <tr>
            {$rowsHTML}
        </tr>
        ";

        $text = "
        <table class='thinlist monthList'>
            <thead>
                <tr>
                    <th>Jan</th>
                    <th>Feb</th>
                    <th>Mar</th>
                    <th>Apr</th>
                    <th>May</th>
                    <th>Jun</th>
                    <th>Jul</th>
                    <th>Aug</th>
                    <th>Sep</th>
                    <th>Oct</th>
                    <th>Nov</th>
                    <th>Dec</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";
        return trim($text);
    }
}
