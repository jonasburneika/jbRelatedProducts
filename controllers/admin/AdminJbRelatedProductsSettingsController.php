<?php

class AdminJbRelatedProductsSettingsController extends ModuleAdminController
{
    /** @var bool Is bootstrap used */
    public $bootstrap = true;

    public function __construct()
    {
        $this->className = 'Configuration';
        $this->table = 'configuration';
        $this->identifier = 'id_configuration';

        parent::__construct();

        $this->initOptions();
        $this->content .= $this->module->getMenu();
    }

    public function postProcess()
    {
        foreach ($this->module->configurations as $configuration => $value) {
            $name = $this->module->prefix . $configuration;
            $value = Configuration::get($name);

            $newValue = Tools::getValue($name);
            if (Tools::isSubmit($name) && $value != $newValue) {
                JbRelatedProductsLog::logInfo(
                    $this->trans(
                        'Parameter %conf% was changed from %old% to %new%',
                        [
                            '%conf%' => $name,
                            '%old%' => $value,
                            '%new%' => $newValue,
                        ],
                        'Modules.JbRelatedProducts.Admin')
                );
            }
        }
        parent::postProcess();
    }

    private function initOptions()
    {
        $this->fields_options = array(
            'general' => array(
                'title' => $this->l('Related products settings'),
                'tabs' => array(
                    'relation' => $this->l('Generic settings'),
                    'other' => $this->l('Other settings'),
                ),
                'fields' => array(
                    $this->module->prefix . 'PRODUCTS_QUANTITY' => array(
                        'title' => $this->trans('Amount of products', [], 'Modules.JbRelatedProducts.Admin'),
                        'type' => 'text',
                        'tab' => 'relation',
                        'desc' => $this->trans('Number of related product to display', [], 'Modules.JbRelatedProducts.Admin'),
                    ),
                    $this->module->prefix . 'RELATION_CATEGORY' => array(
                        'title' => $this->trans('Same Category', [], 'Modules.JbRelatedProducts.Admin'),
                        'desc' => $this->trans('Display products, which shares at least one category.', [], 'Modules.JbRelatedProducts.Admin'),
                        'type' => 'bool',
                        'tab' => 'relation',
                    ),
                    $this->module->prefix . 'RELATION_DEFAULT_CATEGORY' => array(
                        'title' => $this->trans('Same Default Category', [], 'Modules.JbRelatedProducts.Admin'),
                        'desc' => $this->trans('Display products, which are from same default category.', [], 'Modules.JbRelatedProducts.Admin'),
                        'type' => 'bool',
                        'tab' => 'relation',
                    ),
                    $this->module->prefix . 'RELATION_FEATURES' => array(
                        'title' => $this->trans('Same Features', [], 'Modules.JbRelatedProducts.Admin'),
                        'desc' => $this->trans('Display products, which has at least one matching feature.', [], 'Modules.JbRelatedProducts.Admin'),
                        'type' => 'bool',
                        'tab' => 'relation',
                    ),
                    $this->module->prefix . 'RELATION_MANUFACTURER' => array(
                        'title' => $this->trans('Same Manufacturer', [], 'Modules.JbRelatedProducts.Admin'),
                        'desc' => $this->trans('Display products, which are from same manufacturer.', [], 'Modules.JbRelatedProducts.Admin'),
                        'type' => 'bool',
                        'tab' => 'relation',
                    ),
                    $this->module->prefix . 'RELATION_SUPPLIERS' => array(
                        'title' => $this->trans('Same Supplier', [], 'Modules.JbRelatedProducts.Admin'),
                        'desc' => $this->trans('Display products, which are supplied by same Supplier.', [], 'Modules.JbRelatedProducts.Admin'),
                        'type' => 'bool',
                        'tab' => 'relation',
                    ),
                    $this->module->prefix . 'PRODUCTS_LOG_DAYS' => array(
                        'title' => $this->trans('Log storage in days', [], 'Modules.JbRelatedProducts.Admin'),
                        'type' => 'text',
                        'tab' => 'other',
                    ),
                    $this->module->prefix . 'RELATION_MANUALLY' => array(
                        'title' => $this->trans('Allow select related product manually', [], 'Modules.JbRelatedProducts.Admin'),
                        'desc' => $this->trans('It will display configuration block at product edit page on Options tab', [], 'Modules.JbRelatedProducts.Admin'),
                        'type' => 'bool',
                        'tab' => 'other',
                    ),
                    $this->module->prefix . 'RELATION_APPEND' => array(
                        'title' => $this->trans('Append product by generic settings', [], 'Modules.JbRelatedProducts.Admin'),
                        'desc' => $this->trans('If product has not manually selected related products, use generic products, by preselected criteria', [], 'Modules.JbRelatedProducts.Admin'),
                        'type' => 'bool',
                        'tab' => 'other',
                    ),
                    $this->module->prefix . 'RELATION_REASSURANCE' => array(
                        'title' => $this->trans('Display products at Reassurance Hook', [], 'Modules.JbRelatedProducts.Admin'),
                        'type' => 'bool',
                        'tab' => 'other',
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
        );
    }

//    public function sa
}
