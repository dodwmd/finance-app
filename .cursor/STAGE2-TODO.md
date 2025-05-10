# Vibe Finance - Phase 2: Business Accounting Features

This document outlines the features and requirements for expanding Vibe Finance into a comprehensive accounting tool for Australian businesses and individuals. We will treat major sections as Epics and sub-features as User Stories/Tasks.

## General Requirements (Applicable to all new modules/Epics)

These requirements should be considered and implemented for each Epic and its associated User Stories/Tasks.

*   **User Interface (UI):** Ensure clean, intuitive, and responsive design consistent with the existing Vibe Finance application.
*   **User Experience (UX):** Implement streamlined workflows, clear navigation, and helpful tooltips/guidance.
*   **Authentication & Authorization:** Leverage existing Laravel Sanctum authentication. Ensure appropriate user roles and permissions for accessing and managing different modules (e.g., admin, employee, accountant).
*   **Validation:** Implement robust input validation for all forms to ensure data integrity.
*   **Error Handling:** Provide clear and user-friendly error messages.
*   **Data Storage:** Ensure efficient and secure data storage using the existing database (SQLite or MySQL as configured).
*   **Reporting:** Implement basic reporting capabilities within each module (e.g., list views with filtering and sorting). Advanced reporting will be handled in the dedicated "Reports" module.
*   **Audit Trails:** Track key actions and changes for accountability (e.g., creation, modification, deletion of records).
*   **Australian Localization:**
    *   Currency: Set AUD ($) by default.
    *   Date Formats: Use DD/MM/YYYY.
    *   GST (Goods and Services Tax): Implement functionality to track and calculate GST (10%) on relevant transactions.
    *   ABN (Australian Business Number): Add fields for storing and validating ABNs for businesses and customers.
    *   TFN (Tax File Number): Implement secure handling for employee TFNs.
*   **API Endpoints:** Create RESTful API endpoints for each new module, consistent with the existing API structure (Memory ID: 2cd80478-2f8f-4fbe-beac-be92efd7bf46).
*   **Testing:** Develop comprehensive unit and feature tests (including repository tests - Memory IDs: 9234c3a1-a4f2-4118-9b48-e2c7aabc0633, 76fa8dd7-442e-4285-9dbe-96a86f2be5df) and Dusk browser tests (Memory ID: 368460ac-d3bf-47c7-b280-61187c740533) for all new functionalities. Ensure code linting and quality standards are met.
*   **Documentation:** Update API.md and create user-facing documentation/help guides as needed.

## Module Specific Requirements (Epics & User Stories)

### Epic 1: Bank and Cash Accounts
*Purpose: Manage all business bank accounts, credit cards, and petty cash.*

- [x] **Story:** As a user, I want to add new bank/cash accounts, specifying name, type (bank, credit card, cash), account number, BSB, and opening balance.
- [x] **Story:** As a user, I want to edit existing bank/cash account details.
- [x] **Story:** As a user, I want to delete bank/cash accounts (with appropriate warnings/checks).
    - [x] Implement `destroy` method in `BankAccountController`.
    - [x] Add delete button/link to `index.blade.php` with a confirmation dialog.
    - [x] Ensure only the owner can delete their bank account.
    - [x] Feature tests for deleting bank accounts (guest access, owner access, non-owner access denied, successful deletion, confirmation of deletion).
    - [x] Consider implications of deleting an account with transactions (e.g., soft delete, disallow if transactions exist, archive option - for now, basic delete is fine, mark for later review).
- [ ] **Story:** As a user, I want to view a list of all my accounts with their current balances.
- [ ] **Story:** As a user, I want to record deposits made into an account, linking them to the Transactions module.
- [ ] **Story:** As a user, I want to record withdrawals made from an account, linking them to the Transactions module.
- [ ] **Story:** As a user, I want to import bank statements (CSV, QIF, OFX) to assist with reconciliation.
- [ ] **Story:** As a user, I want to match imported transactions with existing transactions in the system.
- [ ] **Story:** As a user, I want to mark an account as the primary/default account for certain operations.
- [ ] **Story:** As a user, I want to track undeposited funds separately.
- [ ] **NFR Check:** Ensure all General and Non-Functional Requirements are met for this Epic (see 'General Requirements' section for details).

### Epic 2: Receipts
*Purpose: Record and manage proofs of purchase for expenses.*

- [ ] **Story:** As a user, I want to upload receipt images (JPEG, PNG) or PDF documents.
- [ ] **Story:** As a user, I want to manually enter receipt details: vendor, date, total amount, GST component, description, and expense category.
- [ ] **Story:** As a user, I want to link an uploaded/entered receipt to a specific expense transaction.
- [ ] **Story:** As a user, I want to link an uploaded/entered receipt to an expense claim.
- [ ] **Story:** As a user, I want to search for receipts by vendor, date range, or amount.
- [ ] **Story:** As a user, I want to filter my list of receipts.
- [ ] **(Stretch Goal) Story:** As a user, I want the system to attempt OCR on uploaded receipts to pre-fill details.
- [ ] **NFR Check:** Ensure all General and Non-Functional Requirements are met for this Epic.

### Epic 3: Payments
*Purpose: Record payments made by the business (e.g., to suppliers, for bills).*

- [ ] **Story:** As a user, I want to create new payment records, specifying payee, date, amount, payment method (bank transfer, credit card, cash), reference number, and notes.
- [ ] **Story:** As a user, I want to link a payment to a specific bill or supplier invoice (if/when Accounts Payable exists).
- [ ] **Story:** As a user, I want to categorize payments (e.g., utilities, rent, supplies).
- [ ] **Story:** As a user, I want to track the GST paid component of a payment.
- [ ] **Story:** As a user, I want to generate a summary or report of payments made over a period.
- [ ] **(Stretch Goal) Story:** As a user, I want to process batch payments to multiple payees.
- [ ] **NFR Check:** Ensure all General and Non-Functional Requirements are met for this Epic.

### Epic 4: Expense Claims
*Purpose: Allow employees or business owners to claim reimbursement for business-related expenses.*

- [ ] **Story:** As an employee/user, I want to submit a new expense claim, including date, description, and a list of individual expense items (with amounts and categories).
- [ ] **Story:** As an employee/user, I want to attach receipts to each individual expense item within my claim.
- [ ] **Story:** As a manager/admin, I want to review submitted expense claims.
- [ ] **Story:** As a manager/admin, I want to approve or reject expense claims, with an option to add comments.
- [ ] **Story:** As an employee/user, I want to track the status of my submitted expense claims (e.g., submitted, approved, rejected, reimbursed).
- [ ] **Story:** As an admin/finance user, I want to record the reimbursement details for an approved claim (date, method), linking to the Payments module.
- [ ] **NFR Check:** Ensure all General and Non-Functional Requirements are met for this Epic.

### Epic 5: Customers
*Purpose: Manage customer information for sales and invoicing.*

- [ ] **Story:** As a user, I want to add new customer records, including name, ABN, contact person, email, phone, billing address, shipping address, and notes.
- [ ] **Story:** As a user, I want to edit existing customer details.
- [ ] **Story:** As a user, I want to delete customer records (with appropriate warnings/checks, e.g., if they have invoices).
- [ ] **Story:** As a user, I want to view a list of all my customers.
- [ ] **Story:** As a user, I want to search and filter my customer list (e.g., by name, ABN).
- [ ] **Story:** As a user, I want to view the sales history for a specific customer (linked to Sales Invoices).
- [ ] **Story:** As a user, I want to set a default GST status (e.g., GST applicable, GST-free) for a customer.
- [ ] **(Stretch Goal) Story:** As a user, I want to group customers into categories.
- [ ] **NFR Check:** Ensure all General and Non-Functional Requirements are met for this Epic.

### Epic 6: Sales Invoices
*Purpose: Create and manage invoices for goods or services provided to customers.*

- [ ] **Story:** As a user, I want to create a new sales invoice, including my business details, customer details, an invoice number, date, and due date.
- [ ] **Story:** As a user, I want invoice numbers to be auto-incrementing or allow manual input with checks for uniqueness.
- [ ] **Story:** As a user, I want to add line items to an invoice, specifying description, quantity, unit price, and GST applicability.
- [ ] **Story:** As a user, I want the invoice to automatically calculate GST per line item and the total GST amount for the invoice.
- [ ] **Story:** As a user, I want to include my business ABN and logo on the invoice.
- [ ] **Story:** As a user, I want to save an invoice as a draft.
- [ ] **Story:** As a user, I want to send an invoice to a customer via email, with the invoice attached as a PDF.
- [ ] **Story:** As a user, I want to mark an invoice as paid.
- [ ] **Story:** As a user, I want to track the status of my invoices (draft, sent, paid, overdue).
- [ ] **Story:** As a user, I want to record payments received against an invoice, linking to Bank/Cash Accounts.
- [ ] **Story:** As a user, I want to generate sales reports by customer, product/service, or date range.
- [ ] **(Stretch Goal) Story:** As a user, I want to set up recurring invoices for regular billing.
- [ ] **(Stretch Goal) Story:** As a user, I want to create credit notes for customers.
- [ ] **NFR Check:** Ensure all General and Non-Functional Requirements are met for this Epic.

### Epic 7: Billable Time
*Purpose: Track time spent on projects or tasks for invoicing customers.*

- [ ] **Story:** As a user, I want to record time entries, specifying employee/user, customer, project/task, date, hours worked, and an optional hourly rate.
- [ ] **Story:** As a user, I want to add a description to my time entries.
- [ ] **Story:** As a user, I want to mark time entries as billable or non-billable.
- [ ] **Story:** As a user, I want a simple timer (start/stop) to track time spent on a task.
- [ ] **Story:** As a user, I want to generate a sales invoice from selected billable time entries, pulling customer and rate information.
- [ ] **Story:** As a user, I want to report on billable vs. non-billable hours per project or customer.
- [ ] **(Stretch Goal) Story:** As a user, I want to set different default hourly rates for different tasks or employees.
- [ ] **NFR Check:** Ensure all General and Non-Functional Requirements are met for this Epic.

### Epic 8: Employees
*Purpose: Manage employee information for payroll and HR.*

- [ ] **Story:** As an admin, I want to add new employee records, including name, contact details, address, TFN, date of birth, employment start date, and employment type (full-time, part-time, casual).
- [ ] **Story:** As an admin, I want to set a pay rate/salary for each employee.
- [ ] **Story:** As an admin, I want to securely store employee TFNs (ensuring encryption at rest and restricted access).
- [ ] **Story:** As an admin, I want to edit existing employee details.
- [ ] **Story:** As an admin, I want to deactivate/archive employee records (e.g., upon termination).
- [ ] **Story:** As an admin, I want to store employee bank account details for payroll processing.
- [ ] **Story:** As an admin, I want to define standard pay items (e.g., ordinary hours, overtime rates, common allowances, standard deductions).
- [ ] **(Stretch Goal) Story:** As an admin, I want to track employee leave entitlements (annual leave, sick leave) and balances.
- [ ] **NFR Check:** Ensure all General and Non-Functional Requirements are met for this Epic.

### Epic 9: Payslips
*Purpose: Generate payslips for employees and manage payroll processing.*

- [ ] **Story:** As an admin, I want to calculate gross pay for an employee based on their rate/salary and hours worked (if applicable).
- [ ] **Story:** As an admin, I want the system to calculate PAYG withholding tax based on current ATO tax tables for each employee.
- [ ] **Story:** As an admin, I want the system to calculate superannuation guarantee contributions (e.g., 11% of Ordinary Time Earnings, check current rate and rules).
- [ ] **Story:** As an admin, I want to include other pay items (allowances, deductions) in the payslip calculation.
- [ ] **Story:** As an admin, I want to generate a payslip document (printable/PDF) for an employee, showing all required details (employer ABN, employee name, pay period, gross pay, tax, super, net pay, YTD figures).
- [ ] **Story:** As an admin, I want to record a payroll run for a specific pay period.
- [ ] **Story:** As an admin, I want to be able to email payslips to employees.
- [ ] **Story:** As an admin, I want to link salary payments to the Payments module or Bank/Cash Accounts.
- [ ] **(Major Stretch Goal / Future Phase) Story:** As an admin, I want to integrate with Single Touch Payroll (STP) for reporting to the ATO.
- [ ] **NFR Check:** Ensure all General and Non-Functional Requirements are met for this Epic.

### Epic 10: Journal Entries
*Purpose: Allow for manual adjustments and recording of non-standard transactions (e.g., accruals, depreciation, error corrections) using double-entry bookkeeping principles.*

- [ ] **Story:** As an accountant/admin, I want to create manual journal entries, specifying date, description, and multiple lines for debit and credit accounts with their respective amounts.
- [ ] **Story:** As an accountant/admin, I want the system to ensure that total debits equal total credits for each journal entry before saving.
- [ ] **Story:** As an accountant/admin, I want to select accounts from the Chart of Accounts for each line of the journal entry.
- [ ] **Story:** As an accountant/admin, I want to view a history of all journal entries.
- [ ] **Story:** As an accountant/admin, I want the ability to reverse a previously posted journal entry.
- [ ] **NFR Check:** Ensure all General and Non-Functional Requirements are met for this Epic.

### Epic 11: Reports
*Purpose: Provide comprehensive financial reports for business analysis and compliance.*

- [ ] **Story:** As a user, I want to generate a Profit and Loss (Income Statement) report for a selected period, showing revenue, COGS (if applicable), gross profit, operating expenses, and net profit/loss.
- [ ] **Story:** As a user, I want to generate a Balance Sheet report as of a specific date, showing assets, liabilities, and equity.
- [ ] **Story:** As a user, I want to generate a Cash Flow Statement report for a selected period, tracking cash inflows and outflows from operating, investing, and financing activities.
- [ ] **Story:** As a user, I want to generate a GST Report (for BAS preparation) summarizing GST collected and GST paid for a reporting period.
- [ ] **Story:** As a user, I want to generate an Aged Receivables report showing outstanding customer invoices and their aging.
- [ ] **Story:** As a user, I want to generate an Aged Payables report showing outstanding supplier bills and their aging (if/when Accounts Payable is implemented).
- [ ] **Story:** As a user, I want to generate a Sales Tax Report (could be part of GST report or separate).
- [ ] **Story:** As a user, I want to generate a Payroll Summary report detailing payroll expenses over a period.
- [ ] **Story:** As a user, I want to be able to filter reports by date range and other relevant criteria (e.g., department, if applicable).
- [ ] **Story:** As a user, I want to export generated reports to PDF format.
- [ ] **Story:** As a user, I want to export generated reports to CSV format.
- [x] **Story:** As a user/admin, I want to manage a customizable Chart of Accounts (COA), <!-- pre-filled with a standard Australian COA, --> and be able to add, edit, or disable accounts. (Basic management implemented; pre-fill is future enhancement. See Foundation task for API/Unit tests).
- [ ] **NFR Check:** Ensure all General and Non-Functional Requirements are met for this Epic (see 'General Requirements' section for details).

## Next Steps Priority (Checklist Format)

1.  **Foundation:**
    - [x] Define and implement a basic Chart of Accounts (COA) - (This is the core of Epic 11's last story, but foundational for many others. It should be prioritized as an early task within Epic 11 or as a standalone foundational task).
        - [x] Implement CRUD functionality (Controller, Models, Views, Requests).
        - [x] Implement Dusk browser tests for COA management.
        - [x] Ensure PHPStan static analysis passes for COA code.
        - [ ] Implement Unit and Feature tests for `ChartOfAccountController` and related components.
        - [ ] Implement RESTful API endpoints for COA management.
        - [ ] (Optional/Future) Pre-fill with a more "standard Australian COA" in seeder.
        - [ ] (Consider) Implement Audit Trails for COA changes.
        - [ ] (Consider) Update API.md if/when API endpoints are added.
    - [ ] Implement the Bank and Cash Accounts module (Epic 1).
2.  **Core Transactions:**
    - [ ] Implement Receipts module (Epic 2).
    - [ ] Implement Payments module (Epic 3).
3.  **Sales Cycle:**
    - [ ] Implement Customers module (Epic 5).
    - [ ] Implement Sales Invoices module (Epic 6).
4.  **Expenses:**
    - [ ] Implement Expense Claims module (Epic 4).
5.  **Payroll (Complex - may need further breakdown):**
    - [ ] Implement Employees module (Epic 8).
    - [ ] Implement Payslips module (Epic 9) (initial version, manual PAYG/Super calculations if tables are complex to integrate immediately).
6.  **Accounting Engine:**
    - [ ] Implement Journal Entries module (Epic 10) (essential for accountants and manual adjustments).
7.  **Time Tracking:**
    - [ ] Implement Billable Time module (Epic 7).
8.  **Reporting:**
    - [ ] Develop the Reports module (Epic 11) incrementally, starting with P&L, Balance Sheet, and GST Report (after COA is established).

This `TODO.md` will serve as a living document and will be updated as development progresses and new requirements or clarifications emerge.
