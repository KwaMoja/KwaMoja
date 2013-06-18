<?php

CreateTable('modules',
"CREATE TABLE `modules` (
	`secroleid` INT(11) NOT NULL DEFAULT 8,
	`modulelink` VARCHAR(10) NOT NULL DEFAULT '',
	`reportlink` VARCHAR(4) NOT NULL DEFAULT '',
	`modulename` VARCHAR(25) NOT NULL DEFAULT '',
	`sequence` INT(11) NOT NULL DEFAULT 1,
	PRIMARY KEY (`secroleid`, `modulelink`),
	CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`)
)",
$db);

CreateTable('menuitems',
"CREATE TABLE `menuitems` (
	`secroleid` int(11) NOT NULL DEFAULT 8,
	`modulelink` VARCHAR(10) NOT NULL DEFAULT '',
	`menusection` VARCHAR(15) NOT NULL DEFAULT '',
	`caption` VARCHAR(60) NOT NULL DEFAULT '',
	`url` VARCHAR(60) NOT NULL DEFAULT '',
	`sequence` INT(11) NOT NULL DEFAULT 1,
	PRIMARY KEY (`secroleid`, `modulelink`, `menusection`, `caption`),
	CONSTRAINT `menuitems_ibfk_1` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`),
	CONSTRAINT `menuitems_ibfk_2` FOREIGN KEY (`secroleid`, `modulelink`) REFERENCES `modules` (`secroleid`, `modulelink`)
)",
$db);

NewModule('orders', 'ord', _('Sales'), 1, $db);
NewModule('AR', 'ar', _('Receivables'), 2, $db);
NewModule('AP', 'ap', _('Payables'), 3, $db);
NewModule('PO', 'prch', _('Purchases'), 4, $db);
NewModule('stock', 'inv', _('Inventory'), 5, $db);
NewModule('manuf', 'man', _('Manufacturing'), 6, $db);
NewModule('GL', 'gl', _('General Ledger'), 7, $db);
NewModule('FA', 'fa', _('Asset Manager'), 8, $db);
NewModule('PC', 'pc', _('Petty Cash'), 9, $db);
NewModule('system', 'sys', _('Setup'), 10, $db);
NewModule('Utilities', 'utils', _('Utilities'), 11, $db);

NewMenuItem('orders', 'Transactions', _('Enter An Order or Quotation'), '/SelectOrderItems.php?NewOrder=Yes', 1, $db);
NewMenuItem('orders', 'Transactions', _('Enter Counter Sales'), '/CounterSales.php', 2, $db);
NewMenuItem('orders', 'Transactions', _('Enter Counter Returns'), '/CounterReturns.php', 3, $db);
NewMenuItem('orders', 'Transactions', _('Print Picking Lists'), '/PDFPickingList.php', 4, $db);
NewMenuItem('orders', 'Transactions', _('Outstanding Sales Orders/Quotations'), '/SelectSalesOrder.php', 5, $db);
NewMenuItem('orders', 'Transactions', _('Special Order'), '/SpecialOrder.php', 6, $db);
NewMenuItem('orders', 'Transactions', _('Recurring Order Template'), '/SelectRecurringSalesOrder.php', 7, $db);
NewMenuItem('orders', 'Transactions', _('Process Recurring Orders'), '/RecurringSalesOrdersProcess.php', 8, $db);

NewMenuItem('orders', 'Reports', _('Order Inquiry'), '/SelectCompletedOrder.php', 1, $db);
NewMenuItem('orders', 'Reports', _('Print Price Lists'), '/PDFPriceList.php', 2, $db);
NewMenuItem('orders', 'Reports', _('Order Status Report'), '/PDFOrderStatus.php', 3, $db);
NewMenuItem('orders', 'Reports', _('Orders Invoiced Reports'), '/PDFOrdersInvoiced.php', 4, $db);
NewMenuItem('orders', 'Reports', _('Daily Sales Inquiry'), '/DailySalesInquiry.php', 5, $db);
NewMenuItem('orders', 'Reports', _('Sales By Sales Type Inquiry'), '/SalesByTypePeriodInquiry.php', 6, $db);
NewMenuItem('orders', 'Reports', _('Sales By Category Inquiry'), '/SalesCategoryPeriodInquiry.php', 7, $db);
NewMenuItem('orders', 'Reports', _('Top Sellers Inquiry'), '/SalesTopItemsInquiry.php', 8, $db);
NewMenuItem('orders', 'Reports', _('Order Delivery Differences Report'), '/PDFDeliveryDifferences.php', 9, $db);
NewMenuItem('orders', 'Reports', _('Delivery In Full On Time (DIFOT) Report'), '/PDFDIFOT.php', 10, $db);
NewMenuItem('orders', 'Reports', _('Sales Order Detail Or Summary Inquiries'), '/SalesInquiry.php', 11, $db);
NewMenuItem('orders', 'Reports', _('Top Sales Items Report'), '/TopItems.php', 12, $db);
NewMenuItem('orders', 'Reports', _('Worst Sales Items Report'), '/NoSalesItems.php', 13, $db);
NewMenuItem('orders', 'Reports', _('Sales With Low Gross Profit Report'), '/PDFLowGP.php', 14, $db);
NewMenuItem('orders', 'Reports', _('Sell Through Support Claims Report'), '/PDFSellThroughSupportClaim.php', 15, $db);

NewMenuItem('orders', 'Maintenance', _('Select Contract'), '/SelectContract.php', 1, $db);
NewMenuItem('orders', 'Maintenance', _('Create Contract'), '/Contracts.php', 2, $db);
NewMenuItem('orders', 'Maintenance', _('Sell Through Support Deals'), '/SellThroughSupport.php', 3, $db);

NewMenuItem('AR', 'Transactions', _('Select Order to Invoice'), '/SelectSalesOrder.php', 1, $db);
NewMenuItem('AR', 'Transactions', _('Create A Credit Note'), '/SelectCreditItems.php?NewCredit=Yes', 2, $db);
NewMenuItem('AR', 'Transactions', _('Enter Receipts'), '/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer', 3, $db);
NewMenuItem('AR', 'Transactions', _('Allocate Receipts or Credit Notes'), '/CustomerAllocations.php', 4, $db);

NewMenuItem('AR', 'Reports', _('Where Allocated Inquiry'), '/CustWhereAlloc.php', 1, $db);
NewMenuItem('AR', 'Reports', _('Print Invoices or Credit Notes'), '/PrintCustTrans.php', 2, $db);
NewMenuItem('AR', 'Reports', _('Print Statements'), '/PrintCustStatements.php', 3, $db);
NewMenuItem('AR', 'Reports', _('Sales Analysis Reports'), '/SalesAnalRepts.php', 4, $db);
NewMenuItem('AR', 'Reports', _('Aged Customer Balances/Overdues Report'), '/AgedDebtors.php', 5, $db);
NewMenuItem('AR', 'Reports', _('Re-Print A Deposit Listing'), '/PDFBankingSummary.php', 6, $db);
NewMenuItem('AR', 'Reports', _('Debtor Balances At A Prior Month End'), '/DebtorsAtPeriodEnd.php', 7, $db);
NewMenuItem('AR', 'Reports', _('Customer Listing By Area/Salesperson'), '/PDFCustomerList.php', 8, $db);
NewMenuItem('AR', 'Reports', _('Sales Graphs'), '/SalesGraph.php', 9, $db);
NewMenuItem('AR', 'Reports', _('List Daily Transactions'), '/PDFCustTransListing.php', 10, $db);
NewMenuItem('AR', 'Reports', _('Customer Transaction Inquiries'), '/CustomerTransInquiry.php', 11, $db);

NewMenuItem('AR', 'Maintenance', _('Add Customer'), '/Customers.php', 1, $db);
NewMenuItem('AR', 'Maintenance', _('Select Customer'), '/SelectCustomer.php', 2, $db);

NewMenuItem('AP', 'Transactions', _('Select Supplier'), '/SelectSupplier.php', 1, $db);
NewMenuItem('AP', 'Transactions', _('Supplier Allocations'), '/SupplierAllocations.php', 2, $db);

NewMenuItem('AP', 'Reports', _('Aged Supplier Report'), '/AgedSuppliers.php', 1, $db);
NewMenuItem('AP', 'Reports', _('Payment Run Report'), '/SuppPaymentRun.php', 2, $db);
NewMenuItem('AP', 'Reports', _('Remittance Advices'), '/PDFRemittanceAdvice.php', 3, $db);
NewMenuItem('AP', 'Reports', _('Outstanding GRNs Report'), '/OutstandingGRNs.php', 4, $db);
NewMenuItem('AP', 'Reports', _('Supplier Balances At A Prior Month End'), '/SupplierBalsAtPeriodEnd.php', 5, $db);
NewMenuItem('AP', 'Reports', _('List Daily Transactions'), '/PDFSuppTransListing.php', 6, $db);
NewMenuItem('AP', 'Reports', _('Supplier Transaction Inquiries'), '/SupplierTransInquiry.php', 7, $db);

NewMenuItem('AP', 'Maintenance', _('Add Supplier'), '/Suppliers.php', 1, $db);
NewMenuItem('AP', 'Maintenance', _('Select Supplier'), '/SelectSupplier.php', 2, $db);
NewMenuItem('AP', 'Maintenance', _('Maintain Factor Companies'), '/Factors.php', 3, $db);

NewMenuItem('PO', 'Transactions', _('Purchase Orders'), '/PO_SelectOSPurchOrder.php', 1, $db);
NewMenuItem('PO', 'Transactions', _('Add Purchase Order'), '/PO_Header.php?NewOrder=Yes', 2, $db);
NewMenuItem('PO', 'Transactions', _('Create a New Tender'), '/SupplierTenderCreate.php?New=Yes', 3, $db);
NewMenuItem('PO', 'Transactions', _('Edit Existing Tenders'), '/SupplierTenderCreate.php?Edit=Yes', 4, $db);
NewMenuItem('PO', 'Transactions', _('Process Tenders and Offers'), '/OffersReceived.php', 5, $db);
NewMenuItem('PO', 'Transactions', _('Orders to Authorise'), '/PO_AuthoriseMyOrders.php', 6, $db);
NewMenuItem('PO', 'Transactions', _('Shipment Entry'), '/SelectSupplier.php', 7, $db);
NewMenuItem('PO', 'Transactions', _('Select A Shipment'), '/Shipt_Select.php', 8, $db);

NewMenuItem('PO', 'Reports', _('Purchase Order Inquiry'), '/PO_SelectPurchOrder.php', 1, $db);
NewMenuItem('PO', 'Reports', _('Purchase Order Detail Or Summary Inquiries'), '/POReport.php', 2, $db);
NewMenuItem('PO', 'Reports', _('Supplier Price List'), '/SuppPriceList.php', 3, $db);

NewMenuItem('PO', 'Maintenance', _('Maintain Supplier Price Lists'), '/SupplierPriceList.php', 1, $db);
NewMenuItem('PO', 'Maintenance', _('Clear Orders with Quantity on Back Orders'), '/POClearBackOrders.php', 2, $db);

NewMenuItem('stock', 'Transactions', _('Receive Purchase Orders'), '/PO_SelectOSPurchOrder.php', 1, $db);
NewMenuItem('stock', 'Transactions', _('Bulk Inventory Transfer') . ' - ' . _('Dispatch'), '/StockLocTransfer.php', 2, $db);
NewMenuItem('stock', 'Transactions', _('Bulk Inventory Transfer') . ' - ' . _('Receive'), '/StockLocTransferReceive.php', 3, $db);
NewMenuItem('stock', 'Transactions', _('Inventory Location Transfers'), '/StockTransfers.php?New=Yes', 4, $db);
NewMenuItem('stock', 'Transactions', _('Inventory Adjustments'), '/StockAdjustments.php?NewAdjustment=Yes', 5, $db);
NewMenuItem('stock', 'Transactions', _('Reverse Goods Received'), '/ReverseGRN.php', 6, $db);
NewMenuItem('stock', 'Transactions', _('Enter Stock Counts'), '/StockCounts.php', 7, $db);
NewMenuItem('stock', 'Transactions', _('Create a New Internal Stock Request'), '/InternalStockRequest.php?New=Yes', 8, $db);
NewMenuItem('stock', 'Transactions', _('Authorise Internal Stock Requests'), '/InternalStockRequestAuthorisation.php', 9, $db);
NewMenuItem('stock', 'Transactions', _('Fulfil Internal Stock Requests'), '/InternalStockRequestFulfill.php', 10, $db);

NewMenuItem('stock', 'Reports', _('Serial Item Research Tool'), '/StockSerialItemResearch.php', 1, $db);
NewMenuItem('stock', 'Reports', _('Print Price Labels'), '/PDFPrintLabel.php', 2, $db);
NewMenuItem('stock', 'Reports', _('Reprint GRN'), '/ReprintGRN.php', 3, $db);
NewMenuItem('stock', 'Reports', _('Inventory Item Movements'), '/StockMovements.php', 4, $db);
NewMenuItem('stock', 'Reports', _('Inventory Item Status'), '/StockStatus.php', 5, $db);
NewMenuItem('stock', 'Reports', _('Inventory Item Usage'), '/StockUsage.php', 6, $db);
NewMenuItem('stock', 'Reports', _('Inventory Quantities'), '/InventoryQuantities.php', 7, $db);
NewMenuItem('stock', 'Reports', _('Reorder Level'), '/ReorderLevel.php', 8, $db);
NewMenuItem('stock', 'Reports', _('Stock Dispatch'), '/StockDispatch.php', 9, $db);
NewMenuItem('stock', 'Reports', _('Inventory Valuation Report'), '/InventoryValuation.php', 10, $db);
NewMenuItem('stock', 'Reports', _('Mail Inventory Valuation Report'), '/MailInventoryValuation.php', 11, $db);
NewMenuItem('stock', 'Reports', _('Inventory Planning Report'), '/InventoryPlanning.php', 12, $db);
NewMenuItem('stock', 'Reports', _('Inventory Planning Based On Preferred Supplier Data'), '/InventoryPlanningPrefSupplier.php', 13, $db);
NewMenuItem('stock', 'Reports', _('Inventory Stock Check Sheets'), '/StockCheck.php', 14, $db);
NewMenuItem('stock', 'Reports', _('Make Inventory Quantities CSV'), '/StockQties_csv.php', 15, $db);
NewMenuItem('stock', 'Reports', _('Compare Counts Vs Stock Check Data'), '/PDFStockCheckComparison.php', 16, $db);
NewMenuItem('stock', 'Reports', _('All Inventory Movements By Location/Date'), '/StockLocMovements.php', 17, $db);
NewMenuItem('stock', 'Reports', _('List Inventory Status By Location/Category'), '/StockLocStatus.php', 18, $db);
NewMenuItem('stock', 'Reports', _('Historical Stock Quantity By Location/Category'), '/StockQuantityByDate.php', 19, $db);
NewMenuItem('stock', 'Reports', _('List Negative Stocks'), '/PDFStockNegatives.php', 20, $db);
NewMenuItem('stock', 'Reports', _('Period Stock Transaction Listing'), '/PDFPeriodStockTransListing.php', 21, $db);
NewMenuItem('stock', 'Reports', _('Stock Transfer Note'), '/PDFStockTransfer.php', 22, $db);

NewMenuItem('stock', 'Maintenance', _('Add A New Item'), '/Stocks.php', 1, $db);
NewMenuItem('stock', 'Maintenance', _('Select An Item'), '/SelectProduct.php', 2, $db);
NewMenuItem('stock', 'Maintenance', _('Sales Category Maintenance'), '/SalesCategories.php', 3, $db);
NewMenuItem('stock', 'Maintenance', _('Add or Update Prices Based On Costs'), '/PricesBasedOnMarkUp.php', 4, $db);
NewMenuItem('stock', 'Maintenance', _('View or Update Prices Based On Costs'), '/PricesByCost.php', 5, $db);
NewMenuItem('stock', 'Maintenance', _('Upload new prices from csv file'), '/UploadPriceList.php', 6, $db);
NewMenuItem('stock', 'Maintenance', _('Reorder Level By Category/Location'), '/ReorderLevelLocation.php', 7, $db);
NewMenuItem('stock', 'Maintenance', _('ABC Ranking Methods'), '/ABCRankingMethods.php', 8, $db);
NewMenuItem('stock', 'Maintenance', _('ABC Ranking Groups'), '/ABCRankingGroups.php', 9, $db);
NewMenuItem('stock', 'Maintenance', _('Run ABC Ranking Analysis'), '/ABCRunAnalysis.php', 10, $db);

NewMenuItem('manuf', 'Transactions', _('Work Order Entry'), '/WorkOrderEntry.php?New=True', 1, $db);
NewMenuItem('manuf', 'Transactions', _('Select A Work Order'), '/SelectWorkOrder.php', 2, $db);

NewMenuItem('manuf', 'Reports', _('Select A Work Order'), '/SelectWorkOrder.php', 1, $db);
NewMenuItem('manuf', 'Reports', _('Costed Bill Of Material Inquiry'), '/BOMInquiry.php', 2, $db);
NewMenuItem('manuf', 'Reports', _('Where Used Inquiry'), '/WhereUsedInquiry.php', 3, $db);
NewMenuItem('manuf', 'Reports', _('Bill Of Material Listing'), '/BOMListing.php', 4, $db);
NewMenuItem('manuf', 'Reports', _('Indented Bill Of Material Listing'), '/BOMIndented.php', 5, $db);
NewMenuItem('manuf', 'Reports', _('List Components Required'), '/BOMExtendedQty.php', 6, $db);
NewMenuItem('manuf', 'Reports', _('List Materials Not Used anywhere'), '/MaterialsNotUsed.php', 7, $db);
NewMenuItem('manuf', 'Reports', _('Indented Where Used Listing'), '/BOMIndentedReverse.php', 8, $db);
NewMenuItem('manuf', 'Reports', _('MRP'), '/MRPReport.php', 9, $db);
NewMenuItem('manuf', 'Reports', _('MRP Shortages'), '/MRPShortages.php', 10, $db);
NewMenuItem('manuf', 'Reports', _('MRP Suggested Purchase Orders'), '/MRPPlannedPurchaseOrders.php', 11, $db);
NewMenuItem('manuf', 'Reports', _('MRP Suggested Work Orders'), '/MRPPlannedWorkOrders.php', 12, $db);
NewMenuItem('manuf', 'Reports', _('MRP Reschedules Required'), '/MRPReschedules.php', 13, $db);

NewMenuItem('manuf', 'Maintenance', _('Work Centre'), '/WorkCentres.php', 1, $db);
NewMenuItem('manuf', 'Maintenance', _('Bills Of Material'), '/BOMs.php', 2, $db);
NewMenuItem('manuf', 'Maintenance', _('Copy a Bill Of Materials Between Items'), '/CopyBOM.php', 3, $db);
NewMenuItem('manuf', 'Maintenance', _('Master Schedule'), '/MRPDemands.php', 4, $db);
NewMenuItem('manuf', 'Maintenance', _('Auto Create Master Schedule'), '/MRPCreateDemands.php', 5, $db);
NewMenuItem('manuf', 'Maintenance', _('MRP Calculation'), '/MRP.php', 6, $db);

NewMenuItem('GL', 'Transactions', _('Bank Account Payments Entry'), '/Payments.php?NewPayment=Yes', 1, $db);
NewMenuItem('GL', 'Transactions', _('Bank Account Receipts Entry'), '/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL', 2, $db);
NewMenuItem('GL', 'Transactions', _('Journal Entry'), '/GLJournal.php?NewJournal=Yes', 3, $db);
NewMenuItem('GL', 'Transactions', _('Bank Account Payments Matching'), '/BankMatching.php?Type=Payments', 4, $db);
NewMenuItem('GL', 'Transactions', _('Bank Account Receipts Matching'), '/BankMatching.php?Type=Receipts', 5, $db);

NewMenuItem('GL', 'Reports', _('Trial Balance'), '/GLTrialBalance.php', 1, $db);
NewMenuItem('GL', 'Reports', _('Account Inquiry'), '/SelectGLAccount.php', 2, $db);
NewMenuItem('GL', 'Reports', _('Account Listing'), '/GLAccountReport.php', 3, $db);
NewMenuItem('GL', 'Reports', _('Account Listing to CSV File'), '/GLAccountCSV.php', 4, $db);
NewMenuItem('GL', 'Reports', _('General Ledger Journal Inquiry'), '/GLJournalInquiry.php', 5, $db);
NewMenuItem('GL', 'Reports', _('Bank Account Reconciliation Statement'), '/BankReconciliation.php', 6, $db);
NewMenuItem('GL', 'Reports', _('Cheque Payments Listing'), '/PDFChequeListing.php', 7, $db);
NewMenuItem('GL', 'Reports', _('Bank Transactions Inquiry'), '/DailyBankTransactions.php', 8, $db);
NewMenuItem('GL', 'Reports', _('Monthly Bank Inquiry'), '/MonthlyBankTransactions.php', 9, $db);
NewMenuItem('GL', 'Reports', _('Profit and Loss Statement'), '/GLProfit_Loss.php', 10, $db);
NewMenuItem('GL', 'Reports', _('Balance Sheet'), '/GLBalanceSheet.php', 11, $db);
NewMenuItem('GL', 'Reports', _('Tag Reports'), '/GLTagProfit_Loss.php', 12, $db);
NewMenuItem('GL', 'Reports', _('Tax Reports'), '/Tax.php', 13, $db);

NewMenuItem('GL', 'Maintenance', _('GL Account'), '/GLAccounts.php', 1, $db);
NewMenuItem('GL', 'Maintenance', _('GL Budgets'), '/GLBudgets.php', 2, $db);
NewMenuItem('GL', 'Maintenance', _('Account Groups'), '/AccountGroups.php', 3, $db);
NewMenuItem('GL', 'Maintenance', _('Account Sections'), '/AccountSections.php', 4, $db);
NewMenuItem('GL', 'Maintenance', _('GL Tags'), '/GLTags.php', 5, $db);

NewMenuItem('FA', 'Transactions', _('Add a new Asset'), '/FixedAssetItems.php', 1, $db);
NewMenuItem('FA', 'Transactions', _('Select an Asset'), '/SelectAsset.php', 2, $db);
NewMenuItem('FA', 'Transactions', _('Change Asset Location'), '/FixedAssetTransfer.php', 3, $db);
NewMenuItem('FA', 'Transactions', _('Depreciation Journal'), '/FixedAssetDepreciation.php', 4, $db);

NewMenuItem('FA', 'Reports', _('Asset Register'), '/FixedAssetRegister.php', 1, $db);

NewMenuItem('FA', 'Maintenance', _('Asset Categories Maintenance'), '/FixedAssetCategories.php', 1, $db);
NewMenuItem('FA', 'Maintenance', _('Add or Maintain Asset Locations'), '/FixedAssetLocations.php', 2, $db);

NewMenuItem('PC', 'Transactions', _('Assign Cash to PC Tab'), '/PcAssignCashToTab.php', 1, $db);
NewMenuItem('PC', 'Transactions', _('Claim Expenses From PC Tab'), '/PcClaimExpensesFromTab.php', 2, $db);
NewMenuItem('PC', 'Transactions', _('Expenses Authorisation'), '/PcAuthorizeExpenses.php', 3, $db);

NewMenuItem('PC', 'Reports', _('PC Tab General Report'), '/PcReportTab.php', 1, $db);

NewMenuItem('PC', 'Maintenance', _('Types of PC Tabs'), '/PcTypeTabs.php', 1, $db);
NewMenuItem('PC', 'Maintenance', _('PC Tabs'), '/PcTabs.php', 2, $db);
NewMenuItem('PC', 'Maintenance', _('PC Expenses'), '/PcExpenses.php', 3, $db);
NewMenuItem('PC', 'Maintenance', _('Expenses for Type of PC Tab'), '/PcExpensesTypeTab.php', 4, $db);

NewMenuItem('system', 'Transactions', _('Company Preferences'), '/CompanyPreferences.php', 1, $db);
NewMenuItem('system', 'Transactions', _('Configuration Settings'), '/SystemParameters.php', 2, $db);
NewMenuItem('system', 'Transactions', _('User Maintenance'), '/WWW_Users.php', 3, $db);
NewMenuItem('system', 'Transactions', _('Maintain Security Tokens'), '/SecurityTokens.php', 4, $db);
NewMenuItem('system', 'Transactions', _('Access Permissions Maintenance'), '/WWW_Access.php', 5, $db);
NewMenuItem('system', 'Transactions', _('Page Security Settings'), '/PageSecurity.php', 6, $db);
NewMenuItem('system', 'Transactions', _('Bank Accounts'), '/BankAccounts.php', 7, $db);
NewMenuItem('system', 'Transactions', _('Currency Maintenance'), '/Currencies.php', 8, $db);
NewMenuItem('system', 'Transactions', _('Tax Authorities and Rates Maintenance'), '/TaxAuthorities.php', 9, $db);
NewMenuItem('system', 'Transactions', _('Tax Group Maintenance'), '/TaxGroups.php', 10, $db);
NewMenuItem('system', 'Transactions', _('Dispatch Tax Province Maintenance'), '/TaxProvinces.php', 11, $db);
NewMenuItem('system', 'Transactions', _('Tax Category Maintenance'), '/TaxCategories.php', 12, $db);
NewMenuItem('system', 'Transactions', _('List Periods Defined'), '/PeriodsInquiry.php', 13, $db);
NewMenuItem('system', 'Transactions', _('Report Builder Tool'), '/reportwriter/admin/ReportCreator.php', 14, $db);
NewMenuItem('system', 'Transactions', _('View Audit Trail'), '/AuditTrail.php', 15, $db);
NewMenuItem('system', 'Transactions', _('Geocode Setup'), '/GeocodeSetup.php', 16, $db);
NewMenuItem('system', 'Transactions', _('Form Layout Editor'), '/FormDesigner.php', 17, $db);
NewMenuItem('system', 'Transactions', _('Label Templates Maintenance'), '/Labels.php', 18, $db);
NewMenuItem('system', 'Transactions', _('SMTP Server Details'), '/SMTPServer.php', 19, $db);
NewMenuItem('system', 'Transactions', _('Mailing Group Maintenance'), '/MailingGroupMaintenance.php', 20, $db);

NewMenuItem('system', 'Reports', _('Sales Types'), '/SalesTypes.php', 1, $db);
NewMenuItem('system', 'Reports', _('Customer Types'), '/CustomerTypes.php', 2, $db);
NewMenuItem('system', 'Reports', _('Supplier Types'), '/SupplierTypes.php', 3, $db);
NewMenuItem('system', 'Reports', _('Credit Status'), '/CreditStatus.php', 4, $db);
NewMenuItem('system', 'Reports', _('Payment Terms'), '/PaymentTerms.php', 5, $db);
NewMenuItem('system', 'Reports', _('Set Purchase Order Authorisation levels'), '/PO_AuthorisationLevels.php', 6, $db);
NewMenuItem('system', 'Reports', _('Payment Methods'), '/PaymentMethods.php', 7, $db);
NewMenuItem('system', 'Reports', _('Sales People'), '/SalesPeople.php', 8, $db);
NewMenuItem('system', 'Reports', _('Sales Areas'), '/Areas.php', 9, $db);
NewMenuItem('system', 'Reports', _('Shippers'), '/Shippers.php', 10, $db);
NewMenuItem('system', 'Reports', _('Sales GL Interface Postings'), '/SalesGLPostings.php', 11, $db);
NewMenuItem('system', 'Reports', _('COGS GL Interface Postings'), '/COGSGLPostings.php', 12, $db);
NewMenuItem('system', 'Reports', _('Freight Costs Maintenance'), '/FreightCosts.php', 13, $db);
NewMenuItem('system', 'Reports', _('Discount Matrix'), '/DiscountMatrix.php', 14, $db);

NewMenuItem('system', 'Maintenance', _('Inventory Categories Maintenance'), '/StockCategories.php', 1, $db);
NewMenuItem('system', 'Maintenance', _('Inventory Locations Maintenance'), '/Locations.php', 2, $db);
NewMenuItem('system', 'Maintenance', _('Discount Category Maintenance'), '/DiscountCategories.php', 3, $db);
NewMenuItem('system', 'Maintenance', _('Units of Measure'), '/UnitsOfMeasure.php', 4, $db);
NewMenuItem('system', 'Maintenance', _('MRP Available Production Days'), '/MRPCalendar.php', 5, $db);
NewMenuItem('system', 'Maintenance', _('MRP Demand Types'), '/MRPDemandTypes.php', 6, $db);
NewMenuItem('system', 'Maintenance', _('Maintain Internal Departments'), '/Departments.php', 7, $db);
NewMenuItem('system', 'Maintenance', _('Maintain Internal Stock Categories to User Roles'),'/InternalStockCategoriesByRole.php', 8, $db);
NewMenuItem('system', 'Maintenance', _('Report a problem with KwaMoja'), '/ReportBug.php', 9, $db);
NewMenuItem('system', 'Maintenance', _('Upload a KwaMoja plugin file'), '/PluginUpload.php', 10, $db);
NewMenuItem('system', 'Maintenance', _('Install a KwaMoja plugin'), '/PluginInstall.php', 11, $db);
NewMenuItem('system', 'Maintenance', _('Remove a KwaMoja plugin'), '/PluginUnInstall.php', 12, $db);

NewMenuItem('Utilities', 'Transactions', _('Change A Customer Code'), '/Z_ChangeCustomerCode.php', 1, $db);
NewMenuItem('Utilities', 'Transactions', _('Change A Customer Branch Code'), '/Z_ChangeBranchCode.php', 2, $db);
NewMenuItem('Utilities', 'Transactions', _('Change A Supplier Code'), '/Z_ChangeSupplierCode.php', 3, $db);
NewMenuItem('Utilities', 'Transactions', _('Change A Location Code'), '/Z_ChangeLocationCode.php', 4, $db);
NewMenuItem('Utilities', 'Transactions', _('Change An Inventory Item Code'), '/Z_ChangeStockCode.php', 5, $db);
NewMenuItem('Utilities', 'Transactions', _('Change A General Ledger Code'), '/Z_ChangeGLAccountCode.php', 6, $db);
NewMenuItem('Utilities', 'Transactions', _('Update costs for all BOM items, from the bottom up'), '/Z_BottomUpCosts.php', 7, $db);
NewMenuItem('Utilities', 'Transactions', _('Re-apply costs to Sales Analysis'), '/Z_ReApplyCostToSA.php', 8, $db);
NewMenuItem('Utilities', 'Transactions', _('Delete sales transactions'), '/Z_DeleteSalesTransActions.php', 9, $db);
NewMenuItem('Utilities', 'Transactions', _('Reverse all supplier payments on a specified date'), '/Z_ReverseSuppPaymentRun.php', 10, $db);

NewMenuItem('Utilities', 'Reports', _('Show Local Currency Total Debtor Balances'), '/Z_CurrencyDebtorsBalances.php', 1, $db);
NewMenuItem('Utilities', 'Reports', _('Show Local Currency Total Suppliers Balances'), '/Z_CurrencySuppliersBalances.php', 2, $db);
NewMenuItem('Utilities', 'Reports', _('Show General Transactions That Do Not Balance'), '/Z_CheckGLTransBalance.php', 3, $db);
NewMenuItem('Utilities', 'Reports', _('List of items without picture'), '/Z_ItemsWithoutPicture.php', 4, $db);

NewMenuItem('Utilities', 'Maintenance', _('Maintain Language Files'), '/Z_poAdmin.php', 1, $db);
NewMenuItem('Utilities', 'Maintenance', _('Make New Company'), '/Z_MakeNewCompany.php', 2, $db);
NewMenuItem('Utilities', 'Maintenance', _('Data Export Options'), '/Z_DataExport.php', 3, $db);
NewMenuItem('Utilities', 'Maintenance', _('Import Stock Items from .csv'), '/Z_ImportStocks.php', 4, $db);
NewMenuItem('Utilities', 'Maintenance', _('Import Fixed Assets from .csv file'), '/Z_ImportFixedAssets.php', 5, $db);
NewMenuItem('Utilities', 'Maintenance', _('Create new company template SQL file and submit to KwaMoja'), '/Z_CreateCompanyTemplateFile.php', 6, $db);
NewMenuItem('Utilities', 'Maintenance', _('Re-calculate brought forward amounts in GL'), '/Z_UpdateChartDetailsBFwd.php', 7, $db);
NewMenuItem('Utilities', 'Maintenance', _('Re-Post all GL transactions from a specified period'), '/Z_RePostGLFromPeriod.php', 8, $db);
NewMenuItem('Utilities', 'Maintenance', _('Purge all old prices'), '/Z_DeleteOldPrices.php', 9, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>