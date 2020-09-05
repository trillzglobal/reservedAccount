
 DROP TABLE IF EXISTS `rA_vendors`;

CREATE TABLE `rA_vendors`(
     `id` bigint NOT NULL AUTO_INCREMENT,
    `appName` varchar(20) NOT NULL, 
    `appID` varchar(20) NOT NULL, 
    `webhook` text NOT NULL, 
    `charges` int(5) NOT NULL, 
    `hashkey` mediumtext NULL,
    PRIMARY KEY(`id`),
    UNIQUE KEY `appID`(`appID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

 DROP TABLE IF EXISTS `rA_vendors_request`;

CREATE TABLE `rA_vendors_request`(
     `id` bigint NOT NULL AUTO_INCREMENT,
    `appName` varchar(20) NOT NULL, 
    `appID` varchar(20) NOT NULL, 
    `dateTime` datetime DEFAULT NULL, 
    `customerReference` varchar(30) NOT NULL, 
    `customerName` varchar(100) DEFAULT NULL, 
    `customerEmail` varchar(100) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;


 DROP TABLE IF EXISTS `rA_customer_account`;

CREATE TABLE `rA_customer_account`(
    `id` bigint NOT NULL AUTO_INCREMENT,
    `appName` varchar(20) NOT NULL, 
    `appID` varchar(20) NOT NULL,
    `customerReference` varchar(30) NOT NULL, 
    `customerName` varchar(100) DEFAULT NULL, 
    `customerEmail` varchar(100) DEFAULT NULL, 
    `accountNumber` varchar(15) NOT NULL, 
    `status` int(2) NOT NULL, 
    `dateTime` datetime DEFAULT NULL, 
    `reservationReference` varchar(30) DEFAULT NULL, 
    `apiCall` text DEFAULT NULL, 
    `apiResponse` text DEFAULT NULL, 
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

    
DROP TABLE IF EXISTS `rA_webhook_transaction`;

CREATE TABLE `rA_webhook_transaction`(
    `id` bigint NOT NULL AUTO_INCREMENT, 
    `transactionReference` varchar(40) DEFAULT NULL, 
    `customerReference` varchar(40)DEFAULT NULL, 
    `amountPaid` varchar(10) DEFAULT NULL, 
    `totalAmount` varchar(10) DEFAULT NULL, 
    `dateTime` datetime DEFAULT NULL, 
    `apiCall` text DEFAULT NULL , 
    `appName` varchar(20) NOT NULL, 
    `appID` varchar(20) NOT NULL, 
    `appCharges` varchar(10) NOT NULL, 
    `amountSent` varchar(10) NOT NULL, 
    `accountNumber` varchar(10) DEFAULT NULL, 
    `status` int(2) NOT NULL,
     PRIMARY KEY(`id`)

)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;