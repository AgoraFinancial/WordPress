<?php
// Get FAQ categories

// Get all faq-category terms under "Publications" to remove from display
$exclude = get_term_by('slug', 'publications', 'faq-category');
$exclude = $exclude->term_id;

$terms = get_terms(array(
    'taxonomy'  => 'faq-category',
    'exclude_tree'   => $exclude,
));

if ($terms) {
?>
<h2>Help Categories</h2>
<ul class="no-bullet faq-category-list">
    <?php foreach ($terms as $term) { ?>
    <li>
        <a href="<?php echo get_term_link($term->term_id); ?>">
            <?php echo $term->name; ?>
        </a>
    </li>
    <?php } ?>
</ul>
<?php
}