<?
class CPL_Admin_Modules_WebBasic_Content_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $dataArray = $this->model->dataArray;

        $rows  = '';
        $country_name  = '';
        $country_name_header  = '';

        $rowCounter = 0;

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $refTitle ='';
            if ($cpCfg['m.webBasic.section.showRefTitle']){
                $refTitle = $listObj->getListDataCell($row['title_ref']);
            }

            $member_only = '';
            if ($cpCfg['m.webBasic.content.showMemberOnly'] == 1){
                $member_only = $listObj->getListDataCell($fn->getYesNo($row['member_only']), 'center');
            }

            if ($cpCfg['m.webBasic.content.showCountry'] == 1){
                $country = $fnModCountry->getCountryValueCellInList($row, 1);
            } else {
                $country = $fnModCountry->getCountryValueCellInList($row);
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$refTitle}
            {$country}
            {$listObj->getListSortOrderField($row, 'content_id')}
            {$listObj->getListDataCell($row['section_title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListDateCell($row['content_date'])}
            {$listObj->getListDataCell($row['content_type']. '')}
            {$member_only}
            {$listObj->getListDataCell($row['content_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['content_id'])}
            {$listObj->getListRowEnd($row['content_id'])}
            ";
            $rowCounter++;
        }

        $member_only = '';
        if ($cpCfg['m.webBasic.content.showMemberOnly'] == 1){
            $member_only = $listObj->getListHeaderCell('Members Only', 'c.member_only', 'headerCenter');
        }

        if ($cpCfg['m.webBasic.content.showCountry'] == 1){
            $country = $fnModCountry->getCountryLabelCellInList(1);
        } else {
            $country = $fnModCountry->getCountryLabelCellInList();
        }

        $refTitle ='';
        if ($cpCfg['m.webBasic.section.showRefTitle']){
            $refTitle = $listObj->getListHeaderCell('Ref. Title', 'c.title_ref');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.title', 'Title'), 'c.title')}
        {$refTitle}
        {$country}
        {$listObj->getListSortOrderImage()}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.content.lbl.section', 'Section'), 'section_title')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.content.lbl.category', 'Category'), 'category_title')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.content.lbl.subCategory', 'Sub Category'), 'sub_category_title')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.content.lbl.contentDate', 'Content Date'), 'c.content_date')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.content.lbl.contentType', 'Content Type'), 'c.content_type')}
        {$member_only}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.content.lbl.id', 'ID'), 'c.content_id', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.content.lbl.published', 'Published'), 'c.published', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.webBasic.content.title', 'Title'), 'title')}
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
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $externalLink     = '';
        $internalLink     = '';
        $published        = '';
        $author           = '';
        $memberOnly       = '';
        $latest           = '';
        $favourite        = '';
        $descriptionShort = '';
        $cloudTags        = '';
        $metaData         = '';
        $sectionText      = '';
        $country          = '';
        $sqlCategory      = '';
        $sqlSubCategory   = '';
        $embedCode        = '';
        $refTitle         = '';
        $downloadBrochure = '';
        $showInWeb        = '';
        $showInMobile     = '';

        if ($cpCfg['m.webBasic.section.showRefTitle']){
            $refTitle = $formObj->getTBRow('Ref. Title', 'title_ref', $row['title_ref']);
        }

        $wMultiYear = getCPWidgetObj('common_multiYear', false);

        $expSection     = array('detailValue' => $row['section_title']);
        $expCategory    = array('detailValue' => $row['category_title']);
        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $modSec = getCPModuleObj('webBasic_section');
        $modCat = getCPModuleObj('webBasic_category');
        $modSubCat = getCPModuleObj('webBasic_subCategory');

        $sqlSection = $modSec->model->getSectionSQL();

        if ($row['section_id'] != ''){
            $sqlCategory = $modCat->model->getCategorySQLBySection($row['section_id']);
        }

        if ($row['category_id'] != ''){
            $sqlSubCategory = $modSubCat->model->getSubCategorySQL($row['category_id']);
        }

        if ($cpCfg['m.webBasic.content.showExtLinkField'] == 1){
            $externalLink = $formObj->getTBRow($ln->gd('m.webBasic.content.lbl.externalLink', 'External Link'), 'external_link', $row['external_link'] );
        }

        if ($cpCfg['m.webBasic.content.showIntLinkField'] == 1){
            $internalLink = $formObj->getTBRow($ln->gd('m.webBasic.content.lbl.internalLink', 'Internal Link'), 'internal_link', $row['internal_link'] );
        }

        if ($cpCfg['m.webBasic.content.showMemberOnly'] == 1){
            $memberOnly = $formObj->getYesNoRRow('Member Only', 'member_only', $row['member_only'] );
        }

        if ($cpCfg['m.webBasic.content.showLatest'] == 1){
            $latest = $formObj->getYesNoRRow($ln->gd('m.webBasic.contentlbl.latest', 'Latest'), 'latest', $row['latest'] );
        }

        if ($cpCfg['m.webBasic.content.showFavourite'] == 1){
            $favourite = $formObj->getYesNoRRow('Favourite', 'favourite', $row['favourite']);
        }

        if ($cpCfg['m.webBasic.content.showShortDesc'] == 1){
            if ($cpCfg['m.webBasic.content.showFCKShortDesc'] == 1){
                $descriptionShort = $formObj->getHTMLEditor($ln->gd('m.webBasic.contentlbl.shortDescription', 'Short Description'),
                                        'description_short', $ln->gfv($row, 'description_short', '0'));
            } else {
                $descriptionShort = $formObj->getTARow($ln->gd('m.webBasic.contentlbl.shortDescription', 'Short Description'),
                                        'description_short', $ln->gfv($row, 'description_short'));
            }
        }

        if ($cpCfg['m.webBasic.content.showEmbedCode'] == 1){
            $embedCode = $formObj->getTARow('Embed Code', 'embed_code', $row['embed_code'] );
        }

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        if ($cpCfg['m.webBasic.content.showCountry'] == 1){
            $country = $fnModCountry->getCountryDropDown($formObj->mode, $row, 1);
        } else {
            $country = $fnModCountry->getCountryDropDown($formObj->mode, $row);
        }

        if ($cpCfg['m.webBasic.content.showCloudTags'] == 1) {
            $sqlCombo   = "SELECT tags_id FROM tags_history WHERE record_id = {$row['content_id']}";
            $result2    = $db->sql_query($sqlCombo);
            $selTagsArr = $dbUtil->getResultsetAsArrayForForm($result2);

            $sqlCombo = '
            SELECT tags_id
                  ,tag_text
            FROM tags
            ORDER BY tag_text
            ';

            $cloudTags  = $formObj->getHeaderRow('Tags/Labels');
            $cloudTags .= $formObj->getCheckboxRowBySQL('Cloud Tags', 'tags_ids[]', $sqlCombo,  $selTagsArr);
        }

        if ($cpCfg['m.webBasic.content.showMetaData'] == 1) {
            $metaData = $formObj->getMetaData($row);
        }

        if ($cpCfg['m.webBasic.content.showDownloadBrochure'] == 1) {
            $downloadBrochure = $formObj->getYesNoRRow('Download Brochure', 'show_brochure', $row['show_brochure']);
        }

        $recordUrl = '';
        if ($formObj->mode == 'detail'){
            $link   = "index.php?module={$row['section_id']}&content_id={$row['content_id']}";
            $link  .= isset($row['sub_category_id']) ? "&sub_category_id={$row['sub_category_id']}" : "";
            $recordUrl = $formObj->getTBRow("Record URL", "record_url", $link  );
        }

        if ($cpCfg['m.webBasic.content.showOkForWeb']){
            $showInWeb = $formObj->getYesNoRRow('OK for Web', 'ok_for_web', $row['ok_for_web']);
        }

        if ($cpCfg['m.webBasic.content.showOkForMobile']){
            $showInMobile = $formObj->getYesNoRRow('OK for Mobile', 'ok_for_mobile', $row['ok_for_mobile']);
        }

        if ($cpCfg['m.webBasic.content.showAuthor']){
            $author = $formObj->getTBRow('Author', 'author', $row['author']);
        }

        $fieldset1 = "
        {$formObj->getTBRow($ln->gd('m.webBasic.content.lbl.title', 'Title'), 'title', $ln->gfv($row, 'title', '0'))}
        {$refTitle}
        {$formObj->getDDRowBySQL($ln->gd('m.webBasic.content.lbl.section', 'Section'), 'section_id', $sqlSection, $row['section_id'], $expSection)}
        {$formObj->getDDRowBySQL($ln->gd('m.webBasic.content.lbl.category', 'Category'), 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        {$formObj->getDDRowBySQL($ln->gd('m.webBasic.content.lbl.subCategory', 'Sub Category'), 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
        {$formObj->getDDRowByArr($ln->gd('m.webBasic.content.lbl.contentType', 'Content Type'), 'content_type', $cpCfg['m.webBasic.content.recordTypeArr'], $row['content_type'])}
        ";

        $fieldset2 = "
        {$formObj->getYesNoRRow($ln->gd('m.webBasic.content.lbl.showTitle', 'Show Title'), 'show_title', $row['show_title'])}
        {$formObj->getYesNoRRow($ln->gd('m.webBasic.content.lbl.published', 'Published'), 'published', $row['published'])}
        {$latest}
        {$favourite}
        {$formObj->getDateRow($ln->gd('m.webBasic.content.lbl.contentDate', 'Content Date'), 'content_date', $row['content_date'])}
        {$externalLink}
        {$internalLink}
        {$country}
        {$downloadBrochure}
        {$author}
        {$memberOnly}
        {$showInWeb}
        {$showInMobile}
        {$wMultiYear->view->getCpYearRow($row)}
        {$embedCode}
        {$descriptionShort}
        {$recordUrl}
        ";

        $fieldset3 = $formObj->getHTMLEditor($ln->gd('m.webBasic.content.lbl.description', 'Description'), 'description', $ln->gfv($row, 'description', '0'));

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.webBasic.content.lbl.contentDetails', 'Content Details'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.webBasic.content.lbl.otherProperties', 'Other Properties'), $fieldset2)}
        {$formObj->getFieldSetWrapped($ln->gd('m.webBasic.content.lbl.description', 'Description'), $fieldset3)}
        {$metaData}
        {$cloudTags}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $ln = Zend_Registry::get('ln');

        $relPics = '';
        if ($cpCfg['m.webBasic.content.hasRelatedPics']){
            $relPics = $media->getRightPanelMediaDisplay('Related Pictures', 'webBasic_content', 'relatedPicture', $row);
        }

        $text = "
        {$media->getRightPanelMediaDisplay($ln->gd('m.webBasic.content.lbl.picture', 'Picture'), 'webBasic_content', 'picture', $row)}
        {$relPics}
        {$media->getRightPanelMediaDisplay($ln->gd('m.webBasic.content.lbl.attachment', 'Attachments'), 'webBasic_content', 'attachment', $row)}
        ";

        if ($cpCfg['m.webBasic.content.hasTab'] == 1){
            $text .= $displayLinkData->getLinkPortalMain('webBasic_content', 'webBasic_tabContentLink', 'Tab Content', $row);
        }

        if ($cpCfg['m.webBasic.content.hasHistory'] == 1){
            $text .= $displayLinkData->getLinkPortalMain('webBasic_content', 'webBasic_contentHistoryLink', 'Content History', $row);
        }

        if ($cpCfg['cp.hasMultiSites']) {
            $text .= $displayLinkData->getLinkPortalMain('webBasic_content', 'common_siteLink', 'Sites Linked', $row);
        }

        if ($cpCfg['m.webBasic.content.hasRelatedContent'] == 1){
            $text .= $displayLinkData->getLinkPortalMain('webBasic_content', 'webBasic_contentLink', 'Related Content', $row);
        }

        if ($cpCfg['m.webBasic.content.showOtherPicture'] == 1) {
            $text .= $media->getRightPanelMediaDisplay('Other Picture', 'webBasic_content', 'otherPicture', $row);
        }

        if ($cpCfg['m.webBasic.content.showComments']) {
            $comment = getCPPluginObj('common_comment');
            $record_id = $fn->getIssetParam($row, 'content_id');
            $text .= "
            {$comment->getView(array(
                 'roomName' => 'webBasic_content'
                ,'recordId' => $record_id
            ))}
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getDuplicate(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $content_id = $fn->getReqParam('record_id');

        $SQL = "
        SELECT a.*
        FROM content a
        WHERE a.content_id = {$content_id}
        ";
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $this->fieldsArray = array();
        $fa = &$this->fieldsArray;

        foreach($row as $key => $value){
            if(!is_int($key)){
                $fa[$key] =  $value;
            }
        }

        $fa['content_id']        =  '';
        $fa['published']         =  0;
        $fa['creation_date']     =  date('Y-m-d H:i:s');
        $fa['modification_date'] =  date('Y-m-d H:i:s');

        $SQLNew                     = $dbUtil->getInsertSQLStringFromArray($fa, 'content');
        $resultNew                  = $db->sql_query($SQLNew);
        $new_content_id             = $db->sql_nextid();

        $this->getDuplicateMedia($content_id, $new_content_id);

        $fn->returnAfterNewSave($new_content_id, 'detail', false);
    }

    /**
     *
     */
    private function getDuplicateMedia($content_id, $new_content_id){
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');
        $cpUtil = Zend_Registry::get('cpUtil');

        $mediaArrayObj->setMediaArray('content');
        $mediaArray = $mediaArrayObj->mediaArray;

        $SQL = "
        SELECT a.*
        FROM media a
        WHERE a.record_id = {$content_id}
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['record_id']        = $new_content_id;
            $fa['media_type']       = $row['media_type'];
            $fa['actual_file_name'] = $row['actual_file_name'];
            $fa['file_name']        = $row['file_name'];
            $fa['content_type']     = $row['content_type'];
            $fa['media_size']       = $row['media_size'];
            $fa['room_name']        = $row['room_name'];
            $fa['record_type']      = $row['record_type'];
            $fa['lang']             = $row['lang'];
            $fa['creation_date']    = date('Y-m-d H:i:s');

            $SQLMedia    = $dbUtil->getInsertSQLStringFromArray($fa, 'media');
            $resultMedia = $db->sql_query($SQLMedia);
            $media_id    = $db->sql_nextid();

            $sourceFileName  = $row['file_name'];
            $destFileName    = $media_id.'_'.$row['actual_file_name'];

            $module = $row['room_name'];
            $recordType = $row['record_type'];

            if ($row['media_type'] == 'image'){
               if (array_key_exists('thumbFolder', $mediaArray[$module][$recordType])) {
                  $path = $mediaArray[$module][$recordType]['thumbFolder'];
                  $copy = $cpUtil->smartCopy($path.$sourceFileName, $path.$destFileName);
               }

               if (array_key_exists('mediumFolder', $mediaArray[$module][$recordType])) {
                 $path  = $mediaArray[$module][$recordType]['mediumFolder'];
                 $copy = $cpUtil->smartCopy($path.$sourceFileName, $path.$destFileName);
               }

               if (array_key_exists('normalFolder', $mediaArray[$module][$recordType])) {
                  $path = $mediaArray[$module][$recordType]['normalFolder'];
                  $copy = $cpUtil->smartCopy($path.$sourceFileName, $path.$destFileName);
               }

               if (array_key_exists('largeFolder', $mediaArray[$module][$recordType])) {
                 $path = $mediaArray[$module][$recordType]['largeFolder'];
                 $copy = $cpUtil->smartCopy($path.$sourceFileName, $path.$destFileName);
               }

            } else {
               $path  = $mediaArray[$module][$recordType]['normalFolder'];
               $copy = $cpUtil->smartCopy($path.$sourceFileName, $path.$destFileName);
            }

            if($copy){
                $fa1 = array();
                $fa1['file_name'] = $media_id . '_' . $row['actual_file_name'];

                $whereCondition = "WHERE media_id = {$media_id}";
                $SQLUpdate      = $dbUtil->getUpdateSQLStringFromArray($fa1, 'media', $whereCondition);
                $resultUpdate   = $db->sql_query($SQLUpdate);
            }
        }
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $special_search  = $fn->getReqParam('special_search');
        $content_type    = $fn->getReqParam('content_type');
        $content_year  = $fn->getReqParam('content_year');
        $content_month = $fn->getReqParam('content_month');

        $secOptions    = '';
        $catOptions    = '';
        $subCatOptions = '';
        $countryFilter = '';
        $correspondent = '';
        $jurisdiction  = '';
        $year          = '';
        $month         = '';
        $site = '';

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        if ($cpCfg['cp.hasMultiSites'] == 1) {
            $site_id = $fn->getReqParam('site_id');

            $sqlSites = $fn->getDDSQL('common_site');

            $site = "
            <td>
                <select name='site_id'>
                    <option value=''>{$ln->gd('m.webBasic.content.lbl.site', 'Site')}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSites, $site_id)}
                </select>
            </td>
            ";
        }

        if ($cpCfg['m.webBasic.content.showCorrespondent'] == 1) {
            $correspondent_id = $fn->getReqParam('correspondent_id');

            $modCorres = getCPModuleObj('lawNews_correspondent');
            $sqlCorres = $modCorres->model->getCorrespondentSQL();

            $correspondent = "
            <td>
                <select name='correspondent_id'>
                    <option value=''>{$ln->gd('m.webBasic.content.lbl.correspondent', 'Correspondent')}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlCorres, $correspondent_id)}
                </select>
            </td>
            ";
        }

        if ($cpCfg['m.webBasic.content.showJurisdiction'] == 1) {
            $jurisdiction_id = $fn->getReqParam('jurisdiction_id');

            $sqlJurisdiction = $fn->getDDSQL('lawNews_jurisdiction');

            $jurisdiction = "
            <td>
                <select name='jurisdiction_id'>
                    <option value=''>{$ln->gd('m.webBasic.content.lbl.jurisdiction', 'Jurisdiction')}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlJurisdiction, $jurisdiction_id)}
                </select>
            </td>
            </tr>
            <tr>
            ";
        }

        if ($cpCfg['m.webBasic.content.showMonthYearFilter'] == 1) {

			$SQLYear= $fn->getContentYearSQL(array());
			$yearOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLYear, $content_year);

			$SQLMonth = getCPModuleObj('core_valuelist')->model
			        ->getValuelistSQL('months', array('useCode' => TRUE, 'orderBy' => 'code'));
			$monthOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLMonth, $content_month);

            $year = "
            <td>
                <select name='content_year'>
                    <option value=''>{$ln->gd('m.webBasic.content.lbl.year', 'Year')}</option>
                    {$yearOptions}
                </select>
            </td>
            ";

            $month = "
            <td>
                <select name='content_month'>
                    <option value=''>{$ln->gd('m.webBasic.content.lbl.month', 'Month')}</option>
                    {$monthOptions}
                </select>
            </td>
            ";
        }


        if ($cpCfg['m.webBasic.content.showCountry'] == 1) {
            $countryFilter = $fnModCountry->getCountryDropDown('search', '', 1);
        } else {
            $countryFilter = $fnModCountry->getCountryDropDown('search');
        }

        $modSec = getCPModuleObj('webBasic_section');
        $sqlSection = $modSec->model->getSectionSQL();

        if ($tv['section_id'] != "") {
            $modCat = getCPModuleObj('webBasic_category');
            $SQLCat = $modCat->model->getCategorySQL($tv['section_id']);

            $catOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLCat, $tv['category_id']);
        }

        if ($tv['category_id'] != '') {
            $modCat = getCPModuleObj('webBasic_subCategory');
            $SQLSubCat = $modCat->model->getSubCategorySQL($tv['category_id']);
            $subCatOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLSubCat, $tv['sub_category_id']);
        }

        $text = "
        <td>
            <select name='section_id'>
                <option value=''>{$ln->gd('m.webBasic.content.lbl.section', 'Section')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlSection, $tv['section_id'])}
            </select>
        </td>
        <td>
            <select name='category_id'>
                <option value=''>{$ln->gd('m.webBasic.content.lbl.category', 'Category')}</option>
                {$catOptions}
            </select>
        </td>
        <td class='sub_category_id'>
            <select name='sub_category_id' >
                <option value=''>{$ln->gd('m.webBasic.content.lbl.subCategory', 'Sub Category')}</option>
                {$subCatOptions}
            </select>
        </td>
        <td>
            <select name='content_type'>
                <option value=''>{$ln->gd('m.webBasic.content.lbl.contentType', 'Content Type')}</option>
                {$cpUtil->getDropDown1($cpCfg['m.webBasic.content.recordTypeArr'], $content_type)}
            </select>
        </td>
        {$correspondent}
        {$jurisdiction}
        {$site}
        <td>
            <select name='special_search'>
                <option value=''>{$ln->gd('m.webBasic.content.lbl.specialSearch', 'Special Search')}</option>
                {$cpUtil->getDropDown1($cpCfg['m.webBasic.content.specialSearchArr'], $special_search)}
            </select>
        </td>
        {$month}
        {$year}
        {$countryFilter}
        ";

        return $text;
    }

    /**
     * Adding help button form in the content list. Used in USS Products (ARIF)
     */
    function getHelpContentTask() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $module_name = $fn->getReqParam('module_name');

		$SQL = "
		SELECT c.title
		      ,c.description
		FROM content c
		LEFT JOIN (section s) ON (c.section_id = s.section_id)
		WHERE c.published = 1
		  AND s.section_type = '{$module_name}'
		";
        $result = $db->sql_query($SQL);

		$text = '';
        $i = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $text .= "
		    <div class='helpContentTaskForm'>
		    	  <div class='toggle'></div>
		       <div class='helpContentTask'>
		    		<strong><div class='contentTitle'>
		    			{$i}. {$row['title']}
		    		</div></strong>
		            <div class ='contentDescription'>
		    			{$row['description']}
		            </div>
		        </div>
	          </div>
            ";
            $i++;
        }
        return $text;
    }

    /**
     * Adding GET STARTED BUTTON form in the content list. Used in USS Products (THAMIM)
     */
    function getStartedContentTask() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

		$SQL = "
		SELECT c.title
		      ,c.description
		FROM content c
		WHERE c.published = 1
		AND c.content_type = 'Get Started'
		ORDER BY c.sort_order
		";
        $result = $db->sql_query($SQL);

		$text = '';
        $i = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $text .= "
		    <div class=startedContentTaskForm'>
		    	  <div class='toggle'></div>
		       <div class='startedContentTask'>
		    		<strong><div class='contentTitle'>
		    			{$i}. {$row['title']}
		    		</div></strong>
		            <div class ='contentDescription'>
		    			{$row['description']}
		            </div>
		        </div>
	          </div>
            ";
            $i++;
        }
        return $text;
    }

}