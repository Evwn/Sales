<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'category_id', 'name', 'description', 'brand', 'model', 'unit_id', 'unit_value', 'sku', 'barcode', 'upc', 'ean', 'isbn', 'mpn', 'sold_by', 'is_for_sale', 'price', 'cost', 'track_stock', 'is_composite', 'in_stock', 'low_stock', 'is_taxable', 'tax_group_id', 'created_by', 'updated_by'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function unit() { return $this->belongsTo(Unit::class); }
    public function variants()
    {
        return $this->hasMany(ItemVariant::class);
    }
    public function components()
    {
        return $this->hasMany(ItemComponent::class, 'item_id');
    }
    public function componentItems()
    {
        return $this->belongsToMany(Item::class, 'item_components', 'item_id', 'component_item_id');
    }
    public function componentVariants()
    {
        return $this->belongsToMany(ItemVariant::class, 'item_components', 'item_id', 'component_variant_id');
    }
    public function modifiers() { return $this->belongsToMany(Modifier::class, 'item_modifiers'); }
    public function discounts() { return $this->belongsToMany(Discount::class, 'item_discounts'); }
    public function taxGroup() { return $this->belongsTo(TaxGroup::class); }
    public function stockItems()
    {
        return $this->hasMany(StockItem::class);
    }
    public function purchaseItems() { return $this->hasMany(PurchaseItem::class); }
} 