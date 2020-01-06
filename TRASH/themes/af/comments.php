<?php
// Do not load if password protected
if ( post_password_required() ) {
    return;
}

// Paginated comments
$comments_nav = get_the_comments_navigation(array(
            'prev_text' =>'&laquo; Older Comments',
            'next_text' => 'Newer Comments &raquo;',
        ));
?>

<div id="comments" class="comments-area">

    <?php
    if ( have_comments() ) : ?>

        <h2 class="comments-title">Comments</h2>

        <?php echo $comments_nav; ?>

        <ol class="comment-list">
            <?php
                wp_list_comments( array(
                    'style'      => 'ol',
                    'short_ping' => true,
                    'avatar_size' => 32,
                ) );
            ?>
        </ol>

        <?php echo $comments_nav; ?>

        <?php
        if ( !comments_open() ) : ?>
            <div class="callout alert no-comments">Comments are closed.</div>
        <?php
        endif;

    endif;

    comment_form();

    ?>

</div>