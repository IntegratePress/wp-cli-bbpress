<?php
/**
 * Manage bbPress Engagements.
 *
 * @since 1.0.0
 */
class BBPCLI_Engagement extends BBPCLI_Component {

	/**
	 * Add a topic to user's engagements.
	 *
	 * ## OPTIONS
	 *
	 * --user-id=<user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * --topic-id=<topic-id>
	 * : Identifier for the topic.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp engagement add --user-id=5465 --topic-id=65476
	 *    Success: Engagement successfully added.
	 *
	 *    $ wp bbp engagement add --user-id=user_test --topic-id=354354
	 *    Success: Engagement successfully added.
	 *
	 * @alias create
	 */
	public function add( $args, $assoc_args ) {
		// Check if user exists.
		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );
		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or ID.' );
		}

		// Check if topic exists.
		$topic_id = $assoc_args['topic-id'];
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		// True if added.
		if ( bbp_add_user_engagement( $user->ID, $topic_id ) ) {
			WP_CLI::success( 'Engagement successfully  added.' );
		} else {
			WP_CLI::error( 'Could not add the engagement.' );
		}
	}

	/**
	 * Remove a topic from user's engagements.
	 *
	 * ## OPTIONS
	 *
	 * --user-id=<user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * --topic-id=<topic-id>
	 * : Identifier for the topic.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp engagement remove --user-id=5465 --topic-id=65476
	 *    Success: Engagement successfully removed.
	 *
	 *    $ wp bbp engagement remove --user-id=user_test --topic-id=28468
	 *    Success: Engagement successfully removed.
	 *
	 * @alias delete
	 */
	public function remove( $args, $assoc_args ) {
		// Check if user exists.
		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );
		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or ID.' );
		}

		// Check if topic exists.
		$topic_id = $assoc_args['topic-id'];
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		// True if removed.
		if ( bbp_remove_user_engagement( $user->ID, $topic_id ) ) {
			WP_CLI::success( 'Engagement successfully removed.' );
		} else {
			WP_CLI::error( 'Could not remove engagement.' );
		}
	}

	/**
	 * List the users who have engaged in a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>
	 * : Identifier for the topic.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp bbp subscription list_users --object-id=242
	 *     165465 654564 1161 313
	 *
	 *     $ wp bbp subscription list_users --object-id=45765 --type=another-type
	 *     54564 465465 65465
	 */
	public function list_users( $args, $assoc_args ) {
		$topic_id = $args[0];

		// Check if topic exists.
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		$ids = bbp_get_topic_engagements( $topic_id );

		if ( ! $ids ) {
			echo implode( ' ', $ids ); // WPCS: XSS ok.
		} else {
			WP_CLI::error( 'Could not find any users.' );
		}
	}

	/**
	 * List user's topics engagements.
	 *
	 * ## OPTIONS
	 *
	 * <user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - ids
	 *   - count
	 *   - json
	 *   - haml
	 * ---
	 *
	 * ## EXAMPLE
	 *
	 *     $ wp bbp favorite list_topics 456
	 *     $ wp bbp favorite list_topics 456546 --format=ids
	 */
	public function list_topics( $args, $assoc_args ) {
		$formatter = $this->get_formatter( $assoc_args );

		// Check if user exists.
		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );
		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or ID.' );
		}

		$query_args = array(
			'meta_query' => array( // WPCS: slow query ok.
				array(
					'key'     => '_bbp_engagement',
					'value'   => $user->ID,
					'compare' => 'NUMERIC',
				),
			),
		);

		$topics = bbp_get_user_engagements( $query_args );

		if ( 'ids' === $formatter->format ) {
			echo implode( ' ', wp_list_pluck( $topics->posts, 'ID' ) ); // WPCS: XSS ok.
		} elseif ( 'count' === $formatter->format ) {
			$formatter->display_items( $topics->posts );
		} else {
			$formatter->display_items( $topics->posts );
		}
	}

	/**
	 * Recalculate all of the users who have engaged in a topic.
	 *
	 * ## OPTIONS
	 *
	 * <topic-id>
	 * : Identifier for the topic.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp engagement recalculate 132
	 *    Success: Engagement successfully recalculated.
	 */
	public function recalculate( $args, $assoc_args ) {
		// Check if topic exists.
		$topic_id = $assoc_args['topic-id'];
		if ( ! bbp_is_topic( $topic_id ) ) {
			WP_CLI::error( 'No topic found by that ID.' );
		}

		if ( bbp_recalculate_topic_engagements( $topic_id, true ) ) {
			WP_CLI::success( 'Engagements successfully recalculated.' );
		} else {
			WP_CLI::error( 'Could not recalculate engagements.' );
		}
	}
}

WP_CLI::add_command( 'bbp engagement', 'BBPCLI_Engagement' );
