<?php
/* Load assets */
App::asset_registry()->add_css([
    App::config()->base_url().'/assets/css/small-components/page-overview.css',
]);

$render_file_collection = $render_file_collection ?? include(SRC_DIR.
                        '/include/small-components/file-collection.php');

function display_subpage($node, $t, $render_file_collection) { ?>
    <div class="element">
        <div class="head">
            <?php
                if (!is_null($node->url)) {
                    ?><a class="title" href="<?php
                        echo htmlspecialchars($node->url);
                    ?>"><?php
                } else {
                    ?><span class="title"><?php
                }

                if (!is_null($node->icon)) {
                    ?><i class="fa-regular <?php
                        echo htmlspecialchars($node->icon);
                    ?>"></i><?php
                }

                if (!is_null($node->icon) && !is_null($node->text)) {
                    ?><span> </span><?php
                }

                if (!is_null($node->text)) {
                    ?><span><?php
                        echo htmlspecialchars($node->text);
                    ?></span><?php
                }

                if (!is_null($node->url)) {
                    ?></a><?php
                } else {
        			?></span><?php
                }
            ?>
            <div class="content">
                <?php if (!is_null($node->topics) && !empty($node->topics)): ?>
                    <label>
                        <?php echo htmlspecialchars($t['topics']); ?>
                    </label>
                    <div class="topics">
                        <ul class="aligned">
                            <?php foreach ($node->topics as $topic): ?>
                            <li>
                                <?php echo htmlspecialchars($topic); ?>
                            </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif ?>
    
                <?php $id = $node->file_collection_id; ?>
                <?php $files = App::file_service()->collection_files($id); ?>
                <?php if (!is_null($id) && !empty($files)): ?>
                    <label>
                         <?php echo htmlspecialchars($t['files']); ?>
                    </label>
                    <div>
                        <?php $render_file_collection(
                            $id,
                            minimal: true,
                        ); ?>
                    </div>
                <?php endif ?>
            </div>
        </div>
        <div class="children">
            <?php
                foreach ($node->children as $child)
                    display_subpage($child, $render_file_collection);
            ?>
        </div>
    </div>
<?php }

return function($deep = false) use ($page, $render_file_collection) { ?>
    <?php $t = App::lang()->component('page-overview'); ?>
    <div class="page-overview">
        <?php
            $trees = App::page_overview()
                        ->from($page['current_page'], deep: $deep);
            foreach ($trees as $tree)
                display_subpage($tree, $t, $render_file_collection);
        ?>
    </div>
<?php } ?>
