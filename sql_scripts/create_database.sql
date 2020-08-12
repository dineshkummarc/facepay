-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 10, 2020 at 11:43 PM
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
create dateabase `db_facepay`;
-- --------------------------------------------------------

--
-- Table structure for table `tbl_settings`
--

CREATE TABLE `tbl_settings` (
  `id` int(11) NOT NULL,
  `keyCol` varchar(255) NOT NULL,
  `valCol` varchar(4000) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_settings`
--

INSERT INTO `tbl_settings` (`id`, `keyCol`, `valCol`) VALUES
(1, 'training_path', 'C:/xampp/htdocs/facepay/all_upload/training/'),
(2, 'test_path', 'C:/xampp/htdocs/facepay/all_upload/testdata/'),
(3, 'face_recognition_url_format', 'http://127.0.0.1:5000/image/boundingbox/%userId%');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id` int(11) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(2000) NOT NULL,
  `cardName` varchar(250) NOT NULL,
  `cardNumber` varchar(25) NOT NULL,
  `cardPin` varchar(5) NOT NULL,
  `cardCvv` varchar(3) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_images`
--

CREATE TABLE `tbl_user_images` (
  `id` bigint(20) NOT NULL,
  `userId` int(11) NOT NULL,
  `imageName` varchar(4000) DEFAULT NULL,
  `imageData` blob
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_image_auth_reqs`
--

CREATE TABLE `tbl_user_image_auth_reqs` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `imageName` varchar(4000) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_settings`
--
ALTER TABLE `tbl_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `tbl_user_images`
--
ALTER TABLE `tbl_user_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_user_image_auth_reqs`
--
ALTER TABLE `tbl_user_image_auth_reqs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_settings`
--
ALTER TABLE `tbl_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_images`
--
ALTER TABLE `tbl_user_images`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_image_auth_reqs`
--
ALTER TABLE `tbl_user_image_auth_reqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

CREATE TABLE `tbl_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product` varchar(45) NOT NULL,
  `quantity` smallint(5) NOT NULL,
  `user_id` INT(11) ,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `db_facepay`.`tbl_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Very simple order table'

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
