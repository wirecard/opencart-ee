Feature: CreditCard3DSPurchaseHappyPath
  As a guest  user
  I want to make a purchase with a Credit Card 3DS
  And to see that transaction was successful

  Background:
    Given I prepare checkout
    And I fill fields with "Customer data"
    Then I see "Wirecard Credit Card"

  @ui_test @patch @minor @major
  Scenario: purchase
#    Given I check "I have read and agree to the Terms & Conditions"
    Given I click "Continue"
    When I fill fields with "Valid Credit Card Data"
    And I click "Confirm Order"
    And I am redirected to "Verified" page
    And I enter "wirecard" in field "Password"
    And I click "Continue"
    Then I am redirected to "Order Received" page
    And I see "Your order has been placed!"
