<?php
/**
 * Controls data/logic for products
 */

class AF_Products extends AF_Theme {
    // Placeholder construct function to prevent re-running parent construct
    public function __construct() {}

    // Get single prdoduct data. Still under construction.
    public function single_product_data($single_product) {
        $thumb_attr = array(
            'class' => "product-thumb",
        );
        $product_thumb = get_the_post_thumbnail( $single_product, '', $thumb_attr );
        if (empty($product_thumb)) {
            $product_thumb = ''; //'<img src="http://placehold.it/300" class="'.$thumb_attr['class'].'">';
        }
        $product_title = $single_product->post_title;
        $product_url = get_permalink( $single_product );
        $product_excerpt = get_the_excerpt( $single_product);
        $product_order_url = get_field('order_form_link', $single_product);
        $product_data = array(
            'product_thumb' => $product_thumb,
            'product_url' => $product_url,
            'product_title' => $product_title,
            'product_excerpt' => $product_excerpt,
            'product_order_url' => $product_order_url
        );
        return $product_data;
    }

    // Get single prdoduct data. Still under construction.
    public function product_excerpt_html($single_product) {
        $product_data = $this->single_product_data($single_product);
        $html .= '
        <div class="column product-grid">
            ' . $product_data['product_thumb'] . '
            <div class="inner">
                <h3><a href="' . $product_data['product_url'] . '">' . $product_data['product_title'] . '</a></h3>
                <p class="article-exerpt">' . $product_data['product_excerpt'] . '</p>
                <p><a href="' . $product_data['product_order_url'] . '" class="button expanded" data-event-category="Buy Now Button">Buy Now</a></p>
                <p><a href="' . $product_data['product_url'] . '" class="button expanded">View More</a></p>
            </div>
        </div>
        ';
        return $html;
    }
}