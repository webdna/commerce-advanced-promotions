<?php

namespace webdna\commerce\enhancedpromotions\migrations;

use Craft;
use craft\commerce\db\Table;
use craft\commerce\elements\Order;
use craft\db\Migration;
use craft\db\Table as CraftTable;
use craft\helpers\MigrationHelper;
use Exception;
use ReflectionClass;
use yii\base\NotSupportedException;

/**
 * Installation Migration
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class Install extends Migration
{
	public function safeUp(): bool
	{
		$this->createTables();
		$this->createIndexes();
		$this->addForeignKeys();
		$this->insertDefaultData();
	
		return true;
	}
	
	public function safeDown(): bool
	{
		$this->dropForeignKeys();
		$this->dropTables();
	
		return true;
	}
	
	public function createTables(): void
	{
		$this->createTable('{{%commerce-enhanced-promotions_couponcodes}}', [
			'code' => $this->string()->notNull(),
			'discountId' => $this->integer()->notNull(),
			'orderId' => $this->integer()->notNull(),
		]);
		
		$this->createTable('{{%commerce-enhanced-promotions_discounts}}', [
			'id' => $this->primaryKey(),
			'type' => $this->string()->notNull(),
			'name' => $this->string()->notNull(),
			'description' => $this->text(),
			'data' => $this->text(),
			'dateFrom' => $this->dateTime(),
			'dateTo' => $this->dateTime(),
			'enabled' => $this->boolean()->notNull()->defaultValue(true),
			'ignoreSales' => $this->boolean()->notNull()->defaultValue(false),
			'sortOrder' => $this->integer(),
			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid' => $this->uid(),
		]);
	}
	
	public function createIndexes(): void
	{
		$this->createIndex(null, '{{%commerce-enhanced-promotions_couponcodes}}', 'code', false);
		$this->createIndex(null, '{{%commerce-enhanced-promotions_couponcodes}}', 'discountId', false);
		$this->createIndex(null, '{{%commerce-enhanced-promotions_couponcodes}}', 'orderId', false);
		$this->createIndex(null, '{{%commerce-enhanced-promotions_couponcodes}}', ['discountId', 'orderId'], true);
		
		$this->createIndex(null, '{{%commerce-enhanced-promotions_discounts}}', 'dateFrom', false);
		$this->createIndex(null, '{{%commerce-enhanced-promotions_discounts}}', 'dateTo', false);
	}
	
	public function addForeignKeys(): void
	{
		$this->addForeignKey(null, '{{%commerce-enhanced-promotions_couponcodes}}', ['orderId'], Table::ORDERS, ['id'], 'CASCADE', 'CASCADE');
		$this->addForeignKey(null, '{{%commerce-enhanced-promotions_couponcodes}}', ['discountId'], Table::DISCOUNTS, ['id'], 'CASCADE', 'CASCADE');
	}
	
	public function insertDefaultData(): void
	{
		
	}
	
	public function dropForeignKeys(): void
	{
		MigrationHelper::dropAllForeignKeysOnTable('{{%commerce-enhanced-promotions_couponcodes}}', $this);
		MigrationHelper::dropAllForeignKeysOnTable('{{%commerce-enhanced-promotions_discounts}}', $this);
	}
	
	public function dropTables(): void
	{
		$this->dropTableIfExists('{{%commerce-enhanced-promotions_couponcodes}}');
		$this->dropTableIfExists('{{%commerce-enhanced-promotions_discounts}}');
	}
}