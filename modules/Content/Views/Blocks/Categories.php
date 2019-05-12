<?php foreach ($categories as $category) { ?>
<div style="padding: 10px 0 10px 0; border-bottom: 1px solid #eee;">
    <a class="pull-left" href="<?= site_url('category/' .$category->slug); ?>"><?= $category->name; ?></a> <span class="pull-right"><?= $category->count; ?></span>
    <div class="clearfix"></div>
</div>
<?php } ?>
