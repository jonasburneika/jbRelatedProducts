<?php

class JbRelatedProductsInstaller {
    public function install(): bool
    {
        $queries = [];

        /** JbRelatedProductsLog */
        $queries[] = '
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
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        foreach ($queries as $query){
            if(!Db::getInstance()->execute($query)){
                $this->uninstall();
                return false;
            }
        }
        return true;

    }

    public function uninstall(): bool
    {
        $queries = [];
        /** JbRelatedProductsLog */
        $queries[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'jb_relprod_log`;';

        foreach ($queries as $query){
            if(!Db::getInstance()->execute($query)){
                return false;
            }
        }
        return true;
    }
}