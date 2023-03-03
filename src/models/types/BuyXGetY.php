<?php

namespace webdna\commerce\enhancedpromotions\models\types;

use Craft;
use craft\base\Model;
use webdna\commerce\enhancedpromotions\models\Type;

/**
 * Discount model
 */
class BuyXGetY extends Type
{
    public string $name = 'Buy X Get Y';
    
    public function init(): void
    {
        $this->data = [
            'allPurchasables' => [],
            'allCategories' => [],
            'categoryRelationshipType' => '',
        ];
    }
    
}
