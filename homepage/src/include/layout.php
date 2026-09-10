<?php
/**
 * Main layout of all pages.
 * 
 * This file can be included in any php (using
 * include($SRC_DIR.'/include/layout.php');) and manages the layout of the page
 * (headers, css files, js files, top bar, side bar, etc.). Make sure to first
 * define $SRC_DIR, then require config.php. Also define $page as an array
 * containing all infos used by this file. Possible values:
 * 
 *  $page = [
 *      // Current page identifier needed e.g. for highlighting in side bar
 *      'current_page' => 'somepage',
 *      
 *      // Path to the php file with the contents for this page (set by default
 *      // using current_page unless specified)
 *      'content_path' => '/path/to/page/contents',
 *      
 *      // Flag for using the default title format (Mauro Müller – {title}), if
 *      // false, just uses {title} (defaults to true)
 *      'title_default' => true,
 *      
 *      // Title of the page, as set by the html <title> tag. Modified unless
 *      // title_default is false. Leave empty to get title from lang files.
 *      'title' => 'Some Page',
 *      
 *      // If false, doesn't load the default stylesheets (defaults to true)
 *      'css_default' => true,
 *      
 *      // Additional stylesheets to be loaded
 *      'css_additional' => [
 *          '/path/to/some/css,
 *          '/path/to/some/other/css,
 *      ],
 *      
 *      // If false, doesn't load the default scripts (defaults to true)
 *      'js_default' => true,
 *      
 *      // Additional scripts to be loaded
 *      'js_additional' => [
 *          '/path/to/some/js,
 *          '/path/to/some/other/js,
 *      ],
 *      
 *      // If false, doesn't show the top bar (defaults to true)
 *      'topbar' => true,
 *      
 *      // If false, doesn't show the side bar (defaults to true)
 *      'sidebar' => true,
 * 
 *      // Favicon iconset (defaults to 'default')
 *      'icons' => 'default',
 *  ];
 */

/*
 * Parse options from $page
 */

// Current page
$current_page = $page['current_page'];

// Content path
$content_path = $SRC_DIR.'/contents/'.$current_page;
if (is_dir($content_path)) $content_path .= '/_page';
$content_path .= '.php';
if (isset($page['content_path'])) $content_path = $page['content_path'];

// Title
$title = $page['title'] ?? App::lang()->page($current_page)['title'];
if ($page['title_default'] ?? true)
    $title = 'Mauro Müller' . (($title != '') ? ' – ' : '') . $title;

// CSS & JS
if (!isset($page['css_default']) || $page['css_default'])
    App::asset_registry()->add_css([
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css',
        App::config()->base_url().'/assets/css/style.css',
    ]);
if (isset($page['css_additional'])) App::asset_registry()->add_css($page['css_additional']);

if (!isset($page['js_default']) || $page['js_default'])
    App::asset_registry()->add_js([
        App::config()->base_url().'/assets/js/script.js',
    ]);
if (isset($page['js_additional'])) App::asset_registry()->add_js($page['js_additional']);

// Top/side bar
$topbar = true;
if (isset($page['topbar']) && !$page['topbar']) $topbar = false;

$sidebar = true;
if (isset($page['sidebar']) && !$page['sidebar']) $sidebar = false;

$iconset = $page['icons'] ?? 'default';
$icon_url = App::config()->base_url() . '/assets/icons/' . $iconset;

/* Load used phps */
$render_content = include($content_path);
$render_topbar = $topbar ? include($SRC_DIR.'/include/topbar.php')
                            : function() {};
$render_sidebar = $sidebar ? include($SRC_DIR.'/include/sidebar.php')
                            : function() {};
?>

<!DOCTYPE html>

<html class="no-transition"> <!-- class removed by script.js after loading -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo htmlspecialchars($title); ?></title>

    <link rel="icon" type="image/svg+xml"
        href="<?php echo htmlspecialchars($icon_url . '/icon.svg'); ?>">
    <link rel="icon" type="image/x-icon"
        href="<?php echo htmlspecialchars($icon_url . '/favicon.ico'); ?>">
    <link rel="apple-touch-icon" sizes="180x180"
        href="<?php echo htmlspecialchars($icon_url . '/icon-180.png'); ?>">
    <link rel="icon" type="image/png" sizes="192x192"
        href="<?php echo htmlspecialchars($icon_url . '/icon-192.png'); ?>">
    <link rel="icon" type="image/png" sizes="512x512"
        href="<?php echo htmlspecialchars($icon_url . '/icon-512.png'); ?>">

    <?php
        App::asset_registry()->css_tags();
        App::asset_registry()->js_tags();
    ?>
</head>

<body>

<?php $render_topbar(); ?>
<?php $render_sidebar(); ?>

<div class="content">
    <?php $render_content(); ?>
</div>

</body>

</html>
