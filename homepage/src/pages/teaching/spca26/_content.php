<?php

$render_page_overview = $render_page_overview ?? include(SRC_DIR.
                        '/include/small-components/page-overview.php');

return function() use ($meta, $page, $render_page_overview) { ?>
    <?php $t = App::lang()->page($page['current_page']); ?>
    <h1><?php echo htmlspecialchars($t['h1']); ?></h1>

    <div class="course-info">
        <div class="inner">
            <label>
                <?php echo htmlspecialchars($t['time']); ?>
            </label>
            <div>
                <?php echo htmlspecialchars(format_event(
                    new DateTimeImmutable('2026-09-16 12:15', new DateTimeZone('Europe/Zurich')),
                    new DateTimeImmutable('2026-09-16 14:00', new DateTimeZone('Europe/Zurich')),
                    show_date: false,
                )); ?>
            </div>

            <label>
                <?php echo htmlspecialchars($t['room']); ?>
            </label>
            <div>
                ETZ H 91
            </div>

            <label>
                <?php echo htmlspecialchars($t['links']); ?>
            </label>
            <a class="right" href="https://moodle-app2.let.ethz.ch/course/view.php?id=29124">
                <?php echo htmlspecialchars($t['moodle']); ?>
            </a>
            <a class="right" href="">
                <?php echo htmlspecialchars($t['gitlab']); ?>
            </a>
            <a class="right" href="https://expert.ethz.ch/enrolled/AS26/spca/exercises">
                <?php echo htmlspecialchars($t['code_expert']); ?>
            </a>
        </div>
    </div>

    <div class="spacer half"></div>

    <?php $render_page_overview(); ?>
<?php } ?>
