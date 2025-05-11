# Bank Statement Imports

This document outlines the process for importing bank statements into the Vibe Finance application. This feature allows users to upload CSV files containing transaction data, review the imported transactions, categorize them, and finalize their inclusion in their financial records.

## Feature Overview

-   **CSV Upload**: Import bank statements in standard CSV format.
-   **Column Mapping**: The system attempts to automatically map CSV columns (like Date, Description, Amount) to the required transaction fields. If mapping is incomplete, users are notified.
-   **Staging Area**: Imported transactions are first loaded into a staging area where they can be reviewed before being finalized.
-   **Duplicate Detection**: The system checks for potential duplicate transactions against already existing finalized transactions based on account, amount, and date proximity.
-   **Transaction Review**: Users can:
    -   Assign categories to staged transactions.
    -   Approve transactions to move them from staging to the main transaction log.
    -   Ignore transactions (e.g., if they are duplicates or irrelevant).
-   **User Feedback**: Clear messages guide the user through the import and review process.

## Importing a Bank Statement

1.  **Navigate to Bank Account**: Go to the specific bank account page for which you want to import a statement.
2.  **Access Import Form**: Click on the "Import Statement" (or similarly named) button/link.
3.  **Upload CSV File**:
    -   Choose the CSV file from your computer.
    -   The CSV file should ideally have headers that the system can recognize (e.g., "Date", "Description", "Amount", "Transaction Type"). Common variations are often handled.
    -   Ensure your CSV includes columns for at least: Transaction Date, Description, and Amount. A 'Type' (Credit/Debit) column is also beneficial.
4.  **Submit for Import**: Upload the file. The system will process it and:
    -   Create a `BankStatementImport` record to track this batch.
    -   Attempt to map the CSV headers.
    -   If mapping is successful, create `StagedTransaction` records.
    -   Notify you of any issues, such as missing essential columns or if all rows failed to parse.

## Reviewing Staged Transactions

After a successful import, you will be directed to the "Review Staged Transactions" page for that bank account (or you can navigate there manually).

On this page, you will see a table of transactions that have been imported but not yet finalized. For each transaction:

-   **Date, Description, Amount, Type**: Displayed as parsed from the CSV.
-   **Category**:
    -   You can select a category from the dropdown. Changing the category will automatically update the staged transaction.
    -   The system might suggest a category based on past transactions or rules (if implemented).
-   **Status**:
    -   **Pending Review**: Default status for newly imported, non-duplicate transactions.
    -   **Potential Duplicate**: If the system detects a similar transaction already in your main transaction log, it will be flagged.
        -   Details of the matched transaction (ID, Date, Description, Amount) will be shown.
        -   The row will be highlighted (e.g., with an orange background).
        -   A "(Dup?)" marker may appear next to the description.
    -   **Imported**: The transaction has been approved and moved to the main log.
    -   **Ignored**: The transaction has been marked to be excluded.
-   **Actions**:
    -   **Approve**:
        -   Finalizes the transaction, moving it from staging to your main transaction log.
        -   The bank account balance will be updated.
        -   The transaction will be associated with the selected category.
    -   **Ignore**:
        -   Marks the transaction as 'ignored'. It will not be imported into your main transaction log.
        -   This is useful for actual duplicates or transactions you don't want to track. The "Ignore" button may appear differently (e.g., orange) for potential duplicates.

### Handling Potential Duplicates

When a transaction is flagged as a "Potential Duplicate":
1.  **Review the Matched Transaction Details**: Compare the staged transaction with the details provided for the existing transaction it potentially matches.
2.  **Decide**:
    -   If it **is a duplicate**, use the "Ignore" button to prevent it from being added to your main transaction log.
    -   If it **is not a duplicate** (i.e., it's a legitimate, separate transaction despite similarities), you can "Approve" it. You may also want to categorize it first.

## CSV File Format and Column Mapping

-   **Essential Columns**:
    -   `Date`: The date of the transaction. Various common date formats are usually parsable (e.g., YYYY-MM-DD, DD/MM/YYYY, MM/DD/YYYY).
    -   `Description`: A textual description of the transaction.
    -   `Amount`: A numerical value for the transaction.
        -   Some banks use a single amount column with negative values for debits/withdrawals and positive for credits/deposits.
        -   Others use separate "Debit" and "Credit" columns. The system tries to infer this.
-   **Optional but Recommended Columns**:
    -   `Type`: A column indicating "Credit", "Debit", "Deposit", "Withdrawal", etc. This helps resolve ambiguity if amounts are all positive.
-   **Automatic Mapping**: The system attempts to map headers like "Transaction Date", "Date Posted", "Memo", "Details", "Withdrawal Amount", "Deposit Amount", "Credit", "Debit" to the internal fields.
-   **Mapping Feedback**: If essential columns cannot be mapped from the CSV headers, the import process will warn you, and transactions might not be staged.

## Troubleshooting

-   **Import Fails / No Transactions Staged**:
    -   Check if your CSV file is correctly formatted and not corrupted.
    -   Ensure your CSV headers are reasonably standard or match what the system expects.
    -   Verify that essential data (date, description, amount) is present for most rows.
-   **Incorrectly Parsed Data**:
    -   Date format issues: Ensure your dates are in a common, unambiguous format.
    -   Amount issues: Check for currency symbols or other non-numeric characters in amount columns that might interfere with parsing (though the system tries to handle common currency symbols).

By following these guidelines, you can efficiently import and manage your bank statements within Vibe Finance.
