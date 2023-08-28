<?php

class AdminJbRelatedProductsLogsController extends ModuleAdminController
{
    /** @var bool Is bootstrap used */
    public $bootstrap = true;

    public function __construct()
    {
        $this->className = 'JbRelatedProductsLog';
        $this->table = 'jb_relprod_log';
        $this->identifier = 'id_jb_relprod_log';

        parent::__construct();

        $this->initList();
        $this->content .= $this->module->getMenu();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/admin.css');
    }

    public function postProcess()
    {
        if (Tools::isSubmit('deleteAll')) {
            $days = (int) Configuration::get($this->module->prefix . 'PRODUCTS_LOG_DAYS');
            JbRelatedProductsLog::deleteLogs(true, $days);

            $url = $this->context->link->getAdminLink(JbRelatedProducts::CONTROLLER_LOGS);
            $url = Tools::url($url, 'conf=1');
            Tools::redirectAdmin($url);
        }

        return parent::postProcess();
    }

    private function initList()
    {
        $this->fields_list = array(
            'id_jb_relprod_log' => array(
                'title' => $this->l('ID'),
                'type' => 'int',
                'filter_key' => 'a!id_jb_relprod_log',
                'class' => 'fixed-width-xl',
                'align' => 'right'
            ),
            'id_product' => array(
                'title' => $this->l('Product ID'),
                'type' => 'int',
                'filter_key' => 'a!id_product',
                'class' => 'fixed-width-xl',
                'align' => 'right'
            ),
            'type' => array(
                'title' => $this->l('Type'),
                'filter_key' => 'a!type',
                'align' => 'center',
                'type' => 'select',
                'list' => array(
                    JbRelatedProductsLog::TYPE_SUCCESS => $this->l('Success'),
                    JbRelatedProductsLog::TYPE_ERROR => $this->l('Error'),
                    JbRelatedProductsLog::TYPE_WARNING => $this->l('Warning'),
                    JbRelatedProductsLog::TYPE_INFO => $this->l('Info'),
                )
            ),
            'message' => array(
                'title' => $this->l('Message'),
                'filter_key' => 'a!message',
            ),
            'employee' => array(
                'title' => $this->l('Employee'),
                'type' => 'text',
                'search' => false,
                'class' => 'fixed-width-xl',
            ),
            'date_add' => array(
                'title' => $this->l('Date'),
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
                'class' => 'fixed-width-xl',
                'align' => 'right'
            ),
        );
        $this->list_no_link = true;
        $this->_orderBy = 'a!id_jb_relprod_log';
        $this->_orderWay = 'desc';

        $this->_select = '
            IF (a.`type` = "' . JbRelatedProductsLog::TYPE_SUCCESS . '", "' . $this->l('Success') . '", 
                IF (a.`type` = "' . JbRelatedProductsLog::TYPE_ERROR . '", "' . $this->l('Error') . '", 
                    IF (a.`type` = "' . JbRelatedProductsLog::TYPE_WARNING . '", "' . $this->l('Warning') . '", 
                        "' . $this->l('Info') . '"))) AS `type`,
            CONCAT(e.firstname," ", e.lastname) as `employee`';

        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'employee` e on e.`id_employee` = a.`id_employee`';
        $this->page_header_toolbar_btn['instructions'] = array(
            'href' => 'https://r.mtdv.me/videos/F0hYupeByo',
            'desc' => $this->l('Instructions'),
            'icon' => 'icon icon-book',
            'target' => '_blank'
        );

        $this->page_header_toolbar_btn['delete'] = array(
            'href' => Tools::url($this->context->link->getAdminLink(JbRelatedProducts::CONTROLLER_LOGS), 'deleteAll=1'),
            'desc' => $this->l('Clear logs'),
            'icon' => 'icon icon-trash',
        );
    }
}