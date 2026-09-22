<?php
/* Load assets */
App::asset_registry()->add_css([
    'small-components/file-collection.css',
]);
App::asset_registry()->add_js([
    'small-components/file-collection.js',
]);

function display_file($file, $minimal, $t) { ?>
    <div class="file">
        <?php if ($file->is_link()): ?>
        <div class="link">
            <a target="_blank" href="<?php
                echo htmlspecialchars($file->url);
            ?>" class="main-link">
                <i class="<?php
                    echo htmlspecialchars($file->get_fa_icon());
                ?>"></i>
                <div class="file-info">
                    <span class="file-name">
                        <?php echo htmlspecialchars($file->name); ?>
                    </span>
                    <?php if (!$minimal): ?>
                    <span class="file-modified">
                        <?php
                            echo htmlspecialchars($t['modified']);
                            echo htmlspecialchars(format_timestamp(
                                $file->modified_at,
                                date: 'short',
                            ));
                        ?>
                    </span>
                    <?php endif ?>
                </div>
            </a>
        </div>
        <?php else: ?>
        <form method="post" action="/files/download" class="file-download">
            <input type="hidden" name="file_uuid"
            value="<?php echo htmlspecialchars($file->file_uuid_string); ?>">
            <button type="submit" class="main-link">
                <i class="<?php
                    echo htmlspecialchars($file->get_fa_icon());
                ?>"></i>
                <div class="file-info">
                    <span class="file-name">
                        <?php echo htmlspecialchars($file->name); ?>
                    </span>
                    <?php if (!$minimal): ?>
                    <span class="file-modified">
                        <?php
                            echo htmlspecialchars($t['modified']);
                            echo htmlspecialchars(format_timestamp(
                                $file->modified_at,
                                date: 'short',
                            ));
                        ?>
                    </span>
                    <?php endif ?>
                </div>
            </button>
        </form>
        <?php endif ?>
        <?php if (!$minimal): ?>
            <?php if (App::authorization()
                        ->has_permission('download_private_files')): ?>
            <form method="post" action="/files/toggle-visibility">
                <input type="hidden" name="file_uuid" value="<?php
                    echo htmlspecialchars($file->file_uuid_string);
                ?>">
                <input type="hidden" name="return" value="<?php
                    echo htmlspecialchars($_SERVER['REQUEST_URI']);
                ?>">
                <button type="submit" <?php
                    if (!App::authorization()
                            ->has_permission('modify_file_visibility'))
                        echo 'disabled';
                ?>>
                    <?php $icon = $file->public ? 'fa-eye' : 'fa-eye-slash'; ?>
                    <i class="fa-regular <?php
                        echo htmlspecialchars($icon);
                    ?>"></i>
                </button>
            </form>
            <?php endif ?>
            <?php if (App::authorization()->has_permission('delete_files')): ?>
            <form method="post" action="/files/delete">
                <input type="hidden" name="file_uuid" value="<?php
                    echo htmlspecialchars($file->file_uuid_string);
                ?>">
                <input type="hidden" name="return" value="<?php
                    echo htmlspecialchars($_SERVER['REQUEST_URI']);
                ?>">
                <button type="submit">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </form>
            <?php endif ?>
        <?php endif ?>
    </div>
<?php }

return function($collection_uuid, $minimal = false) { ?>
    <?php $t = App::lang()->component('file-collection'); ?>
    <div class="file-collection <?php echo $minimal ? 'minimal' : ''; ?>">
        <?php
            $files = App::file_service()->collection_files($collection_uuid);
            foreach ($files as $file)
                display_file($file, $minimal, $t);
        ?>

        <?php if (!$minimal): ?>
            <?php if (App::authorization()->has_permission('upload_files')): ?>
            <form
                method="post"
                action="/files/upload"
                enctype="multipart/form-data"
                class="upload-form"
            >
                <input type="hidden" name="collection_uuid"
                    value="<?php echo htmlspecialchars($collection_uuid); ?>">
                <input type="file" name="file" required>
                <label>
                    <input type="checkbox" name="public" checked>
                    <?php echo htmlspecialchars($t['public_label']); ?>
                </label>

                <br>

                <button type="submit">
                    <?php echo htmlspecialchars($t['upload_button']); ?>
                </button>

                <progress 
                    class="upload-progress"
                    value="0"
                    max="100"
                    hidden
                ></progress>
                <span class="upload-progress-percent"></span>
                <span class="upload-status"></span>
            </form>
            <form
                method="post"
                action="/files/add-link"
                class="upload-form"
            >
                <input type="hidden" name="collection_uuid" value="<?php
                    echo htmlspecialchars($collection_uuid);
                ?>">
                <input type="hidden" name="return" value="<?php
                    echo htmlspecialchars($_SERVER['REQUEST_URI']);
                ?>">

                <input type="text" name="name" placeholder="<?php
                    echo htmlspecialchars($t['link_name']);
                ?>" required>
                <input type="text" name="url" placeholder="<?php
                    echo htmlspecialchars($t['link_url']);
                ?>" required>
                
                <br>
                
                <label>
                    <input type="checkbox" name="public" checked>
                    <?php echo htmlspecialchars($t['public_label_link']); ?>
                </label>
                <button type="submit">
                    <?php echo htmlspecialchars($t['link_submit_button']); ?>
                </button>
            </form>
            <?php endif ?>
        <?php endif ?>
    </div>
<?php } ?>
