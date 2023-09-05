<?php

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class JbRelatedProductsRelationShip
{

    private $context;

    private $module;

    public $limit;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('jbrelatedproducts');
        $this->context = Context::getContext();
        $this->limit = (int)Configuration::get($this->module->prefix . 'PRODUCTS_QUANTITY');
    }

    /**
     * Retrieve related product IDs based on a given product ID.
     *
     * This method queries the database to find distinct related product IDs based on the provided product ID.
     * It searches for related products where the provided product ID is found either in
     * the `id_product1` or `id_product2` column of the database table.
     * The result is limited to a specified number of entities.
     *
     * @param int $idProduct The product ID for which related products are to be retrieved.
     * @return array|false An array of related product IDs or `false` if an error occurs.
     *
     * DISCLAIMER: Description was written with ChatGPT
     */
    public function getRelatedProducts(int $idProduct)
    {
        $sql = '
        SELECT 
            DISTINCT CASE
                WHEN id_product1 = "' . (int)$idProduct . '" 
                THEN id_product2
                ELSE id_product1
            END AS id_product
        FROM `' . _DB_PREFIX_ . 'jb_relprod_relationships` 
        WHERE 
            `id_product1` =  "' . (int)$idProduct . '" OR 
            `id_product2` =  "' . (int)$idProduct . '"';

        if ($this->limit) {
            $sql .= ' LIMIT ' . (int)$this->limit;
        }

        return DB::getInstance()->executeS($sql);
    }


    /**
     * Set related products for a given product ID.
     *
     * This method establishes relationships between a given product and a list of related products.
     * It takes a product ID and an array of related product IDs, then inserts corresponding entries
     * into the database table for maintaining product relationships.
     *
     * @param int $idProduct The product ID for which related products are being set.
     * @param int|int[] $relatedProducts An array of related product IDs or a single related product ID.
     * @return void
     *
     * DISCLAIMER: Description was written with ChatGPT
     */
    public static function setRelatedProducts(int $idProduct, $relatedProducts): void
    {
        if (empty($relatedProducts)) {
            return;
        }
        if (!is_array($relatedProducts)) {
            $relatedProducts = [$relatedProducts];
        }
        $data = [];
        $context = Context::getContext();
        $translator = $context->getTranslator();
        foreach ($relatedProducts as $relatedProduct) {
            $data[] = [
                'id_product1' => (int)$idProduct,
                'id_product2' => (int)$relatedProduct,
            ];
        }
        try {
            DB::getInstance()->insert('jb_relprod_relationships', $data);
        } catch (Exception $e) {
            JbRelatedProductsLog::logError(
                $translator->trans('Unable to set related products. Get error "%s"',
                    ['%s' => $e->getMessage()],
                    'Modules.JbRelatedProducts.RelationShip'
                ),
                $idProduct
            );
            return;
        }
        JbRelatedProductsLog::logInfo(
            $translator->trans('Assigned new related products',
                [],
                'Modules.JbRelatedProducts.RelationShip'
            ),
            $idProduct
        );
    }

    public static function removeRelationShip($idProduct)
    {
        $context = Context::getContext();
        $translator = $context->getTranslator();

        $dbInstance = DB::getInstance();
        if ($dbInstance->execute('
            DELETE 
            FROM `' . _DB_PREFIX_ . 'jb_relprod_relationships`
            WHERE 
                `id_product1` = ' . (int)$idProduct . ' OR 
                id_product2 = ' . (int)$idProduct . ';'
        )) {
            JbRelatedProductsLog::logSuccess(
                $translator->trans('Deleted relationship with Product ID:%s',
                    ['%s' => $idProduct],
                    'Modules.JbRelatedProducts.RelationShip'
                ), $idProduct);
            return true;
        } else {
            JbRelatedProductsLog::logError(
                $translator->trans('Unable to remove products %d relationship. Get error "%s"',
                    [
                        '%d' => $idProduct,
                        '%s' => $dbInstance->getMsgError()
                    ],
                    'Modules.JbRelatedProducts.RelationShip'
                ),
                $idProduct
            );
        }
    }


    public function getRelatedProductsBySettings($idProduct, $usedIds, $limit)
    {
        $sql = new DbQuery();
        $sql->select('p.`id_product`');
        $sql->from('product', 'p');
        $sql->join(Shop::addSqlAssociation('product', 'p'));
        $this->setResultLimit($sql, $limit);
        $this->setWhereCategory($sql, $idProduct);
        $this->setWhereDefaultCategory($sql, $idProduct);
        $this->setWhereManufacturer($sql, $idProduct);
        $this->setWhereSupplier($sql, $idProduct);
        $this->setWhereFeatures($sql, $idProduct);
        $sql->where('p.`id_product` NOT IN (' . implode(',', $usedIds) . ')');
        $sql->where('product_shop.`active` = "1"');
        $sql->where('product_shop.`available_for_order` = "1"');
        $sql->groupBy('p.`id_product`');

        return Db::getInstance()->executeS($sql);
    }


    private function setResultLimit(&$sql, $limit)
    {
        if ($limit) {
            $sql->limit($limit);
        }
    }


    private function setWhereCategory(&$sql, $idProduct)
    {

        $enable = Configuration::get($this->module->prefix . 'RELATION_CATEGORY');
        if (!$enable) {
            return;
        }

        $categories = Product::getProductCategories($idProduct);
        if (!empty($categories)) {
            $sql->join('JOIN `' . _DB_PREFIX_ . 'category_product` cp ON cp.`id_product` = p.`id_product`');
            $sql->where('cp.`id_category`' . ($enable == -1 ? 'NOT' : '') . ' in (' . implode(',', $categories) . ')');
        }
    }

    private function setWhereDefaultCategory(&$sql, $idProduct)
    {
        $enable = Configuration::get($this->module->prefix . 'RELATION_DEFAULT_CATEGORY');
        if (!$enable) {
            return;
        }

        $sql->join('JOIN `' . _DB_PREFIX_ . 'product` pc ON p.`id_category_default` ' . ($enable == -1 ? '!=' : '=') . ' pc.`id_category_default`');
        $sql->where('pc.`id_product` = ' . (int)$idProduct);

    }

    private function setWhereManufacturer(&$sql, $idProduct)
    {

        $enable = Configuration::get($this->module->prefix . 'RELATION_MANUFACTURER');
        if (!$enable) {
            return;
        }

        $sql->join('JOIN `' . _DB_PREFIX_ . 'product` pm ON p.`id_manufacturer` ' . ($enable == -1 ? '!=' : '=') . ' pm.`id_manufacturer`');
        $sql->where('pm.`id_product` = ' . (int)$idProduct . ' and pm.`id_manufacturer` != "0"');

    }

    private function setWhereSupplier(&$sql, $idProduct)
    {

        $enable = Configuration::get($this->module->prefix . 'RELATION_SUPPLIERS');
        if (!$enable) {
            return;
        }

        $sql->join('JOIN `' . _DB_PREFIX_ . 'product` psu ON p.`id_supplier` ' . ($enable == -1 ? '!=' : '=') . ' psu.`id_supplier`');
        $sql->where('psu.`id_product` = ' . (int)$idProduct . ' and psu.`id_supplier` != "0"');

    }

    private function setWhereFeatures(&$sql, $idProduct)
    {
        $enable = Configuration::get($this->module->prefix . 'RELATION_FEATURES');
        if (!$enable) {
            return;
        }

        $featureSql = new DbQuery();
        $featureSql->select('fp2.id_product');
        $featureSql->FROM('feature_product', 'fp1');
        $featureSql->join('JOIN `' . _DB_PREFIX_ . 'feature_product` fp2 ON fp2.`id_feature_value` ' . ($enable == -1 ? '!=' : '=') . ' fp1.`id_feature_value`');
        $featureSql->where('fp1.`id_product` = ' . (int)$idProduct);

        $sql->where('p.`id_product` in (' . $featureSql->__toString() . ')');

    }

    public function assembleProducts($products)
    {
        return $this->getAssembledProducts($products);
    }

    private function getAssembledProducts($products)
    {
        $presenter = new \PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );


        $products_for_template = [];
        $assembler = new ProductAssembler($this->context);
        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();


        foreach ($products as $rawProduct) {
            $products_for_template[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct),
                $this->context->language
            );
        }

        return $products_for_template;
    }

}