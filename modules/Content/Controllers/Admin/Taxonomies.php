<?php

namespace Modules\Content\Controllers\Admin;

use Nova\Database\ORM\ModelNotFoundException;
use Nova\Http\Request;
use Nova\Support\Facades\Cache;
use Nova\Support\Facades\Config;
use Nova\Support\Facades\Redirect;
use Nova\Support\Facades\Response;
use Nova\Support\Facades\Validator;
use Nova\Support\Facades\View;
use Nova\Support\Arr;
use Nova\Support\Str;

use Modules\Content\Models\Taxonomy;
use Modules\Content\Models\Term;
use Modules\Content\Support\Facades\TaxonomyType;
use Modules\Platform\Controllers\Admin\BaseController;


class Taxonomies extends BaseController
{

    protected function validator(array $data, $id = 0)
    {
        $taxonomies = implode(',', TaxonomyType::getNames());

        //
        $taxonomy = Arr::get($data, 'taxonomy', 'unknown');

        // The Validation rules.
        $rules = array(
            'taxonomy'    => 'required|in:' .$taxonomies,
            'name'        => 'required|min:3|max:255|valid_text',
            'slug'        => 'min:4|max:100|alpha_dash|unique_slug:' .$taxonomy .',' .intval($id),
            'description' => 'min:3|max:1000|valid_text',
        );

        $messages = array(
            'unique_slug' => __d('content', 'The :attribute field is not an unique Taxonomy slug.'),
            'valid_text'  => __d('content', 'The :attribute field is not a valid text.'),
        );

        $attributes = array(
            'taxonomy'    => __d('content', 'Taxonomy'),
            'name'        => __d('content', 'Name'),
            'slug'        => __d('content', 'Slug'),
            'description' => __d('content', 'Description'),
        );

        // Add the custom Validation Rule commands.
        Validator::extend('unique_slug', function ($attribute, $value, $parameters)
        {
            list ($taxonomy, $id) = array_pad($parameters, 2, null);

            $query = Taxonomy::where('taxonomy', $taxonomy)->whereHas('term', function ($query) use ($value)
            {
                $query->where('slug', $value);

            })->where('id', '<>', (int) $id);

            return ! $query->exists();
        });

        Validator::extend('valid_text', function($attribute, $value, $parameters)
        {
            return ($value == strip_tags($value));
        });

        return Validator::make($data, $rules, $messages, $attributes);
    }

    public function index(Request $request, $slug)
    {
        $taxonomyType = TaxonomyType::findBySlug($slug);

        $type = $taxonomyType->name();

        //
        $name = $taxonomyType->label('name');

        $items = Taxonomy::where('taxonomy', $type)->paginate(15);

        if ($taxonomyType->isHierarchical()) {
            $results = Taxonomy::with('children')->where('taxonomy', $type)->where('parent_id', 0)->get();

            $taxonomies = $this->generateTaxonomySelectOptions($type, 0, 0, $results);
        } else {
            $taxonomies = '';
        }

        return $this->createView()
            ->shares('title', $taxonomyType->label('title'))
            ->with(compact('items', 'type', 'name', 'taxonomyType', 'taxonomies'));
    }

    public function store(Request $request)
    {
        $ajaxRespose = ($request->ajax() || $request->wantsJson());

        $input = $request->all();

        // Validate the Input data.
        $validator = $this->validator($input);

        if ($validator->fails()) {
            if ($ajaxRespose) {
                // The request was made by the Post Editor via AJAX.
                return Response::json(array('error' => $validator->errors()), 400);
            }

            return Redirect::back()->withInput()->withErrors($validator->errors());
        }

        $type = Arr::get($input, 'taxonomy');

        //
        $name = Arr::get($input, 'name');

        if (empty($slug = Arr::get($input, 'slug'))) {
            $slug = Term::uniqueSlug($name, $type);
        }

        $description = Arr::get($input, 'description');

        $parentId = (int) Arr::get($input, 'parent', 0);

        // Create the Term.
        $term = Term::create(array(
            'name' => $name,
            'slug' => $slug,
        ));

        // Create the Taxonomy.
        $taxonomy = Taxonomy::create(array(
            'term_id'     => $term->id,
            'taxonomy'    => $type,
            'description' => $description,
            'parent_id'   => $parentId,
        ));

        // Invalidate the content caches.
        Cache::section('content')->flush();

        if ($ajaxRespose) {
            // The request was made by the Post Editor via AJAX, so we will return a fresh taxonomies selector.
            $selected = Arr::get($input, $type, array());

            // Add also the fresh category ID.
            $selected[] = $taxonomy->id;

            //
            $results = Taxonomy::with('children')->where('taxonomy', $type)->where('parent_id', 0)->get();

            $taxonomies = $this->generateTaxonomyCheckBoxes($type, $selected, $results);

            return Response::json(array(
                'taxonomyId' => $taxonomy->id,
                'taxonomies' => $taxonomies
            ));
        }

        $taxonomyType = TaxonomyType::make($type);

        return Redirect::back()
            ->with('success', __d('content', 'The {0} <b>{1}</b> was successfully created.', $taxonomyType->label('name'), $name));
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        try {
            $taxonomy = Taxonomy::with('term')->findOrFail($id);
        }
        catch (ModelNotFoundException $e) {
            return Redirect::back()->with('danger', __d('content', 'Taxonomy not found: #{0}', $id));
        }

        $term = $taxonomy->term;

        // Validate the Input data.
        $validator = $this->validator($input, $taxonomy->id);

        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator->errors());
        }

        $type = Arr::get($input, 'taxonomy');

        if ($type !== $taxonomy->taxonomy) {
            return Redirect::back()->with('danger', __d('content', 'The requested Taxonomy type [{0}] does not match.', $type));
        }

        $name = Arr::get($input, 'name');

        if (empty($slug = Arr::get($input, 'slug'))) {
            $slug = Term::uniqueSlug($name, $type, $taxonomy->id);
        }

        // Update the Term.
        $term->name = $name;
        $term->slug = $slug;

        $term->save();

        // Update the Taxonomy.
        $taxonomy->description = Arr::get($input, 'description');

        $taxonomy->parent_id = (int) Arr::get($input, 'parent', 0);

        $taxonomy->save();

        // Invalidate the content caches.
        Cache::section('content')->flush();

        //
        $taxonomyType = TaxonomyType::make($type);

        return Redirect::back()
            ->with('success', __d('content', 'The {0} <b>{1}</b> was successfully updated.', $taxonomyType->label('name'), $name));
    }

    public function destroy($id)
    {
        try {
            $taxonomy = Taxonomy::with('term', 'children')->findOrFail($id);
        }
        catch (ModelNotFoundException $e) {
            return Redirect::back()->with('danger', __d('content', 'Taxonomy not found: #{0}', $id));
        }

        $taxonomy->children->each(function ($child) use ($taxonomy)
        {
            $child->parent_id = $taxonomy->parent_id;

            $child->save();
        });

        $taxonomy->term->delete();

        $taxonomy->delete();

        // Invalidate the content caches.
        Cache::section('content')->flush();

        //
        $taxonomyType = TaxonomyType::make($taxonomy->taxonomy);

        $name = $taxonomyType->label('name');

        return Redirect::back()
            ->with('success', __d('content', 'The {0} <b>{1}</b> was successfully deleted.', $name, $taxonomy->name));
    }

    public function data($id, $parentId)
    {
        $taxonomy = Taxonomy::findOrFail($id);

        //
        $results = Taxonomy::with('children')->where('taxonomy', $type = $taxonomy->taxonomy)->where('parent_id', 0)->get();

        $taxonomies = $this->generateTaxonomySelectOptions($type, $id, $parentId, $results);

        return Response::make($taxonomies, 200);
    }

    protected function generateTaxonomyCheckBoxes($type, array $selected = array(), $taxonomies = null, $level = 0)
    {
        $view = 'Modules/Content::Partials/Admin/Taxonomies/TaxonomyCheckBox';

        //
        $result = '';

        foreach ($taxonomies as $taxonomy) {
            $result .= View::make($view, compact('type', 'taxonomy', 'level', 'selected'))->render();

            // Process the children.
            $taxonomy->load('children');

            if ($taxonomy->children->isEmpty()) {
                continue;
            }

            $result .= $this->generateTaxonomyCheckBoxes($type, $selected, $taxonomy->children, $level + 1);
        }

        return $result;
    }

    protected function generateTaxonomySelectOptions($type, $currentId, $parentId, $taxonomies, $level = 0)
    {
        $view = 'Modules/Content::Partials/Admin/Taxonomies/TaxonomySelectOption';

        //
        $taxonomy = null;

        if ($level === 0) {
            $result = View::make($view, compact('taxonomy', 'level', 'parentId'))->render();
        } else {
            $result = '';
        }

        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy->id == $currentId) {
                continue;
            }

            $result .= View::make($view, compact('taxonomy', 'level', 'parentId'))->render();

            // Process the children.
            $taxonomy->load('children');

            if ($taxonomy->children->isEmpty()) {
                continue;
            }

            $result .= $this->generateTaxonomySelectOptions($type, $currentId, $parentId, $taxonomy->children, $level + 1);
        }

        return $result;
    }
}
