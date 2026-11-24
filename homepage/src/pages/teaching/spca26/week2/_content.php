<?php

$render_file_collection = $render_file_collection ?? include(SRC_DIR.
                        '/include/small-components/file-collection.php');

return function() use ($meta, $page, $render_file_collection) { ?>
    <?php $t = App::lang()->page($page['current_page']); ?>
    <h1><?php echo htmlspecialchars($t['h1']); ?></h1>

    <div class="week-info">
        <div class="inner">
            <label>
                <?php echo htmlspecialchars($t['time']); ?>
            </label>
            <div>
                <?php echo htmlspecialchars(format_event(
                    new DateTimeImmutable('2026-09-23 14:15', new DateTimeZone('Europe/Zurich')),
                    new DateTimeImmutable('2026-09-23 16:00', new DateTimeZone('Europe/Zurich')),
                )); ?>
            </div>

            <label>
                <?php echo htmlspecialchars($t['room']); ?>
            </label>
            <div>
                ETZ H 91
            </div>

            <label>
                <?php echo htmlspecialchars($t['topics']); ?>
            </label>
            <div>
                <ul class="aligned">
                    <?php foreach ($t['topics_content'] as $topic): ?>
                    <li>
                        <?php echo htmlspecialchars($topic); ?>
                    </li>
                    <?php endforeach ?>
                </ul>
            </div>

            <label>
                <?php echo htmlspecialchars($t['exercise']); ?>
            </label>
            <div>
                Data Lab
            </div>
        </div>
    </div>

    <div class="spacer half"></div>

    <?php $id = $meta['data']['file_collection_ids']['main']; ?>
    <?php $render_file_collection($id); ?>
<?php } ?>
