<?php

namespace webdna\commerce\enhancedpromotions\models;

use Craft;
use craft\commerce\models\Discount as CommerceDiscount;
use craft\helpers\UrlHelper;

/**
 * Discount model
 */
class Discount extends CommerceDiscount
{
    public string $label = '';
    
    /**
     * @var array|null The data of this discount type
     */
    //public ?array $data = null;
    
    
    public function getClassname(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }
    
    
    public function getCpEditUrl(): string
    {
        return UrlHelper::cpUrl('commerce/promotions/types/'. $this->getClassname() .'/' . $this->id);
    }
    
}
