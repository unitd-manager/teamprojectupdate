<?
class CP_Admin_Modules_Web2_Blog_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rowCounter = 0;
        $rows       = "";
        foreach ($dataArray as $row){
            $rows .="
    		{$listObj->getListRowHeader($row, $rowCounter)}
    		{$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListSortOrderField($row, 'blog_id')}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListDataCell($row['blog_id'], 'center')}
    		{$listObj->getListPublishedImage($row['published'] , $row['blog_id'])}
    		{$listObj->getListRowEnd($row['blog_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
    	{$listObj->getListHeaderCell('Title', 'b.title')}
        {$listObj->getListSortOrderImage()}
    	{$listObj->getListHeaderCell('Date', 'b.creation_date')}
    	{$listObj->getListHeaderCell('ID', 'b.blog_id', 'headerCenter')}
    	{$listObj->getListHeaderCell('Published', 'a.published', 'headerCenter')}
    	{$listObj->getListHeaderEnd()}
        {$rows}
	    {$listObj->getListFooter()}
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
        {$displayLinkData->getLinkPortalMain('web2_blog', 'web2_tagsLink', 'Tags Linked', $row)}
        {$comment->getView(array(
             'roomName' => 'web2_blog'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    //==================================================================//

}