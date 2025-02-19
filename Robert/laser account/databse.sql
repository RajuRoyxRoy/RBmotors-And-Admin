CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bank_account VARCHAR(50) NOT NULL,
    narration TEXT NOT NULL,
    debit DECIMAL(10, 2) DEFAULT 0,
    credit DECIMAL(10, 2) DEFAULT 0,
    current_balance DECIMAL(10, 2) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_number VARCHAR(50) UNIQUE NOT NULL,
    bank_name VARCHAR(100) NOT NULL
);
