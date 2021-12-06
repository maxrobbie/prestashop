<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
class Search extends SearchCore
{
    /*
    * module: modulereference
    * date: 2021-10-22 04:09:03
    * version: 1.0.0
    */
    public static function find(
        $id_lang,
        $expr,
        $page_number = 1,
        $page_size = 1,
        $order_by = 'position',
        $order_way = 'desc',
        $ajax = false,
        $use_cookie = true,
        Context $context = null
    ) {
        if (!$context) {
            $context = Context::getContext();
        }
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        if ($page_number < 1) {
            $page_number = 1;
        }
        if ($page_size < 1) {
            $page_size = 1;
        }
        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            return false;
        }
        $scoreArray = [];
        $fuzzyLoop = 0;
        $eligibleProducts2 = null;
        $words = Search::extractKeyWords($expr, $id_lang, false, $context->language->iso_code);
        $fuzzyMaxLoop = (int) Configuration::get('PS_SEARCH_FUZZY_MAX_LOOP');
        $psFuzzySearch = (int) Configuration::get('PS_SEARCH_FUZZY');
        $psSearchMinWordLength = (int) Configuration::get('PS_SEARCH_MINWORDLEN');
        foreach ($words as $key => $word) {
            if (empty($word) || strlen($word) < $psSearchMinWordLength) {
                unset($words[$key]);
                continue;
            }
            $sql_param_search = self::getSearchParamFromWord($word);
            $sql = 'SELECT DISTINCT si.id_product ' .
                 'FROM ' . _DB_PREFIX_ . 'search_word sw ' .
                 'LEFT JOIN ' . _DB_PREFIX_ . 'search_index si ON sw.id_word = si.id_word ' .
                 'LEFT JOIN ' . _DB_PREFIX_ . 'product_shop product_shop ON (product_shop.`id_product` = si.`id_product`) ' .
                 'WHERE sw.id_lang = ' . (int) $id_lang . ' ' .
                 'AND sw.id_shop = ' . $context->shop->id . ' ' .
                 'AND product_shop.`active` = 1 ' .
                 'AND product_shop.`visibility` IN ("both", "search") ' .
                 'AND product_shop.indexed = 1 ' .
                 'AND sw.word LIKE ';
            while (!($result = $db->executeS($sql . "'" . $sql_param_search . "';", true, false))) {
                if (!$psFuzzySearch
                    || $fuzzyLoop++ > $fuzzyMaxLoop
                    || !($sql_param_search = static::findClosestWeightestWord($context, $word))
                ) {
                    break;
                }
            }
            if (!$result) {
                unset($words[$key]);
                continue;
            }
            $productIds = array_column($result, 'id_product');
            if ($eligibleProducts2 === null) {
                $eligibleProducts2 = $productIds;
            } else {
                $eligibleProducts2 = array_intersect($eligibleProducts2, $productIds);
            }
            $scoreArray[] = 'sw.word LIKE \'' . $sql_param_search . '\'';
        }
        if (!count($words)) {
            return $ajax ? [] : ['total' => 0, 'result' => []];
        }
        $sqlScore = '';
        if (!empty($scoreArray) && is_array($scoreArray)) {
            $sqlScore = ',( ' .
                'SELECT SUM(weight) ' .
                'FROM ' . _DB_PREFIX_ . 'search_word sw ' .
                'LEFT JOIN ' . _DB_PREFIX_ . 'search_index si ON sw.id_word = si.id_word ' .
                'WHERE sw.id_lang = ' . (int) $id_lang . ' ' .
                'AND sw.id_shop = ' . $context->shop->id . ' ' .
                'AND si.id_product = p.id_product ' .
                'AND (' . implode(' OR ', $scoreArray) . ') ' .
                ') position';
        } 
        $sqlGroups = '';
        if (Group::isFeatureActive()) {
            $groups = FrontController::getCurrentCustomerGroups();
            $sqlGroups = 'AND cg.`id_group` ' . (count($groups) ? 'IN (' . implode(',', $groups) . ')' : '=' . (int) Group::getCurrent()->id);
        }
        $results = $db->executeS(
            'SELECT DISTINCT cp.`id_product` ' .
            'FROM `' . _DB_PREFIX_ . 'category_product` cp ' .
            (Group::isFeatureActive() ? 'INNER JOIN `' . _DB_PREFIX_ . 'category_group` cg ON cp.`id_category` = cg.`id_category`' : '') . ' ' .
            'INNER JOIN `' . _DB_PREFIX_ . 'category` c ON cp.`id_category` = c.`id_category` ' .
            'INNER JOIN `' . _DB_PREFIX_ . 'product` p ON cp.`id_product` = p.`id_product` ' .
            Shop::addSqlAssociation('product', 'p', false) . ' ' .
            'WHERE c.`active` = 1 ' .
            'AND product_shop.`active` = 1 ' .
            'AND product_shop.`visibility` IN ("both", "search") ' .
            'AND product_shop.indexed = 1 ' . $sqlGroups,
            true,
            false
        );
        $eligibleProducts = [];
        foreach ($results as $row) {
            $eligibleProducts[] = $row['id_product'];
        }
        $eligibleProducts = array_unique(array_intersect($eligibleProducts, array_unique($eligibleProducts2)));
        if (!count($eligibleProducts)) {
            return $ajax ? [] : ['total' => 0, 'result' => []];
        }
        $product_pool = '';
        foreach ($eligibleProducts as $id_product) {
            if ($id_product) {
                $product_pool .= (int) $id_product . ',';
            }
        }
        if (empty($product_pool)) {
            return $ajax ? [] : ['total' => 0, 'result' => []];
        }
        $product_pool = ((strpos($product_pool, ',') === false) ? (' = ' . (int) $product_pool . ' ') : (' IN (' . rtrim($product_pool, ',') . ') '));
        if ($ajax) {
            $sql = 'SELECT DISTINCT p.id_product, pl.name pname, cl.name cname,
						cl.link_rewrite crewrite, pl.link_rewrite prewrite ' . $sqlScore . '
					FROM ' . _DB_PREFIX_ . 'product p
					INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (
						p.`id_product` = pl.`id_product`
						AND pl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . '
					)
					' . Shop::addSqlAssociation('product', 'p') . '
					INNER JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (
						product_shop.`id_category_default` = cl.`id_category`
						AND cl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('cl') . '
					)
					WHERE p.`id_product` ' . $product_pool . '
					ORDER BY position DESC LIMIT 10';
            return $db->executeS($sql, true, false);
        }
        if (strpos($order_by, '.') > 0) {
            $order_by = explode('.', $order_by);
            $order_by = pSQL($order_by[0]) . '.`' . pSQL($order_by[1]) . '`';
        }
        $alias = '';
        if ($order_by == 'price') {
            $alias = 'product_shop.';
        } elseif (in_array($order_by, ['date_upd', 'date_add'])) {
            $alias = 'p.';
        }
		$sqlJonAN = '';
        $sqlWhereExt = '';
		if (!empty($scoreArray) && is_array($scoreArray) && ($_GET['controller'] == 'refer')) {
            $sqlWhereExt = "\n" . ' pn.article_number LIKE \'%' . $_GET['s'] . '%\'';
            $sqlJonAN = "\n".'LEFT JOIN ' . _DB_PREFIX_ . 'product_numbers pn ON p.id_product = pn.id_product';
        }else{
			$sqlWhereExt = 'p.`id_product` ' . $product_pool;
		} 
        $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity,
				pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`name`,
			 image_shop.`id_image` id_image, il.`legend`, m.`name` manufacturer_name ' . $sqlScore . ',
				DATEDIFF(
					p.`date_add`,
					DATE_SUB(
						"' . date('Y-m-d') . ' 00:00:00",
						INTERVAL ' . (Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20) . ' DAY
					)
				) > 0 new' . (Combination::isFeatureActive() ? ', product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity, IFNULL(product_attribute_shop.`id_product_attribute`,0) id_product_attribute' : '') . '
				FROM ' . _DB_PREFIX_ . 'product p
				' . Shop::addSqlAssociation('product', 'p') . '
				INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (
					p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . '
				)
				' . (Combination::isFeatureActive() ? 'LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` product_attribute_shop FORCE INDEX (id_product)
				    ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop=' . (int) $context->shop->id . ')' : '') . '
				' . Product::sqlStock('p', 0) . '
				LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m FORCE INDEX (PRIMARY)
				    ON m.`id_manufacturer` = p.`id_manufacturer`
				LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop FORCE INDEX (id_product)
					ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int) $context->shop->id . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $id_lang . ')' . $sqlJonAN.'
				WHERE ' . $sqlWhereExt. '
				GROUP BY product_shop.id_product
				' . ($order_by ? 'ORDER BY  ' . $alias . $order_by : '') . ($order_way ? ' ' . $order_way : '') . '
				LIMIT ' . (int) (($page_number - 1) * $page_size) . ',' . (int) $page_size;
        $result = $db->executeS($sql, true, false);
        $sql = 'SELECT COUNT(*)
				FROM ' . _DB_PREFIX_ . 'product p
				' . Shop::addSqlAssociation('product', 'p') . '
				INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (
					p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . '
				)
				LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
				WHERE p.`id_product` ' . $product_pool;
        $total = $db->getValue($sql, false);
        if (!$result) {
            $result_properties = false;
        } else {
            $result_properties = Product::getProductsProperties((int) $id_lang, $result);
        }
        return ['total' => $total, 'result' => $result_properties];
    }
}