<?php

namespace App\TelsonApp;
use App\TelsonApp\Filters\TA_Filters;
use App\TelsonApp\Filters\TA_Filters_Renderer;
use App\TelsonApp\Warranty\TA_Warranty;
use App\TelsonApp\SingleProduct\TA_SingleProduct;
use App\TelsonApp\SingleProduct\TA_TelsonInfoOptions;
use App\TelsonApp\ShareButtons\TA_ShareButtons;
use App\TelsonApp\Recommended\TA_RecommendedProducts;
use App\TelsonApp\Shop\TA_Shop;
use App\TelsonApp\Search\TA_ProductSearch;
use App\TelsonApp\Testimonials\TA_Testimonials;
use App\TelsonApp\Attributes\TA_Attributes;

// use App\TelsonApp\Reviews\TA_Reviews;

if (!defined('ABSPATH')) {
    die;
}


/**
 * Class TA_Init
 */
final class TA_Init
{
    private static function init(): array
    {
       return [
           TA_Filters::class,
           TA_Filters_Renderer::class,
           TA_Warranty::class,
           TA_TelsonInfoOptions::class,
           TA_ShareButtons::class,
           TA_RecommendedProducts::class,
           TA_Shop::class,
           TA_ProductSearch::class,
           TA_Testimonials::class,
           TA_Attributes::class,
           TA_SingleProduct::class,


       ];
    }

    public static function register() : void
    {
        foreach (self::init() as $class)
        {
            new $class();
        }
    }
}