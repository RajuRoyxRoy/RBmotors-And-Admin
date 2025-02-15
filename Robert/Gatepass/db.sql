
-- Create Table
CREATE TABLE gatepass_receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    vehicle_number VARCHAR(50) NOT NULL,
    invoice_no VARCHAR(50) NOT NULL,
    invoice_value DECIMAL(10, 2) NOT NULL,
    gate_pass_no VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Example Data
INSERT INTO gatepass_receipts (customer_name, vehicle_number, invoice_no, invoice_value, gate_pass_no)
VALUES ('John Doe', 'KA-01-AB-1234', 'INV12345', 15000.50, 'GP56789');
