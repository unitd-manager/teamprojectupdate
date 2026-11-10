<?
class CP_Admin_Modules_ELearn_Points_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('elearn_points');
        $modObj['tableName'] = 'points';
        $modObj['keyField']  = 'points_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'hasEditInList' => 0
           ,'actBtnsList'   => array()
        ));
    }

    //==================================================================//
    //==================================================================//
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');
        $book_id     = $fn->getReqParam('book_id');
        $student     = $fn->getReqParam('student_id');
        $record_type     = $fn->getReqParam('record_type');

        $SQLBook = "
        SELECT DISTINCT 
               p.book_id
              ,b.title
        FROM points p
        LEFT JOIN book b ON (b.book_id = p.book_id)
        WHERE b.title != ''
        ORDER BY b.title
        ";
                
        $SQLActivity = "
        SELECT DISTINCT 
               record_type
              ,record_type
        FROM points 
        WHERE record_type != ''
        ORDER BY record_type
        ";

        $SQLStudent = "
        SELECT DISTINCT 
               s. student_id
              ,CONCAT_WS(' ', st.first_name, st.last_name ) AS student_name 
        FROM submission s
        LEFT JOIN student st ON st.student_id = s.student_id
        ORDER BY st.first_name
        ";

        $bookType = $dbUtil->getDropDownFromSQLCols2($db, $SQLBook, $book_id);
        $recordType = $dbUtil->getDropDownFromSQLCols2($db, $SQLActivity, $record_type);
        $studentType = $dbUtil->getDropDownFromSQLCols2($db, $SQLStudent, $student);

        $text = "
        <td class='fieldValue'>
            <select name='book_id'>
                <option value=''>Select Book</option>
                {$bookType}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='record_type'>
                <option value=''>Record Type</option>
                {$recordType}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='student_id'>
                <option value=''>Select Student</option>
                {$studentType}
            </select>
        </td>
        ";
        
        return $text;

    }

    //==================================================================//
    //==================================================================//

}