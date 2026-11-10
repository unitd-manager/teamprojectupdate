<?
class CP_Admin_Modules_Web2_Tags_View extends CP_Common_Modules_Web2_Tags_View
{
    /**
     *
     */
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows       = "";
        $tagsGroupHeader = '';
        $tagsGroupValue = '';

        foreach ($dataArray as $row){
        	
        	if ($cpCfg['m.web2.tags.hasGroup'] == 1){
        		$tagsGroupValue = $listObj->getListDataCell($row['tag_group']);
        	}
        	
            $rows .="
    		{$listObj->getListRowHeader($row, $rowCounter)}
    		{$listObj->getGoToDetailText($rowCounter, $row['tag_text'])}
            {$tagsGroupValue}
            {$listObj->getListDataCell($row['tags_id'], 'center')}
    		{$listObj->getListPublishedImage($row['published'] , $row['tags_id'])}
    		{$listObj->getListRowEnd($row['tags_id'])}
			";

        	$rowCounter++;
		}
         
        if ($cpCfg['m.web2.tags.hasGroup'] == 1){
        	$tagsGroupHeader = $listObj->getListHeaderCell($ln->gd('m.web2.tags.lbl.tagGroup', 'Tag Group'), 't.tag_group');
        }

        $text = "
    	{$listObj->getListHeader()}
    	{$listObj->getListHeaderCell($ln->gd('m.web2.tags.lbl.tagText', 'Tag Text'), 't.tag_text')}
    	{$tagsGroupHeader}
    	{$listObj->getListHeaderCell($ln->gd('m.web2.tags.lbl.id', 'ID'), 't.tags_id', 'headerCenter')}
    	{$listObj->getListHeaderCell($ln->gd('m.web2.tags.lbl.published', 'Published'), 'a.published', 'headerCenter')}
    	{$listObj->getListHeaderEnd()}
        {$rows}
	    {$listObj->getListFooter()}
		";
        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $fieldset = "
        {$formObj->getTBRow($ln->gd('m.web2.tags.lbl.tagText', 'Tag Text'), 'tag_text')}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $formObj->mode = $tv['action'];
        
        $tagsGroup = '';
        $parent    = '';
        $cssStyle  = '';
        
        $exp = array('sqlType' => 'OneField');

        if ($cpCfg['m.web2.tags.hasGroup'] == 1){
            $tagsGroup = $formObj->getDDRowByArr($ln->gd('m.web2.tags.lbl.tagGroup', 'Tag Group'), 'tag_group', $this->fns->getTagsGroupArray(), $row['tag_group']);
        }

        if ($cpCfg['m.web2.tags.hasChildren'] == 1){
            $sqlParent = "
            SELECT tags_id
                  ,tag_text
            FROM tags
            WHERE tags_id != {$row['tags_id']}
            AND parent_id = 0 OR parent_id IS NULL
            ORDER BY tag_text
            ";
            $parent = $formObj->getDDRowBySQL($ln->gd('m.web2.tags.lbl.parent', 'Parent'), 'parent_id', $sqlParent, $row['parent_id']);
        }

        if ($cpCfg['m.web2.tags.hasCSSStyle'] == 1){
            $cssStyle = $formObj->getTBRow($ln->gd('m.web2.tags.lbl.cssStyle'), 'css_style' , $row['css_style']);
        }

        $fielset1  = "
        {$formObj->getTBRow($ln->gd('m.web2.tags.lbl.tagText', 'Tag Text'), 'tag_text', $ln->gfv($row, 'tag_text', '0'))}
        {$tagsGroup}
        {$parent}
        {$cssStyle}
        {$formObj->getYesNoRRow($ln->gd('m.web2.tags.lbl.published', 'Published'), 'published', $row['published'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.web2.tags.lbl.mainDetails', 'Main Details'), $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $text = "
        ";

        return $text;
    }
    
}