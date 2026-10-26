<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasBilingualFields;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_OUT_OF_STOCK = 'out_of_stock';

    protected $fillable = [
        'slug', 'sku', 'name_bn', 'name_en', 'short_description_bn', 'short_description_en',
        'description_bn', 'description_en', 'status', 'unit', 'is_seasonal',
        'season_start_month', 'season_end_month', 'is_featured_home', 'sort_order',
        'seo_title_bn', 'seo_title_en', 'seo_description_bn', 'seo_description_en',
    ];

    protected $casts = [
        'is_seasonal' => 'boolean',
        'is_featured_home' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /** Products a customer may see: published, or published-but-out-of-stock (shown with a disabled button). */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_OUT_OF_STOCK]);
    }

    public function scopePurchasable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->purchasable()->whereHas('variants', function (Builder $v) {
            $v->where('is_active', true)->where(function (Builder $s) {
                $s->whereNull('stock_quantity')->orWhere('stock_quantity', '>', 0);
            });
        });
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.addcslashes(trim($term), '%_\\').'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('name_bn', 'like', $like)
                ->orWhere('name_en', 'like', $like)
                ->orWhere('sku', 'like', $like);
        });
    }

    public function getNameAttribute(): string
    {
        return $this->localized('name');
    }

    public function getShortDescriptionAttribute(): string
    {
        return $this->localized('short_description');
    }

    public function getDescriptionAttribute(): string
    {
        return $this->localized('description');
    }

    /** Lowest current price across active variants, in paisa. */
    public function minPricePaisa(): ?int
    {
        $prices = $this->variants->where('is_active', true)->map->currentPricePaisa();

        return $prices->isEmpty() ? null : $prices->min();
    }

    /**
     * Seasonal products are only "in season" between their start and end month.
     * Ranges may wrap the year end (e.g. November to February).
     */
    public function isInSeason(?CarbonInterface $date = null): bool
    {
        if (! $this->is_seasonal || ! $this->season_start_month || ! $this->season_end_month) {
            return true;
        }

        $month = ($date ?? now())->month;
        $start = $this->season_start_month;
        $end = $this->season_end_month;

        return $start <= $end
            ? ($month >= $start && $month <= $end)
            : ($month >= $start || $month <= $end);
    }
}
