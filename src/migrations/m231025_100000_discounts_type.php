<?php

namespace webdna\commerce\enhancedpromotions\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\MigrationHelper;
use craft\commerce\db\Table;
use craft\db\Table as CraftTable;

/**
 * m231023_103441_discounts migration.
 */
class m231025_100000_discounts_type extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        
        $this->alterColumn('{{%commerce-enhanced-promotions_discounts}}', 'type', $this->string());

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m231025_100000_discounts_type cannot be reverted.\n";
        return false;
    }
}
