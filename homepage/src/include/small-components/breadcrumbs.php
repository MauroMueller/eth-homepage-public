<?php
/* Load assets */
App::asset_registry()->add_css([
    App::config()->base_url().'/assets/css/small-components/breadcrumbs.css',
]);

/* The mix of php and html here is a bit messy, but if the php opening and
 * closing tags were on the lines they belonged on, there would be extra spaces
 * in the html, messing it up. This is the cleanest solution I found.
 */
return function($page) { ?>
    <nav class="breadcrumbs">
        <?php $breadcrumb_array = App::breadcrumbs()->from($page); ?>
        <?php
            foreach ($breadcrumb_array as $crumb) {
                ?><div class="breadcrumb"><?php

                if (!is_null($crumb->url)) {
                    ?><a class="breadcrumb-content" href="<?php
                        echo htmlspecialchars($crumb->url);
                    ?>"><?php
                } else {
                    ?><span class="breadcrumb-content"><?php
                }

                if (!is_null($crumb->icon)) {
                    ?><i class="fa-regular <?php
                        echo htmlspecialchars($crumb->icon);
                    ?>"></i><?php
                }

                if (!is_null($crumb->icon) && !is_null($crumb->text)) {
                    ?><span> </span><?php
                }

                if (!is_null($crumb->text)) {
                    ?><span><?php
                        echo htmlspecialchars($crumb->text);
                    ?></span><?php
                }

                if (!is_null($crumb->url)) {
                    ?></a><?php
                } else {
        			?></span><?php
                }
                
                ?></div><?php
            }
        ?>
    </nav>
<?php } ?>
