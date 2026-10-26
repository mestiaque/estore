
# MASTER DEVELOPMENT PROMPT

## Laravel Universal Retail / Store Management Package

### Extend Existing Metheme Package

You are a senior Laravel architect, package developer, database architect, POS/ERP engineer, and QA engineer.

Your task is to build a **production-grade, reusable, highly configurable Universal Retail & Store Management Package for Laravel**.

This package will be installed into and extend an existing Laravel application/framework called **Metheme**.

The package must NOT be designed for only one type of business.

It must be generic enough to support:

* Grocery / মুদি দোকান
* Pharmacy / Medical shop
* Furniture shop
* Hardware shop
* Computer shop
* Electronics shop
* Mobile shop
* Cosmetics shop
* Clothing shop
* General retail shop
* Wholesale shop
* Multi-purpose stores

The architecture must be modular, extensible, maintainable, testable, and suitable for future development.

---

# 1. FIRST RULE — INSPECT THE EXISTING PROJECT BEFORE CODING

Before writing or modifying any code:

1. Inspect the complete existing project structure.
2. Identify Laravel version.
3. Identify PHP version.
4. Identify existing Metheme package structure.
5. Identify:

   * Models
   * Controllers
   * Services
   * Actions
   * Repositories
   * Traits
   * Events
   * Listeners
   * Policies
   * Permissions
   * Roles
   * Routes
   * Views
   * Components
   * Layouts
   * Database conventions
   * Naming conventions
   * Existing package architecture
   * Existing User model
   * Existing authentication system
   * Existing settings system
   * Existing notification system
   * Existing media/file system
   * Existing menu/sidebar system
   * Existing permission system
   * Existing localization system
6. Do NOT assume Metheme's architecture.
7. Reuse existing Metheme functionality wherever appropriate.
8. Do NOT duplicate functionality already provided by Metheme.
9. Do NOT replace existing Metheme functionality unless absolutely necessary.
10. Maintain backward compatibility with the existing project.
11. Do not create conflicting model/table/class names.

Before implementation, create an internal architecture map of the existing project and use it as the foundation for this package.

---

# 2. CORE OBJECTIVE

Build a reusable package tentatively named:

`Retail`

or, if the existing project has a package naming convention, follow that convention.

The package must provide a complete retail/store management system.

The package must be modular.

The architecture should conceptually look like:

Metheme
|
+-- Retail Core
|
+-- Store / Branch
+-- Warehouse
+-- Product
+-- Category
+-- Brand
+-- Unit
+-- Barcode
+-- Inventory
+-- Purchase
+-- Supplier
+-- Sales / POS
+-- Customer
+-- Returns
+-- Discount
+-- Payment
+-- Loyalty / Points
+-- Customer Level
+-- Membership
+-- EMI
+-- Bank
+-- Invoice
+-- Cash Register
+-- Expense
+-- Reports
+-- Settings

---

# 3. ARCHITECTURAL PRINCIPLES

Follow these principles strictly:

* SOLID
* DRY
* KISS where appropriate
* Separation of concerns
* Dependency inversion
* Service-oriented business logic
* Action classes where appropriate
* Form Requests for validation
* Policies / permissions for authorization
* Events and listeners for decoupled side effects
* Database transactions for financial/stock operations
* Proper database indexes
* Foreign keys where appropriate
* Soft deletes where appropriate
* Auditability
* Testability
* Extensibility
* No unnecessary over-engineering

Do NOT put all business logic inside Controllers.

Controllers should remain thin.

Business logic should live in appropriate:

* Services
* Actions
* Domain classes
* Events
* Listeners
* Value objects
* Policies

depending on the existing Metheme architecture.

---

# 4. MULTI-STORE ARCHITECTURE

The package must support:

* Single store
* Multiple stores
* Branches
* Warehouses
* Multiple warehouses per store

Conceptually:

Company
|
+-- Store A
|      |
|      +-- Warehouse A
|      +-- Warehouse B
|
+-- Store B
|
+-- Warehouse C

Do not hard-code a single store.

Every relevant transaction must be able to identify its:

* Store
* Warehouse
* User/operator

where applicable.

If Metheme already has an organization/company/tenant concept, integrate with it instead of creating a duplicate system.

---

# 5. PRODUCT MANAGEMENT

Create a highly flexible Product system.

A product may represent:

* Rice
* Oil
* Medicine
* Laptop
* Mouse
* Keyboard
* Sofa
* Chair
* Hammer
* Cable
* Cosmetics
* Clothing
* Electronics

Product must support:

* Name
* SKU
* Category
* Brand
* Unit
* Purchase price
* Sale price
* Wholesale price
* Minimum sale price if needed
* Tax
* Discount eligibility
* Stock tracking
* Batch tracking
* Expiry tracking
* Serial tracking
* Barcode
* Fractional quantity
* Point earning
* Warranty
* Product attributes
* Status
* Notes
* Image/media if Metheme supports media

Do not assume every product needs every feature.

Each product must be configurable.

---

# 6. PRODUCT TRACKING TYPES

Support at least:

## Normal Product

Example:

Mouse = 1 piece

## Batch Product

Example:

Medicine:

Batch A
Quantity: 100
Expiry: 2027-05-10

Batch B
Quantity: 200
Expiry: 2028-01-20

## Expiry Product

Products may have expiry dates.

Examples:

* Medicine
* Food
* Cosmetics

## Serial Product

Each unit has unique serial:

Laptop:

* SN001
* SN002
* SN003

Mobile:

* IMEI001
* IMEI002

## Fractional Product

Support decimal quantities:

Rice:
1.5 KG

Oil:
2.75 Liter

Wire:
12.5 Meter

The database and business logic must NOT assume integer quantity.

Use appropriate decimal precision.

---

# 7. UNIT MANAGEMENT

Create dynamic unit management.

Examples:

* Piece
* KG
* Gram
* Liter
* ML
* Meter
* Feet
* Box
* Carton
* Packet
* Dozen
* Set
* Bundle

Support unit conversion.

Examples:

1 Carton = 24 Pieces

1 KG = 1000 Gram

1 Liter = 1000 ML

A product can define:

* Base unit
* Purchase unit
* Sale unit

The system must calculate stock correctly when different units are used.

Example:

Purchase:
10 cartons

Sale:
3 pieces

Remaining:
237 pieces

Do not implement unit conversion in a hard-coded manner.

---

# 8. PRODUCT ATTRIBUTES / VARIANTS

Support dynamic product attributes.

Examples:

Furniture:

* Color
* Material
* Size

Computer:

* RAM
* Storage
* Processor
* Generation

Clothing:

* Size
* Color

Electronics:

* Model
* Voltage
* Warranty

Do not create separate database columns for every possible attribute.

Use a flexible attribute/value architecture.

Support variants where appropriate.

Example:

T-Shirt
Red / M
Red / L
Blue / M
Blue / L

Each variant may have:

* SKU
* Barcode
* Price
* Stock
* Image
* Attributes

---

# 9. BARCODE SYSTEM

Support two types of barcode.

## Existing Manufacturer Barcode

Example:

8901234567890

Store and search products using existing barcode.

## Generated Store Barcode

If a product does not have a barcode, the system must allow generating one.

Generated barcode must be unique.

Allow configurable barcode formats/prefixes.

Example:

STORE-000001

Do not assume one specific barcode standard.

Design barcode generation as a replaceable service.

---

# 10. BARCODE LABEL GENERATION

Allow users to generate printable barcode labels.

Label must support:

* Product name
* Barcode
* SKU
* Price
* Batch
* Expiry
* Custom fields

Support configurable label sizes.

Examples:

* 40x30 mm
* 50x30 mm
* 60x40 mm
* Custom

The implementation must work with common thermal label printers where possible.

Do not hard-code a single label size.

---

# 11. PURCHASE MODULE

Create complete purchase management.

Purchase fields may include:

* Supplier
* Store
* Warehouse
* Purchase invoice number
* Date
* Reference number
* Notes
* Subtotal
* Discount
* Tax
* Grand total
* Paid
* Due
* Payment status

Purchase items:

* Product
* Variant
* Batch
* Serial
* Quantity
* Unit
* Purchase price
* Sale price
* Discount
* Tax
* Expiry
* Total

Purchase must happen inside a database transaction.

When purchase is completed:

* Stock increases
* Stock ledger is created
* Batch is created if applicable
* Serial records are created if applicable
* Supplier balance is updated
* Payment transaction is created if applicable

Never simply update stock without creating an auditable stock movement.

---

# 12. STOCK LEDGER — CRITICAL

Stock Ledger must be the authoritative history of stock movements.

Support movement types:

* Opening Stock
* Purchase
* Purchase Return
* Sale
* Sale Return
* Stock Adjustment
* Damage
* Loss
* Transfer In
* Transfer Out
* Manual Correction

Every movement should contain enough information to audit:

* Product
* Variant
* Store
* Warehouse
* Batch
* Serial if applicable
* Quantity
* Unit
* Direction
* Reference type
* Reference ID
* User
* Date/time
* Notes

Never make stock impossible to audit.

Prefer an append-only ledger model.

If a summarized/current stock table is used for performance, it must be derived/synchronized safely from the ledger.

---

# 13. STOCK ADJUSTMENT

Provide stock adjustment functionality.

Reasons:

* Damage
* Lost
* Expired
* Physical count mismatch
* Correction
* Opening balance

Require a reason.

Do not allow silent stock changes.

Record:

* Old quantity
* Adjustment quantity
* New quantity
* Reason
* User
* Timestamp

---

# 14. STOCK TRANSFER

Support:

Store A
-> Warehouse A
-> Warehouse B
-> Store B

Transfer must have states such as:

* Draft
* Requested
* Approved
* In Transit
* Received
* Cancelled

Stock should only change according to clearly defined state transitions.

Prevent double stock deduction.

---

# 15. BATCH MANAGEMENT

Batch must support:

* Batch number
* Product
* Variant
* Store
* Warehouse
* Purchase reference
* Purchase date
* Expiry date
* Purchase price
* Sale price
* Quantity

Multiple batches of the same product must coexist.

Example:

Napa:

Batch A:
100 units

Batch B:
250 units

Do not merge batches incorrectly.

---

# 16. EXPIRY MANAGEMENT

Support:

* Expired products
* Products expiring soon
* Configurable expiry warning period

Reports:

* Expired
* Expiring in 7 days
* Expiring in 30 days
* Expiring in 60 days

Do not assume only pharmacy uses expiry.

---

# 17. FEFO / FIFO / COSTING

Design stock consumption as configurable.

Potential strategies:

* FIFO
* FEFO
* Average Cost
* Batch-specific costing

For products with expiry, FEFO should be available.

Do not hard-code one costing strategy.

Create a replaceable inventory costing/stock allocation service.

---

# 18. SUPPLIER MANAGEMENT

Supplier:

* Name
* Company
* Phone
* Email
* Address
* Tax information if applicable
* Opening balance
* Credit limit
* Status
* Notes

Support:

* Supplier purchase
* Supplier payment
* Purchase return
* Supplier due
* Supplier statement

---

# 19. SALES / POS

Build a fast POS system.

The POS must support:

* Barcode scanning
* Product search
* SKU search
* Product name search
* Category search
* Customer selection
* Walk-in customer
* Quantity
* Decimal quantity
* Batch selection
* Serial selection
* Price selection
* Discount
* Tax
* Loyalty point redemption
* Membership benefit
* Customer level benefit
* Multiple payment methods
* Hold sale
* Resume sale if appropriate
* Sale return
* Invoice printing

The POS must be optimized for speed.

Do not make every action require a page reload if the existing frontend architecture supports AJAX/Livewire/Vue/React/etc.

Follow the technology already used by Metheme.

---

# 20. WALK-IN CUSTOMER

Sales must work without a registered customer.

Provide a configurable default:

"Walk-in Customer"

Do not force users to create a customer for every cash sale.

---

# 21. SALES PRICING

Support multiple price concepts.

At minimum:

* Purchase price
* Retail price
* Wholesale price

But design it so future price lists can be added.

Potential:

* Retail
* Wholesale
* Dealer
* Member
* Special price

Price selection should be configurable.

---

# 22. PRICE HISTORY

Never destroy important historical pricing information.

Maintain price history where required.

Example:

January:
Purchase = 70
Sale = 80

February:
Purchase = 75
Sale = 85

Historical sales must remain tied to the price actually used at that time.

Do not recalculate old invoices using today's price.

---

# 23. DISCOUNT ENGINE

Build a flexible discount engine.

Support:

* Item discount
* Product discount
* Category discount
* Brand discount
* Customer discount
* Membership discount
* Customer level discount
* Quantity-based discount
* Invoice-level discount
* Coupon discount

Example:

Buy 10 = 5%

Buy 20 = 10%

Gold customer = 5%

Platinum customer = 10%

Discount rules must be configurable.

Do not hard-code discount percentages.

---

# 24. SALE RETURN

Sale return must NOT delete the original sale.

Flow:

Original Sale
->
Sale Return
->
Stock Increase
->
Customer Refund / Credit

Support:

* Full return
* Partial return

Example:

Original sale:
10 units

Returned:
2 units

Remaining sold:
8 units

Prevent returning more than the originally sold quantity minus previous returns.

Return price/refund must follow clear business rules and remain auditable.

---

# 25. PURCHASE RETURN

Similarly:

Purchase
->
Purchase Return
->
Stock Decrease
->
Supplier Refund/Credit

Prevent returning more than available purchased quantity.

---

# 26. PAYMENT SYSTEM

Create a generic payment system.

Support:

* Cash
* Bank
* Card
* Mobile Banking
* Credit
* EMI
* Other custom payment methods

Payment methods must be configurable.

Do not hard-code only Cash/Card.

---

# 27. MIXED PAYMENT

A single sale must support multiple payment methods.

Example:

Cash = 20,000

Card = 30,000

Mobile Banking = 10,000

Total = 60,000

The payment engine must validate that total payment equals required amount unless the sale is intentionally credit/due.

---

# 28. CUSTOMER MANAGEMENT

Customer fields:

* Name
* Phone
* Email
* Address
* Customer code
* Membership
* Customer level
* Credit limit
* Opening balance
* Total purchase
* Total orders
* Total points
* Available points
* Status
* Notes

Do not store values that should be derived unless there is a performance reason.

---

# 29. CUSTOMER DUE / CREDIT

Support customer credit.

Track:

* Credit sale
* Payment
* Return
* Adjustment

Customer statement must show:

Date
Reference
Debit
Credit
Balance

Do not implement due as a simple manually editable number.

Use transaction/ledger concepts.

---

# 30. LOYALTY POINT SYSTEM

Create a dedicated loyalty system.

Products may define:

* Earn points
* Point multiplier
* Eligible for points or not

Example:

Product A:
Earn = 5 points

Product B:
Earn = 10 points

Points must be recorded as transactions.

Types:

* Earn
* Redeem
* Adjustment
* Expired
* Reversal

Do not simply increment/decrement a single customer points column without transaction history.

---

# 31. POINT REDEMPTION

Configurable rules:

* Minimum points required
* Conversion rate
* Maximum redemption per invoice
* Maximum percentage of invoice payable by points
* Point expiry
* Eligible products
* Eligible customer levels

Example:

100 points = 10 currency units

If customer has 250 points:

250 points = 25 currency units

The conversion must be configurable.

---

# 32. POINT REVERSAL

If a sale generated points and later the sale is returned:

The system must correctly reverse the previously earned points.

Similarly, if points were redeemed and the transaction is reversed, handle the reversal safely.

This must be transactional.

---

# 33. CUSTOMER LEVEL SYSTEM

Create configurable customer levels.

Example:

Bronze
0–999 points

Silver
1,000–4,999

Gold
5,000–9,999

Platinum
10,000+

But do NOT hard-code these values.

Allow administrators to configure level rules.

Level determination may be based on:

* Points
* Total purchase
* Number of orders
* Combination of conditions

Design the rule engine to be extensible.

---

# 34. CUSTOMER LEVEL BENEFITS

Each level may provide:

* Discount
* Extra points
* Special pricing
* Special offers
* Membership benefits

Example:

Bronze = 0%

Silver = 3%

Gold = 5%

Platinum = 10%

Do not hard-code.

---

# 35. MEMBERSHIP SYSTEM

Membership must be separate from customer level.

A customer can have:

* Membership
* Customer Level

at the same time.

Membership may have:

* Name
* Code
* Fee
* Duration
* Discount
* Point multiplier
* Benefits
* Start date
* Expiry date
* Status

Examples:

* Regular
* Silver
* Gold
* Premium
* Corporate

All configurable.

---

# 36. EMI SYSTEM

Create a generic EMI engine.

Support:

* EMI provider
* Bank
* EMI plan
* Duration
* Interest
* Processing fee
* Customer fee
* Merchant fee
* Minimum amount
* Maximum amount

Example:

Bank A:
3 months
6 months
12 months

Bank B:
6 months
9 months
12 months

Do not hard-code banks.

---

# 37. EMI CALCULATION

Support configurable calculation strategies.

Possible:

* Flat interest
* Percentage interest
* Fixed fee
* Processing fee
* Custom calculation

Do not assume one EMI formula.

Create an EMI calculation service/interface.

Document exactly how each strategy works.

---

# 38. EMI CONTRACT

When a customer purchases using EMI, create an EMI contract.

Store:

* Customer
* Sale
* Bank/provider
* Plan
* Principal
* Interest
* Fees
* Total payable
* Down payment
* Remaining amount
* Installment count
* Start date
* Due dates
* Status

Generate installment records.

---

# 39. EMI INSTALLMENTS

Each installment should contain:

* Installment number
* Due date
* Amount
* Paid amount
* Remaining amount
* Paid date
* Status
* Late fee if applicable

Statuses:

* Pending
* Partially Paid
* Paid
* Overdue
* Cancelled

Support partial payment if appropriate.

---

# 40. INVOICE SYSTEM

Create a reusable invoice engine.

Invoice types:

* A4
* Thermal 80mm
* Thermal 58mm
* Custom

Invoice must contain:

* Store information
* Customer
* Invoice number
* Date
* Items
* Quantity
* Unit
* Price
* Discount
* Tax
* Total
* Payment information
* Due
* Points earned
* Points redeemed
* EMI information if applicable

Historical invoices must remain reproducible.

---

# 41. INVOICE NUMBERING

Create configurable invoice numbering.

Examples:

INV-2026-000001

POS-2026-000001

STORE1-INV-000001

Support:

* Prefix
* Year
* Store code
* Counter code
* Sequential number

Prevent duplicate invoice numbers.

Handle concurrent requests safely.

---

# 42. A4 AND THERMAL PRINTING

Invoice rendering must be separated from sales logic.

The sale should not care whether the invoice is:

* A4
* Thermal
* PDF
* Print view

Use a template/renderer architecture.

Allow future invoice templates.

---

# 43. CASH REGISTER

Support multiple cash registers/counters.

Each register may have:

* Opening balance
* Sales
* Refunds
* Expenses
* Other income
* Cash adjustment
* Closing balance

Provide opening/closing workflow.

Do not allow unauthorized closing.

---

# 44. EXPENSE MANAGEMENT

Support store expenses.

Examples:

* Rent
* Electricity
* Transport
* Salary
* Maintenance
* Other

Expense:

* Category
* Amount
* Payment method
* Store
* Date
* Reference
* Notes
* User

Expenses should integrate with cash register/payment ledger where appropriate.

---

# 45. REPORTING

Build report architecture instead of hard-coding reports directly into controllers.

Required reports:

## Sales

* Daily sales
* Date range sales
* Product-wise sales
* Category-wise sales
* Customer-wise sales
* Cashier-wise sales
* Store-wise sales
* Payment-method-wise sales

## Purchase

* Purchase report
* Supplier-wise purchase
* Product-wise purchase

## Inventory

* Current stock
* Stock valuation
* Stock movement
* Low stock
* Out of stock
* Expiring soon
* Expired
* Batch-wise stock
* Serial-wise stock

## Financial

* Profit
* Expense
* Customer due
* Supplier due
* Payment report
* Cash register report

## Loyalty

* Points earned
* Points redeemed
* Point liability
* Customer level report

## EMI

* Active EMI
* Paid installments
* Pending installments
* Overdue installments

## Returns

* Sales returns
* Purchase returns

Reports must use optimized queries.

Avoid N+1 queries.

---

# 46. PROFIT CALCULATION

Profit must be based on proper costing.

Support configurable costing:

* FIFO
* Average cost
* Batch cost

Profit should consider:

Sale Revenue

* Cost of Goods Sold
* Discounts
* Returns
* Relevant expenses where appropriate

Do not calculate profit simply as:

sale_price - current_product_purchase_price

because historical purchase prices can differ.

---

# 47. DATABASE DESIGN

Create proper migrations.

Every table must have:

* Appropriate primary key
* Foreign keys
* Indexes
* Unique constraints where needed
* Nullable fields where logically appropriate
* Timestamps
* Soft deletes where appropriate

Use proper decimal precision for:

* Money
* Quantity
* Tax
* Percentage

Never use floating point for financial amounts.

Use decimal/numeric types.

---

# 48. MONEY HANDLING

Never use PHP float for money calculations where precision matters.

Use:

* Decimal database columns
* Proper rounding rules
* Centralized money calculation logic

Respect configurable currency/decimal settings.

Do not hard-code BDT even though Bangladesh may be the initial market.

Currency should be configurable.

---

# 49. TAX

Build a generic tax system.

Support:

* Tax enabled/disabled
* Tax rate
* Tax-inclusive pricing
* Tax-exclusive pricing
* Item-level tax
* Invoice-level tax if required

Do not hard-code VAT percentages.

---

# 50. SETTINGS

Create configurable settings for:

* Currency
* Decimal places
* Quantity precision
* Invoice prefix
* Barcode prefix
* Default customer
* Default warehouse
* Default store
* Tax behavior
* Discount behavior
* Loyalty rules
* Membership
* EMI
* Stock strategy
* Invoice template
* Thermal paper size
* Low stock threshold
* Expiry warning days

Use Metheme's existing settings system if available.

---

# 51. PERMISSIONS

Integrate with Metheme's existing permission system.

If Metheme has no permission system, create a modular one.

Potential permissions:

products.view
products.create
products.update
products.delete

purchases.view
purchases.create
purchases.update
purchases.delete
purchases.return

sales.view
sales.create
sales.update
sales.delete
sales.return

customers.view
customers.create
customers.update
customers.delete

suppliers.view
suppliers.create
suppliers.update

inventory.view
inventory.adjust
inventory.transfer

payments.view
payments.create

emi.view
emi.create
emi.payment

loyalty.view
loyalty.adjust

reports.sales
reports.purchase
reports.stock
reports.profit

settings.manage

Use actual permission conventions from Metheme if they differ.

---

# 52. AUDIT LOG

All sensitive operations must be auditable.

Examples:

* Sale created
* Sale cancelled
* Sale returned
* Purchase created
* Purchase returned
* Stock adjusted
* Stock transferred
* Price changed
* Discount manually overridden
* Point adjusted
* Customer balance adjusted
* EMI modified
* Payment created
* Payment reversed

Use Metheme's existing audit system if available.

Otherwise create a clean audit abstraction.

---

# 53. BUSINESS RULES

All critical business operations must use database transactions.

At minimum:

Sale completion
Purchase completion
Sale return
Purchase return
Stock transfer
Stock adjustment
Payment creation
Point redemption
Point reversal
EMI payment

If any part fails, the whole operation should rollback.

Prevent:

* Negative stock unless explicitly allowed
* Duplicate stock deduction
* Duplicate point earning
* Duplicate payment
* Duplicate invoice number
* Returning more than sold
* Returning more than purchased
* Duplicate serial assignment
* Duplicate barcode

---

# 54. CONCURRENCY

Assume multiple cashiers may sell the same product simultaneously.

Use:

* Database transactions
* Row locking where appropriate
* Unique constraints
* Atomic operations

Prevent overselling caused by race conditions.

Do not rely only on frontend validation.

---

# 55. NEGATIVE STOCK

Make this configurable.

Options:

Allow negative stock

OR

Prevent negative stock

Default should preferably be prevent-negative-stock.

When prevented, return a proper validation/business exception.

---

# 56. SERIAL NUMBER RULES

A serialized product must not allow the same serial to be sold twice.

Track lifecycle:

Available
Reserved
Sold
Returned
Warranty/Service
Damaged
Lost

Do not physically delete serial history.

---

# 57. WARRANTY

Design optional warranty support.

For serial products:

* Warranty duration
* Warranty start
* Warranty expiry
* Warranty status

This should be extensible.

---

# 58. PRODUCT DELETE RULES

Do not allow destructive deletion of products with historical transactions.

Prefer:

* Archive
* Disable
* Soft delete

Historical sales/purchases must remain valid.

---

# 59. IMPORT / EXPORT

Support future-ready import/export.

At minimum architecture should allow:

* Product CSV import
* Customer CSV import
* Supplier CSV import
* Opening stock import

Validate imports before committing.

Show row-level errors.

Do not partially import silently.

---

# 60. API

Create API-ready architecture.

Do not necessarily expose every API immediately, but business logic must not depend on web controllers.

API can later support:

* Products
* Stock
* POS
* Sales
* Purchases
* Customers
* Payments

Use API Resources if appropriate.

---

# 61. FRONTEND

First inspect Metheme's frontend stack.

If Metheme uses:

* Blade
* Livewire
* Vue
* React
* Alpine
* Bootstrap
* Tailwind

follow the existing conventions.

Do not introduce a completely different frontend framework without necessity.

UI must be:

* Responsive
* Clean
* Fast
* Keyboard friendly
* POS friendly

POS should prioritize speed.

---

# 62. DASHBOARD

Create configurable dashboard widgets.

Examples:

* Today's sales
* Today's purchase
* Today's profit
* Customer due
* Supplier due
* Low stock
* Expiring products
* Top selling products
* Top customers
* Cash position
* EMI overdue

Use reusable widgets.

---

# 63. SEARCH

Product search must be fast.

Search by:

* Name
* SKU
* Barcode
* Brand
* Category
* Variant

Barcode lookup must be optimized with indexes.

---

# 64. PRODUCT SALE RULES

The system must determine:

1. Product
2. Variant
3. Warehouse
4. Batch if applicable
5. Serial if applicable
6. Available quantity
7. Unit conversion
8. Price
9. Discount
10. Tax
11. Loyalty eligibility

before completing the sale.

---

# 65. STOCK ALLOCATION

Create an abstraction such as:

StockAllocator

Possible strategies:

FIFOStockAllocator
FEFOStockAllocator
BatchStockAllocator

Do not place allocation logic directly in SaleController.

---

# 66. LOYALTY SERVICE

Create an abstraction such as:

LoyaltyService

Responsibilities:

* Calculate earned points
* Earn points
* Redeem points
* Reverse points
* Adjust points
* Expire points
* Calculate customer level

Every point movement must be recorded.

---

# 67. PRICING SERVICE

Create a centralized pricing engine.

Possible responsibilities:

* Resolve base price
* Resolve price list
* Customer price
* Membership discount
* Level discount
* Product discount
* Quantity discount
* Coupon
* Manual discount authorization

The POS should ask the pricing engine rather than calculating random discounts itself.

---

# 68. PAYMENT SERVICE

Create centralized PaymentService.

Responsibilities:

* Create payment
* Validate payment
* Mixed payment
* Refund
* Reverse payment
* Customer credit
* Supplier payment
* EMI payment

---

# 69. INVOICE SERVICE

Create InvoiceService.

Responsibilities:

* Generate invoice number
* Build invoice data
* Render A4
* Render thermal
* Generate PDF
* Print view

Keep rendering independent from sale logic.

---

# 70. EVENT ARCHITECTURE

Create useful domain events.

Examples:

SaleCreated
SaleCompleted
SaleCancelled
SaleReturned

PurchaseCreated
PurchaseCompleted
PurchaseReturned

StockAdjusted
StockTransferred

PaymentReceived
PaymentRefunded

PointsEarned
PointsRedeemed
PointsReversed

CustomerLevelChanged

EMICreated
EMIPaymentReceived
EMIOverdue

Use listeners for side effects.

---

# 71. QUEUES

Use queues where appropriate for non-critical tasks:

* Invoice PDF generation
* Large report generation
* Bulk barcode generation
* Notifications
* Import processing

Do NOT queue operations where immediate transactional consistency is required.

---

# 72. NOTIFICATIONS

If Metheme has notifications, integrate with it.

Potential notifications:

* Low stock
* Expiring product
* Customer EMI overdue
* Supplier due
* Customer due
* Transfer pending

Do not hard-code notification channels.

---

# 73. TESTING

This is mandatory.

Write tests.

At minimum:

## Unit tests

* Unit conversion
* Price calculation
* Discount calculation
* Tax calculation
* Loyalty calculation
* EMI calculation
* Customer level calculation
* Stock allocation

## Feature tests

* Product creation
* Purchase
* Purchase return
* Sale
* Sale return
* Stock adjustment
* Stock transfer
* Payment
* Customer credit
* Point earn
* Point redeem
* Point reversal
* EMI
* Invoice

## Edge cases

Test:

* Decimal quantity
* Zero quantity
* Negative quantity
* Duplicate barcode
* Duplicate serial
* Expired batch
* Partial return
* Multiple returns
* Mixed payment
* Insufficient stock
* Concurrent stock operations
* Point reversal
* EMI partial payment

Do not consider the module complete until tests pass.

---

# 74. DATABASE SEEDERS

Create realistic demo seeders where appropriate.

Include sample:

* Store
* Warehouse
* Category
* Brand
* Units
* Products
* Suppliers
* Customers
* Membership levels
* Customer levels
* Payment methods
* Banks
* EMI plans

Do not put fake production data into migrations.

Use seeders.

---

# 75. DOCUMENTATION

Create documentation covering:

* Installation
* Configuration
* Metheme integration
* Database structure
* Product setup
* Stock setup
* POS usage
* Purchase workflow
* Sale workflow
* Return workflow
* Loyalty setup
* Membership
* EMI
* Invoice printing
* Barcode
* Permissions
* Events
* Extension points
* API usage
* Testing

---

# 76. PACKAGE CONFIGURATION

Create a package config file.

For example:

`config/retail.php`

But if Metheme has a package configuration convention, follow it.

Config must be publishable if appropriate.

---

# 77. ROUTES

Separate package routes.

Use proper route naming.

For example:

retail.products.*
retail.purchases.*
retail.sales.*
retail.customers.*
retail.inventory.*
retail.reports.*

But follow Metheme's naming conventions if they differ.

---

# 78. MENU INTEGRATION

If Metheme has a dynamic menu/sidebar system:

Automatically integrate Retail menus.

Possible menu:

Retail
Dashboard
POS
Sales
Sale Returns
Purchases
Purchase Returns
Products
Categories
Brands
Inventory
Stock Transfer
Customers
Suppliers
Loyalty
Membership
EMI
Payments
Cash Register
Expenses
Reports
Settings

Do not duplicate Metheme's menu system.

---

# 79. ERROR HANDLING

Create meaningful domain/business exceptions.

Examples:

InsufficientStockException
DuplicateBarcodeException
DuplicateSerialException
InvalidReturnException
InvalidPointRedemptionException
InvalidPaymentException
InvalidEMIException

Controllers should convert these into appropriate UI/API responses.

Do not expose raw database exceptions to users.

---

# 80. VALIDATION

Use Form Requests or the existing Metheme validation convention.

Validate both frontend and backend.

Never trust POS frontend values.

Especially validate:

* Price
* Discount
* Quantity
* Batch
* Serial
* Payment
* Customer
* Points
* EMI
* Stock

---

# 81. SECURITY

Follow Laravel security best practices.

Protect against:

* Unauthorized access
* Mass assignment
* CSRF
* SQL injection
* XSS
* IDOR
* Unauthorized price manipulation
* Unauthorized discount manipulation
* Unauthorized stock adjustment

Use:

* Policies
* Gates
* Validation
* Authorization
* Proper route middleware

---

# 82. PRICE MANUAL OVERRIDE

If cashier manually changes price or discount beyond allowed limits:

Require configurable permission.

Example:

Cashier:
Maximum discount = 5%

Manager:
Maximum discount = 20%

Admin:
Unlimited

Do not trust client-side restrictions.

---

# 83. CUSTOMER POINT LIABILITY

Points represent potential customer benefit.

The system must preserve complete point transaction history.

Never allow administrators to silently change points.

If manual adjustment is allowed:

* Reason required
* User recorded
* Audit recorded

---

# 84. RETURN AND LOYALTY INTERACTION

When a customer returns a product:

If original sale earned 50 points:

Return must reverse the correct points.

If customer already spent those points:

The system must handle the resulting negative/adjustment scenario according to configurable business rules.

Do not ignore this edge case.

---

# 85. RETURN AND DISCOUNT INTERACTION

Suppose invoice had:

Subtotal = 1,000
Discount = 100
Paid = 900

Customer returns one item.

Refund must follow a defined proportional/line-level discount policy.

Do not simply refund the item's original full price.

Document the chosen calculation strategy.

Make it configurable where practical.

---

# 86. EMI AND RETURNS

If an EMI sale is partially returned:

The system must define how:

* Principal
* Interest
* Fees
* Remaining installments

are recalculated or adjusted.

Do not allow an inconsistent EMI balance.

Create an EMI adjustment/refund workflow.

---

# 87. MULTI-CURRENCY

Even if initial deployment uses BDT:

Do not hard-code currency symbols throughout the package.

Currency should be configurable.

If Metheme already provides currency functionality, reuse it.

---

# 88. TIMEZONE

Use Laravel/application timezone conventions.

Do not manually mix server timezone and business timezone.

Transactions and timestamps must be consistent.

---

# 89. SOFT DELETE / HISTORICAL DATA

Never destroy transactional history.

Products, customers, suppliers, etc. may be archived.

Sales, payments, stock ledger, EMI transactions should remain auditable.

---

# 90. DATABASE PERFORMANCE

Add indexes for frequently searched fields:

* Barcode
* SKU
* Product name where appropriate
* Invoice number
* Customer phone
* Supplier phone
* Batch number
* Serial number
* Transaction date
* Store ID
* Warehouse ID
* Product ID

Avoid excessive indexing.

Use composite indexes where appropriate.

---

# 91. N+1 PREVENTION

Use:

* eager loading
* aggregate queries
* optimized joins
* pagination

where appropriate.

Do not load thousands of products unnecessarily.

POS product search should be optimized.

---

# 92. PAGINATION

All large datasets must be paginated:

* Products
* Customers
* Suppliers
* Sales
* Purchases
* Stock movements
* Payments
* EMI installments
* Reports

Do not render entire tables unnecessarily.

---

# 93. BULK OPERATIONS

Support future-ready bulk operations:

* Bulk barcode generation
* Bulk price update
* Bulk product import
* Bulk stock adjustment
* Bulk label printing

Architecture should allow these without redesign.

---

# 94. EXTENSION / PLUGIN ARCHITECTURE

The Retail package itself should be extensible.

Future modules may include:

* Restaurant POS
* Restaurant kitchen
* Delivery
* E-commerce integration
* WooCommerce integration
* Online orders
* SMS
* WhatsApp
* Accounting
* Payroll
* Warranty service
* Repair service

Do not tightly couple Retail Core to these future modules.

---

# 95. MODULE TOGGLE

Where practical, modules should be configurable.

Example:

Retail Core = enabled

Loyalty = enabled

Membership = enabled

EMI = disabled

Serial tracking = enabled

This allows different businesses to use different features.

Do not make the system unnecessarily complex; use a clean module/feature configuration strategy.

---

# 96. CODING RULES

Follow the existing Metheme coding style.

Do NOT:

* create giant controllers
* create giant models
* put SQL everywhere
* duplicate logic
* hard-code business rules
* hard-code discount percentages
* hard-code EMI banks
* hard-code product types
* hard-code currencies
* hard-code invoice templates
* hard-code barcode formats

Use constants/enums/configuration/value objects where appropriate.

---

# 97. ENUMS

Where Laravel/PHP version supports it cleanly, use enums for stable internal states such as:

SaleStatus
PurchaseStatus
PaymentStatus
ReturnStatus
EMIStatus
InstallmentStatus
StockMovementType
SerialStatus

But do not use enums for values that administrators need to dynamically configure.

---

# 98. DATABASE TRANSACTION RULE

For every financial or inventory transaction:

BEGIN TRANSACTION

Validate
Lock required rows
Create transaction records
Update relevant summaries
Create ledger entries
Dispatch required events appropriately

COMMIT

If anything fails:

ROLLBACK

Do not create half-completed sales/purchases.

---

# 99. IDEMPOTENCY

Where duplicate requests can happen, design protection.

Examples:

User double-clicks "Complete Sale".

The system must not create:

2 invoices
2 stock deductions
2 payments

Use appropriate unique transaction/reference mechanisms.

---

# 100. NO FAKE IMPLEMENTATION

Do NOT create placeholder methods like:

```php
// TODO
// implement later
return true;
```

Do not claim a feature is implemented if it is not.

If a feature cannot be fully implemented because a dependency or existing Metheme behavior is unknown:

1. Inspect the codebase.
2. Determine the correct integration point.
3. Implement against the actual code.
4. If genuinely impossible, document the exact blocker.

Never silently fake functionality.

---

# 101. NO ASSUMPTIONS

If the existing Metheme already has:

User
Role
Permission
Company
Branch
Media
Settings
Notifications
Audit logs
Currency
Address
Contact

reuse them.

Do not create duplicates without a technical reason.

---

# 102. PACKAGE ISOLATION

The Retail package must remain logically isolated.

Avoid modifying unrelated Metheme files.

If modifications are required:

* Keep them minimal
* Explain them
* Make them integration-safe

Prefer service providers/hooks/events/extensions over direct invasive changes.

---

# 103. INSTALLATION

The final package should ideally be installable through Composer/package architecture.

If the current repository is monolithic rather than a true Composer package, still keep a package boundary so it can later be extracted.

Provide:

* Service Provider
* Config
* Routes
* Migrations
* Views
* Translations
* Commands
* Assets

according to Laravel package conventions.

---

# 104. MIGRATION SAFETY

Migrations must:

* Have proper dependency order
* Use foreign keys carefully
* Be reversible where practical
* Avoid destructive changes
* Work on fresh installation
* Work on existing Metheme installation

Do not assume empty database.

---

# 105. INITIAL IMPLEMENTATION ORDER

Implement in this exact logical sequence:

PHASE 1
Inspect Metheme
Architecture map
Integration strategy

PHASE 2
Package foundation
Service provider
Config
Routes
Permissions
Menu integration

PHASE 3
Store
Warehouse
Product
Category
Brand
Unit
Unit conversion
Product attributes
Variants
Barcode

PHASE 4
Inventory
Stock ledger
Batch
Expiry
Serial
Stock adjustment
Stock transfer
Costing

PHASE 5
Supplier
Purchase
Purchase payment
Purchase return

PHASE 6
Customer
Pricing
Discount
POS
Sale
Payment
Sale return

PHASE 7
Loyalty
Points
Point redemption
Point reversal
Customer levels
Membership

PHASE 8
EMI
Banks
EMI plans
Contracts
Installments
Payments
Overdue

PHASE 9
Cash register
Expenses
Financial ledgers

PHASE 10
Invoice
A4
Thermal
Barcode labels

PHASE 11
Reports
Dashboard
Exports

PHASE 12
Testing
Performance
Security
Documentation

---

# 106. AFTER EACH PHASE

After implementing each phase:

1. Run migrations.
2. Run tests.
3. Fix errors.
4. Check static analysis if available.
5. Check routes.
6. Check permissions.
7. Check database integrity.
8. Check UI integration.
9. Check edge cases.
10. Do not proceed with known broken functionality.

---

# 107. REQUIRED FINAL OUTPUT

When the implementation is complete, provide:

1. Final package structure
2. All created files
3. All modified Metheme files
4. Database schema summary
5. Installation instructions
6. Configuration instructions
7. Permission list
8. Route list
9. Event/listener list
10. Service/action list
11. Testing instructions
12. Example usage
13. Extension guide
14. Known limitations, if any

---

# 108. IMPORTANT DEVELOPMENT BEHAVIOR

You are not just generating code snippets.

You are responsible for implementing the complete feature set inside the actual codebase.

Therefore:

* Inspect first.
* Plan first.
* Implement incrementally.
* Test continuously.
* Fix your own errors.
* Respect existing architecture.
* Do not overwrite unrelated code.
* Do not stop after creating migrations.
* Do not stop after creating models.
* Do not stop after creating controllers.
* Implement complete end-to-end functionality.

A feature is considered complete only when:

Database
+
Model
+
Business logic
+
Validation
+
Authorization
+
Controller/API
+
UI
+
Routes
+
Events
+
Tests
+
Documentation

are appropriately implemented.

---

# 109. CRITICAL BUSINESS FLOW

The following flow must work end-to-end:

## PURCHASE

Supplier
->
Purchase
->
Purchase Items
->
Batch/Serial
->
Stock Ledger
->
Inventory
->
Supplier Balance
->
Payment

## SALE

Customer
->
POS
->
Product
->
Batch/Serial
->
Pricing
->
Discount
->
Membership
->
Customer Level
->
Point Redemption
->
Tax
->
Payment
->
Sale
->
Stock Ledger
->
Point Earn
->
Customer Level Update
->
Invoice

## SALE RETURN

Original Sale
->
Return
->
Validate Returnable Quantity
->
Stock Increase
->
Refund/Credit
->
Point Reversal
->
Customer Balance Update

## PURCHASE RETURN

Purchase
->
Return
->
Validate Returnable Quantity
->
Stock Decrease
->
Supplier Credit/Refund

## EMI

Sale
->
EMI Plan
->
Bank
->
EMI Contract
->
Installments
->
Payments
->
Balance
->
Completion

---

# 110. EXAMPLE PRODUCT TYPES TO TEST

Create tests for at least these scenarios:

## Grocery

Rice:

* KG
* Decimal quantity
* No barcode
* Generated barcode

## Oil

Oil:

* Liter
* Decimal quantity
* Bulk purchase
* Partial sale

## Medicine

Medicine:

* Batch
* Expiry
* FEFO
* Barcode

## Computer

Laptop:

* Serial
* Barcode
* Warranty
* EMI

## Furniture

Sofa:

* Variant
* Color
* Size
* Piece unit

## Hardware

Cable:

* Meter
* Decimal quantity
* Manufacturer barcode

If these scenarios work, the architecture is likely flexible enough for many other retail businesses.

---

# 111. UI REQUIREMENTS

The UI should have:

* Clean navigation
* Searchable tables
* Filters
* Pagination
* Bulk actions
* Modal where appropriate
* Confirmation before destructive actions
* Responsive design
* Keyboard-friendly POS
* Barcode scanner friendly input
* Printable invoice views
* Loading states
* Validation messages
* Empty states
* Error states

Follow Metheme UI conventions.

---

# 112. POS KEYBOARD SUPPORT

Where practical, support:

F2 = Search

F4 = Customer

F8 = Payment

F9 = Hold

F10 = Complete

ESC = Close modal

But do not hard-code if Metheme already has keyboard shortcuts.

Make shortcuts configurable if practical.

---

# 113. OFFLINE CONSIDERATION

Do not implement full offline POS unless the existing application architecture supports it.

However, keep the POS architecture future-ready for:

* offline cart
* sync
* conflict resolution

Do not pretend the application is offline-capable if it is not.

---

# 114. REPORT DATE FILTERS

Reports should support:

* Today
* Yesterday
* This week
* This month
* Previous month
* This year
* Custom range

Use proper timezone handling.

---

# 115. EXPORT

Where appropriate support:

* CSV
* Excel
* PDF
* Print

Use existing Metheme/export infrastructure if available.

---

# 116. LOCALIZATION

The package should support translation.

At minimum architecture must allow:

* English
* Bengali

Do not hard-code user-facing strings inside business logic.

Use translation files.

---

# 117. LOGGING

Critical failures should be logged.

Do not log sensitive payment/card information.

Do not expose sensitive customer information unnecessarily.

---

# 118. DATA INTEGRITY

The system must maintain consistency between:

Sale
Sale Items
Payments
Stock
Customer Balance
Points
Invoice

and between:

Purchase
Purchase Items
Payments
Stock
Supplier Balance

If one transaction fails, dependent state must not become inconsistent.

---

# 119. FINAL QUALITY STANDARD

The resulting system should feel like a serious commercial retail ERP/POS foundation, not a demo CRUD project.

Avoid simplistic implementation such as:

```php
$product->stock -= $qty;
```

without ledger/audit/transaction handling.

Avoid:

```php
$customer->points += 10;
```

without point transaction history.

Avoid:

```php
$sale->delete();
```

for financial transactions.

Avoid hard-coded:

```php
$discount = 10;
```

Avoid:

```php
$emi = $price / 12;
```

without a defined EMI calculation strategy.

The system must model real business transactions.

---

# 120. VERY IMPORTANT — START HERE

Do NOT immediately generate hundreds of files.

First inspect the existing Metheme project.

Then produce:

## STEP A

Architecture analysis of the existing project.

## STEP B

Recommended Retail package architecture.

## STEP C

Database ERD/design.

## STEP D

Module dependency map.

## STEP E

Implementation plan.

Then start implementation phase-by-phase.

If tools are available, inspect the actual files directly.

If you have terminal/file access, use it.

If you have database access, inspect the schema.

Do not ask unnecessary questions if the answer can be determined by inspecting the codebase.

Only ask me a question when a decision genuinely cannot be determined from the existing project and making an assumption could cause architectural damage.

---

# 121. IMPORTANT — DO NOT STOP AT ANALYSIS

After analysis, continue with implementation.

Do not simply give me a plan and wait.

Start implementing the package in the existing codebase.

If the environment limits the amount of code that can be generated in one response, continue in logical phases and clearly track completed vs pending work.

Never regenerate already completed files unnecessarily.

Maintain a clear implementation state.

---

# 122. CODE QUALITY

Use clean Laravel conventions.

Prefer:

* dependency injection
* typed properties
* return types
* DTOs/value objects where useful
* service classes
* actions
* policies
* events
* listeners
* resources
* form requests
* enums where appropriate
* database transactions
* query scopes where useful

Avoid unnecessary repositories if they only wrap Eloquent without providing value.

Follow the architecture already established by Metheme when it differs.

---

# 123. IMPORTANT — EXTEND METHEME, DON'T FIGHT IT

The Retail package exists to extend Metheme.

Therefore:

DO:

* Reuse Metheme
* Integrate with Metheme
* Follow Metheme conventions
* Use existing authentication
* Use existing permissions
* Use existing UI
* Use existing settings
* Use existing components

DON'T:

* Create a second authentication system
* Create a second role system
* Create a second permission system
* Create a second settings engine
* Create duplicate user tables
* Replace Metheme UI unnecessarily

---

# 124. FINAL ACCEPTANCE TEST

Before declaring the package complete, verify this scenario:

A new store is created.

Then:

1. Create warehouse.
2. Create category.
3. Create brand.
4. Create units.
5. Create a normal product.
6. Create a decimal product.
7. Create a batch product.
8. Create an expiry product.
9. Create a serial product.
10. Assign barcode.
11. Generate barcode for another product.
12. Create supplier.
13. Purchase products.
14. Verify stock.
15. Check stock ledger.
16. Sell product.
17. Use decimal quantity.
18. Apply discount.
19. Use customer.
20. Earn points.
21. Redeem points.
22. Apply membership discount.
23. Apply customer-level discount.
24. Complete mixed payment.
25. Generate A4 invoice.
26. Generate thermal invoice.
27. Return part of sale.
28. Verify stock.
29. Verify refund.
30. Verify point reversal.
31. Create purchase return.
32. Verify supplier balance.
33. Create EMI sale.
34. Generate installments.
35. Pay installment.
36. Verify EMI balance.
37. Check customer statement.
38. Check sales report.
39. Check purchase report.
40. Check stock report.
41. Check profit report.
42. Check audit log.

Every step must work correctly.

---

# 125. FINAL INSTRUCTION

Build this as a **real reusable Laravel Retail/Store Management package**, not as a tutorial/demo.

The package must be:

* Dynamic
* Flexible
* Modular
* Multi-store ready
* Multi-warehouse ready
* Batch ready
* Expiry ready
* Serial ready
* Barcode ready
* Fractional quantity ready
* POS ready
* Purchase ready
* Sale ready
* Return ready
* Discount ready
* Customer ready
* Loyalty ready
* Membership ready
* Customer-level ready
* EMI ready
* Bank-wise EMI ready
* Payment ready
* Invoice ready
* A4 ready
* Thermal ready
* Reporting ready
* API ready
* Extensible
* Testable
* Secure
* Auditable
* Production-oriented

Most importantly:

**Do not hard-code assumptions that restrict the system to grocery, pharmacy, computer, furniture, or any single business type.**

Build a generic retail engine with configurable behavior.

**Start by inspecting the existing Metheme codebase and then implement the system phase-by-phase.**
