<?php

class WC_Trackdesk_Utils {
	const INPUT_NAME_FORM_ACTION = 'wc_trackdesk_form_action';

	public static function get_requested_value( $key, $default = '' ) {
		$value = $default;

		if ( isset( $_REQUEST[ $key ] ) && is_string( $_REQUEST[ $key ] ) ) {
			$value = sanitize_text_field( trim( $_REQUEST[ $key ] ) );
		}

		return $value;
	}

	public static function get_requested_textarea_value( $key, $default = '' ) {
		$value = $default;

		if ( isset( $_REQUEST[ $key ] ) && is_string( $_REQUEST[ $key ] ) ) {
			$value = sanitize_textarea_field( trim( $_REQUEST[ $key ] ) );
		}

		return $value;
	}

	public static function form_action_input( $value ): void {
		echo '<input name="' . self::INPUT_NAME_FORM_ACTION . '" type="hidden" value="' . esc_attr( $value ) . '" />';
	}

	public static function is_current_form_action( $form_action ): bool {
		return WC_Trackdesk_Utils::get_requested_value( WC_Trackdesk_Utils::INPUT_NAME_FORM_ACTION ) === $form_action;
	}

	public static function is_current_page( $page_id ): bool {
		return WC_Trackdesk_Utils::get_requested_value( 'page' ) === $page_id;
	}

	public static function is_wc_subscription_activated(): bool {
		return class_exists( 'WC_Subscription' );
	}

	public static function get_subscription_external_cid( $subscription_id ): string {
		return "woo-sub-" . $subscription_id;
	}

	public static function get_conversion_amount_from_order( WC_Order $order ): float {
		return $order->get_total() - $order->get_shipping_total();
	}

	public static function get_array_value( array $array, string $key, $default ) {
		return array_key_exists( $key, $array )
			? $array[ $key ]
			: $default;
	}
}
