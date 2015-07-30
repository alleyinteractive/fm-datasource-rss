<?php

/**
 * RSS Datasource
 */
class Custom_Datasource_RSS extends Fieldmanager_Datasource {

	/**
	 * Force ajax.
	 *
	 * @var boolean
	 */
	public $use_ajax = true;

	/**
	 * Get an display value by stored value.
	 *
	 * @param mixed $value The stored value.
	 * @return string What appears in the field (e.g. the <option> text node or
	 *                the text in an autocomplete field).
	 */
	public function get_value( $value ) {
		$value = json_decode( $value, true );
		return isset( $value['title'], $value['source'] ) ? sprintf( _x( '%s (%s)', 'rss item title and source', 'fm-datasource-rss' ), $value['title'], $value['source'] ) : '';
	}

	/**
	 * Get items for the field.
	 *
	 * @param string $fragment Optional. Search fragment.
	 * @return array {value} => {text for display}
	 */
	public function get_items( $fragment = null ) {
		$items = Datasource_RSS_Settings()->get_feed_items();
		if ( empty( $items ) ) {
			return $items;
		}

		foreach ( $items as $item ) {
			$value = json_encode( $item );
			if ( $fragment && false === stripos( $value, $fragment ) ) {
				continue;
			}
			$ret[ $value ] = sprintf( _x( '%s (%s)', 'rss item title and source', 'fm-datasource-rss' ), $item['title'], $item['source'] );
		}

		return $ret;
	}

	/**
	 * Get view link. Currently disabled.
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function get_view_link( $value ) {
		return isset( $value['link'] ) ? $value['link'] : '';
	}

	/**
	 * Get edit link. Not possible, so disabled.
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function get_edit_link( $value ) {
		return '';
	}
}