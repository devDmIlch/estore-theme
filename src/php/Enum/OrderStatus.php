<?php
/**
 * Order status enumerator class
 *
 * @package estore/theme
 * @since 0.0.1
 */

namespace EStore\Enum;

enum OrderStatus: int {

	// Enumerator cases.
	case undefined = 0;
	case payment_pending = 1;
	case payment_complete = 2;
	case order_complete = 3;
	case requested_cancellation = 9;
	case cancelled_by_user = 10;
	case cancelled_by_admin = 20;

	/**
	 * Returns a name of the status.
	 *
	 * @param ?OrderStatus $status Enumerator value.
	 *
	 * @return string name of a status.
	 **/
	public static function get_order_status_title( ?OrderStatus $status ): string {
		return match ( $status ) {
			self::payment_pending        => __( 'Очікується Оплата', 'estore-theme' ),
			self::payment_complete       => __( 'Сплачено', 'estore-theme' ),
			self::order_complete         => __( 'Завершено', 'estore-theme' ),
			self::requested_cancellation => __( 'Відправлений запит на скасування', 'estore-theme' ),
			self::cancelled_by_user      => __( 'Скасовано Користувачем', 'estore-theme' ),
			self::cancelled_by_admin     => __( 'Скасовано Магазином', 'estore-theme' ),
			default                      => __( 'Без статусу', 'estore-theme' ),
		};
	}
}
