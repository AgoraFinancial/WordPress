<?php
// Work in progress as of 11/6/17
class AF_Single_Product {
    private $product_thumb;
    private $product_title;
    private $product_url;
    private $product_content;
    private $product_excerpt;
    private $product_order_url;
    private $product_faqs;
    private $product_testimonials;

    public function __construct() {
        global $af_theme, $post;

        $thumb_attr = array('class' => 'article-thumb large');

        $this->product_thumb = get_the_post_thumbnail( $post, '', $thumb_attr );
        $this->product_title = $post->post_title;
        $this->product_url = get_permalink( $post );
        $this->product_content = apply_filters('the_content', get_post_field('post_content', $post->ID));
        $this->product_excerpt = $post->post_excerpt;
        $this->product_order_url = get_field('order_form_link', $post->ID);
        $this->product_faqs = get_field('faqs');
        $this->product_testimonials = get_field('testimonials');
        $this->build_template();
    }

    // Put together the various components to assemble the homepage
    public function build_template() {
        global $af_templates;

        $af_templates->af_head();
        $af_templates->af_header();
        $af_templates->af_breadcrumbs();
        $this->main_content();
        $af_templates->af_footer();

        // $af_theme->header_html();
        // $af_theme->top_header();
        // $this->breadcrumbs();
        // $this->main_content();
        // $af_theme->footer_html();
    }

    public function main_content() {
        global $post;
?>
        <!--END Main Content Area-->
        <section class="main-content-wrapper">
            <div class="row">
                <div class="small-12 medium-4 large-3 columns">
                    <?php echo $this->product_thumb; ?>
                </div>
                <div class="small-12 medium-8 large-9 article-excerpt-wrapper columns">
                    <h1><?php echo $this->product_title; ?></h1>
                    <p class="article-exerpt"><?php echo $this->product_excerpt; ?></p>
                    <p><a target="_blank" href="<?php echo $this->product_order_url; ?>" class="button large">Buy Now</a></p>
                </div>
            </div>
            <div class="row">
                <?php echo $this->product_tabs_html(); ?>
            </div>
            <div class="row">
                <div class="small-12 medium-4 large-3 columns">
                    <?php echo $this->product_thumb; ?>
                </div>
                <div class="small-12 medium-8 large-9 article-excerpt-wrapper columns">
                    <h1>Try <?php echo $this->product_title; ?> Today!</h1>
                    <p><?php echo $this->product_excerpt; ?></p>
                    <p><a target="_blank" href="<?php echo $this->product_order_url; ?>" class="button large">Buy Now</a></p>
                </div>
            </div>
        </section>
        <!--END Main Content Area-->
<?php
    }

    private function product_tabs_html() {
        $tabs_key = 1;

        // Tabbed navigation html
        $tabs_nav_html = '<ul class="tabs" data-tabs id="example-tabs">';
        $tabs_nav_html .= '<li class="tabs-title is-active"><a href="#panel'.$tabs_key.'" aria-selected="true">Details</a></li>';

        // Content of the tabs
        $tabs_content_html = '<div class="tabs-content" data-tabs-content="example-tabs">';
        $tabs_content_html .= '<div class="tabs-panel is-active" id="panel'.$tabs_key.'">';
        $tabs_content_html .= $this->product_content;
        $tabs_content_html .= '</div>';

        if(!empty($this->product_faqs)) {
            $tabs_key++;
            $tabs_nav_html .= '<li class="tabs-title"><a href="#panel'.$tabs_key.'">FAQ</a></li>';
            $tabs_content_html .= '<div class="tabs-panel" id="panel'.$tabs_key.'">';

            foreach($this->product_faqs as $product_faq) {
                $tabs_content_html .= '<div class="single-faq">';
                     $tabs_content_html .= '<h3>'.$product_faq['faq_title'].'</h3>';
                     $tabs_content_html .= $product_faq['faq_content'];
                $tabs_content_html .= '</div>';
            }
            $tabs_content_html .= '</div>';
        }

        if(!empty($this->product_testimonials)) {
            $tabs_key++;
            $tabs_nav_html .= '<li class="tabs-title"><a href="#panel'.$tabs_key.'">Customer Reviews</a></li>';
            $tabs_content_html .= '<div class="tabs-panel" id="panel'.$tabs_key.'">';

            foreach($this->product_testimonials as $product_testimonial) {
                $tabs_content_html .= '<div class="single-testimonial">';
                $tabs_content_html .= $product_testimonial['testimonial_content'];
                $tabs_content_html .= $product_testimonial['testimonial_byline'];
                $tabs_content_html .= '</div>';

            }
            $tabs_content_html .= '</div>';
        }

        // Close out the tabs html nav and content
        $tabs_nav_html .= '</ul>';
        $tabs_content_html .= '</div>';

        $final_html = $tabs_nav_html;
        $final_html .= $tabs_content_html;

        return $final_html;
    }

    public function breadcrumbs() {
?>
        <!--Breadcrumbs-->
        <section class="main-breadcrumb-wrapper">
            <div class="row">
                <div class="small-12 columns">
                    <nav aria-label="You are here:" role="navigation">
                      <ul class="breadcrumbs">
                        <li><a href="/">Home</a></li>
                        <li><a href="/products">Products</a></li>
                      </ul>
                    </nav>
                </div>
            </div>
        </section>
        <!--END Breadcrumbs-->
<?php
    }
}
new AF_Single_Product;