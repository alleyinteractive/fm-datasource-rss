<?php
/**
 * RSS Datasource Settings
 */

class Datasource_RSS_Settings {

	private static $instance;

	protected $rss_cache_duration = HOUR_IN_SECONDS;

	protected $items = array();

	protected $checksums = array();

	protected $items_per_feed = 10;

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Datasource_RSS_Settings;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
		fm_register_submenu_page( 'rss_datasource_settings', 'options-general.php', __( 'RSS Feed Datasource Settings', 'fm-datasource-rss' ), __( 'RSS Feeds', 'fm-datasource-rss' ) );
		add_action( 'fm_submenu_rss_datasource_settings', array( $this, 'settings' ) );
		add_filter( 'wp_feed_cache_transient_lifetime', array( $this, 'cache_duration' ), 10, 2 );
	}

	public function settings() {
		$fm = new Fieldmanager_Group( array(
			'name' => 'rss_datasource_settings',
			'children' => array(
				'urls' => new Fieldmanager_Link( array(
					'label' => __( 'Feed URLs', 'fm-datasource-rss' ),
					'one_label_per_item' => false,
					'limit' => 0,
					'extra_elements' => 0,
					'minimum_count' => 1,
					'add_more_label' => __( 'Add another feed', 'fm-datasource-rss' ),
				) ),
			),
		) );
		$fm->activate_submenu_page();
	}

	public function get_urls() {
		$settings = get_option( 'rss_datasource_settings', array() );
		return ! empty( $settings['urls'] ) ? $settings['urls'] : array();
	}

	public function get_feed_items() {
		$urls = $this->get_urls();
		if ( empty( $urls ) ) {
			return new WP_Error( 'rssd-no-feeds', __( 'There are no feeds available to update', 'fm-datasource-rss' ) );
		}

		foreach ( $urls as $url ) {
			$cache_key = 'rssd_' . md5( $url );
			if ( false === ( $contents = get_transient( $cache_key ) ) ) {
				$contents = $this->parse_feed( $url );
				if ( is_wp_error( $contents ) ) {
					// If we hit an error, cache nothing for a minute
					set_transient( $cache_key, array(), MINUTE_IN_SECONDS );
				} else {
					set_transient( $cache_key, $contents, $this->rss_cache_duration );
				}
			}

			$this->items = array_merge( $this->items, $contents );
		}

		usort( $this->items, array( $this, 'sort_items_by_date' ) );

		return $this->items;
	}

	public function parse_feed( $url ) {
		$feed = fetch_feed( $url );
		if ( is_wp_error( $feed ) ) {
			return $feed;
		}

		if ( ! $feed->get_item_quantity() ) {
			$feed->__destruct();
			unset( $feed );
			return new WP_Error( 'rssd-empty-feed', __( 'An error has occurred, which probably means the feed is down. Try again later.', 'fm-datasource-rss' ) );
		}

		$source = strip_tags( $feed->get_title() );

		$return = array();
		foreach ( $feed->get_items( 0, $this->items_per_feed ) as $item ) {
			// Link
			$link = $item->get_link();
			while ( stristr( $link, 'http' ) != $link ) {
				$link = substr( $link, 1 );
			}
			$link = esc_url_raw( strip_tags( $link ) );
			if ( empty( $link ) ) {
				// Skip items without links
				continue;
			}

			// Title
			$title = @htmlspecialchars_decode( trim( strip_tags( $item->get_title() ) ) );
			if ( empty( $title ) ) {
				$title = __( 'Untitled' );
			}

			// Summary
			$summary = @htmlspecialchars_decode( $item->get_description() );
			$summary = wp_trim_words( $summary, 55, ' [&hellip;]' );
			if ( '[...]' == substr( $summary, -5 ) ) {
				// Change existing [...] to [&hellip;].
				$summary = substr( $summary, 0, -5 ) . '[&hellip;]';
			}

			// Date
			$date = $item->get_date( 'U' );
			$date_formatted = $date ? date_i18n( get_option( 'date_format' ), $date ) : '';

			// Author
			$author = $item->get_author();
			if ( is_object( $author ) ) {
				$author = $author->get_name();
				$author = strip_tags( $author );
			}

			$return[ $link ] = compact( 'source', 'link', 'title', 'date', 'date_formatted', 'summary', 'author' );
		}

		return $return;
	}

	/**
	 * usort function to sort items by date desc.
	 *
	 * @param  array $a
	 * @param  array $b
	 * @return int
	 */
	public function sort_items_by_date( $a, $b ) {
		if ( $a['date'] == $b['date'] ) {
		    return 0;
		}

		return ( $a['date'] > $b['date'] ) ? -1 : 1;
	}

	/**
	 * Modify the default cache duration time for our feeds.
	 *
	 * @param  int $duration Number of seconds to cache.
	 * @param  string $url Feed URL.
	 * @return int Filtered cache duration.
	 */
	public function cache_duration( $duration, $url ) {
		if ( in_array( $url, $this->get_urls() ) ) {
			return $this->rss_cache_duration;
		}

		return $duration;
	}
}

function Datasource_RSS_Settings() {
	return Datasource_RSS_Settings::instance();
}
Datasource_RSS_Settings();