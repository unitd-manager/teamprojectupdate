<?
class CP_Admin_Modules_Common_LoginHistory_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT l.* 
            ,DATE_FORMAT(l.creation_date, '%d-%m-%Y') AS login_date
            ,DATE_FORMAT(l.creation_date, '%H:%i')    AS login_time
            ,CONCAT_WS(' ', s.first_name, s.last_name ) AS contact_name
        FROM login_history l
        LEFT JOIN (student s) ON (l.student_id = s.student_id )
        ";
        
        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar($linkRecType = '') {
    }
}
