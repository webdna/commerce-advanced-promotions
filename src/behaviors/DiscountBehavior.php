<?php

namespace webdna\commerce\enhancedpromotions\behaviors;

use craft\commerce\models\Discount;
use craft\commerce\Plugin as Commerce;
use craft\events\ModelEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use webdna\commerce\enhancedpromotions\records\Discount as DiscountRecord;
use RuntimeException;
use yii\base\Behavior;
use yii\base\InvalidConfigException;

/**
 * Discount behavior.
 *
 * @property-read array $couponCodes
 */
class DiscountBehavior extends Behavior
{
	private ?string $_type = null;
	
	private ?array $_data = null;
	
	/**
	 * @inheritdoc
	 */
	public function attach($owner)
	{
		if (!$owner instanceof Discount) {
			throw new RuntimeException('DiscountBehavior can only be attached to an Discount model');
		}

		parent::attach($owner);

	}

	
	public function setType(?string $type): void
	{
		$this->_type = $type;
	}
	public function getType(): ?string
	{
		if (!$this->_type) {
			$this->getEnhanced();
		}
		
		return $this->_type;
	}
	
	public function setData(?array $data): void
	{
		$this->_data = $data;
	}
	public function getData(): ?array
	{
		if (!$this->_data) {
			$this->getEnhanced();
		}
		
		return $this->_data;
	}
	
	
	private function getEnhanced(): void
	{
		if ($record = DiscountRecord::find()->where(['discountId' => $this->owner->id])->select('*')->one()) {
			$this->_type = $record->type;
			$this->_data = Json::decodeIfJson($record->data);
		}
	}
}
