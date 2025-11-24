<?php

if(!function_exists('bookory_dokan_sold_store')){
    function bookory_dokan_sold_store(){
        global $product;
        $vendor       = dokan_get_vendor_by_product( $product );
        if(!$vendor->id){
            return;
        }
        $store_info   = $vendor->get_shop_info();
        ?>
        <div class="sold-by-meta">
            <span class="sold-by-label"><?php esc_html_e( 'Sold By:', 'bookory' ); ?> </span>
            <a href="<?php echo esc_attr( $vendor->get_shop_url() ); ?>"><?php echo esc_html( $store_info['store_name'] ); ?></a>
        </div>
        <?php
    }
}

if (!function_exists('bookory_add_vendor_info_on_product_single_page')) {
    function bookory_add_vendor_info_on_product_single_page() {
        global $product;
        $vendor       = dokan_get_vendor_by_product( $product );
        if(!$vendor->id){
            return;
        }
        $store_info   = $vendor->get_shop_info();
        $store_rating = $vendor->get_rating();
        $show_vendor_info = dokan_get_option( 'show_vendor_info', 'dokan_appearance', 'off' );
        if ( 'on' === $show_vendor_info ) {
            ?>
            <div class="dokan-vendor-info-wrap">
                <div class="dokan-vendor-image">
                    <img src="<?php echo esc_url( $vendor->get_avatar() ); ?>" alt="<?php echo esc_attr( $store_info['store_name'] ); ?>">
                </div>
                <div class="dokan-vendor-info">
                    <div class="dokan-vendor-name">
                        <h5><?php echo esc_html( $store_info['store_name'] ); ?></h5>
                        <?php apply_filters( 'dokan_product_single_after_store_name', $vendor ); ?>
                    </div>
                    <div class="dokan-vendor-rating">
                        <?php echo wp_kses_post( dokan_generate_ratings( $store_rating['rating'], 5 ) ); ?>
                    </div>
                    <a class="dokan-button-vendor" href="<?php echo esc_attr( $vendor->get_shop_url() ); ?>">
                        <?php echo esc_html__('View Vendor', 'bookory')?>
                    </a>
                </div>
            </div>
            <?php
        }
        else{
            return;
        }
    }
}