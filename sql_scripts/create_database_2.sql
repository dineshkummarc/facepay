-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2020 at 03:12 AM
-- Server version: 10.1.40-MariaDB
-- PHP Version: 7.1.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_facepay`
--
CREATE DATABASE IF NOT EXISTS `db_facepay` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `db_facepay`;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_order`
--

CREATE TABLE IF NOT EXISTS `tbl_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product` varchar(45) NOT NULL,
  `quantity` smallint(5) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `is_confirmed` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Very simple order table';

-- --------------------------------------------------------

--
-- Table structure for table `tbl_settings`
--

CREATE TABLE IF NOT EXISTS `tbl_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyCol` varchar(255) NOT NULL,
  `valCol` varchar(4000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_settings`
--
TRUNCATE TABLE `tbl_settings`;

INSERT INTO `tbl_settings` (`id`, `keyCol`, `valCol`) VALUES
(1, 'training_path', 'C:/xampp/htdocs/facepay/all_upload/training/'),
(2, 'test_path', 'C:/xampp/htdocs/facepay/all_upload/testdata/'),
(3, 'face_recognition_url_format', 'http://127.0.0.1:5000/image/boundingbox/%userId%'),
(4, 'num_recogs', '5');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE IF NOT EXISTS `tbl_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(200) NOT NULL,
  `password` varchar(2000) NOT NULL,
  `cardName` varchar(250) NOT NULL,
  `cardNumber` varchar(25) NOT NULL,
  `cardPin` varchar(5) NOT NULL,
  `cardCvv` varchar(3) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id`, `username`, `password`, `cardName`, `cardNumber`, `cardPin`, `cardCvv`, `status`) VALUES
(1, 'osagbemio', '123456', 'OLUWAKAYODE OSAGBEMI', '1234565656787', '1234', '123', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_images`
--

CREATE TABLE IF NOT EXISTS `tbl_user_images` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `imageName` varchar(4000) DEFAULT NULL,
  `imageData` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_image_auth_reqs`
--

CREATE TABLE IF NOT EXISTS `tbl_user_image_auth_reqs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `imageName` varchar(4000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_order`
--
ALTER TABLE `tbl_order`
  ADD CONSTRAINT `tbl_order_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
