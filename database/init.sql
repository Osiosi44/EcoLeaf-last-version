-- Import into an empty database selected in your hosting control panel.
CREATE TABLE users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	full_name varchar(100) NOT NULL,
	username varchar(50) NOT NULL,
	phone_number varchar(15) NOT NULL,
	addres text,
	email varchar(255) NOT NULL,
	hashed_password varchar(255) NOT NULL,
	role enum('admin','user') DEFAULT 'user',
	created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(50) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2), -- Optional, can be used for base price
	picture1 varchar(255) DEFAULT NULL,
    picture2 varchar(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE service_options (
    option_id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    option_name VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL, -- Price specific to the option
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(service_id)
);


-- Table: orders
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address VARCHAR(255) NOT NULL,
    pickup_date DATE NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'in_progress', 'complete', 'cancelled') DEFAULT 'pending',
    total DECIMAL(10, 2) DEFAULT 0.00, -- Calculate in application logic
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: order_items
CREATE TABLE order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    service_id INT NOT NULL,
    service_option_id INT, 
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL, 
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id),
    FOREIGN KEY (service_option_id) REFERENCES service_options(option_id)
);
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,    -- Who wrote the review
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE          
);

-- populate 

INSERT INTO services (service_name, description, price, picture1, picture2) VALUES
('Dry Cleaning', 'Get your delicate garments looking like new with our gentle dry cleaning service.','15','images/dry-cleaning.png','images/images.jpg'),
('Wash and Fold', 'Conveniently clean your everyday clothes with our wash and fold service.','1.75','images/clothes.png','images/male.png'),
('Alterations and Repairs', 'Give your garments a new lease on life with our expert alteration and repair services.','4','images/dry-cleaning.png','images/package-1.webp'),
('Special Care Washing', 'Trust us to clean your delicate or special-care items with our gentle washing techniques.','15','images/dry-cleaning.png','images/pack.jpg'),
('Ironing and Pressing', 'Get your garments looking crisp and polished with our expert ironing and pressing services.','6','images/dry-cleaning.png','images/delux2.jpg'),
('Pick up and Fast Delivery',null,'7.99','images/fast-delivery.png','images/demo.png');





-- Dry Cleaning Options
INSERT INTO service_options (service_id, option_name, price) VALUES
(1, 'Suits', 20.00),
(1, 'Dresses', 15.00),
(1, 'Coats', 25.00),
(1, 'Wedding Gowns', 200.00);

-- Wash and Fold Options
INSERT INTO service_options (service_id, option_name, price) VALUES
(2, 'Clothes', 1.75),  -- price per lb
(2, 'Towels', 1.75),   -- price per lb
(2, 'Bedding', 1.75),  -- price per lb
(2, 'Curtains', 8.00);

-- Alterations and Repairs Options
INSERT INTO service_options (service_id, option_name, price) VALUES
(3, 'Hemming Pants', 15.00),
(3, 'Altering Dresses', 35.00),
(3, 'Repairing Zippers', 20.00),
(3, 'Replacing Buttons', 4.00);

-- Special Care Washing Options
INSERT INTO service_options (service_id, option_name, price) VALUES
(4, 'Woolens', 15.00),
(4, 'Silks', 18.00),
(4, 'Suedes', 50.00),
(4, 'Leathers', 75.00);

-- Ironing and Pressing Options
INSERT INTO service_options (service_id, option_name, price) VALUES
(5, 'Shirts', 6.00),
(5, 'Pants', 7.00),
(5, 'Dresses', 12.00),
(5, 'Table Linens', 20.00);


-- Apply once to an existing EcoLeaf database before uploading the upgraded PHP files.
-- This migration preserves all customer accounts, orders, services, and reviews.
CREATE TABLE IF NOT EXISTS security_limits (
    bucket_key CHAR(64) NOT NULL PRIMARY KEY,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    window_start BIGINT NOT NULL,
    INDEX security_limits_window (window_start)
) ENGINE=InnoDB;
