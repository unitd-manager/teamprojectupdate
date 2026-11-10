<?
class CP_Common_Lib_ArrayMasterLink
{
    var $linksArray     = array();
    var $actionButArrayLink = array();

    /**
     *
     */
    function __construct() {
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($tv['module'] != ''){
            $modObj = Zend_Registry::get('currentModule');
            $funcName = "setLinksArray";
            if (method_exists($modObj->fns, $funcName)) {
                $modObj->fns->$funcName($this);
            }
            foreach ($modulesArr[$tv['module']]['depModulesForJSS'] as $module){
                $modObj = getCPModuleObj($module);
                if (method_exists($modObj->fns, $funcName)) {
                    $modObj->fns->$funcName($this);
                }
            }
        }
    }

    /**
     *
     */
    function registerLinksArray($linkObj, $overrideArr = array()){
        foreach($overrideArr as $key => $value){
            $linkObj[$key] = $value;
        }

        $this->linksArray[$linkObj['name']] = $linkObj;

        return $linkObj;
    }

    /**
     *
     */
    function getLinksArrayObj($mainRoom, $linkRoom, $paramArr = array()) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $arr1['name'] = "{$mainRoom}#{$linkRoom}";

        $linkModNameInfo = explode('_', $linkRoom);

        $linkModFold = $linkModNameInfo[0];
        $linkModName = $linkModNameInfo[1];

        $arr1['classFileName']            = $linkRoom;
        $arr1['title']                    = ucfirst($linkModName);
        $arr1['listTitle']                = $arr1['title'] . ' - List';

        $arr1['listLimit']                = '50';
        $arr1['portalListLimit']          = '10';
        $arr1['keyField']                 = $linkModName . '_id';
        $arr1['keyFieldForLinking']       = '';
        $arr1['keyFieldForHistory']       = ''; // used when the main room & link room are the same eg: related products & this has to be set as related_product_id
        $arr1['recordTypeForHistory']     = ''; // used when the history table has 2 or more types of records eg: tag_history will have record_type = Blog / Content
        $arr1['moduleForHistory']         = ''; // eg: site_link has records by module name
        $arr1['linkMultiple']             = 1;

        //fieldsArr is a collection of all the fieldObjs
        $arr1['fieldsArr'] = array('xxxxxxxx');

        /**
        this is required when the main room key field name used in history table is different
        ex: blog#tags, in this tags_history table there is not field called blog_id
        but record_id is used instead, ref: function.php in web2/blog
        **/
        $arr1['mainRoomKeyFldNameInHistTbl'] = '';
        $arr1['linkRoomKeyFldNameInHistTbl'] = '';

        $arr1['linkRoomTableName']        = $linkModName;
        $arr1['historyTableName']         = '';
        $arr1['historyTableKeyField']     = '';
        $arr1['displayTitleFieldName']    = 'a.title';

        $arr1['showViewBtnInDetail']      = 0;
        $arr1['showLinkPanelInNew']       = 1;
        $arr1['showLinkPanelInDetail']    = 1;
        $arr1['showLinkPanelInEdit']      = 1;
        $arr1['hasPortalNew']             = 1;
        $arr1['hasPortalEdit']            = 0;
        $arr1['hasPortalDelete']          = 0;
        $arr1['hasPortalDetail']          = 0;
        $arr1['hasGridEdit']              = 1;
        $arr1['hasListBoxSort']           = 0;
        $arr1['fieldlabel']               = array();
        $arr1['fieldClassArray']          = array();
        $arr1['hasModalChoose']           = true;

        $arr1['linkingType']              = 'modal'; // modal or portal or grid or checkbox

        $arr1['portalSearchFunction']     = '';
        $arr1['showAnchorInLinkPortal']   = 1;
        $arr1['hasToggle']                = 0;
        $arr1['openExpanded']             = 1;

        $arr1['hiddenColumnPosArr']       = array();
        $arr1['openLinkWindowForPortal']  = 1;

        $arr1['portalDialogWidth']        = 600;
        $arr1['portalDialogHeight']       = 400;
        $arr1['portalDialogWidthInternal'] = 'auto';
        $arr1['hasChildren']              = false;
        $arr1['childLinkKey']             = '';
        $arr1['childFieldLabel']          = array();
        $arr1['childFieldClassArray']     = array();

        $arr1['addButtonText']            = 'Add';
        $arr1['childAddButtonText']       = 'Add Child';

        $arr1['chooseLinkValidateJsMethod'] = '';
        $arr1['editLinkItemValidateJsMethod'] = '';
        $arr1['beforeCloseFnName'] = '';

        /*
         * must be instance of getLinkAnchorObj
         * ex: $arr1['anchorFieldsArr'] = array('product_code' => $inst->getLinkAnchorObj('product_code', 'product_id'),
         *                                      'company_code' => $inst->getLinkAnchorObj('company_code', 'company_id')
         *                                )
         */
        $arr1['anchorFieldsArr'] = array();


        /* ex: array('stock'). We need choose the field "stock_sum" in the link SQL as for ex:
         SELECT pi.product_item_id
               ,pi.stock
               ,pi.xxx
               ,pi.yyy
               ,(SELECT SUM(stock) FROM product_item WHERE product_id = {$id}) AS stock_sum
         FROM product_item pi
         WHERE pi.product_id = {$id}
        */
        $arr1['summaryFieldsArray'] = array(); //ex: array('stock')
        $arr1['hideFieldsArray'] = array(); //we can use this array hide columns in the link SQL foundset

        //if true it will call the method get<<linkName>>HistoryCallback in the FnMod (ex: getEnquiryProductHistoryCallback)
        $arr1['hasHistoryCallback'] = false;

        $arr1['showAlternativeRowColor'] = true;

        //to show this link in the header panel of link
        //ex: array('url' => '#', 'title' => 'Edit Inventory', 'class' => 'editInventory')
        $arr1['linkHeaderHyperLink'] = null;


        if ($arr1['historyTableName'] != '' && $arr1['historyTableKeyField'] == '') {
            $arr1['historyTableKeyField'] = $arr1['historyTableName'] . '_id';
        }

        //values: textbox, dropdown, singleCheckbox, date, time
        //default: textbox
        $arr1['gridFieldTypeArray']    = array();
        $arr1['additionalFieldsArray'] = array();
        $arr1['additionalFieldsArrayDetail'] = array(); /** used in order#orderItem **/

        // implicitly set when we need to pass a deifferent fld id in place of the actual record id
        // ex: if you have a 3 table linking such as contact / interest / interest contact
        // if the linking is done using choose method but we need to edit the history record for any purpose
        // such as updating another field - we need to enable the protal edit feature and change the record id
        $arr1['recordIdFldName'] = '';
        $arr1['showRowSerialNo'] = true;
        $arr1['autoSaveGrid'] = 1;
        $arr1['surroundLinkPortalTblWithForm'] = true;
        $arr1['sortGridRecordsBy'] = '';

        foreach($paramArr as $key => $value){
            $arr1[$key] = $value;
        }

        return $arr1;
    }

    /**
     *
     */
    function getLinkAnchorObj($anchorFieldName, $idFieldName, $showIdField = false, $module = '', $exp = array()) {
        $fn = Zend_Registry::get('fn');
        $showLinkInEdit = $fn->getIssetParam($exp, 'showLinkInEdit', false);

        $arr1['anchorFieldName'] = $anchorFieldName;
        $arr1['idFieldName']     = $idFieldName;
        $arr1['showIdField']     = $showIdField;
        $arr1['room']            = $module;
        $arr1['showLinkInEdit']  = $showLinkInEdit;

        return $arr1;
    }

    /**
     *
     */
    function getFieldObj($fieldName, $exp = array()) {
        $arr1['fieldName'] = $fieldName;

        $arr1['align']             = isset($exp['align']) ? $exp['align'] : ''; //css class name for alignment
        $arr1['isGridFldEditable'] = isset($exp['isGridFldEditable']) ? $exp['isGridFldEditable'] : true;

        return $arr1;
    }
}
