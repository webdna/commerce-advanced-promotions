<?php

namespace webdna\commerce\enhancedpromotions\models;

use Craft;
use craft\base\Model;

/**
 * Discount model
 */
class Discount extends Model
{
    /**
     * @var int|null ID
     */
    public ?int $id = null;
    
    /**
     * @var string Name of the discount
     */
    public string $name = '';
    
    /**
     * @var string Type of the discount
     */
    public string $type = '';
    
    /**
     * @var string|null The description of this discount
     */
    public ?string $description = null;
    
    /**
     * @var array|null The data of this discount type
     */
    public ?array $data = null;
    
    /**
     * @var DateTime|null Date the discount is valid from
     */
    public ?DateTime $dateFrom = null;
    
    /**
     * @var DateTime|null Date the discount is valid to
     */
    public ?DateTime $dateTo = null;
    
    /**
     * @var bool Discount enabled?
     */
    public bool $enabled = true;
    
    /**
     * @var bool stopProcessing
     */
    public bool $stopProcessing = false;
    
    /**
     * @var int|null sortOrder
     */
    public ?int $sortOrder = 999999;
    
    /**
     * @var DateTime|null
     */
    public ?DateTime $dateCreated = null;
    
    /**
     * @var DateTime|null
     */
    public ?DateTime $dateUpdated = null;
    
    /**
     * @var bool Discount ignores sales
     */
    public bool $ignoreSales = true;
    
    
    public function getCpEditUrl(): string
    {
        return UrlHelper::cpUrl('commerce/promotions/types/' . $this->id);
    }
    
    
    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            [['name'], 'required'],
        ]);
    }
}
