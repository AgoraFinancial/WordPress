<main class="content-wrapper">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <article class="publication-single">
                <?php if(!$pub_folded) {
                    echo $content;
                } else {
                    echo 'This publication is no longer available.';
                }?>
            </article>
        </div>
    </div>
</main>