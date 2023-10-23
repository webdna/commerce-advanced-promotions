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
class m231023_103441_discounts extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        MigrationHelper::dropAllForeignKeysOnTable('{{%commerce-enhanced-promotions_discounts}}', $this);
        
        $this->dropTableIfExists('{{%commerce-enhanced-promotions_discounts}}');
        
        $this->createTable('{{%commerce-enhanced-promotions_discounts}}', [
            'id' => $this->primaryKey(),
            'discountId' => $this->integer()->notNull(),
            'type' => $this->string()->notNull(),
            'data' => $this->text(),
            'uid' => $this->uid(),
        ]);
        
        $this->createIndex(null, '{{%commerce-enhanced-promotions_discounts}}', 'discountId', false);
        $this->createIndex(null, '{{%commerce-enhanced-promotions_discounts}}', 'type', false);
        
        $this->addForeignKey(null, '{{%commerce-enhanced-promotions_discounts}}', ['discountId'], Table::DISCOUNTS, ['id'], 'CASCADE', 'CASCADE');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m231023_103441_discounts cannot be reverted.\n";
        return false;
    }
}
