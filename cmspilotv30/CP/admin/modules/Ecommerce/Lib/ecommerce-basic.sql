--
-- Table structure for table `basket`
--

CREATE TABLE IF NOT EXISTS `basket` (
  `basket_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `qty` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `unit_price` float(10,2) DEFAULT '0.00',
  `session_id` char(32) NOT NULL DEFAULT '',
  `module` varchar(50) NOT NULL DEFAULT '',
  `record_id` int(10) unsigned NOT NULL DEFAULT '0',
  `creation_date` datetime DEFAULT NULL,
  `modification_date` datetime DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`basket_id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE IF NOT EXISTS `order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_status` varchar(25) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `order_code` varchar(15) DEFAULT NULL,
  `payment_method` varchar(25) DEFAULT NULL,
  `record_type` varchar(25) NOT NULL DEFAULT 'Product',
  `module` varchar(50) DEFAULT NULL,
  `currency` char(3) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `shipping_first_name` varchar(100) DEFAULT NULL,
  `shipping_last_name` varchar(100) DEFAULT NULL,
  `shipping_email` varchar(100) DEFAULT NULL,
  `shipping_phone` varchar(25) DEFAULT NULL,
  `shipping_address1` varchar(100) DEFAULT NULL,
  `shipping_address2` varchar(100) DEFAULT NULL,
  `shipping_address_city` varchar(100) DEFAULT NULL,
  `shipping_address_area` varchar(100) DEFAULT NULL,
  `shipping_address_state` varchar(100) DEFAULT NULL,
  `shipping_address_country_code` varchar(2) DEFAULT NULL,
  `shipping_address_po_code` varchar(15) DEFAULT NULL,
  `shipping_charge` float(10,2) DEFAULT '0.00',
  `cust_first_name` varchar(100) DEFAULT NULL,
  `cust_last_name` varchar(100) DEFAULT NULL,
  `cust_email` varchar(100) DEFAULT NULL,
  `cust_phone` varchar(25) DEFAULT NULL,
  `cust_address1` varchar(100) DEFAULT NULL,
  `cust_address2` varchar(100) DEFAULT NULL,
  `cust_address_city` varchar(100) DEFAULT NULL,
  `cust_address_area` varchar(100) DEFAULT NULL,
  `cust_address_state` varchar(100) DEFAULT NULL,
  `cust_address_country_code` varchar(2) DEFAULT NULL,
  `cust_address_po_code` varchar(15) DEFAULT NULL,
  `memo` text,
  `creation_date` datetime DEFAULT NULL,
  `modification_date` datetime DEFAULT NULL,
  `flag` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE IF NOT EXISTS `order_item` (
  `order_item_id` int(10) NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `record_id` int(10) unsigned NOT NULL DEFAULT '0',
  `qty` int(10) unsigned NOT NULL DEFAULT '0',
  `unit_price` decimal(10,2) DEFAULT '0.00',
  `item_title` varchar(255) DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`order_item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE IF NOT EXISTS `product` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_code` varchar(50) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `sub_category_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `description_short` text,
  `chi_title` varchar(255) DEFAULT NULL,
  `chi_description` text,
  `chi_description_short` text,
  `qty_in_stock` int(11) DEFAULT '0',
  `price` float(10,2) DEFAULT NULL,
  `published` int(1) DEFAULT NULL,
  `member_only` int(1) DEFAULT NULL,
  `latest` int(1) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `meta_title` varchar(500) DEFAULT NULL,
  `meta_description` text,
  `meta_keyword` text,
  `creation_date` datetime DEFAULT NULL,
  `modification_date` datetime DEFAULT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
