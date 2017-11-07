Feature: Manage bbPress Users

  Scenario: User CRUD operations
    Given a bbPress install

    When I try `wp user get bogus-user`
    Then the return code should be 1
    And STDOUT should be empty

    When I run `wp user create testuser2 testuser2@example.com --first_name=test --last_name=user --role=author --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {USER_ID}

    When I run `wp bbp user set_role --user-id={USER_ID} --role=moderator`
    Then STDOUT should contain:
      """
      Success: New role for user set: moderator
      """
