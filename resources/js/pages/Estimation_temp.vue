const currencyMap = { USD: 1, EUR: 2, GBP: 3, BDT: 4 };
const statusMap = { Pending: 1, Approved: 2, Cancelled: 3, Expired: 4 };
const jobTypeMap = { Plumbing: 1, Painting: 2, Electrical: 3, Cleaning: 4, Carpentry: 5, Others: 6 };
const paymentMethodMap = { Cash: 1, "Credit Card": 2, "Debit Card": 3, "Bank Transfer": 4, Check: 5, Others: 6 };
const approvalMethodMap = { "Phone Call": 1, "Text Message": 2, Email: 3, "In Person": 4, Mail: 5 };

const reverseCurrencyMap = { 1: "USD", 2: "EUR", 3: "GBP", 4: "BDT" };
const reverseStatusMap = { 1: "Pending", 2: "Approved", 3: "Cancelled", 4: "Expired" };
const reverseJobTypeMap = { 1: "Plumbing", 2: "Painting", 3: "Electrical", 4: "Cleaning", 5: "Carpentry", 6: "Others" };
const reversePaymentMethodMap = { 1: "Cash", 2: "Credit Card", 3: "Debit Card", 4: "Bank Transfer", 5: "Check", 6: "Others" };
const reverseApprovalMethodMap = { 1: "Phone Call", 2: "Text Message", 3: "Email", 4: "In Person", 5: "Mail" };
