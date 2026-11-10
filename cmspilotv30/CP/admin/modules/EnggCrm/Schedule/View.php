<?
class CP_Admin_Modules_EnggCrm_Schedule_View extends CP_Common_Lib_ModuleViewAbstract
{

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $am = Zend_Registry::get('am');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $project_id = $fn->getReqParam('project_id');

        $SQLProj = "
        SELECT project_id
              ,title 
        FROM project 
        ORDER BY title
        ";

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='project_id'>
                <option value=''>Project</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLProj, $project_id)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";

        
        return $text;
    }
}
