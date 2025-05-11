# Managing Bank and Cash Accounts

This guide explains how to manage your bank accounts, credit cards, and petty cash within Vibe Finance.

## Overview

You can add, view, edit, and delete various types of financial accounts. Each account will track its balance based on the transactions recorded.

## Viewing Your Accounts

-   **Access**: Navigate to the "Bank Accounts" section from the main dashboard or navigation menu.
-   **Information Displayed**: The list typically shows:
    -   Account Name
    -   Account Type (Bank Account, Credit Card, Cash)
    -   Account Number (partial, for reference)
    -   Current Balance

## Adding a New Account

1.  **Navigation**: From the "Bank Accounts" list page, click the "Add New Account" (or similarly named) button.
2.  **Account Details Form**:
    -   **Account Name**: Provide a clear and descriptive name (e.g., "NAB Cheque Account", "Business Visa Card", "Office Petty Cash").
    -   **Account Type**: Select the appropriate type from the dropdown: "Bank Account", "Credit Card", or "Cash".
    -   **Account Number**: (Optional for cash accounts) Enter the full account number.
    -   **BSB**: (Primarily for Australian bank accounts, optional otherwise) Enter the BSB number.
    -   **Opening Balance**: Enter the balance of the account as of the date you are adding it to Vibe Finance. For credit cards, this might be a negative value if there's an outstanding balance.
    -   **Opening Balance Date**: Select the date for which the opening balance is effective. This is crucial for correct historical tracking.
3.  **Saving**: Click "Save" or "Create Account". The new account will appear in your list.

## Editing an Account

1.  **Navigation**: From the "Bank Accounts" list, find the account you wish to edit. There will typically be an "Edit" button or icon associated with each account.
2.  **Modifiable Details**: You can usually update details such as:
    -   Account Name
    -   Account Number
    -   BSB
    -   _Note_: Modifying the 'Account Type' or 'Opening Balance'/'Opening Balance Date' after transactions have been recorded can have significant implications on your financial records and reporting. The system might restrict these changes or require careful confirmation.
3.  **Saving Changes**: Click "Save" or "Update Account" after making your modifications.

## Deleting an Account

1.  **Navigation**: In the "Bank Accounts" list, find the account you wish to delete. There will be a "Delete" button or icon.
2.  **Confirmation**: You will be asked to confirm the deletion (e.g., "Are you sure you want to delete this account? This action cannot be undone.").
3.  **Impact of Deletion**:
    -   Be cautious. If an account has transactions linked to it, deleting the account might be prevented, or it might lead to those transactions becoming unlinked or archived. Understand the current system behavior before deleting accounts with history.
    -   Typically, accounts with a zero balance and no recent activity are safer to delete if they are truly no longer in use.

## Recording Deposits

Deposits increase the balance of your bank or cash accounts.

1.  **Navigation**: Go to the specific bank account's detail page or find an "Add Deposit" / "Record Deposit" option, often available directly from the account list or the account's view page.
2.  **Deposit Details Form**:
    -   **Date**: The date the deposit was made or cleared in your account.
    -   **Amount**: The numerical value of the deposit.
    -   **Description/Reference**: Provide meaningful details (e.g., "Customer Payment - INV001", "Cash Sale 2025-05-10", "Owner Contribution").
    -   **Category**: (Optional) If the deposit isn't directly from a sales invoice (e.g., an owner investment, a refund received), you might categorize it here.
3.  **Saving**: Submitting the form will create a new transaction record, and the respective account's balance will be updated.

## Recording Withdrawals

Withdrawals decrease the balance of your bank or cash accounts (or increase the balance owing on a credit card).

1.  **Navigation**: Similar to deposits, find an "Add Withdrawal" / "Record Withdrawal" option for the specific account.
2.  **Withdrawal Details Form**:
    -   **Date**: The date the withdrawal was made or cleared.
    -   **Amount**: The numerical value of the withdrawal.
    -   **Description/Reference**: Provide meaningful details (e.g., "Office Supplies - Officeworks", "ATM Withdrawal", "Monthly Software Subscription").
    -   **Category**: Select an appropriate expense category for the withdrawal. This is important for expense tracking and reporting.
3.  **Saving**: This creates a new transaction record, and the account's balance is updated accordingly.

## Account Balances

-   The balance displayed for each account is dynamically calculated based on its 'Opening Balance' and all subsequent 'Transactions' (deposits, withdrawals, and approved transactions from bank statement imports).
-   Regularly reconciling your Vibe Finance account balances with your actual bank statements is recommended to ensure accuracy.
