<?
class CP_Admin_Modules_EnterpriseIms_BatchTransfer_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList(){
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $modulesArr = Zend_Registry::get('modulesArr');
        
        $modGroupCourse = getCPModuleObj('enterpriseIms_course');
        $sqlCourse = $modGroupCourse->model->getCourseSQL();        
        $courseRow = $formObj->getDDRowBySQL($modulesArr['enterpriseIms_course']['title'], 'enrollment_course_id', $sqlCourse, '', array('rowCls' => 'showme'));
        
        $sqlYear = "SELECT DISTINCT year_of_enrollment FROM course_contact";
        $expYear = array('sqlType' => 'OneField');

        $levelRow = $formObj->getDDRowBySQL($modulesArr['enterpriseIms_level']['title'], 'enrollment_level_id', '', '', array('rowCls' => 'hideme'));
        $batchRow = $formObj->getDDRowBySQL($modulesArr['enterpriseIms_batch']['title'], 'enrollment_batch_id', '', '', array('rowCls' => 'hideme'));

        $formAction = "index.php?module=enterpriseIms_batchTransfer&_spAction=batchTransferStudentSubmit&showHTML=0";
        $studentSelectedResult = '';
        $_SESSION['selectedContactIds'] = array();
        $studentSelectedResult = $this->getSelectedStudentList();
        
        $yearArray = array(date('Y'), date('Y') + 1, date('Y') + 2);
        $expEdit   = array('isEditable' => 0);
        
        $text = "
        <div class='floatbox batchTransfer mt10 ml10'>
            <div class='studentList'>
                <div class='subcl leftCol'>
                    {$this->getCourseTraineeSearchForm()}
                    <div id='studentSearchResult'>
                    </div>
                </div>
            </div>
            
            <div class='studentSelected'>
                <form id='studentSelectedForm' class='yform columnar' method='post' action='{$formAction}'>
                    <h2>Selected Students</h2>
                    <div class='mb10'>
                        <div class=''>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
                        {$formObj->getDDRowByArr('Year', 'enrollment_year', $yearArray)}                        
                        {$formObj->getYesNoRRow('Graduated', 'graduated', '0')}
                        {$courseRow}
                        {$levelRow}
                        {$batchRow}
                        {$formObj->getSubmitButtonRow('Transfer Students')}
                    </div>
                    <div id='studentSelectedResult'>
                        {$studentSelectedResult}
                    </div>
                </form>
            </div>
        </div>
        ";
        
        return $text;
    }

    /**
     *
     */
    function getCourseTraineeSearchForm(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $modulesArr = Zend_Registry::get('modulesArr');
        
        $modGroupCourse = getCPModuleObj('enterpriseIms_course');
        $sqlCourse = $modGroupCourse->model->getCourseSQL();        
        $courseRow = $formObj->getDDRowBySQL($modulesArr['enterpriseIms_course']['title'], 'course_id', $sqlCourse);
        
        $sqlYear = "SELECT DISTINCT year_of_enrollment FROM course_contact";
        $expYear = array('sqlType' => 'OneField');

        $levelRow = $formObj->getDDRowBySQL($modulesArr['enterpriseIms_level']['title'], 'level_id', '', '', array('rowCls' => 'hideme'));
        $batchRow = $formObj->getDDRowBySQL($modulesArr['enterpriseIms_batch']['title'], 'batch_id', '', '', array('rowCls' => 'hideme'));

        $action ='index.php?module=enterpriseIms_batchTransfer&_spAction=studentSearchResult&showHTML=0';
        $text = "
        <form id='studentSearchForm' class='yform columnar' method='post' action='{$action}'>
            <h2> Choose Students</h2>
            {$formObj->getDDRowBySQL('Previous Enrollment Year', 'year', $sqlYear, '', $expYear)}
            {$courseRow}
            {$levelRow}
            {$batchRow}
            {$formObj->getSubmitButtonRow('Display Students')}
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getStudentSearchResult(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        
        if (!$this->model->getStudentSearchValidate()){
            return $validate->getErrorMessageXML();
        }

        $course_id  = $fn->getReqParam('course_id');
        $level_id   = $fn->getReqParam('level_id');
        $batch_id   = $fn->getReqParam('batch_id');
        $year       = $fn->getReqParam('year');
        
        $rows = '';
        
        $sqlContact = $this->model->getContactsFromCourseContact();
        $result  = $db->sql_query($sqlContact);  

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>{$row['first_name']}</td>
                <td>{$row['registration_no']}</td>
                <td class='txtCenter'><a href='#' class='addTrainee' contact_id='{$row['contact_id']}'>Add</a></td>
            </tr>
            ";
        }

        $text = "
        <table class='studentSearchRow thinlist'>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Reg No</th>
                    <th class='txtCenter'><a href='#' class='addAllTrainee' 
                    year='{$year}' course_id='{$course_id}' level_id='{$level_id}' batch_id='{$batch_id}'>Add All</a></th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";

        $validate = Zend_Registry::get('validate');
        return $validate->getSuccessMessageXML('', $text);
    }

    /**
     *
     */
    function getSelectedStudentListRow(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        
        $contact_id = $fn->getReqParam('contact_id'); 
        
        //below code will be used in case if a new trainee is added.
        if ($contact_id != ''){
            $_SESSION['selectedContactIds'][] = $contact_id;
        }

        if($contact_id == ''){
            $contact_id = $_SESSION['newTrainee'];
        }
        
        $text = '';
        $rows = '';

        $sqlContact = "
        SELECT c.*
        FROM contact c
        WHERE c.contact_id = {$contact_id}
        ";
        $result  = $db->sql_query($sqlContact);  
        $row     = $db->sql_fetchrow($result);
        
        $levelOptions    = '';
        $rows = "
        <tr contact_id_row ='{$row['contact_id']}'>
            <td class='first_name'>{$row['first_name']}</td>
            <td class='reg_no'>{$row['registration_no']}</td>
            <td class='txtCenter'><a href='#' class='removeTrainee' contact_id='{$row['contact_id']}'>Remove</a></td>
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
     function getSelectedStudentList(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        
        $rows = '';

        $contact_id = $fn->getReqParam('contact_id');

        if ($contact_id != ''){
            $_SESSION['selectedContactIds'][] = $contact_id;
            //Below code is to avoid double entry of the existing contact_id in session id.
            $existingContactIds = join(',', $_SESSION['selectedContactIds']);
            $appendSQl = " AND contact_id NOT IN ($existingContactIds)";
        }
        
        $selectContactIds = join(',', $_SESSION['selectedContactIds']);
        if($selectContactIds != ''){
            $sqlContact  = "
            SELECT c.*
            FROM contact c
            WHERE c.contact_id IN ({$selectContactIds})
            ";
            $result  = $db->sql_query($sqlContact);  
           
            while ($row = $db->sql_fetchrow($result)) {
                $rows .= "
                <tr>
                    <td class='first_name'>{$row['first_name']}</td>
                    <td class='age'>{$row['registration_no']}</td>
                    <td class='txtCenter'><a href='#' class='removeTrainee' contact_id='{$row['contact_id']}'>Remove</a></td>
                </tr>
                ";
            }
        }
        
        $text = "
        <table class='studentsSelectedLinked thinlist'>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Reg No</th>
                    <th class='txtCenter'><a href='#' class='removeAllTrainee'>Remove All</a></th>
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
    function getRemoveTrainee(){
        $fn = Zend_Registry::get('fn');
        
        $contact_id = $fn->getReqParam('contact_id');
        $s = &$_SESSION['selectedContactIds'];

        if(($key= array_search($contact_id, $s)) !== false){
            unset($s[$key]);
        }
    }

    /**
     *
    */
    function getAllSelectedStudentListRow(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        
        $year       = $fn->getReqParam('year'); 
        $course_id  = $fn->getReqParam('course_id'); 
        $level_id   = $fn->getReqParam('level_id'); 
        $batch_id   = $fn->getReqParam('batch_id'); 
        
        $rows = '';
        
        $sqlContact = $this->model->getContactsFromCourseContact();
        $result  = $db->sql_query($sqlContact);  

        while ($row = $db->sql_fetchrow($result)) {
            //below code will be used in case if a new trainee is added.
            $_SESSION['selectedContactIds'][] .= $row['contact_id'];
        
            $rows .= "
            <tr contact_id_row ='{$row['contact_id']}'>
                <td class='first_name'>{$row['first_name']}</td>
                <td class='reg_no'>{$row['registration_no']}</td>
                <td class='txtCenter'><a href='#' class='removeTrainee' contact_id='{$row['contact_id']}'>Remove</a></td>
            </tr>
            "; 
        }

        return $rows;
    }

    /**
     *
    */
    function getRemoveAllTrainee(){
        $fn = Zend_Registry::get('fn');
        
        $_SESSION['selectedContactIds'] = array();
    }
}