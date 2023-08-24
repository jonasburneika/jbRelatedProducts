<?php

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class JbRelatedProducts extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name = 'jbrelatedproducts';
        $this->tab = 'front_office_features';
        $this->author = 'Jonas Burneika';
        $this->version = '1.0.0';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Related product products', [], 'Modules.JbRelatedProducts.Admin');
        $this->description = $this->trans('Add a block on every product page that displays similar products based on configurations', [], 'Modules.JbRelatedProducts.Admin');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];

        $this->templateFile = 'module:jbrelatedproducts/views/templates/hook/displayRelatedProducts.tpl';
    }

    public function renderWidget($hookName, array $configuration)
    {
        // TODO: Implement renderWidget() method.
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        // TODO: Implement getWidgetVariables() method.
    }

}