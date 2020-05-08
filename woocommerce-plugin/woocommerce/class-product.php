<?php

namespace Pro_Woocommerce\Woocommerce;

class Product {

    private $id;
    private $variants;

    public function __construct(int $post_id = 0){
        $this->id = $post_id;
    }

    /**
     * Creates a product by setting up via the backend.
     * This function normally is just getting called from
     * the rest Endpoint Hook
     */
    public function create($params){
        $insert = $this->create_post_array($params);

        if($params['post_meta']){
            $insert['meta_input'] = $this->create_meta_array($params);
        }

        $result['insert_id'] = wp_insert_post($insert);
        $result['permalink'] = get_permalink($result['insert_id']);


        /**
         * Assign this Product to type variable to be able to handle stocks
         * on variant level
         */

        wp_set_object_terms( $result['insert_id'], 'variable', 'product_type', false );

        return $result;
    }


    public function update($params){
        $update = $this->create_post_array($params);

        if($params['post_meta']){
            $update['meta_input'] = $this->create_meta_array($params);
        }

        $update['ID'] = $this->id;
        $result['insert_id'] = wp_update_post($update);

        return $update;
    }

    /**
     * Gets a specific variation of the variation list
     *
     * @param $post_variant_id
     * @return mixed
     */

    public function get_variant($post_variant_id) {
        if(!$this->variants){
            $this->set_variants();
        }
        return $this->variants[$post_variant_id];
    }

    /**
     * Gets a list of all avlb. product variations
     *
     * @return mixed
     */

    public function get_variants() {
        if(!$this->variants){
            $this->set_variants();
        }
        return $this->variants;
    }

    /**
     * Sets the product variations
     *
     */

    private function set_variants() {
        $this->variants = $this->fetch_variants();
    }

    /**
     * Fetches a list of all avlb. product variations from db
     *
     * @return array
     */

    private function fetch_variants(){

        $args = array(
            'post_type' => 'product_variation',
            'posts_per_page' => -1,
            'post_parent' => $this->id,
            'order' => 'ASC',
            'orderby' => 'menu_order'
        );

        $variants = [];
        $parent = new \WP_Query($args);

        if ($parent->have_posts()) {
            while ($parent->have_posts()) : $parent->the_post();

                $variant_id = get_the_ID();
                $variant = new Variant_Meta($variant_id);
                $variant->fetch();

                $variants[$variant_id] = $variant->get();

            endwhile;
        }

        wp_reset_postdata();

        return $variants;
    }


    /**
     * Create the standard array for creating a ppost
     *
     * @param $params
     * @return array
     */
    private function create_post_array($params) {
        return [
            'post_title' => $params['post_title'],
            'post_type' => 'product',
            'post_status' => $params['post_status'],
        ];
    }


    /**
     * Create the Meta Sub Array for inserting in one shot
     *
     * @param $params
     * @return array
     */
    private function create_meta_array($params) {
        return [
            '_yoast_wpseo_title' => $params['post_meta']['_yoast_wpseo_title'],
            '_yoast_wpseo_metadesc' => $params['post_meta']['_yoast_wpseo_metadesc'],
            '_product_attributes' => $params['post_meta']['attributes'],
        ];
    }

}