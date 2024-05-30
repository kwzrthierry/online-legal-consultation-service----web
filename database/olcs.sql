-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2024 at 10:42 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `olcs`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `date` varchar(120) NOT NULL,
  `duration` enum('9:00 - 12:00','14:30 - 16:20') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `client_id`, `lawyer_id`, `date`, `duration`) VALUES
(22, 5, 5, '2024-05-23', '9:00 - 12:00');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(120) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `birth_date` varchar(40) NOT NULL,
  `sex` enum('male','female') NOT NULL,
  `email` varchar(200) NOT NULL,
  `phone_number` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `first_name`, `last_name`, `birth_date`, `sex`, `email`, `phone_number`) VALUES
(5, 'shyaka', 'crispin', '2024-04-28', 'male', 'sh@gmail.com', '0788906652'),
(6, 'beni', 'yakini', '2024-05-23', 'male', 'beni@gmaiil.com', '');

--
-- Triggers `clients`
--
DELIMITER $$
CREATE TRIGGER `after_update_client_info` AFTER UPDATE ON `clients` FOR EACH ROW BEGIN
    IF OLD.first_name <> NEW.first_name THEN
        INSERT INTO updates_on_client_info (client_id, attribute_changed, old_value, new_value)
        VALUES (NEW.client_id, 'first_name', OLD.first_name, NEW.first_name);
    END IF;
    
    IF OLD.last_name <> NEW.last_name THEN
        INSERT INTO updates_on_client_info (client_id, attribute_changed, old_value, new_value)
        VALUES (NEW.client_id, 'last_name', OLD.last_name, NEW.last_name);
    END IF;
    
    IF OLD.birth_date <> NEW.birth_date THEN
        INSERT INTO updates_on_client_info (client_id, attribute_changed, old_value, new_value)
        VALUES (NEW.client_id, 'birth_date', OLD.birth_date, NEW.birth_date);
    END IF;
    
    IF OLD.sex <> NEW.sex THEN
        INSERT INTO updates_on_client_info (client_id, attribute_changed, old_value, new_value)
        VALUES (NEW.client_id, 'sex', OLD.sex, NEW.sex);
    END IF;
    
    IF OLD.email <> NEW.email THEN
        INSERT INTO updates_on_client_info (client_id, attribute_changed, old_value, new_value)
        VALUES (NEW.client_id, 'email', OLD.email, NEW.email);
    END IF;
    
    IF OLD.phone_number <> NEW.phone_number THEN
        INSERT INTO updates_on_client_info (client_id, attribute_changed, old_value, new_value)
        VALUES (NEW.client_id, 'phone_number', OLD.phone_number, NEW.phone_number);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `consultation`
--

CREATE TABLE `consultation` (
  `consultation_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `cases` varchar(200) NOT NULL,
  `consultation` varchar(200) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consultation`
--

INSERT INTO `consultation` (`consultation_id`, `client_id`, `lawyer_id`, `cases`, `consultation`, `timestamp`) VALUES
(3, 5, 0, 'i am a suspect for tax evasion, how can i proceed', '', '2024-05-22 18:37:08');

-- --------------------------------------------------------

--
-- Table structure for table `document`
--

CREATE TABLE `document` (
  `document_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `document_name` varchar(80) NOT NULL,
  `description` varchar(120) NOT NULL,
  `location` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document`
--

INSERT INTO `document` (`document_id`, `client_id`, `document_name`, `description`, `location`) VALUES
(16, 5, 'B230910113756G06A.pdf', 'Uploaded document for review', 'documents/B230910113756G06A.pdf'),
(17, 5, 'Resume-Thierry-Kwizera.pdf', 'Uploaded document for review', 'documents/Resume-Thierry-Kwizera.pdf'),
(18, 5, 'KnowB4 -- THIERRY KWIZERA.docx', 'Uploaded document for review', 'documents/KnowB4 -- THIERRY KWIZERA.docx');

-- --------------------------------------------------------

--
-- Table structure for table `lawyers`
--

CREATE TABLE `lawyers` (
  `lawyer_id` int(11) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `department` varchar(120) NOT NULL,
  `sex` enum('male','female') NOT NULL,
  `email` varchar(200) NOT NULL,
  `phone_number` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lawyers`
--

INSERT INTO `lawyers` (`lawyer_id`, `full_name`, `department`, `sex`, `email`, `phone_number`) VALUES
(5, 'kwizera thierry', 'Generalist', 'male', 'tt@gmail.com', '345676549'),
(8, 'John Doe', 'Contract', 'male', 'johndoe@example.com', '1234567890'),
(9, 'Jane Smith', 'Lease Agreement', 'female', 'janesmith@example.com', '0987654321'),
(10, 'Michael Johnson', 'Employment Agreement', 'male', 'michaelj@example.com', '1122334455'),
(11, 'Emily Davis', 'Non-Disclosure Agreement (NDA)', 'female', 'emilyd@example.com', '2233445566'),
(12, 'William Brown', 'Power of Attorney', 'male', 'williamb@example.com', '3344556677'),
(13, 'Olivia Wilson', 'Will', 'female', 'oliviaw@example.com', '4455667788'),
(14, 'James Jones', 'Partnership Agreement', 'male', 'jamesj@example.com', '5566778899'),
(15, 'Isabella Garcia', 'Memorandum of Understanding (MOU)', 'female', 'isabellag@example.com', '6677889900'),
(16, 'Alexander Martinez', 'Corporate Resolution', 'male', 'alexanderm@example.com', '7788990011'),
(17, 'Sophia Hernandez', 'Compliance Document', 'female', 'sophiah@example.com', '8899001122');

--
-- Triggers `lawyers`
--
DELIMITER $$
CREATE TRIGGER `after_lawyer_insert` AFTER INSERT ON `lawyers` FOR EACH ROW BEGIN
    
    SET @password = SUBSTRING(MD5(RAND()), 1, 12);
    
    SET @consonants = 'bcdfghjklmnpqrstvwxyz';
    SET @username = 
        CONCAT(
            SUBSTRING(@consonants, 1, 1),
            SUBSTRING(@consonants, 2, 1),
            SUBSTRING(NEW.full_name, 1, 1),
            NEW.lawyer_id
        );
    
    INSERT INTO users (referenced_id, Username,user_type, Password)
    VALUES (NEW.lawyer_id, @username,'lawyer', @password);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mediation_cases`
--

CREATE TABLE `mediation_cases` (
  `med_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `first_party` varchar(80) NOT NULL,
  `second_party` varchar(80) NOT NULL,
  `department` varchar(40) NOT NULL,
  `case_note` varchar(400) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mediation_cases`
--

INSERT INTO `mediation_cases` (`med_id`, `client_id`, `lawyer_id`, `first_party`, `second_party`, `department`, `case_note`) VALUES
(7, 5, 5, 'kwizera thierry', 'shyaka crispin', 'Generalist', 'divorce processing');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `sender` enum('lawyer','client') NOT NULL,
  `message` varchar(400) NOT NULL,
  `date_sent` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `client_id`, `lawyer_id`, `sender`, `message`, `date_sent`) VALUES
(4, 5, 5, 'client', 'I want to schedule an appointment', '2024-05-21 13:25:22'),
(5, 5, 5, 'lawyer', 'You want to talk about which subject', '2024-05-21 13:26:33'),
(6, 5, 5, 'client', 'Land mitation', '2024-05-21 13:27:23'),
(10, 6, 11, 'lawyer', 'HELLO!', '2024-05-22 14:32:53'),
(11, 5, 5, 'lawyer', 'https://chatgpt.com/', '2024-05-22 16:31:34'),
(12, 5, 5, 'lawyer', 'thats the zoom link for our mediation\r\n', '2024-05-22 16:34:07');

-- --------------------------------------------------------

--
-- Table structure for table `request`
--

CREATE TABLE `request` (
  `request_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `document_name` varchar(40) NOT NULL,
  `request` varchar(130) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request`
--

INSERT INTO `request` (`request_id`, `client_id`, `lawyer_id`, `document_name`, `request`) VALUES
(9, 5, 5, 'B230910113756G06A.pdf', 'Review of B230910113756G06A.pdf'),
(10, 5, 12, 'Power of Attorney', 'Preparation of Power of Attorney'),
(11, 5, 5, 'KnowB4 -- THIERRY KWIZERA.docx', 'Review of KnowB4 -- THIERRY KWIZERA.docx'),
(12, 6, 11, 'Non-Disclosure Agreement (NDA)', 'Preparation of Non-Disclosure Agreement (NDA)');

-- --------------------------------------------------------

--
-- Table structure for table `updates_on_client_info`
--

CREATE TABLE `updates_on_client_info` (
  `update_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `attribute_changed` varchar(50) DEFAULT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `updates_on_client_info`
--

INSERT INTO `updates_on_client_info` (`update_id`, `client_id`, `attribute_changed`, `old_value`, `new_value`, `changed_at`) VALUES
(1, 5, 'phone_number', '', '0788906652', '2024-05-19 19:17:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `referenced_id` int(11) NOT NULL,
  `user_type` enum('client','lawyer') NOT NULL,
  `username` varchar(180) NOT NULL,
  `password` varchar(250) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `referenced_id`, `user_type`, `username`, `password`, `creation_date`) VALUES
(9, 5, 'client', 'shyaka', '$2y$10$HAvKAjzfOFnjJvnvaq8EcuubOIrCfXBBVLQt8hIdH9H8cQyJux2ue', '2024-05-18 22:57:10'),
(11, 5, 'lawyer', 'bck5', '332b03236e36', '2024-05-19 19:37:33'),
(12, 6, 'lawyer', 'bcn6', 'a4d7dd43d0c6', '2024-05-19 20:58:16'),
(13, 7, 'lawyer', 'bci7', '78de5b42af02', '2024-05-19 20:58:16'),
(14, 8, 'lawyer', 'bcJ8', 'a539b6bdad50', '2024-05-21 22:41:56'),
(15, 9, 'lawyer', 'bcJ9', 'f657a69b69a8', '2024-05-21 22:41:56'),
(16, 10, 'lawyer', 'bcM10', 'b873bf3c4ed2', '2024-05-21 22:41:56'),
(17, 11, 'lawyer', 'bcE11', 'ba5087661b3c', '2024-05-21 22:41:56'),
(18, 12, 'lawyer', 'bcW12', 'f2583e9d1410', '2024-05-21 22:41:56'),
(19, 13, 'lawyer', 'bcO13', '80f069bc30cb', '2024-05-21 22:41:56'),
(20, 14, 'lawyer', 'bcJ14', 'eb3951434158', '2024-05-21 22:41:56'),
(21, 15, 'lawyer', 'bcI15', 'c4f98cf60bd4', '2024-05-21 22:41:56'),
(22, 16, 'lawyer', 'bcA16', '23d92a08bfa2', '2024-05-21 22:41:56'),
(23, 17, 'lawyer', 'bcS17', '299e3736ca7a', '2024-05-21 22:41:56'),
(24, 6, 'client', 'beni', '$2y$10$L7qHKorjHcjEtAddjs983.pY8U5zmToUI57q.mELb8Jx5wcdkB/EW', '2024-05-22 13:50:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `lawyer_id` (`lawyer_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `consultation`
--
ALTER TABLE `consultation`
  ADD PRIMARY KEY (`consultation_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `lawyer_id` (`lawyer_id`);

--
-- Indexes for table `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `lawyers`
--
ALTER TABLE `lawyers`
  ADD PRIMARY KEY (`lawyer_id`);

--
-- Indexes for table `mediation_cases`
--
ALTER TABLE `mediation_cases`
  ADD PRIMARY KEY (`med_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `lawyer_id` (`lawyer_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `lawyer_id` (`lawyer_id`);

--
-- Indexes for table `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `lawyer_id` (`lawyer_id`);

--
-- Indexes for table `updates_on_client_info`
--
ALTER TABLE `updates_on_client_info`
  ADD PRIMARY KEY (`update_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `referenced_id` (`referenced_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(120) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `consultation`
--
ALTER TABLE `consultation`
  MODIFY `consultation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `document`
--
ALTER TABLE `document`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `lawyers`
--
ALTER TABLE `lawyers`
  MODIFY `lawyer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `mediation_cases`
--
ALTER TABLE `mediation_cases`
  MODIFY `med_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `request`
--
ALTER TABLE `request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `updates_on_client_info`
--
ALTER TABLE `updates_on_client_info`
  MODIFY `update_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`lawyer_id`) REFERENCES `lawyers` (`lawyer_id`);

--
-- Constraints for table `consultation`
--
ALTER TABLE `consultation`
  ADD CONSTRAINT `consultation_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`);

--
-- Constraints for table `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `document_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`);

--
-- Constraints for table `mediation_cases`
--
ALTER TABLE `mediation_cases`
  ADD CONSTRAINT `mediation_cases_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `mediation_cases_ibfk_2` FOREIGN KEY (`lawyer_id`) REFERENCES `lawyers` (`lawyer_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`lawyer_id`) REFERENCES `lawyers` (`lawyer_id`);

--
-- Constraints for table `request`
--
ALTER TABLE `request`
  ADD CONSTRAINT `request_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `request_ibfk_2` FOREIGN KEY (`lawyer_id`) REFERENCES `lawyers` (`lawyer_id`);

--
-- Constraints for table `updates_on_client_info`
--
ALTER TABLE `updates_on_client_info`
  ADD CONSTRAINT `updates_on_client_info_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
