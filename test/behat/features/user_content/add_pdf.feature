@javascript @core @blocktype @blocktype_pdf
Feature: Adding a pdf to a page
    As a student
    I need to be able to add a pdf block to my portfolio
    and check it is visible on the page

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01| user | UserA |

Scenario:
    # Logging in as a user
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    # Configuring the block
    When I click on the add block button
    And I click on "Add" in the "Add new block" "Blocks" property
    And I click on blocktype "PDF"
    And I fill in the following:
    | Block title | Mahara about PDF |

    And I click on "File" in the "#instconf" "css_element"
    And I attach the file "mahara_about.pdf" to "File"
    And I click on "Save" in the "Submission" "Modal" property
    And I display the page
    Then I should see "Mahara about PDF"
