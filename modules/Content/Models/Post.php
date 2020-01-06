<?php

namespace Modules\Content\Models;

use Nova\Database\ORM\Model;
use Nova\Support\Arr;
use Nova\Support\Str;

use Shared\MetaField\HasMetaFieldsTrait;

use Modules\Content\Support\Facades\PostType;

use Modules\Content\Models\PostBuilder;
use Modules\Content\Traits\OrderedTrait;
use Modules\Content\Traits\ShortcodesTrait;
use Modules\Platform\Traits\AliasesTrait;

use ErrorException;


class Post extends Model
{
    use HasMetaFieldsTrait, AliasesTrait, OrderedTrait, ShortcodesTrait;

    //
    protected $table = 'posts';

    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = array(
        'author_id',
        'content',
        'title',
        'excerpt',
        'status',
        'password',
        'name',
        'parent_id',
        'menu_order',
        'type',
        'mime_type',
        'profile_id',
    );

    /**
     * @var array
     */
    protected $with = array('meta');

    /**
     * @var array
     */
    protected $appends = array(
        'slug',
        'url',
        'image',
        'terms',
        'main_category',
        'keywords',
        'keywords_str',
    );

    /**
     * @var array
     */
    protected static $aliases = array(
        'slug' => 'name',
        'url'  => 'guid',
    );

    /**
     * @var string
     */
    protected $postType;

    /**
     * @var array
     */
    protected static $postTypes = array();


    /**
     * @return \Nova\Database\ORM\Relations\HasMany
     */
    public function meta()
    {
        return $this->hasMany('Modules\Content\Models\PostMeta', 'post_id');
    }

    /**
     * @return \Nova\Database\ORM\Relations\HasOne
     */
    public function thumbnail()
    {
        return $this->hasOne('Modules\Content\Models\ThumbnailMeta', 'post_id')->where('key', 'thumbnail_id');
    }

    /**
     * @return \Nova\Database\ORM\Relations\BelongsToMany
     */
    public function taxonomies()
    {
        return $this->belongsToMany(
            'Modules\Content\Models\Taxonomy', 'term_relationships', 'object_id', 'term_taxonomy_id'
        );
    }

    /**
     * @return \Nova\Database\ORM\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('Modules\Content\Models\Comment', 'post_id');
    }

    /**
     * @return \Nova\Database\ORM\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo('Modules\Users\Models\User', 'author_id');
    }

    /**
     * @return \Nova\Database\ORM\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo('Modules\Content\Models\Post', 'parent_id');
    }

    /**
     * @return \Nova\Database\ORM\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany('Modules\Content\Models\Post', 'parent_id');
    }

    /**
     * @return \Nova\Database\ORM\Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany('Modules\Content\Models\Post', 'parent_id')->where('type', 'attachment');
    }

    /**
     * @return \Nova\Database\ORM\Relations\HasMany
     */
    public function revisions()
    {
        return $this->hasMany('Modules\Content\Models\Post', 'parent_id')->where('type', 'revision');
    }

    /**
     * @return PostBuilder
     */
    public function newQuery()
    {
        $query = parent::newQuery();

        if (isset($this->postType)) {
            return $query->where('type', $this->postType);
        }

        return $query;
    }

    /**
     * @param \Nova\Database\Query\Builder $query
     * @return \Modules\Content\Models\Builder\PostBuilder
     */
    public function newQueryBuilder($query)
    {
        return new PostBuilder($query);
    }

    /**
     * @param array $attributes
     * @param null $connection
     * @return mixed
     */
    public function newFromBuilder($attributes = array(), $connection = null)
    {
        $model = $this->getPostInstance((array) $attributes);

        $model->exists = true;

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnection(
            $connection ?: $this->getConnectionName()
        );

        return $model;
    }

    /**
     * @param array $attributes
     * @return static
     */
    protected function getPostInstance(array $attributes)
    {
        if (! empty($type = Arr::get($attributes, 'type'))) {
            $className = $this->resolveModelName($type);
        } else {
            $className = static::class;
        }

        return new $className();
    }

    /**
     * @param string $type
     * @return string
     */
    protected function resolveModelName($type)
    {
        if (! is_null($className = Arr::get(static::$postTypes, $type))) {
            return $className;
        }

        //
        else if (! is_null($className = PostType::findModelByType($type))) {
            return $className;
        }

        return static::class;
    }

    public function getTypeInstance()
    {
        if (isset($this->postType)) {
            $type = $this->postType;
        } else {
            $type = $this->type ?: 'post';
        }

        return PostType::make($type);
    }

    /**
     * Whether the post contains the term or not.
     *
     * @param string $taxonomy
     * @param string $term
     * @return bool
     */
    public function hasTerm($taxonomy, $term)
    {
        return isset($this->terms[$taxonomy]) && isset($this->terms[$taxonomy][$term]);
    }

    /**
     * @param string $type
     */
    public function setPostType($postType)
    {
        $this->postType = $postType;
    }

    /**
     * @return string
     */
    public function getPostType()
    {
        return $this->postType;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->stripShortcodes($this->content);
    }

    /**
     * @return string
     */
    public function getExcerpt()
    {
        return $this->stripShortcodes($this->excerpt);
    }

    /**
     * Gets the featured image if any
     * Looks in meta the _thumbnail_id field.
     *
     * @return string
     */
    public function getImageAttribute()
    {
        if (! is_null($this->thumbnail) && ! is_null($attachment = $this->thumbnail->attachment)) {
            return $attachment->guid;
        }
    }

    /**
     * Gets all the terms arranged taxonomy => terms[].
     *
     * @return array
     */
    public function getTermsAttribute()
    {
        return $this->taxonomies->groupBy(function ($taxonomy)
        {
            return $taxonomy->taxonomy;

        })->map(function ($group)
        {
            return $group->mapWithKeys(function ($item)
            {
                return array($item->term->slug => $item->term->name);
            });

        })->toArray();
    }

    /**
     * Gets the first term of the first taxonomy found.
     *
     * @return string
     */
    public function getMainCategoryAttribute()
    {
        $mainCategory = 'Uncategorized';

        if (! empty($this->terms)) {
            $taxonomies = array_values($this->terms);

            if (! empty($taxonomies[0])) {
                $terms = array_values($taxonomies[0]);

                $mainCategory = $terms[0];
            }
        }

        return $mainCategory;
    }

    /**
     * Gets the keywords as array.
     *
     * @return array
     */
    public function getKeywordsAttribute()
    {
        return collect($this->terms)->map(function ($taxonomy)
        {
            return collect($taxonomy)->values();

        })->collapse()->toArray();
    }

    /**
     * @param string $name The post type slug
     * @param string $class The class to be instantiated
     */
    public static function registerPostType($name, $class)
    {
        static::$postTypes[$name] = $class;
    }

    /**
     * Clears any registered post types.
     */
    public static function clearRegisteredPostTypes()
    {
        static::$postTypes = array();
    }

    /**
     * Get the post format, like the WP get_post_format() function.
     *
     * @return bool|string
     */
    public function getFormat()
    {
        $taxonomy = $this->taxonomies()
            ->where('taxonomy', 'post_format')
            ->first();

        if (! is_null($taxonomy) && isset($taxonomy->term)) {
            return str_replace('post-format-', '', $taxonomy->term->slug);
        }

        return false;
    }

    /**
     * Update the comment count field.
     */
    public function updateCommentCount()
    {
        $this->comment_count = $this->comments()->count();

        $this->save();
    }

    public static function uniqueName($name, $id = null)
    {
        $query = static::query();

        if (! is_null($id)) {
            $query->where('id', '!=', (int) $id);
        }

        $names = $query->lists('name');

        if (! in_array($slug = Str::slug($name), $names)) {
            // The slug is unique, then no further processing is required.
            return $slug;
        }

        $count = 0;

        if (count($segments = explode('-', $slug)) > 1) {
            $last = end($segments);

            if (is_integer($last)) {
                $count = (int) array_pop($segments);

                $slug = implode('-', $segments);
            }
        }

        do {
            $name = ($count === 0) ? $slug : $slug .'-' .$count;

            $count++;
        }
        while (in_array($name, $names));

        return $name;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($name)
    {
        $value = parent::__get($name);

        if (is_null($value) && ! property_exists($this, $name)) {
            return $this->meta->$name;
        }

        return $value;
    }
}
