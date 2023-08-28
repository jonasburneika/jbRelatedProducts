<?php

class JbRelatedProductsLog extends ObjectModel
{
    const TYPE_SUCCESS = 1;

    const TYPE_ERROR = 2;

    const TYPE_WARNING = 3;

    const TYPE_INFO = 4;

    public $id_jb_related_products_log;

    public $type;

    public $id_product;

    public $message;

    public $date_add;

    public $date_upd;

    public static $definition = array(
        'table' => 'jb_relprod_log',
        'primary' => 'id_jb_relprod_log',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'message' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null, $translator = null)
    {
        parent::__construct($id, $id_lang, $id_shop, $translator);
    }

    public function install(): bool
    {

        return Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'jb_relprod_log` (
                `id_jb_relprod_log` INT(11) NOT NULL AUTO_INCREMENT,
                `id_product` INT(11) NOT NULL,
                `type` INT(11) NOT NULL,
                `message` TEXT NOT NULL,
                `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_jb_relprod_log`),
            INDEX(`id_product`, `type`),
            FOREIGN KEY (`id_product`) REFERENCES  `' . _DB_PREFIX_ . 'product`(`id_product`)
            ON DELETE CASCADE ON UPDATE NO ACTION
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'
        );
    }

    public function uninstall()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'jb_relprod_log`;');
    }

    public static function logSuccess($message, $idProduct)
    {
        self::logMessage($message, $idProduct, self::TYPE_SUCCESS);
    }

    public static function logError($message, $idProduct)
    {
        self::logMessage($message, $idProduct, self::TYPE_ERROR);
    }

    public static function logWarning($message, $idProduct)
    {
        self::logMessage($message, $idProduct, self::TYPE_WARNING);
    }

    public static function logInfo($message, $idProduct)
    {
        self::logMessage($message, $idProduct);
    }

    private static function logMessage($message, $idProduct, $type = self::TYPE_INFO)
    {
        $log = new self();
        $log->id_product = (int)$idProduct;
        $log->type = (int)$type;
        $log->message = $message;
        try {
            $log->save();
        } catch (Exception $e) {
            $context = Context::getContext();
            $translator = $context->getTranslator();
            PrestaShopLogger::addLog(
                $translator->trans(
                    'Unable to save Log message. Error: %s ',
                    ['%s' => $e->getMessage()],
                    'Modules.JbRelatedProducts.Log'
                ),
                1,
                null,
                '',
                0,
                true,
                (int)$context->employee->id
            );
        }
    }

    public static function deleteLogs($all = false): bool
    {
        $days = 31;
        $date = date('Y-m-d H:i:s', strtotime('-' . (int)$days . ' days'));
        $where = $all ? '' : ' WHERE `date_add` < "' . pSQL($date) . '"';

        return Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'jb_relprod_log` ' . $where . ';');
    }
}