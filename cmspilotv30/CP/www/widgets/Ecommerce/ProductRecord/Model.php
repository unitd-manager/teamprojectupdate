<?
class CP_Www_Widgets_Ecommerce_ProductRecord_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $c = &$this->controller;
        
        $joinTbls = '';
        if ($c->specialFilter == 'relatedProducts'){
            $joinTbls = "JOIN related_product rp ON (p.product_id = rp.product_id_rel)";
        }

        $SQL   = "
        SELECT p.*
              ,c.title AS category_title
              ,sc.title AS sub_category_title
        FROM product p
        {$joinTbls}
        LEFT JOIN (category c)      ON (p.category_id      = c.category_id)
        LEFT JOIN (sub_category sc) ON (p.sub_category_id  = sc.sub_category_id)
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $c = &$this->controller;
        
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'p';

        $specialFilter = $this->controller->specialFilter;

        $searchVar->sqlSearchVar[] = "p.published = 1";

        if ($specialFilter == 'Latest'){
            $searchVar->sqlSearchVar[] = "p.latest = 1";
        }

        if ($c->specialFilter == 'relatedProducts'){
            $searchVar->sqlSearchVar[] = "rp.product_id  = {$c->product_id}";
        }

        if ($specialFilter == 'Slideshow'){
            $searchVar->sqlSearchVar[] = "p.slideshow = 1";
        }

        if ($specialFilter == 'Favourite'){
            $searchVar->sqlSearchVar[] = "p.favourite = 1";
        }

        $searchVar->sortOrder = "p.sort_order";
    }

    //========================================================//
    function getDataArray() {
        $c = &$this->controller;
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpUrl = Zend_Registry::get('cpUrl');
        $media = Zend_Registry::get('media');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'ecommerce_productRecord');

        $arr = array();
        $counter = 0;

        foreach ($dataArray as $row){
            $tmpArr = &$arr[$counter];
            $tmpArr['product_id']    = $row['product_id'];
            $tmpArr['title']         = $ln->gfv($row, 'title');
            $tmpArr['url']           = $cpUrl->getUrlByRecord($row, 'product_id', array('secType' => $c->prodSecType));
            $tmpArr['desc_short']    = $ln->gfv($row, 'description_short');
            $tmpArr['description']   = $ln->gfv($row, 'description');
            $tmpArr['price']         = $row['price'];
            $tmpArr['qty_in_stock']  = $row['qty_in_stock'];
            $tmpArr['category_id']   = $row['category_id'];
            $tmpArr['sub_category_id']    = $row['sub_category_id'];
            $tmpArr['category_title']     = $row['category_title'];
            $tmpArr['sub_category_title'] = $row['sub_category_title'];

            if ($c->includeMediaRec){
                $tmpArr['mediaPicArray'] = $media->getMediaFilesArray('ecommerce_product', 'picture', $row['product_id']);
            }

            $counter++;
        }
        
        $this->dataArray = $arr;
        return $arr;
    }
}