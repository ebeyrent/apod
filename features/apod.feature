@api @apod
Feature: Apod feature

  Background:
    Given the 'apod' module is enabled
    And users:
      | name                | mail                 | roles         |
      | 00admin             | 00admin@foo.com      | administrator |
      | Dries Buytaert      | dries@buytaert.com   |               |

  Scenario: Ensure as an admin user, I can configure the Door Signs module.
    Given I am logged in as a user with the "administrator" role
    When I visit "/admin/config/media/apod"
    And I fill in "Coz2GoIC0BTKh39KVeNZZNevUGGwsBA3UEOFbHuY" for "NASA API Key"
    And I press the "Save configuration" button
    Then I should see the success message "The configuration options have been saved."

  Scenario: Ensure as a logged in user, I can see the APOD block in the first sidebar.
    Given I am logged in as "Dries Buytaert"
    When I am on the homepage
    Then I should see the "div#block-astronomypictureofthedayblock" element in the "sidebar_first" region








