<?php
/**
 * Manage bbPress Users.
 *
 * @since 1.0.0
 */
class BBPCLI_Users extends BBPCLI_Component {

	/**
	 * Mark a user's topics and replies as spam
	 *
	 * ## OPTIONS
	 *
	 * <user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp user spam --user-id=465456
	 *    Success: User topics and replies marked as spam.
	 *
	 *    $ wp bbp user unham --user-id=user_login
	 *    Success: User topics and replies marked as spam.
	 *
	 * @alias unham
	 */
	public function spam( $args, $assoc_args ) {
		$user = $this->get_user_id_from_identifier( $args[0] );

		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or id' );
		}

		if ( bbp_make_spam_user( $user->ID ) ) {
			WP_CLI::success( 'User topics and replies marked as spam.' );
		} else {
			WP_CLI::error( 'Could not mark topics and replies as spam.' );
		}
	}

	/**
	 * Mark a user's topics and replies as ham
	 *
	 * ## OPTIONS
	 *
	 * <user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp user ham --user-id=465456
	 *    Success: User topics and replies marked as ham.
	 *
	 *    $ wp bbp user unspam --user-id=user_login
	 *    Success: User topics and replies marked as ham.
	 *
	 * @alias unspam
	 */
	public function ham( $args, $assoc_args ) {
		$user = $this->get_user_id_from_identifier( $args[0] );

		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or id' );
		}

		if ( bbp_make_ham_user( $user->ID ) ) {
			WP_CLI::success( 'User topics and replies marked as ham.' );
		} else {
			WP_CLI::error( 'Could not mark topics and replies as ham.' );
		}
	}

	/**
	 * Set user role
	 *
	 * ## OPTIONS
	 *
	 * --user-id<user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * --role<role>
	 * : Role to set for the member. (keymaster, moderator, participant, spectator, blocked)
	 * ---
	 * Default: participant
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp user set_role --user-id=465456 --role=moderator
	 *    Success: New role for user set: moderator
	 *
	 *    $ wp bbp user set_role --user-id=user_login --role=spectator
	 *    Success: New role for user set: spectator
	 */
	public function set_role( $args, $assoc_args ) {
		$user = $this->get_user_id_from_identifier( $assoc_args['user-id'] );

		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or id' );
		}

		$role = $assoc_args['role'];
		if ( ! in_array( $role, $this->forum_roles(), true ) ) {
			$role = 'participant';
		}

		$retval = bbp_set_user_role( $user->ID, $role );

		if ( is_string( $retval ) ) {
			WP_CLI::success( sprintf( 'New role for user set: %s', $retval ) );
		} else {
			WP_CLI::error( 'Could not set new role for user.' );
		}
	}

	/**
	 * Get URL of the user profile page
	 *
	 * ## OPTIONS
	 *
	 * <user>
	 * : Identifier for the user. Accepts either a user_login or a numeric ID.
	 *
	 * ## EXAMPLES
	 *
	 *    $ wp bbp user permalink --user-id=465456
	 *    Success: User profile page: https://example.com/user-slug
	 *
	 *    $ wp bbp user url --user-id=user_login
	 *    Success: User profile page: https://example.com/user-slug
	 *
	 * @alias url
	 */
	public function permalink( $args, $assoc_args ) {
		$user = $this->get_user_id_from_identifier( $args[0] );

		if ( ! $user ) {
			WP_CLI::error( 'No user found by that username or id' );
		}

		$permalink = bbp_get_user_profile_url( $user->ID );

		if ( is_string( $permalink ) ) {
			WP_CLI::success( sprintf( 'User profile page: %s', $permalink ) );
		} else {
			WP_CLI::error( 'Could not find user profile page.' );
		}
	}

	/**
	 * Forum Roles
	 *
	 * @since 1.0.0
	 *
	 * @return array An array of forum roles.
	 */
	protected function forum_roles() {
		return array( 'keymaster, moderator, participant, spectator, blocked ' );
	}
}

WP_CLI::add_command( 'bbp user', 'BBPCLI_Users' );
