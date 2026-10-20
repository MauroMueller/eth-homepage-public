<?php
/* Load assets */
App::asset_registry()->add_css([
    'small-components/poll-collection.css',
]);
App::asset_registry()->add_js([
    'small-components/poll-collection.js',
]);

function display_option($option, $max_votes) { ?>
    <?php $relative_votes = $max_votes != 0
                          ? $option->vote_count / $max_votes * 100
                          : 0 ?>
    <button type="submit" class="option" name="clicked_option"
        value="<?php echo htmlspecialchars($option->id); ?>"
        aria-pressed="<?php echo $option->selected ? 'true' : 'false'; ?>"
        style="--relative-votes: <?php echo htmlspecialchars($relative_votes); ?>%;
               --color: <?php echo htmlspecialchars($option->text_color); ?>;
               --bg: <?php echo htmlspecialchars($option->color); ?>;
               --bg-hover: <?php echo htmlspecialchars($option->hover_color); ?>;
               --outline-selected: <?php echo htmlspecialchars($option->outline_color); ?>;">
        <div class="bar">
            <span class="vote-count"><?php
                echo htmlspecialchars($option->vote_count);
            ?></span>
            <span class="label"><?php
                echo htmlspecialchars($option->label);
            ?></span>
        </div>
        <div class="bar-overflow">
            <span class="label"><?php
                echo htmlspecialchars($option->label);
            ?></span>
        </div>
    </button>
<?php }

function display_poll($poll, $t) { ?>
    <div class="poll">
        <div class="inner">
            <?php $no_special_perms = !App::authorization()
                                          ->has_permission('polls_view_private')
                                   && !App::authorization()
                                          ->has_permission('polls_delete'); ?>
            <div class="info <?php if (is_null($t) && $no_special_perms) echo 'hidden'; ?>">
                <div class="title">
                    <span><?php echo htmlspecialchars($t['title'] ?? ''); ?></span>
                </div>
                <div class="settings">
                    <?php if (App::authorization()->has_permission('polls_view_private')): ?>
                    <form method="post" action="/polls/toggle-visibility">
                        <input type="hidden" name="poll_uuid" value="<?php
                            echo htmlspecialchars($poll->poll_uuid_string);
                        ?>">
                        <input type="hidden" name="return" value="<?php
                            echo htmlspecialchars($_SERVER['REQUEST_URI']);
                        ?>">
                        <button type="submit" <?php
                            if (!App::authorization()->has_permission('polls_modify_visibility'))
                                echo 'disabled';
                        ?>>
                            <?php $icon = $poll->public ? 'fa-eye' : 'fa-eye-slash'; ?>
                            <i class="fa-regular <?php
                                echo htmlspecialchars($icon);
                            ?>"></i>
                        </button>
                    </form>
                    <?php endif ?>
                    <?php if (App::authorization()->has_permission('polls_delete')): ?>
                    <form method="post" action="/polls/delete">
                        <input type="hidden" name="poll_uuid" value="<?php
                            echo htmlspecialchars($poll->poll_uuid_string);
                        ?>">
                        <input type="hidden" name="return" value="<?php
                            echo htmlspecialchars($_SERVER['REQUEST_URI']);
                        ?>">
                        <button type="submit">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </form>
                    <?php endif ?>
                </div>
            </div>
            <form method="post" class="options" action="/polls/update-selection">
                <input type="hidden" name="poll_uuid"
                    value="<?php echo htmlspecialchars($poll->poll_uuid_string); ?>">
                <?php
                    $options = App::poll_repository()->options($poll->poll_uuid_string);
                    $max_votes = max(array_map(
                        fn($option) => $option->vote_count,
                        $options,
                    ));
                    foreach ($options as $option)
                        display_option($option, $max_votes);
                ?>
            </form>
        </div>
    </div>
<?php }

return function($collection_uuid) { ?>
    <?php $t = App::lang()->component('poll'); ?>
    <?php if (App::authorization()->has_permission('polls_view')): ?>
    <div class="poll-collection">
        <?php
            $polls = App::poll_repository()->collection_polls($collection_uuid);
            foreach ($polls as $poll)
                display_poll($poll, $t[$poll->poll_uuid_string] ?? null);
        ?>
    </div>
    <?php endif ?>
<?php } ?>
