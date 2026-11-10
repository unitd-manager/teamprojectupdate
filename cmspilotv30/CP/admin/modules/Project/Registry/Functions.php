<?
class CP_Admin_Modules_Project_Registry_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('project_registry');
        $modObj['tableName'] = 'registry';
        $modObj['keyField']  = 'registry_id';
        $modules->registerModule($modObj, array(
            'moduleGroup'  => 'project'
           ,'hasFlagInList' => 0
           ,'hasMultiLang' => 1
        ));
    }

    //==================================================================//
    //==================================================================//
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $am = Zend_Registry::get('am');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        
        $statusTxt  = '';
        
        $status     = $fn->getReqParam('status');
        $location   = $fn->getReqParam('location');
        $tags_id        = $fn->getReqParam('tags_id');

        $sqlCombo = "
        SELECT value 
        FROM valuelist 
        WHERE key_text = 'hostingServer' 
        ORDER BY sort_order
        ";

        $locationTxt = "
        <td>
            <select name='location'>
                <option value=''>Location</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCombo, $location)}
            </select>
        </td>
        ";

        $sqlCombo = "
        SELECT value 
        FROM valuelist 
        WHERE key_text = 'registryStatus' 
        ORDER BY sort_order
        ";

        $statusTxt = "
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCombo, $status)}
            </select>
        </td>
        ";
     
        $sqlCombo = "
        SELECT a.tags_id, a.tag_text, a.tag_group
        FROM tags a
        ORDER BY a.tag_group, a.tag_text
        ";

        $tagTxt = "
        <td>
            <select name='tags_id'>
                <option value=''>Tag</option>
                {$dbUtil->getDropDownWithSeperator($db, $sqlCombo, $tags_id)}
            </select>
        </td>
        ";

        $text = "
        {$locationTxt}
        {$statusTxt}
        {$tagTxt}
        ";

        return $text;

    }

    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('project_registry', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('project_registry', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

    }
}