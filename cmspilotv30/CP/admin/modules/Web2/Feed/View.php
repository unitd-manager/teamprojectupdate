<?
class CP_Admin_Modules_Web2_Feed_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $dataArray = $this->model->dataArray;

        $rows  = '';
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['content_date'])}
            {$listObj->getListDataCell($row['feed_source'])}
            {$listObj->getListDataCell($row['feed_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['feed_id'])}
            {$listObj->getListRowEnd($row['feed_id'])}
            ";
            $rowCounter++;
        }


        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'c.title')}
        {$listObj->getListHeaderCell('Feed Date', 'f.content_date')}
        {$listObj->getListHeaderCell('Source', 'f.feed_source')}
        {$listObj->getListHeaderCell('Feed ID', 'f.feed_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');

        $published        = '';
        $metaData         = '';

        $expNotEditable = array('isEditable' => 0);

        if ($cpCfg['m.web2.feed.showMetaData'] == 1) {
            $metaData = $formObj->getMetaData($row);
        }

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getYesNoRRow('Show Title', 'show_title', $row['show_title'])}
        {$formObj->getTBRow('Actual Url', 'actual_url', $row['actual_url'],$expNotEditable)}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getDateRow('Content Date', 'content_date', $row['content_date'])}
        {$formObj->getTBRow('Feed Title', 'title', $row['feed_title'], $expNotEditable)}
        {$formObj->getTBRow('Feed Source', 'feed_source', $row['feed_source'], $expNotEditable)}
        ";

        $fieldset2 = $formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'));

        $text = "
        {$formObj->getFieldSetWrapped('Feed Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$metaData}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
    }
}