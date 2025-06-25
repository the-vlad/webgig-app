<?php
defined( 'ABSPATH' ) || exit;

if ( $max_value && $min_value === $max_value ) {
    ?>
    <div class="quantity-wrapper" style="display: flex; align-items: center; gap: 10px;">
        <input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" class="qty" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $min_value ); ?>" />
    </div>
    <?php
} else {
    ?>
    <div class="quantity-wrapper" style="display: flex; align-items: center; gap: 10px;">
        <button type="button" class="qty-btn minus" style="background: none; border: none; cursor: pointer;">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="#000000" d="M19 12.998H5v-2h14z"></path>
            </svg>
        </button>
        <input
            type="number"
            id="<?php echo esc_attr( $input_id ); ?>"
            class="input-text qty text"
            step="<?php echo esc_attr( $step ); ?>"
            min="<?php echo esc_attr( $min_value ); ?>"
            max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>"
            name="<?php echo esc_attr( $input_name ); ?>"
            value="<?php echo esc_attr( $input_value ); ?>"
            title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ); ?>"
            size="4"
            inputmode="<?php echo esc_attr( $inputmode ); ?>" />
        <button type="button" class="qty-btn plus" style="background: none; border: none; cursor: pointer;">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                <path fill="#000000" d="M11 21v-8H3v-2h8V3h2v8h8v2h-8v8z"></path>
            </svg>
        </button>
    </div>
    <?php
}
?>
