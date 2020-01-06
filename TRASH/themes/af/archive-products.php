<?php
class AF_Archive_Products {

    public function __construct() {
        $this->build_template();
    }

    // Put together the various components to assemble the homepage
    public function build_template() {
        global $af_theme;

        $af_theme->header_html();
        $af_theme->top_header();
        $this->main_content();
        $af_theme->footer_html();
    }

    private function get_category_data() {
        $cat_data = array();

        // Loop through the rows of data
        while (have_rows('categories')) {
            the_row();

            // Display a sub field value
            array_push($cat_data, get_sub_field('category'));
        }
        return $cat_data;
    }

    private function get_product_archives() {
        global $wp_query, $af_theme;

        $html = '';
        foreach ($wp_query->posts as $single_post) {
            $html .= $af_theme->product_excerpt_html($single_post);
        }
        return $html;
    }


    public function main_content() {
        global $wp_query;
?>
        <!--Masthead-->
        <section class="main-masthead-wrapper">
            <div class="row">
                <div class="small-12 columns">
                    <h1><?php echo $wp_query->queried_object->label; ?></h1>
                </div>
            </div>
        </section>
        <!--END Masthead-->
        <!--Main Content Area-->
        <main class="content-wrapper">
            <!--SINGLE EXCERPT-->
            <div class="row small-up-1 medium-up-3 large-up-4">
                <?php echo $this->get_product_archives(); ?>
            </div>
            <div class="row single-article-excerpt-wrapper">
                <div class="small-12 centered columns">
                    <?php echo wp_pagenavi(array('echo' => false)); ?>
                </div>
            </div>
            <!--END SINGLE EXCERPT -->
        </main>
        <!--END Main Content Area-->
<?php
    }
}
new AF_Archive_Products;