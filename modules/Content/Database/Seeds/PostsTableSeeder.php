<?php

namespace Modules\Content\Database\Seeds;

use Nova\Database\ORM\Model;
use Nova\Database\Seeder;

use Modules\Content\Models\Post;
use Modules\Content\Models\Taxonomy;


class PostsTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate the table before seeding.
        Post::truncate();

        //
        // The Archives Block.
        $post = Post::create(array(
            'id'             => 1,
            'author_id'      => 1,
            'content'        => null,
            'title'          => 'Archives',
            'name'           => 'block-archives',
            'excerpt'        => null,
            'status'         => 'publish',
            'menu_order'     => 1,
            'type'           => 'block',
            'guid'           => site_url('1'),
            'comment_status' => 'closed',
        ));

        // Setup the Metadata.
        $post->saveMeta(array(
            'block_show_title'        => 1,
            'block_visibility_mode'   => 'show',
            'block_visibility_path'   => '',
            'block_visibility_filter' => 'any',
            'block_widget_position'   => 'content-posts-sidebar',

            'block_handler_class' => 'Modules\Content\Blocks\Archives',
            'block_handler_param' => null,
        ));

        //
        // The Categories Block.
        $post = Post::create(array(
            'id'             => 2,
            'author_id'      => 1,
            'content'        => null,
            'title'          => 'Categories',
            'name'           => 'block-categories',
            'excerpt'        => null,
            'status'         => 'publish',
            'menu_order'     => 2,
            'type'           => 'block',
            'guid'           => site_url('2'),
            'comment_status' => 'closed',
        ));

        // Setup the Metadata.
        $post->saveMeta(array(
            'block_show_title'        => 1,
            'block_visibility_mode'   => 'show',
            'block_visibility_path'   => '',
            'block_visibility_filter' => 'any',
            'block_widget_position'   => 'content-posts-sidebar',

            'block_handler_class' => 'Modules\Content\Blocks\Categories',
            'block_handler_param' => null,
        ));

        //
        // The Search Block.
        $post = Post::create(array(
            'id'             => 3,
            'author_id'      => 1,
            'content'        => null,
            'title'          => 'Search',
            'name'           => 'block-search',
            'excerpt'        => null,
            'status'         => 'publish',
            'menu_order'     => 0,
            'type'           => 'block',
            'guid'           => site_url('3'),
            'comment_status' => 'closed',
        ));

        // Setup the Metadata.
        $post->saveMeta(array(
            'block_show_title'        => 0,
            'block_visibility_mode'   => 'show',
            'block_visibility_path'   => '',
            'block_visibility_filter' => 'any',
            'block_widget_position'   => 'content-posts-sidebar',

            'block_handler_class' => 'Modules\Content\Blocks\Search',
            'block_handler_param' => null,
        ));

        //
        // The sample Post.
        $post = Post::create(array(
            'id'             => 4,
            'author_id'      => 1,
            'content'        => 'Welcome to Nova Framework. This is your first post. Edit or delete it, then start writing!',
            'title'          => 'Hello world!',
            'name'           => 'hello-world',
            'excerpt'        => null,
            'status'         => 'publish',
            'type'           => 'post',
            'guid'           => site_url('sample-post'),
            'comment_status' => 'open',
        ));

        $post->taxonomies()->sync(array(1, 3));

        $post->taxonomies->each(function ($taxonomy)
        {
            $taxonomy->updateCount();
        });

        //
        // The sample Page.
        $post = Post::create(array(
            'id'             => 5,
            'author_id'      => 1,
            'content'        => 'This is an example page. It\'s different from a blog post because it will stay in one place and will show up in your site navigation (in most themes). Most people start with an About page that introduces them to potential site visitors. It might say something like this:

<blockquote>Hi there! I\'m a bike messenger by day, aspiring actor by night, and this is my website. I live in Los Angeles, have a great dog named Jack, and I like pi&#241;a coladas. (And gettin\' caught in the rain.)</blockquote>

...or something like this:

<blockquote>The XYZ Doohickey Company was founded in 1971, and has been providing quality doohickeys to the public ever since. Located in Gotham City, XYZ employs over 2,000 people and does all kinds of awesome things for the Gotham community.</blockquote>

As a new Nova user, you should go to <a href="' .site_url('admin') . '">your dashboard</a> to delete this page and create new pages for your content. Have fun!',

            'title'          => 'Sample Page',
            'name'           => 'sample-page',
            'excerpt'        => null,
            'status'         => 'publish',
            'type'           => 'page',
            'guid'           => site_url('sample-page'),
            'comment_status' => 'closed',
        ));

        $post->saveMeta('page_template', 'default');

        //$post->taxonomies()->attach(2);

        $post->taxonomies->each(function ($taxonomy)
        {
            $taxonomy->updateCount();
        });

        //
        // The sample MenuItems.

        $taxonomy = Taxonomy::where('taxonomy', 'nav_menu')->whereHas('term', function ($query)
        {
            $query->where('slug', 'main-menu');

        })->firstOrFail();

        $post = Post::create(array(
            'id'             => 6,
            'author_id'      => 1,
            'content'        => null,
            'title'          => $name = 'Frontpage',
            'name'           => '6',
            'excerpt'        => null,
            'status'         => 'publish',
            'menu_order'     => 0,
            'type'           => 'nav_menu_item',
            'guid'           => site_url('6'),
            'comment_status' => 'closed',
        ));

        // Setup the Metadata.
        $post->saveMeta(array(
            'menu_item_type'             => 'custom',
            'menu_item_menu_item_parent' => 0,
            'menu_item_object'           => 'custom',
            'menu_item_object_id'        => $post->id,
            'menu_item_target'           => null,
            'menu_item_url'              => site_url(),
        ));

        $post->taxonomies()->attach($taxonomy);

        //
        $post = Post::create(array(
            'id'             => 7,
            'author_id'      => 1,
            'content'        => null,
            //'title'          => 'Sample Page',
            'name'           => '7',
            'excerpt'        => null,
            'status'         => 'publish',
            'menu_order'     => 7,
            'type'           => 'nav_menu_item',
            'guid'           => site_url('7'),
            'comment_status' => 'closed',
        ));

        // Setup the Metadata.
        $post->saveMeta(array(
            'menu_item_type'             => 'page',
            'menu_item_menu_item_parent' => 0,
            'menu_item_object'           => 'page',
            'menu_item_object_id'        => 5,
            'menu_item_target'           => null,
            'menu_item_url'              => null,
        ));

        $post->taxonomies()->attach($taxonomy);

        // Update the Taxonomy count.
        $taxonomy->updateCount();
    }
}
