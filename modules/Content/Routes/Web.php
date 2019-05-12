<?php

/*
|--------------------------------------------------------------------------
| Module Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for the module.
| It's a breeze. Simply tell Nova the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

use Modules\Content\Support\Facades\PostType as Posts;
use Modules\Content\Support\Facades\TaxonomyType as Taxonomies;


// The Media Files serving.
Route::get('media/serve/{name}', 'Attachments@serve');


// The Content dispatching.
Route::paginate('archive/{year}/{month}', array(
    'uses' => 'Content@archive',

    'where' => array(
        'year'  => '\d+',
        'month' => '\d+',
    ),
));

Route::paginate('/', 'Content@homepage');

Route::paginate('search', 'Content@search');

//
Route::get('{slug}', 'Content@show')->order(101);

Route::get('content/{id}', 'Content@show')->where('id', '\d+');

// Content unlocking for the Password Protected pages and posts.
Route::post('content/{id}', 'Content@unlock')->where('id', '\d+');

// Comments.
Route::post('content/{id}/comment', 'Comments@store')->where('id', '\d+');

// Taxonomies.
Route::paginate('{type}/{slug}', 'Content@taxonomy')->where('type', Taxonomies::routePattern(false))->order(100);


// The Adminstration Routes.
Route::group(array('prefix' => 'admin', 'middleware' => 'auth', 'namespace' => 'Admin', 'where' => array('id'  => '\d+')), function ()
{
    // The Media CRUD.
    Route::get( 'media',                'Attachments@index');
    Route::post('media/update/{field}', 'Attachments@update');
    Route::post('media/delete',         'Attachments@destroy');

    Route::post('media/upload',   'Attachments@upload');
    Route::get( 'media/uploaded', 'Attachments@uploaded');

    // The Blocks positions.
    Route::get( 'blocks', 'Blocks@index');
    Route::post('blocks', 'Blocks@order');

    // The Menus CRUD.
    Route::paginate( 'menus', 'Menus@index');

    Route::post('menus',              'Menus@store');
    Route::post('menus/{id}',         'Menus@update');
    Route::post('menus/{id}/destroy', 'Menus@destroy');

    // The Menu Items CRUD.
    Route::get( 'menus/{id}',                        'MenuItems@index');
    Route::post('menus/{id}/items/{itemId}',         'MenuItems@update');
    Route::post('menus/{id}/items/{itemId}/destroy', 'MenuItems@destroy');

    Route::post('menus/{id}/{mode}', 'MenuItems@store')->where('mode', '(posts|taxonomies|custom)');

    Route::post('menus/{id}/items', 'MenuItems@order');

    // The Comments CRUD.
    Route::get( 'comments',                'Comments@index');
    Route::get( 'comments/{id}',           'Comments@load');
    Route::post('comments/{id}',           'Comments@update');
    Route::post('comments/{id}/destroy',   'Comments@destroy');

    Route::post('comments/{id}/approve',   'Comments@approve');
    Route::post('comments/{id}/unapprove', 'Comments@unapprove');

    // The Posts CRUD.
    Route::get('content/create/{type}',  'Posts@create')->where('type', Posts::routePattern(false));

    //
    Route::get( 'content/{id}/edit',    'Posts@edit');
    Route::post('content/{id}',         'Posts@update');
    Route::post('content/{id}/restore', 'Posts@restore');
    Route::post('content/{id}/destroy', 'Posts@destroy');

    Route::get( 'content/{id}/revisions', 'Posts@revisions');

    Route::post('content/{id}/tags', 'Posts@addTags');

    Route::post('content/{id}/tags/{tagId}/detach', 'Posts@detachTag')->where('tagId', '\d+');

    // The Posts listing.
    Route::get('content/{type}', 'Posts@index')->where('type', Posts::routePattern(true));

    //
    Route::get('taxonomies/{type}/{slug}', 'Posts@taxonomy')->where('type', Taxonomies::routePattern(false));

    // The Taxonomies CRUD.
    Route::post('taxonomies',              'Taxonomies@store');
    Route::post('taxonomies/{id}',         'Taxonomies@update');
    Route::post('taxonomies/{id}/destroy', 'Taxonomies@destroy');

    // For AJAX.
    Route::get('taxonomies/{id}/{parentId}', 'Taxonomies@data')->where('parentId', '\d+');

    // The Taxonomies listings.
    Route::get('taxonomies/{type}', 'Taxonomies@index')->where('type', Taxonomies::routePattern(true));
});
