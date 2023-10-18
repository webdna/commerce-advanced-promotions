<?php

namespace webdna\commerce\enhancedpromotions\models;

use Craft;
use craft\base\Model;

/**
 * Advanced Promotions settings
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================
    
    public bool $multiCouponCodes = true;
    
    
    // Public Methods
    // =========================================================================
    
    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            
        ];
    }
}
