<?
class CP_Common_Plugins_Common_Media_View extends CP_Common_Lib_PluginViewAbstract
{

    /**
     *
     */
    function getRightPanelMediaDisplay($displayTitle = '', $module = '', $recordType = '', $row = '', $exp = array()) {
        $tv            = Zend_Registry::get('tv');
        $ln            = Zend_Registry::get('ln');
        $fn            = Zend_Registry::get('fn');
        $modulesArr    = Zend_Registry::get('modulesArr');
        $cpCfg         = Zend_Registry::get('cpCfg');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');
        $mediaArray    = Zend_Registry::get('mediaArray');

        if (!isset($mediaArray[$module][$recordType])){
            $mediaArrayObj->setMediaArray($module);
        }
        //we need to REGET the mediaArray here since we are setting the mediaArray above
        $mediaArray = Zend_Registry::get('mediaArray');

        if ($tv['action'] == "search" ) {
            return;
        }

        $mediaType     = $mediaArray[$module][$recordType]['mediaType'];
        $openExpanded  = $mediaArray[$module][$recordType]['openExpanded'];
        $hasCrop       = $mediaArray[$module][$recordType]['hasCrop'];
        $hasZoomImage  = $mediaArray[$module][$recordType]['hasZoomImage'];
        $hasNew        = $mediaArray[$module][$recordType]['hasNew'];

        $keyField     = $modulesArr[$module]['keyField'];
        $keyFieldId   = ($row !='') ? $row[$keyField] : '';
        if ($hasCrop){
            CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jcrop-0.9.8'));
        }

        $rows = '';

        //-----------------------------------------------------------//
        if ($hasZoomImage && $mediaType == 'image'){
            $zoomPlugin = 'jqColorbox-' . $cpCfg['cp.jqColorBoxVersion'];
            CP_Common_Lib_Registry::arrayMerge('jssKeys', array($zoomPlugin));
        }
        $rows = $this->getMediaFilesDisplay($module, $recordType, $keyFieldId, $exp);

        $toggleText = '';

        if ($tv['action'] == 'detail' || $tv['action'] == 'edit') {
            $toggleText = "<div class='toggle'>&nbsp;</div>";
        }

        $url     = "{$cpCfg['cp.scopeRootAlias']}index.php?plugin=common_media" .
                   "&_spAction=selectMedia&room={$module}&recordType={$recordType}&id={$keyFieldId}" .
                   "&showHTML=0&lang={$tv['lang']}";
        $dispUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?plugin=common_media" .
                   "&_spAction=mediaFilesDisplay&room={$module}&recordType={$recordType}" .
                   "&id={$keyFieldId}&showHTML=0&lang={$tv['lang']}";

        $actionButtons = '';
        if  ($hasNew && ($tv['action'] == 'edit' || $tv['spAction'] == 'editPortal' || $tv['spAction'] == 'edit')) {
            $actionButtons = "<a class='btnSelectMedia' href='{$url}'>{$ln->gd('cp.lbl.add', 'Add')}</a>";
        }

                $actionButtons = "
        <div class='actBtns'>
            {$actionButtons}
        </div>
        ";

        $dragAndDropUpload = "";
        if ($cpCfg['cp.dragAndDropUpload'] == 'dragAndDrop' && $mediaType == 'image'){
            $actionButtons = "";
            $sessionID             = session_id();
            $formSuccessMsg        = $fn->getIssetParam($exp, 'formSuccessMsg', false);
            $queueSizeLimit        = $fn->getIssetParam($exp, 'queueSizeLimit', 5);
            $fileSizeLimit         = $fn->getIssetParam($exp, 'fileSizeLimit', $cpCfg['cp.maxUploadLimit']);
            $fileTypeExts          = $mediaArray[$module][$recordType]['fileTypeExts'];
            $thumbWidth            = $fn->getIssetParam($exp, 'thumbWidth', 200);
            $thumbHeight           = $fn->getIssetParam($exp, 'thumbHeight', 200);
            $thumbProgressBarWidth = $fn->getIssetParam($exp, 'thumbProgressBarWidth', '100%');
            $autoUploadProcess     = $fn->getIssetParam($exp, 'autoUploadProcess', false);

            $dragAndDropUpload = "
            <div class='container mt20'>
                <div id='uploadWrap_{$recordType}' class='file_upload dropzoneMainDiv mb10'>
                    <div class='dropzone'>
                        <div class='dz-message needsclick'>
                            <strong>Drop or click here to upload files</strong>
                        </div>
                    </div>
                    <!--<a class='btn btn-primary mt10 mb10 pull-right startUpload'>UPLOAD</a>-->
                </div>
            </div>

            <script>
                $(function() {
                    var exp = {
                         width: '{$thumbWidth}'
                        ,height: '{$thumbHeight}'
                        ,acceptedFiles: '{$fileTypeExts}'
                        ,progressBarWidth: '{$thumbProgressBarWidth}'
                        ,maxFileSize: '{$fileSizeLimit}'
                        ,autoProcessQueue: '{$autoUploadProcess}'
                        ,maxFiles: '{$queueSizeLimit}'
                        ,formSuccessMsg: '{$formSuccessMsg}'
                    }
                    cpp.common.media.setUploadDragDrop('{$module}', '{$recordType}', '{$keyFieldId}', '{$sessionID}', exp);
                });
            </script>
            ";
        }

        $text = "
        <div class='linkPortalWrapper' id='media__{$module}__{$recordType}'>
            <div expanded='{$openExpanded}' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>{$displayTitle}</div>
                    {$toggleText}
                </div>
            </div>
            <div>
                {$dragAndDropUpload}
                <div class='mediaFilesDisplayWrap' url='{$dispUrl}'>
                    {$rows}
                </div>
                {$actionButtons}
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getSelectMedia($module='', $recType='', $id='') {
        checkLoggedIn();

        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        if ($module == ''){
            $module  = $fn->getReqParam('room');
            $recType = $fn->getReqParam('recordType');
            $id      = $fn->getReqParam('id');
        }

        $exp = array('lang' => $tv['lang']);

        //-------------------------------------------------------//
        $text = "
        {$formObj->getUploadifyObj($module, $recType, $id, $exp)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEditMediaProperties() {
        if (CP_SCOPE == 'admin') {
            checkLoggedIn();
        }
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');

        $text = "";

        $media_id = $fn->getReqParam('media_id');

        //======================================================================//
        $SQL = "
        SELECT *
        FROM media
        WHERE media_id = '{$media_id}'
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0){
            return;
        }

        $row = $db->sql_fetchrow($result);
        $room_name = $row['room_name'];

        $this->model->setMediaArray($room_name);
        $mediaArray = Zend_Registry::get('mediaArray');

        $showColorDdInEdit = $mediaArray[$room_name][$row['record_type']]['showColorDdInEdit'];
        $showProductItemDdInEdit = $mediaArray[$row['room_name']][$row['record_type']]['showProductItemDdInEdit'];

        $lnArray = $cpCfg['cp.availableLanguages'];

        //*********************************************************//
        $caption = '';
        $extraFlds = '';
        $description = '';
        foreach ($lnArray as $key => $value) {
            $fieldName  = $ln->getFieldPrefix($key). "caption";
            $fieldValue = isset($row[$fieldName]) ? $row[$fieldName] : "";

            $lbl = ($key == 'eng') ? '' : "({$value})";
            $caption .= "
            {$formObj->getTBRow("Title {$lbl}", $fieldName, $fieldValue)}
            ";

            $internalLinkFldName  = $ln->getFieldPrefix($key). "internal_link";
            if ($dbUtil->getColumnExists('media', $internalLinkFldName)){
                $extraFlds .= $formObj->getTBRow("Internal Link {$lbl}", $internalLinkFldName, $row[$internalLinkFldName]);
            }

            $descriptionFldName  = $ln->getFieldPrefix($key). "description";
            if ($dbUtil->getColumnExists('media', $descriptionFldName)){
                $description .= $formObj->getTARow("Job Description {$lbl}", $descriptionFldName, $row[$descriptionFldName]);
            }
        }

        if ($dbUtil->getColumnExists('media', 'bg_color')){
            $extraFlds .= $formObj->getTBRow("Background Color", 'bg_color', $row['bg_color']);
        }

        if ($dbUtil->getColumnExists('media', 'video_link')){
            $extraFlds .= $formObj->getTBRow("Video Link", 'video_link', $row['video_link']);
        }

        if ($dbUtil->getColumnExists('media', 'css_style')){
            $extraFlds .= $formObj->getTBRow("CSS Style", 'css_style', $row['css_style']);
        }

        if ($showColorDdInEdit){
            $colorSQL = $fn->getDDSql('ecommerce_color');
            $extraFlds .= $formObj->getDDRowBySQL("Color", 'color_id', $colorSQL, $row['color_id']);
        }

        if ($showProductItemDdInEdit){
            $skuSQL = $fn->getSQL("
                SELECT product_item_id
                      ,sku_no
                FROM product_item"
                ,array(
                     'sku_no IS NOT NULL'
                    ,"product_id = '{$row['record_id']}'"
                )
                ,array(
                    'orderBy' => 'sku_no'
                )
            );

            $extraFlds .= $formObj->getDDRowBySQL("SKU", 'child_id', $skuSQL, $row['child_id']);
        }

        $formAction = "{$cpCfg['cp.scopeRootAlias']}index.php?plugin=common_media&_spAction=saveMediaProperties&showHTML=0";

        if (CP_SCOPE == 'www') {
            $text = "
            {$formObj->getTBRow('Caption ', 'caption', $row['caption'])}
            {$formObj->getTARow('Description ', 'description', $row['description'])}
            ";
        } else {
            $text = "
            {$caption}
            {$formObj->getTBRow('Alternate Text', 'alt_tag_data', $row['alt_tag_data'])}
            {$formObj->getTBRow('Sort Order', 'sort_order', $row['sort_order'])}
            {$formObj->getTBRow('Project', 'project', $row['project'])}
            {$formObj->getTBRow('Note', 'note', $row['note'])}
            {$extraFlds}
            {$description}
            ";
        }

        $text = "
        <form id='frmEditMediaProp' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$text}
                <input type='hidden' name='media_id' value='{$media_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    //==================================================================//
    function getEditImageCaption() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "";

        $media_id  = $fn->getReqParam('media_id');

        //======================================================================//
        $SQL     = "
        SELECT *
        FROM media
        WHERE media_id = '{$media_id}'
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $row = $db->sql_fetchrow($result);

        //*********************************************************//
        $text = "
        <form method='post' action='index.php?plugin=common_media&_spAction=saveImageCaption'>
            <table width='100%' cellpadding='0' cellspacing='0'>
                <tr class='header'>
                    <td class='header'>Edit Image Caption:</td>
                </tr>

                <tr>
                    <td>
                    <input class='inputBox' name='caption' type='text' value='{$row['caption']}' />
                    </td>
                </tr>

                <tr>
                    <td height='40'>
                    <input class='button' type='submit' value='Submit' />
                    </td>
                </tr>
            </table>
            <input type='hidden' name='media_id'   value='{$media_id}' />
        </form>
        ";
        return $text;
    }

    /**
     *
     */
    function getMediaFilesDisplay($module = '', $recordType = '', $id = '', $exp = array()){
        $db = Zend_Registry::get('db');
        $mediaArray = Zend_Registry::get('mediaArray');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');
        $expSQL = $fn->getIssetParam($exp, 'SQL');

        if (CP_SCOPE == 'admin') {
            checkLoggedIn();
        }

        if ($module == ''){
            $module     = $fn->getReqParam('room', '', true);
            $recordType = $fn->getReqParam('recordType', '', true);
            $id         = $fn->getReqParam('id', '', true);
        }
        $isMediaLangSpecific = $mediaArray[$module][$recordType]['isMediaLangSpecific'];
        $hasDelete           = $mediaArray[$module][$recordType]['hasDelete'];
        $hasEditProp         = $mediaArray[$module][$recordType]['hasEditProp'];
        $hasSave             = $mediaArray[$module][$recordType]['hasSave'];
        $forcedSQL           = $mediaArray[$module][$recordType]['SQL'];
        $showNormalImg       = $mediaArray[$module][$recordType]['showNormalImg'];

        $normalImgPicCls = $showNormalImg ? 'normal-img' : '';

        if ($id == '' && $expSQL == '' && $forcedSQL == ''){
            return;
        }

        if (!isset($mediaArray[$module][$recordType])){
            $mediaArrayObj->setMediaArray($module);
        }

        $lang = '';
        if ($isMediaLangSpecific) {
            $lang = $tv['lang'];
        }

        if ($expSQL != ''){
            $SQL = $expSQL;
        } else if ($forcedSQL != ''){
            $SQL = $forcedSQL;
        } else {
            $SQL = $this->model->getMediaSQL($module, $recordType, $id, $lang);
        }
        $result  = $db->sql_query($SQL);
        $list = '';

        $mediaArr1 = &$mediaArray[$module][$recordType];

        $mediaType = $mediaArr1['mediaType'];
        $showFullFileRef = $mediaArr1['showFullFileRef'];

        $folderZoom = $mediaArr1['largeFolderAlias'];
        if (!$mediaArr1['resize']) {
            $folderZoom = $mediaArr1['normalFolderAlias'];
        }
        while ($row = $db->sql_fetchrow($result)){
            $hasMediaUrl = isset($row['media_url']) && $row['media_url'] != '' ? true : false;
            $caption = $ln->gfv($row, 'caption', '0');

            if ($showFullFileRef){
                $folderLarge = $mediaArr1['largeFolderAlias'];
                $fileDisplay = $folderLarge . $row['file_name'];

            } else {
                $fileDisplay = ($caption != '') ? $caption : $row['actual_file_name'];
            }

            $saveUrl = '';
            $filePath = '';
            if ($hasMediaUrl) {
                $saveUrl = $row['media_url'];
            } else {
                $saveUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?plugin=common_media&_spAction=saveMedia&room={$module}" .
                           "&recordType={$recordType}&media_id={$row['media_id']}&showHTML=0";
            }
            $editUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?plugin=common_media&_spAction=editMediaProperties" .
                       "&media_id={$row['media_id']}&id={$id}&showHTML=0";
            $ext = substr($row['file_name'], strrpos($row['file_name'], '.') + 1);

            if ($row['media_type'] == 'image'){
                $folder = '';
                if ($mediaArr1['showNormalImg']) {
                    $folder = $mediaArr1['normalFolderAlias'];
                } else if ($mediaArr1['resize']) {
                    $folder = $mediaArr1['thumbFolderAlias'];
                } else {
                    $folder = $mediaArr1['normalFolderAlias'];
                }

                if ($hasMediaUrl) {
                    $filePath = $row['media_url'];
                } else {
                    $filePath = '/' . $folder . $row['file_name'];
                }
                $picture = "<a href='{$saveUrl}'><img src='{$filePath}' /></a>";
                if ($mediaArr1['hasZoomImage']) {
                    if ($hasMediaUrl) {
                        $filePath = $filePathImg = $row['media_url'];
                    } else {
                        $filePath = $folderZoom . $row['file_name'];
                        $filePathImg = $folder . $row['file_name'];
                    }
                    $title = htmlentities($row['file_name']);
                    $picture = "<a rel='cpZoomGallery' title='{$title}' href='{$filePath}'><img src='{$filePathImg}' /></a>";
                }
                if ($mediaArr1['hasCrop']) {
                    $croppedFilePath = $mediaArr1['croppedFolder'] . $row['file_name'];

                    if (file_exists($croppedFilePath)){
                        $picture .= "&nbsp;<img title='Cropped image' src='{$croppedFilePath}' />";
                    }
                }

            } else {
                $picture = "
                <a class='extIcon {$ext}' href='{$saveUrl}'>
                    <span class='hid'>+</span>
                </a>
                ";
            }

            $actBtns = '';
            $cropBtnText = '';
            $cropClass = '';

            if ($mediaArr1['hasCrop']) {
                $cropInfo  = $mediaArr1['cropInfo'];
                $cropWidth  = $cropInfo['width'];
                $cropHeight = $cropInfo['height'];
                $cropUrl = "index.php?plugin=common_media&_spAction=cropMediaForm&media_id={$row['media_id']}" .
                           "&id={$id}&showHTML=0";
                $cropClass = 'crop';
                $cropBtnText = "
                    <a href='javascript:void(0);' link='{$cropUrl}' id='{$row['media_id']}' title='Crop'
                       cropWidth='{$cropWidth}'
                       cropHeight='{$cropHeight}'
                       class='cropItem cropMedia'>
                        <span class='hid'>-</span>
                    </a>
                ";
            }

            if ($mediaType == 'image' && $hasSave){
                $actBtns .= "
                <a href='{$saveUrl}'
                   title='Download Image' class='saveItem'>
                    <span class='hid'>-</span>
                </a>
                ";
            }
            if ($tv['action'] != 'detail'){

                $delText = '';
                $editText = '';

                if ($hasDelete){
                    $delText = "
                    <a href='javascript:void(0);' id='{$row['media_id']}' title='Delete'
                       class='removeItem removeMedia'>
                        <span class='hid'>+</span>
                    </a>
                    ";
                }

                if ($hasEditProp){
                    $editText = "
                    <a href='javascript:void(0);' link='{$editUrl}' id='{$row['media_id']}'
                       title='Edit Properties' class='editItem editMedia'>
                        <span class='hid'>-</span>
                    </a>
                    ";
                }

                $actBtns .= "
                {$editText}
                {$delText}
                {$cropBtnText}
                ";
            }

            $list .= "
            <tr class='{$normalImgPicCls}'>
                <td class='pic {$cropClass}'>{$picture}</td>
           <td class='caption'>  <table>
                    <tr>
                        <td><h7>{$row['actual_file_name']}</h7></td>
                     
                    </tr>
                </table>
                <table border='1'>
                    <tr>
                        <td><h7>{$row['caption']}</h7></td>
                        <td>{$row['description']}</td>
                        <td>{$row['project']}</td>
                        <td>{$row['note']}</td>
                    </tr>
                </table>
            </td>
                <td class='icons'>
                    {$actBtns}
                </td>
            </tr>
            ";
        }

        $text = "";

        if ($list != ""){
            $text = "
            <table class='mediaFilesDisplay'>
                {$list}
            </table>
            ";
        } else {
            $text = "
            <div class='emptyPanel'>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getMediaFilesDisplayThin($module = "", $recordType = "", $id = ""){
        $db = Zend_Registry::get('db');
        $mediaArray = Zend_Registry::get('mediaArray');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');

        if ($module == ""){
            $module     = $fn->getReqParam('room', '', true);
            $recordType = $fn->getReqParam('recordType', '', true);
            $id         = $fn->getReqParam('id', '', true);
        }

        if ($id == ""){
            return;
        }

        if (!isset($mediaArray[$module][$recordType])){
            $mediaArrayObj->setMediaArray($module);
        }

        $SQL    = $this->model->getMediaSQL($module, $recordType, $id, $tv['lang']);
        $result = $db->sql_query($SQL);
        $rows   = '';

        while ($row = $db->sql_fetchrow($result)){
            $caption     = $ln->gfv($row, "caption", "0");
            $fileDisplay = ($caption != "") ? $caption : $row['actual_file_name'];

            $saveUrl = "{$cpCfg['cp.scopeRootAlias']}index.php?plugin=common_media&_spAction=saveMedia&room={$module}&recordType={$recordType}&media_id={$row['media_id']}&showHTML=0";
            $ext = substr($row['file_name'], strrpos($row['file_name'], ".") + 1);

            $rows .= "
            <li>
                <a class='extIconS {$ext}' href='{$saveUrl}'>{$fileDisplay}</a>
            </li>
            ";
        }

        $text = "";

        if ($rows != ''){
            $text = "
            <ul class='mediaFilesDisplayThin noDefault'>
                {$rows}
            </ul>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getMediaPicture($module, $recordType, $id, $extraParam = array()){
        $this->model->setMediaArray($module, $recordType);

        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');
        $mediaArray = Zend_Registry::get('mediaArray');

        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!isset($mediaArray[$module][$recordType])){
            $mediaArrayObj->setMediaArray($module);
        }

        if ($id == "") return;

        $exp = &$extraParam;
        $style              = isset($exp['style'])              ? $exp['style']              : '';
        $imgStyle           = isset($exp['imgStyle'])           ? $exp['imgStyle']           : '';
        $linkStyle          = isset($exp['linkStyle'])          ? $exp['linkStyle']          : '';
        $folder             = isset($exp['folder'])             ? $exp['folder']             : 'normal';
        $cropAltfolder      = isset($exp['cropAltfolder'])      ? $exp['cropAltfolder']      : 'thumbFolder';

        $zoomFolder         = isset($exp['zoomFolder'])         ? $exp['zoomFolder']         : '';
        $zoomImage          = isset($exp['zoomImage'])          ? $exp['zoomImage']          : 0;
        $zoomPlugin         = isset($exp['zoomPlugin'])         ? $exp['zoomPlugin']         : 'jqColorbox-' . $cpCfg['cp.jqColorBoxVersion'];
        $zoomGroup          = isset($exp['zoomGroup'])          ? $exp['zoomGroup']          : 'cpZoomGallery';
        $zoomClass          = isset($exp['zoomClass'])          ? $exp['zoomClass']          : 'cpZoom';
        $enableGal          = isset($exp['enableGal'])          ? $exp['enableGal']          : true;
        $returnFileNameOnly = isset($exp['returnFileNameOnly']) ? $exp['returnFileNameOnly'] : false;

        $showCaption        = isset($exp['showCaption'])  ? $exp['showCaption']              : 1;
        $linkCaption        = isset($exp['linkCaption'])  ? $exp['linkCaption']              : 0;
        $imageRefId         = isset($exp['imageRefId'])   ? " id='{$exp['imageRefId']}'"     : "";
        $imageLinkId        = isset($exp['imageLinkId'])  ? " id='{$exp['imageLinkId']}'"    : "";
        $url                = isset($exp['url'])          ? $exp['url']                      : '';
        $target             = isset($exp['target'])       ? " target='{$exp['target']}'"     : "";
        $imgAltText         = isset($exp['imgAltText'])   ? $exp['imgAltText']               : '';
        $defaultImg         = false;
        $imgStyle           = ($imgStyle  != "") ? " class='{$imgStyle}'"  : "";
        $linkStyle          = ($linkStyle != "") ? " class='{$linkStyle}'" : "";
        $limit              = isset($exp['limit']) ? $exp['limit'] : "";
        $appendSiteUrl      = isset($exp['appendSiteUrl'])? $exp['appendSiteUrl']            : 0;
        $lnForced           = isset($exp['langForced'])   ? $exp['langForced']               : '';
        $childId            = isset($exp['childId']) ? $exp['childId'] : '';
        $showDeleteIcon     = $fn->getIssetParam($exp, 'showDeleteIcon', false);

        if ($zoomImage == 1){
            CP_Common_Lib_Registry::arrayMerge('jssKeys', array($zoomPlugin));
        }

        if (isset($exp['defaultImg'])) {
            $defaultImg = $exp['defaultImg'];
        } else {
            if (isset($mediaArray[$module][$recordType]['defaultImg'])) {
                $defaultImg = true;
            }
        }

        $mediaArr1 = &$mediaArray[$module][$recordType];

        if (!isset($mediaArr1)){
            return '$mediaArray->' . $module . '->' . $recordType . ' does not exist in media array.php';
        }

        $text = '';

        $exp = array(
             'limit' => $limit
            ,'childId' => $childId
        );

        $SQL = $this->model->getMediaSQL($module, $recordType, $id, $tv['lang'], $exp);
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        if ($numRows == 0 && $tv['lang'] != 'eng' && $lnForced == ''){
            $SQL    = $this->model->getMediaSQL($module, $recordType, $id, 'eng', $exp);
            $result = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
        }

        if ($childId != '' && $numRows == 0){
            $exp['childId'] = '';
            $SQL = $this->model->getMediaSQL($module, $recordType, $id, $tv['lang'], $exp);
            $result = $db->sql_query($SQL);
            $numRows  = $db->sql_numrows($result);
        }

        if ($numRows > 0) { //*** if 1
            while ($row  = $db->sql_fetchrow($result) ){
                $externalLink  = "";
                $module = $row['room_name'];
                if ($folder == 'normal'){
                   if (array_key_exists('normalFolder', $mediaArr1)) {
                      $fileName = $mediaArr1['normalFolderAlias'] . $row['file_name'];
                   } else {
                      $fileName = $mediaArr1['thumbFolderAlias']  . $row['file_name'];
                   }

                } else if ($folder == 'medium'){
                   if (array_key_exists('mediumFolder', $mediaArr1)) {
                      $fileName = $mediaArr1['mediumFolderAlias'] . $row['file_name'];
                   } else {
                      $fileName = $mediaArr1['thumbFolderAlias'] . $row['file_name'];
                   }

                } else if ($folder == 'large'){
                   if (array_key_exists('largeFolder', $mediaArr1)) {
                      $fileName = $mediaArr1['largeFolderAlias'] . $row['file_name'];
                   } else {
                      $fileName = $mediaArr1['thumbFolderAlias'] . $row['file_name'];
                   }

                } else if ($folder == 'cropped'){

                    $fileName = '';

                    if (array_key_exists('croppedFolder', $mediaArr1)){
                        $fileName = $mediaArr1['croppedFolderAlias'] . $row['file_name'];
                    }

                    if (!file_exists($mediaArr1['croppedFolder'] . $row['file_name']) ){
                        $fileName = $mediaArr1[$cropAltfolder] . $row['file_name'];
                    }

                } else {
                   $fileName = $mediaArr1['thumbFolderAlias'] . $row['file_name'];
                }

                if($returnFileNameOnly){
                    return $fileName;
                }

                $externalLink  = $row['external_link'];
                $internalLink  = isset($row['internal_link']) ? $fn->replaceUrlVars($row['internal_link']) : '';

                if ($appendSiteUrl == 1){
                   $fileName  = "{$cpCfg['cp.siteUrlNoSlash']}{$fileName}";
                }

                $altText = ($row['alt_tag_data'] != "") ? $row['alt_tag_data'] : $imgAltText;

                $imgSrc = "<img src='{$fileName}' {$imageRefId} alt='{$row['alt_tag_data']}' border='0' {$imgStyle} usemap='#imageMapImage' />";

                $textTemp = "";

                $caption = $ln->gfv($row, "caption");

                if ($target == '' && $externalLink != ''){
                    $target = " target='_blank'";
                }

                if ($internalLink != ""){
                   $textTemp = "<a href='{$internalLink}'{$target}>{$imgSrc}</a>";
                } else if ($externalLink != ""){
                   $textTemp = "<a href='{$externalLink}'{$target}>{$imgSrc}</a>";
                } else if ($url != ""){
                   $textTemp = "<a href='{$url}'{$imageLinkId} {$linkStyle}{$target}>{$imgSrc}</a>";

                } else if ($zoomImage == 1){
                    $zoomFolder = ($zoomFolder == "normal") ? $mediaArr1["normalFolder"] : $mediaArr1["largeFolder"];
                    $fileLarge  = $zoomFolder . $row['file_name'];

                    $zoomGrp = ($enableGal) ? " rel='{$zoomGroup}'" : '';
                    $zoomCls = (!$enableGal) ? " class='{$zoomClass}'" : '';

                    $textTemp   = "<a href='/{$fileLarge}' title='{$caption}' {$imageLinkId}{$zoomCls}{$zoomGrp}>{$imgSrc}</a>";
                } else {
                    $textTemp = $imgSrc;
                }

                if ($caption != "" && $showCaption == 1){
                    $urlCaption = ($externalLink != "") ? $externalLink : $url;
                    $caption = ($linkCaption == 1 && $urlCaption != "" ) ? "<a href='{$urlCaption}'>{$caption}</a>" : $caption;
                    $textTemp .= "<div class='imageCaption'>{$caption}</div>";
                }

                if ($showDeleteIcon){
                    $textTemp .= "
                    <a href='javascript:void(0);' style='float:right;' id='{$row['media_id']}' title='Delete'
                       class='removeItem removeMedia'>
                        <span class='hid'>+</span>
                    </a>
                    ";
                }

                if ($style != ""){
                    $text .= "<div class='{$style}'>{$textTemp}</div>";
                } else {
                    $text .= "{$textTemp}";
                }
            }
        } else {
            if ($defaultImg){
                $imgSrc  = "<img src='{$mediaArr1['defaultImg']}' {$imageRefId} " . "alt='default image' title='default image' />";

                if ($url != ""){
                    $textTemp = "<a href='{$url}' {$target}>{$imgSrc}</a>";
                } else {
                    $textTemp = $imgSrc;
                }

                if ($style != ""){
                   $text .= "<div class='{$style}'>{$textTemp}</div>";
                } else {
                   $text .= "{$textTemp}";
                }
            }
        }

        return $text;
    }

    /**
     *
     */
    function getCropMediaForm() {
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');
        $mediaArray = Zend_Registry::get('mediaArray');

        $fn = Zend_Registry::get('fn');

        if (CP_SCOPE == 'admin') {
            checkLoggedIn();
        }

        $text = "";

        $media_id = $fn->getReqParam('media_id');

        $row = $fn->getRecordRowByID('media', 'media_id', $media_id);
        $module     = $row['room_name'];
        $recordType = $row['record_type'];

        if (!isset($mediaArray[$module][$recordType])){
            $mediaArrayObj->setMediaArray($module);
        }
        $mediaArr = $mediaArrayObj->mediaArray[$module][$recordType];

        $formAction = "index.php?plugin=common_media&_spAction=saveCroppedImage&showHTML=0";
        $fileName = $mediaArr['largeFolder'] . $row['file_name'];

        $text = "
        <form id='frmCropMedia' class='yform columnar' method='post' action='{$formAction}'>
            <img src='{$fileName}' id='cropbox' />
            <input type='hidden' name='media_id' value='{$media_id}' />
			<input type='hidden' id='cropX' name='cropX' />
			<input type='hidden' id='cropY' name='cropY' />
			<input type='hidden' id='cropW' name='cropW' />
			<input type='hidden' id='cropH' name='cropH' />
        </form>
        ";

        return $text;
    }

}
