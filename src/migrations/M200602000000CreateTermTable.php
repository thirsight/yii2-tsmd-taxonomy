<?php

namespace tsmd\taxonomy\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%term}}`.
 */
class M200602000000CreateTermTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
-- Main table
CREATE TABLE {{%term}} (
    `termid`        INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `taxonomy`      VARCHAR(32)             NOT NULL,
    `parentid`      INT UNSIGNED DEFAULT 0  NULL,
    `parentids`     VARCHAR(255) DEFAULT '' NOT NULL,
    `slug`          VARCHAR(128)            UNIQUE,
    `name`          VARCHAR(128) DEFAULT '' NOT NULL,
    `brief`         VARCHAR(255) DEFAULT '' NOT NULL,
    `tsort`         INT UNSIGNED DEFAULT 0  NOT NULL,
    `subCount`      INT UNSIGNED DEFAULT 0  NOT NULL,
    `relCount`      INT UNSIGNED DEFAULT 0  NOT NULL,
    `createdTime`   INT NOT NULL,
    `updatedTime`   INT NOT NULL,
    INDEX `parentid` (`parentid`),
    INDEX `taxonomy` (`taxonomy`),
    INDEX `name` (`name`),
    INDEX `tsort` (`tsort`),
    CONSTRAINT `fk_self_termid` FOREIGN KEY (`parentid`) REFERENCES {{%term}}(`termid`) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE {{%term}} AUTO_INCREMENT = 1001;

-- Meta table
CREATE TABLE {{%termmeta}} (
    `metaid`        INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `metaTermid`    INT UNSIGNED NOT NULL,
    `metaKey`       VARCHAR(64) NOT NULL,
    `metaValue`     TEXT,
    UNIQUE KEY metaTermidKey (`metaTermid`, `metaKey`),
    INDEX `metaKey` (`metaKey`),
    CONSTRAINT `fk_termmeta_termid` FOREIGN KEY (`metaTermid`) REFERENCES {{%term}}(`termid`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE 
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE {{%termmeta}} AUTO_INCREMENT = 10001;

-- Relationship table
CREATE TABLE {{%term_relationship}} (
    `relid`     INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `relTermid` INT UNSIGNED NOT NULL,
    `objTable`  VARCHAR(64) NOT NULL,
    `objid`     VARCHAR(64) NOT NULL,
    UNIQUE KEY relTermidObj (`relTermid`, `objid`, `objTable`),
    INDEX `objid` (`objid`, `objTable`),
    CONSTRAINT `fk_termrel_termid` FOREIGN KEY (`relTermid`) REFERENCES {{%term}}(`termid`) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE 
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE {{%term_relationship}} AUTO_INCREMENT = 100001;
SQL;

        $sql .= require 'ChinaRegions.php';
        $this->getDb()->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%term_relationship}}');
        $this->dropTable('{{%termmeta}}');
        $this->dropTable('{{%term}}');
    }
}
