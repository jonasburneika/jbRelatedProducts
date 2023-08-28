<?php

class AdminJbRelatedProductsRelationShipsController extends ModuleAdminController
{
    /** @var bool Is bootstrap used */
    public $bootstrap = true;

    public function __construct()
    {
        $this->className = 'Product';
        $this->table = 'product';
        $this->identifier = 'id_product';

        parent::__construct();

        $this->initList();

        $this->content .= $this->module->getMenu();
    }

    private function initList()
    {

        $idLang = (int)$this->context->language->id;
        $idShop = $this->context->language->id;

        $this->fields_list = array(
            'id_product' => array(
                'title' => $this->l('ID'),
                'filter_key' => 'a!id_product'
            ),
            'main_product_name' => array(
                'title' => $this->l('Product'),
                'filter_key' => 'pl!name'
            ),
            'related_id_product' => array(
                'title' => $this->l('Related ID'),
                'search' => false,
            ),
            'related_product_name' => array(
                'title' => $this->l('Related product'),
                'search' => false,
            ),

        );

        $this->_select = '
            a.`id_product`,
            pl.`name` as `main_product_name`,
            plr.`name` as `related_product_name`,
            CASE
                WHEN r.id_product1 = a.id_product 
                THEN r.id_product2
                ELSE r.id_product1
            END AS related_id_product,
            CASE
                WHEN r.id_product1 = a.id_product 
                THEN plr.name
                ELSE plr2.name
            END AS related_product_name
        ';

        $this->_join = '
            JOIN `' . _DB_PREFIX_ . 'jb_relprod_relationships` r ON (
                a.id_product = r.id_product1 OR 
                a.id_product = r.id_product2
            )
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (
                pl.`id_product` = a.`id_product` && 
                pl.`id_lang` = "' . $idLang . '" && 
                pl.`id_shop` = "' . $idShop . '"
            ) 
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` plr ON (
                plr.`id_product` = r.id_product2 && 
                plr.`id_lang` = "' . $idLang . '" && 
                plr.`id_shop` = "' . $idShop . '"
            )  
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` plr2 ON (
                plr2.`id_product` = r.id_product1 && 
                plr2.`id_lang` = "' . $idLang . '" && 
                plr2.`id_shop` = "' . $idShop . '"
            )
        ';

        $this->list_simple_header = true;
        $this->_orderBy = 'a!date_upd';
        $this->_orderWay = 'desc';

        $this->actions = array('remove');

        $this->page_header_toolbar_btn['instructions'] = array(
            'href' => 'https://r.mtdv.me/videos/F0hYupeByo',
            'desc' => $this->l('Instructions'),
            'icon' => 'icon icon-book',
            'target' => '_blank'
        );

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
            ),
        );
    }

    public function displayRemoveLink($token = null, $id, $name = null)
    {
        if (!array_key_exists('Remove', self::$cache_lang)) {
            self::$cache_lang['Remove'] = $this->l('Remove');
        }

        return $this->displayLink($id, self::$cache_lang['Remove'], 'icon-trash', 'relationship');
    }

    private function displayLink($id, $action, $icon, $key)
    {
        $tpl = $this->createTemplate('list_action_remove.tpl');

        $href = $this->context->link->getAdminLink(JbRelatedProducts::CONTROLLER_RELATIONSHIPS);
        $href = Tools::url($href, $this->identifier . '=' . $id);
        $href = Tools::url($href, $key . $this->table);

        $tpl->assign(array(
            'href' => $href,
            'action' => $action,
            'icon' => $icon
        ));

        return $tpl->fetch();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitBulkdeleteproduct')) {
            $this->bulkDelete();
        }

        parent::postProcess();

        if (Tools::isSubmit('relationshipproduct')) {
            $idProduct = Tools::getValue($this->identifier);
            if ($idProduct && JbRelatedProductsRelationShip::removeRelationShip($idProduct)) {
                $url = $this->context->link->getAdminLink(JbRelatedProducts::CONTROLLER_RELATIONSHIPS);
                $url = Tools::url($url, 'conf=1');
                Tools::redirectAdmin($url);
            }
        }
    }

    public function bulkDelete()
    {
        $productBox = Tools::getValue('productBox');
        if (empty($productBox)) {
            return;
        }

        foreach ($productBox as $idProduct) {
            JbRelatedProductsRelationShip::removeRelationShip($idProduct);
        }
        $url = $this->context->link->getAdminLink(JbRelatedProducts::CONTROLLER_RELATIONSHIPS);
        $url = Tools::url($url, 'conf=1');
        Tools::redirectAdmin($url);

    }

}
