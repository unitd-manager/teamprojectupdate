<?
class CP_Admin_Modules_Web2_BlogFat_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray) {
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $dateUtil = Zend_Registry::get('dateUtil');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');

        $rowCounter = 0;
        $rows = '';
        foreach ($dataArray as $row){
            $url = '';

            $titleText = $pager->getGoToDetailText($row['title'], $rowCounter , '');
            $dateFld   = $dateUtil->formatDate($row['creation_date'], '[YYYY.MM.DD HH:MIN AM]');

            $exp = array('style' => 'normalImage', 'limit' => 1, 'folder' => 'normal', 'url' => $url);

            $btnAddCmt = '';
            
            /** is added to suppress any waring from cut html string ***/
            ini_set('display_errors', 0);

            $desc = $ln->gfv($row, 'description', '0');
            $desc = cut_html_string($desc, 300, '...');
            
            $tagsArr = explode(',', $row['tags']);
            
            $tags = '';
            
            $staff_name = '';
            
            $rows .= "
            <div class='row floatbox'>
                <h1>{$titleText}</h1>
                {$desc}
            </div>
            ";
        }
         
        $modTags = includeCPClass('Module', 'web2_tags', 'tags');
        $tagList = $modTags->getTagsList();

        $text = "
        <div class='blogFat'>
            <div class='subcolumns'>
                <div class='c80l'>
                    <div class='subcl'>
                        {$rows}
                    </div>
                </div>
                <div class='c20r'>
                    <div class='subcr'>
                        {$tagList}
                    </div>
                </div>
            </div>
        </div>
		";
        return $text;
    }

    //==================================================================//
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

    //========================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function getEdit($row) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $ln = Zend_Registry::get('ln');
        $am = Zend_Registry::get('am');

        $sqlCombo = "
        SELECT contact_id
              ,CONCAT_WS(' ', first_name, last_name) AS contact_name
        FROM contact 
        WHERE TRIM(CONCAT_WS(' ', first_name, last_name)) != ''
        ORDER BY contact_name
        ";
        $expContact = array('detailValue' => $row['contact_name']);

        $formObj->mode = $tv['action'];

        $fielset1  = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0') )}
        {$formObj->getDDRowBySQL('Contact Name', 'contact_id', $sqlCombo, $row['contact_id'], $expContact)}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $fieldset2 = $formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'));

        $text = "
        {$formObj->getFieldSetWrapped('Blog Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $record_id = $fn->getIssetParam($row, 'blog_id');

        $text = "
        {$displayLinkData->getLinkPortalMain('web2_blogFat', 'web2_tagsLink', 'Tags Linked', $row)}
        {$comment->getView(array(
             'roomName' => 'web2_blogFat'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    //==================================================================//

}