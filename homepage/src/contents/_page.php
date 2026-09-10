<?php
return function() use ($page) { ?>
    <?php $t = App::lang()->page($page['current_page']); ?>
    <h1><?php echo htmlspecialchars($t['h1']); ?></h1>
    <p>
        <?php
            echo htmlspecialchars($t['p_1']);
            ?><a class="text-link" href="/teaching/testcourse"><?php
                echo htmlspecialchars($t['link']);
            ?></a><?php
            echo htmlspecialchars($t['p_2']);
        ?>
    </p>
<?php } ?>
