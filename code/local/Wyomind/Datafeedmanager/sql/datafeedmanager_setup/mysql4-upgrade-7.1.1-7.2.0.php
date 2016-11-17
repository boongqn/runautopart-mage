<?php

$installer = $this;

$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('datafeedmanager_configurations')}"
. " ADD `feed_extrafooter` text, ADD `feed_dateformat` varchar(50) NOT NULL default '{f}';");
$installer->endSetup();
