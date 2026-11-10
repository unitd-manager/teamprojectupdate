<?
class CP_Www_Modules_Edukite_DailyActivity_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $exp = array('class' => 'teacherName');
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'], '', '', $row)}
            {$listObj->getListDataCell($row['teacher_name'], '', '', '', $exp)}
            {$listObj->getListRowEnd($row['daily_activity_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        <div class='dailyActivityList'>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('Title', 'da.title')}
            {$listObj->getListHeaderCell('Teacher Name', 'teacher_name')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getTARow('', 'notes', $row['notes'])}
        {$formObj->getTBRow('Jellyfish Group-time', 'jellyfish_group_time', $row['jellyfish_group_time'])}
        {$formObj->getTBRow('Sea Turtles Group-time', 'sea_turtles_group_time', $row['sea_turtles_group_time'])}
        {$formObj->getTBRow('Whales Group-time', 'whales_group_time', $row['whales_group_time'])}
        {$formObj->getTBRow('Music', 'music', $row['music'])}
        {$formObj->getTBRow('School Readiness Program', 'school_readiness_program', $row['school_readiness_program'])}
        {$formObj->getTBRow('Today’s Meals', 'todays_meals', $row['todays_meals'])}
        {$formObj->getTBRow('Morning Tea', 'morning_tea', $row['morning_tea'])}
        {$formObj->getTBRow('Fruit Break', 'fruit_break', $row['fruit_break'])}
        {$formObj->getTBRow('Lunch', 'lunch', $row['lunch'])}
        {$formObj->getTBRow('Dessert', 'dessert', $row['dessert'])}
		";

        $text = "
        <div class='dailyActivitySubmit'>
            <a id='btnSaveRecord' href='javascript:void(0);'>SUBMIT</a>
        </div>
        {$formObj->getFieldSetWrapped('Notice Type Details', $fieldset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $teacher_id = $fn->getReqParam ('teacher_id');
        $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);

        $sqlTeacher = "
        SELECT t.teacher_id
              ,CONCAT_WS(' ', t.first_name, t.last_name ) AS teacher_name
        FROM teacher t
        WHERE t.status = 'Active'
        ORDER BY teacher_name
        ";

        $staffFilter = "
        <div class='float_left'>
            <tr>
                <select name='teacher_id'>
                    <option value=''>Staff</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlTeacher, $teacher_id)}
                </select>
            </tr>
        </div>
        ";

        $text = "
        <div class='ddFilter'>
            <div class='floatbox'>
                {$staffFilter}
            </div>
        </div>
        ";

        return $text;
    }
}