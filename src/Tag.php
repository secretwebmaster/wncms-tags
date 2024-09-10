<?php

namespace Wncms\Tags;

use ArrayAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as DbCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Wncms\Translatable\Traits\HasTranslations;

class Tag extends Model
{
    use HasTranslations;
    use HasFactory;

    public array $translatable = ['name'];
    protected static bool $isTranslatable;

    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setIsTranslatable();
    }

    public function setIsTranslatable()
    {
        self::$isTranslatable = config('wncms-tags.is_translatable', false);
    }

    public static function getIsTranslatable()
    {
        return self::$isTranslatable;
    }

    public static function getLocale()
    {
        return app()->getLocale();
    }

    public function scopeWithType(Builder $query, string $type = null): Builder
    {
        if (is_null($type)) {
            return $query;
        }

        return $query->where('type', $type)->ordered();
    }

    public function scopeOrdered(Builder $query, string $direction = 'asc')
    {
        return $query->orderBy('order_column', $direction);
    }

    public static function getTypes(): Collection
    {
        return static::groupBy('type')->pluck('type');
    }

    public static function getWithType(string $type): DbCollection
    {
        return static::withType($type)->get();
    }

    public static function findOrCreate(
        string | array | ArrayAccess $values,
        string | null $type = null,
        string | null $locale = null,
    ): Collection | Tag | static {
        $tags = collect($values)->map(function ($value) use ($type, $locale) {
            if ($value instanceof self) {
                return $value;
            }

            return static::findOrCreateFromString($value, $type, $locale);
        });

        return is_string($values) ? $tags->first() : $tags;
    }

    public static function findFromString(string $name, string $type = null, string $locale = null)
    {
        $locale = $locale ?? static::getLocale();

        return static::query()
            ->where('type', $type)
            ->where(function ($query) use ($name, $locale) {
                $query->where("name", $name);
                if(self::getIsTranslatable()){
                    $query->orWhereHas("translations", function($subq) use ($name, $locale){
                        $subq->where('field', 'name')
                            ->where('value', $name)
                            ->where('locale', $locale);
                    });
                }
            })
            ->first();
    }

    public static function findFromStringOfAnyType(string $name, string $locale = null)
    {
        $locale = $locale ?? static::getLocale();
    
        return static::query()
            ->where(function ($query) use ($name, $locale) {
                // Search by original 'name' or 'slug'
                $query->where("name", $name)
                      ->orWhere("slug", $name);
    
                // If the model is translatable, check the translations table
                if (self::getIsTranslatable()) {
                    $query->orWhereHas("translations", function ($subq) use ($name, $locale) {
                        $subq->where(function ($q) use ($name) {
                                $q->where('field', 'name')
                                  ->orWhere('field', 'slug');
                            })
                            ->where('value', $name)   // Match the translated value
                            ->where('locale', $locale); // Match the locale
                    });
                }
            })
            ->get();
    }
    
    public static function findOrCreateFromString(string $name, string $type = null, string $locale = null)
    {
        $locale = $locale ?? static::getLocale();

        $tag = static::findFromString($name, $type, $locale);

        if (! $tag) {
            $tag = static::create([
                'name' => $name,
                'slug' => $name,
                'type' => $type,
            ]);

            if (self::getIsTranslatable() && ($locale != config('app.locale'))) {
                $tag->translations()->create([
                    'field' => 'name',
                    'locale' => $locale,
                    'value' => $name,
                ]);
            }
        }

        return $tag;
    }
}