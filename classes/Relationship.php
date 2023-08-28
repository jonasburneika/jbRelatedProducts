<?php

class JbRelatedProductsRelationShip
{

    /**
     * Retrieve related product IDs based on a given product ID.
     *
     * This method queries the database to find distinct related product IDs based on the provided product ID.
     * It searches for related products where the provided product ID is found either in
     * the `id_product1` or `id_product2` column of the database table.
     * The result is limited to a specified number of entities.
     *
     * @param int $idProduct The product ID for which related products are to be retrieved.
     * @param int $limit The maximum number of related products to be returned.
     * @return array|false An array of related product IDs or `false` if an error occurs.
     *
     * DISCLAIMER: Description was written with ChatGPT
     */
    public static function getRelatedProducts(int $idProduct, int $limit): array|false
    {
        $sql = '
        SELECT 
            DISTINCT CASE
                WHEN id_product1 = "' . (int)$idProduct . '" 
                THEN id_product2
                ELSE id_product1
            END AS related_id_product
        FROM `' . _DB_PREFIX_ . 'jb_relprod_relationships` 
        WHERE 
            `id_product1` =  "' . (int)$idProduct . '" OR 
            `id_product2` =  "' . (int)$idProduct . '"
        LIMIT ' . (int)$limit . ';';

        return DB::getInstance()->execute($sql);
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
    public static function setRelatedProducts(int $idProduct, array|int $relatedProducts): void
    {
        if (empty($relatedProducts)) {
            return;
        }
        if (!is_array($relatedProducts)) {
            $relatedProducts = [$relatedProducts];
        }
        $data = [];

        foreach ($relatedProducts as $relatedProduct) {
            $data[] = [
                'id_product1' => (int)$idProduct,
                'id_product2' => (int)$relatedProduct,
            ];
        }
        try {
            DB::getInstance()->insert('jb_relprod_relationships', $data);
        } catch (Exception $e) {
            $context = Context::getContext();
            $translator = $context->getTranslator();
            JbRelatedProductsLog::logError(
                $translator->trans('Unable to set related products. Get error "%s"',
                    ['%s' => $e->getMessage()],
                    'Modules.JbRelatedProducts.RelationShip'
                ),
                $idProduct
            );
        }
    }
}