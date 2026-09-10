<?php
/* Load assets */
App::asset_registry()->add_css([
    App::config()->base_url().'/assets/css/small-components/file-collection.css',
]);
App::asset_registry()->add_js([
    App::config()->base_url().'/assets/js/small-components/file-collection.js',
]);

function get_fa_icon($mime_type, $filename) {
    $icons = [
        'application/pdf' => 'fa-file-pdf',

        'application/msword' => 'fa-file-word',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' =>
            'fa-file-word',
        'application/vnd.ms-excel' => 'fa-file-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' =>
            'fa-file-excel',
        'application/vnd.ms-powerpoint' => 'fa-file-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' =>
            'fa-file-powerpoint',

        'application/zip' => 'fa-file-zipper',
        'application/x-rar-compressed' => 'fa-file-zipper',
        'application/vnd.rar' => 'fa-file-zipper',
        'application/x-7z-compressed' => 'fa-file-zipper',
        'application/gzip' => 'fa-file-zipper',
        'application/x-tar' => 'fa-file-zipper',

        'text/csv' => 'fa-file-csv',

        'text/markdown' => 'fa-file-code',
        'text/html' => 'fa-file-code',
        'text/css' => 'fa-file-code',
        'text/javascript' => 'fa-file-code',
        'application/javascript' => 'fa-file-code',
        'application/json' => 'fa-file-code',
        'application/xml' => 'fa-file-code',
        'text/xml' => 'fa-file-code',
        'text/x-php' => 'fa-file-code',
        'text/x-python' => 'fa-file-code',
        'text/x-java-source' => 'fa-file-code',
        'text/x-c' => 'fa-file-code',
        'text/x-c++' => 'fa-file-code',
        'text/x-csharp' => 'fa-file-code',
        'text/x-shellscript' => 'fa-file-code',
        'application/x-sh' => 'fa-file-code',
        'text/x-sql' => 'fa-file-code',
        'application/sql' => 'fa-file-code',
        'text/yaml' => 'fa-file-code',
        'application/yaml' => 'fa-file-code',
    ];
    if (isset($icons[$mime_type])) return $icons[$mime_type];

    if (str_starts_with($mime_type, 'image/')) return 'fa-file-image';
    if (str_starts_with($mime_type, 'audio/')) return 'fa-file-audio';
    if (str_starts_with($mime_type, 'video/')) return 'fa-file-video';

    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $code_extensions = [
        'php', 'php3', 'php4', 'php5', 'phtml',
        'js', 'jsx', 'ts', 'tsx',
        'html', 'htm', 'css', 'scss', 'sass', 'less',
        'py', 'pyw',
        'java', 'kt', 'kts',
        'c', 'h', 'cpp', 'cc', 'cxx', 'hpp',
        'cs',
        'go',
        'rs',
        'swift',
        'rb',
        'pl', 'pm',
        'sh', 'bash', 'zsh', 'fish',
        'sql',
        'json', 'json5',
        'xml', 'xsl', 'xslt',
        'yaml', 'yml',
        'toml',
        'md', 'markdown',
        'vue',
        'svelte',
        'graphql', 'gql',
    ];
    if (in_array($extension, $code_extensions, true)) return 'fa-file-code';

    $text_extensions = [
        'txt', 'log', 'ini', 'conf', 'cfg',
        'env', 'properties',
    ];
    if (in_array($extension, $text_extensions, true)) return 'fa-file-lines';

    if (str_starts_with($mime_type, 'text/')) return 'fa-file-lines';

    return 'fa-file';
}

function display_file($file) { ?>
    <div class="file">
        <form method="post" action="/files/download" class="file-download">
            <input type="hidden" name="file_uuid"
            value="<?php echo htmlspecialchars($file->file_uuid_string); ?>">
            <button type="submit" class="file-download-button">
                <?php $icon = get_fa_icon($file->mime_type, $file->name); ?>
                <i class="fa-regular <?php
                    echo htmlspecialchars($icon);
                ?>"></i>
                <span class="file-name">
                    <?php echo htmlspecialchars($file->name); ?>
                </span>
            </button>
        </form>
        <?php if (App::authorization()
                     ->has_permission('download_private_files')): ?>
        <form method="post" action="/files/toggle-visibility">
            <input type="hidden" name="file_uuid"
            value="<?php echo htmlspecialchars($file->file_uuid_string); ?>">
            <button type="submit">
                <?php $icon = $file->public ? 'fa-eye' : 'fa-eye-slash'; ?>
                <i class="fa-regular <?php
                    echo htmlspecialchars($icon);
                ?>"></i>
            </button>
        </form>
        <?php endif ?>
        <?php if (App::authorization()->has_permission('delete_files')): ?>
        <form method="post" action="/files/delete">
            <input type="hidden" name="file_uuid"
            value="<?php echo htmlspecialchars($file->file_uuid_string); ?>">
            <button type="submit">
                <i class="fa-regular fa-trash-can"></i>
            </button>
        </form>
        <?php endif ?>
    </div>
<?php }

return function($collection_uuid) { ?>
    <div class="file-collection">
        <?php
            $files = App::file_service()->collection_files($collection_uuid);
            foreach ($files as $file)
                display_file($file);
        ?>

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
                Make this file public
            </label>

            <br>

            <button type="submit">Upload</button>

            <progress 
                class="upload-progress"
                value="0"
                max="100"
                hidden
            ></progress>
            <span class="upload-progress-percent"></span>
            <span class="upload-status"></span>
        </form>
        <?php endif ?>
    </div>
<?php } ?>
