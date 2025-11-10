-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2025 at 06:06 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `visitor_pass`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(10) NOT NULL,
  `visitor_name` varchar(10) DEFAULT NULL,
  `mobile` varchar(10) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `company` varchar(10) DEFAULT NULL,
  `whom_to_meet` varchar(10) NOT NULL,
  `host_id` int(10) DEFAULT NULL,
  `purpose` varchar(10) DEFAULT NULL,
  `host_name` varchar(10) NOT NULL,
  `num_of_people` int(10) NOT NULL,
  `appointment_time` datetime DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `deleted` int(1) NOT NULL DEFAULT 0,
  `department_id` int(11) DEFAULT NULL,
  `read_status` tinyint(1) NOT NULL DEFAULT 0,
  `company_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `visitor_name`, `mobile`, `email`, `department`, `company`, `whom_to_meet`, `host_id`, `purpose`, `host_name`, `num_of_people`, `appointment_time`, `status`, `deleted`, `department_id`, `read_status`, `company_id`) VALUES
(1, 'sanika par', '8010087978', 'sanikaparitkar@gmail.com', 'IT', 'PDVS', 'bfgfh', 3, 'project', 'host', 3, '2025-09-27 12:22:00', 'pending', 0, 20, 0, NULL),
(20, 'gfyhg', '5764634787', 'sanikaparitkar@gmail.com', 'HR', 'uyhhggfv', 'ghbghb', 3, 'gyjuggf', 'host', 2, '2025-10-03 12:47:00', 'pending', 0, 22, 0, NULL),
(29, 'rrrr', '7058762664', 'sanikaparitkar@gmail.com', 'IT', 'nhgvh', 'hhytc', 3, 'gygyh', 'host', 1, '2025-10-06 20:02:00', 'pending', 0, 20, 0, NULL),
(30, 'rrrr', '7058762664', 'sanikaparitkar@gmail.com', 'HR', 'nhgvh', 'jyjtf', 3, 'hjh', 'host', 5, '2025-10-06 14:36:00', 'pending', 0, 22, 0, NULL),
(31, 'ssssss', '5764634787', 'sanikaparitkar@gmail.com', 'IT', 'PDVS', 'hfgcf', 3, 'cede', 'host', 2, '2025-10-06 15:05:00', 'pending', 0, 20, 0, NULL),
(32, 'rrrr', '7058762664', 'sanikaparitkar@gmail.com', 'IT', 'nhgvh', 'jyt', 3, 'gfcgt', 'host', 4, '2025-10-06 16:56:00', 'pending', 0, 20, 0, NULL),
(33, 'rrrr', '8010087978', 'sanikaparitkar@gmail.com', 'HR', 'nhgvh', 'hfthj', 3, 'fjhygjyu', 'host', 3, '2025-10-08 17:34:00', 'pending', 8, 22, 0, NULL),
(35, 'rrrr', '8010087978', 'sanikaparitkar@gmail.com', 'sales', 'nhgvh', 'jfyjy', 3, 'jhgju', 'host', 5, '2025-10-08 13:47:00', 'pending', 0, 21, 0, NULL),
(36, 'ssssss', '5764634787', 'sanikaparitkar@gmail.com', 'IT', 'PDVS', 'vdfgb', 3, 'jhgh', 'host', 4, '2025-10-08 15:42:00', 'pending', 0, 20, 0, NULL),
(37, 'ssssss', '8010087978', 'sanikaparitkar@gmail.com', 'IT', 'nhgvh', 'gghh', 3, 'interview', 'host', 5, '2025-10-08 15:50:00', 'pending', 0, 20, 0, NULL),
(38, 'rrrr', '7058762664', 'sanikaparitkar@gmail.com', 'HR', 'nhgvh', 'hbfh', 3, 'j', 'host', 3, '2025-10-10 12:37:00', 'pending', 0, 22, 0, NULL),
(39, 'ssss', '8010087978', 'sanikaparitkar@gmail.com', 'IT', 'rtf', 'fhd', 3, 'kjhgf', 'host', 7, '2025-10-11 12:41:00', 'pending', 0, 20, 0, NULL),
(41, 'samu', '0987654326', 'sanikaparitkar@email.com', 'Sales', 'rs', 'fdds', 36, 'hgftd', 'host', 4, '2025-10-13 16:10:00', 'pending', 2, 21, 0, NULL),
(48, 'vini', '0987654321', 'sanikaparitkar@email.com', 'Sales', 'IBG', 'Sanika', 33, 'work', 'host', 3, '2025-10-17 13:47:00', 'pending', 0, 21, 0, NULL),
(51, 'vini', '7058762664', 'sanikaparitkar@email.com', 'Sales', 'IBG', 'Sanika', 36, 'jj', 'host', 3, '2025-10-17 15:05:00', 'pending', 0, 21, 0, NULL),
(54, 'sidhesh', '0987654321', 'sanikaparitkar@email.com', 'Testing', 'sp pvt.ltd', 'sanika', 44, 'project re', 'host', 1, '2025-10-24 07:49:00', 'pending', 0, 4, 0, NULL),
(55, 'sidhesh', '7058762664', 'sanikaparitkar@email.com', 'Testing', 'sp pvt.ltd', 'sanika', 44, 'j', 'host', 2, '2025-10-25 11:56:00', 'pending', 0, 4, 0, NULL),
(56, 'samu', '8010087978', 'sanikaparitkar@email.com', 'Testing', 'PDVS', 'fer', 44, 'k', 'host', 4, '2025-10-25 12:16:00', 'pending', 0, 4, 0, NULL),
(57, 'pppp', '7058762664', 'sanikaparitkar@email.com', 'Sales', 'nnn', 'n,mn,', 44, 'hbn', 'host', 888, '2025-10-26 13:00:00', 'pending', 0, 21, 0, NULL),
(58, 'p', '7058762664', 'sanikaparitkar@email.com', 'Testing', 'nnn', 'n,mn,', 44, 'd', 'host', 888, '2025-11-01 18:00:00', 'pending', 0, 4, 0, NULL),
(59, 'Visitor Na', '8669174390', 'sanikaparitkar@email.com', 'Testing', 'Company 1', 'To Whom Me', 44, 'Purpose 1', 'host', 2, '2025-10-28 14:02:00', 'pending', 0, 4, 0, NULL),
(60, 'rafgt', '7890654778', 'sanikaparitkar@email.com', 'Sales', 'bujhb', 'hgbyu', 44, 'jnhbh', 'host', 6, '2025-10-26 14:28:00', 'pending', 0, 21, 0, NULL),
(61, 'fdddd', '7058762664', 'sanikaparitkar@email.com', 'Coding', 'nhgvh', 'bnn vjhgyh', 44, 'hk', 'host', 5, '2025-10-26 14:31:00', 'pending', 0, 7, 0, NULL),
(62, 'pranita', '9878996654', 'sanikaparitkar@email.com', 'Coding', 'pdvs', 'hkhhujg', 44, 'meeting', 'host', 2, '2025-10-26 16:47:00', 'pending', 0, 7, 0, NULL),
(63, 'fdddd', '8884373563', 'sanikaparitkar@email.com', 'Coding', '4r44r34r', 'tgt', 44, 'v fgvbg', 'host', 3, '2025-10-26 16:49:00', 'pending', 0, 7, 0, NULL),
(64, 'Visitor Na', '8010087978', 'sanikaparitkar@email.com', 'Coding', 'Company 4', 'To Whom Me', 44, '1111', 'host', 5, '2025-11-01 11:03:00', 'pending', 0, 7, 0, NULL),
(65, 'shreya', '1111111111', 'sanikaparitkar@email.com', 'Coding', 'pdvs', 'xyz', 44, 'mmamsanj', 'host', 2, '2025-10-27 14:47:00', 'pending', 0, 7, 0, NULL),
(66, 'kjhbh', '2222222222', 'sanikaparitkar@email.com', 'Coding', 'gdsfh', 'ghyj', 44, 'bhn', 'host', 3, '2025-10-27 17:47:00', 'pending', 0, 7, 0, NULL),
(67, 'shardul', '2222222222', 'sanikaparitkar@email.com', 'deployment', 'pdvs', 'sksjk', 44, 'jxhsui', 'host', 2, '2025-10-27 14:56:00', 'pending', 0, 16, 0, NULL),
(68, 'prajakta', '9370293723', 'sanikaparitkar@email.com', 'deployment', 'pdvs', 'abc', 44, 'asaddssa', 'host', 3, '2025-10-28 13:02:00', 'pending', 0, 16, 0, NULL),
(69, 'prajakta', '9379293723', 'sanikaparitkar@email.com', 'deployment', 'pdvs', 'abc', 44, 'asassdssa', 'host', 3, '2025-10-28 14:04:00', 'pending', 0, 16, 0, NULL),
(71, 'aaaaa', '2222222222', 'sanikaparitkar@email.com', 'deployment', 'pdvs', 'skfjroj', 44, 'vfdgrtgrt', 'host', 4, '0000-00-00 00:00:00', 'pending', 0, 16, 0, NULL),
(72, 'sayli', '2339124782', 'sanikaparitkar@email.com', 'deployment', 'dmndewj', 'wqndioq', 44, 'eoewokj', 'host', 3, '2025-10-30 13:21:00', 'pending', 0, 16, 0, NULL),
(73, 'teja', '3848952084', 'sanikaparitkar@email.com', 'deployment', 'uiweow', 'mnvmcnvmf', 44, 'k33', 'host', 9999, '2025-10-30 13:56:00', 'pending', 0, 16, 0, NULL),
(74, 'sanika', '9999999999', 'sanikaparitkar@email.com', 'deployment', 'pdvs', 'prajkta', 44, 'jnjmhn', 'host', 8, '0000-00-00 00:00:00', 'pending', 1, 16, 0, NULL),
(75, 'gjnh', '3333333333', 'sanikaparitkar@email.com', 'Acceptance\r\n', 'gjh', 'j', 44, 'mjn', 'host', 7, '2025-10-31 16:10:00', 'pending', 1, 6, 0, NULL),
(76, 'sanika', '9999999999', 'sanikaparitkar@email.com', 'Acceptance\r\n', 'jmhnh', 'jmn', 44, 'gh', 'host', 21, '2025-10-31 16:18:00', 'pending', 1, 6, 0, NULL),
(77, 'ABC', '8888888888', 'sanikaparitkar@email.com', 'Acceptance\r\n', 'pdvs', 'xyz', 44, 'apxzksaopi', 'host', 22, '2025-10-29 11:22:00', 'pending', 0, 6, 0, NULL),
(78, 'PRQ', '7675644535', 'sanikaparitkar@email.com', 'Acceptance\r\n', 'pdvs', 'xmnsajkx', 44, 'iuyqe', 'host', 2, '2025-10-29 11:23:00', 'pending', 0, 6, 0, NULL),
(79, 'LMN', '6674653465', 'sanikaparitkar@email.com', 'Acceptance\r\n', 'ps sloatio', 'xnczxn', 44, 'uyuew', 'host', 4, '0000-00-00 00:00:00', 'pending', 1, 6, 0, NULL),
(80, 'JKL', '5784365735', 'sanikaparitkar@email.com', 'Acceptance\r\n', 'IBG', 'bbbb', 44, 'mmm', 'host', 34, '2025-10-28 14:27:00', 'pending', 0, 6, 0, NULL),
(81, 'fhfg', '0987654321', 'sanikaparitkar@email.com', 'Acceptance\r\n', 'IBG', 'vhn', 44, 'hnmb', 'host', 4, '2025-10-31 11:48:00', 'pending', 1, 6, 0, NULL),
(82, 'ABC', '4444444444', 'sanikaparitkar@email.com', 'Acceptance\r\n', 'PDVS', 'nvdmfv', 44, 'ewwe', 'host', 444444, '2025-10-31 12:58:00', 'pending', 1, 6, 0, NULL),
(83, 'AAA', '6666666666', 'sanikaparitkar@email.com', 'deployment', 'PDVS', 'XYZ', 44, 'xncsdkk', 'host', 33, '2025-10-28 14:06:00', 'pending', 0, 16, 0, NULL),
(84, 'DEF', '9843291723', 'sanikaparitkar@email.com', 'deployment', 'PDVS', 'dddd', 44, 'vds', 'host', 33, '2025-10-28 17:03:00', 'pending', 0, 16, 0, NULL),
(85, 'nb', '9999999999', 'sanikaparitkar@email.com', 'deployment', 'PDVS', 'jhnk', 44, 'jnk', 'host', 7, '2025-10-29 14:48:00', 'pending', 0, 16, 0, NULL),
(86, 'nj', '8888888888', 'sanikaparitkar@email.com', 'Coding', 'njk', 'njk', 44, 'hik', 'host', 6, '2025-10-29 14:52:00', 'pending', 0, 7, 0, NULL),
(87, 'PPP', '8767565645', 'sanikaparitkar@email.com', 'Coding', 'PDVS', 'bbbbb', 44, 'mnmnm', 'host', 2324, '2025-11-01 15:30:00', 'pending', 0, 7, 0, NULL),
(88, 'LLL', '8765645434', 'sanikaparitkar@email.com', 'Coding', 'PDVS', 'nbnbh', 44, 'bnjbh', 'host', 4, '2025-10-28 19:31:00', 'pending', 0, 7, 0, NULL),
(89, 'zzz', '9923887322', 'sanikaparitkar@email.com', 'deployment', 'Digital Vi', 'nxbsnxx', 44, 'dnkj', 'host', 4, '2025-10-29 11:02:00', 'pending', 0, 16, 0, NULL),
(90, 'YYY', '7831273518', 'sanikaparitkar@email.com', 'deployment', 'Digital Vi', 'nxbsnxx', 44, 'nbsxsaj', 'host', 5, '2025-10-29 12:03:00', 'pending', 0, 16, 0, NULL),
(91, 'XXX', '8273284328', 'sanikaparitkar@gmail.com', 'IT', 'Digital Vi', 'mdcnwm', 44, 'ndcewjk', 'host', 4, '2025-10-29 13:04:00', 'pending', 0, 20, 0, NULL),
(92, 'WWW', '1812312821', 'sanikaparitkar@gmail.com', 'HR', 'Digital Vi', 'nxbsnxx', 44, 'nxbzcbajs', 'host', 5, '2025-10-29 13:04:00', 'pending', 0, 22, 0, NULL),
(93, 'VVV', '9788767656', 'sanikaparitkar@gmail.com', 'IT', 'nbhgjhgj', 'vbvjh', 44, 'bvhfkty', 'host', 4, '2025-10-29 14:14:00', 'pending', 0, 20, 0, NULL),
(96, 'shardul', '9270293723', 'sanikaparitkar@gmail.com', 'IT', 'pdvs', 'prajakta', 44, 'cmancwk', 'host', 2, '2025-10-29 17:39:00', 'pending', 0, 20, 0, NULL),
(97, 'sanika', '8010087978', 'sanikaparitkar@gmail.com', 'IT', 'pdvs', 'ppspo', 44, 'nsdj', 'host', 5, '2025-10-29 16:40:00', 'pending', 0, 20, 0, NULL),
(98, 'viraj', '8010087978', 'sanikaparitkar@gmail.com', 'HR', 'mxnaxqj', 'mnanms', 44, 'qwsq', 'host', 2, '2025-10-29 15:43:00', 'pending', 0, 22, 0, NULL),
(100, 'prajkta', '9370293723', 'sanikaparitkar@gmail.com', 'IT', 'sssss', 'ded', 44, 'xdddewxd', 'host', 4, '2025-11-05 17:51:00', 'pending', 0, 20, 0, NULL),
(101, 'shardul', '9370293723', 'sanikaparitkar@gmail.com', 'IT', 'sssss', 'gkjhn', 44, 'bjkhn', 'host', 3, '2025-10-29 13:16:00', 'pending', 0, 20, 0, NULL),
(103, 'PS', '9370293723', 'sanikaparitkar@gmail.com', 'HR', 'PDVS', 'ZNxbZNxbsa', 44, 'nxbsanm', 'host', 3, '2025-10-29 16:46:00', 'pending', 0, 22, 0, NULL),
(104, 'MMMM', '7058762664', 'sanikaparitkar@gmail.com', 'Sales', 'PDVS', 'BN', 44, 'VG', 'host', 3, '2025-10-29 18:26:00', 'pending', 1, 21, 0, NULL),
(105, 'ddd', '1232131231', 'sanikaparitkar@gmail.com', 'IT', 'ccdsdc', 'ceded', 44, 'rfre', 'host', 2, '2025-10-30 15:32:00', 'pending', 1, 20, 0, NULL),
(106, 'gjy', '0000000000', 'sanikaparitkar@gmail.com', 'HR', 'vhn', 'vhn', 44, 'truyh', 'host', 1, '2025-10-31 15:04:00', 'pending', 0, 22, 0, NULL),
(107, 'hhhhh', '9923887322', 'sanikaparitkar@gmail.com', 'IT', 'Digital Vi', 'mdcnwm', 44, 'cdscmmn', 'host', 1, '2025-10-30 19:49:00', 'pending', 1, NULL, 0, NULL),
(108, 'pranita', '7410769240', 'sanikaparitkar@gmail.com', 'HR', 'Digital Vi', 'nxbsn', 44, 'dwdw', 'host', 1, '0000-00-00 00:00:00', 'pending', 1, NULL, 0, NULL),
(109, 'shardul', '1812312821', 'sanikaparitkar@gmail.com', 'Sales', 'Digital Vi', 'mdcnwm', 44, 'xasm,asjks', 'host', 3, '2025-11-01 10:16:00', 'pending', 0, NULL, 0, NULL),
(110, 'siddhu', '9788767656', 'sanikaparitkar@gmail.com', 'Testing', 'Digital Vi', 'ss', 44, 'wed', 'host', 1, '2025-11-02 10:18:00', 'pending', 0, NULL, 0, NULL),
(111, 'abcd', '8273284320', 'sanikaparitkar@gmail.com', 'HR', 'Digital Vi', 'nxbsn', 44, 'erferf', 'host', 3, '2025-11-01 14:28:00', 'pending', 0, NULL, 0, NULL),
(112, 'sanika', '8273284328', 'sanikaparitkar@gmail.com', 'IT', 'PDVS', 'vbvjh', 66, 'Zxmmxsnxkj', 'host', 3, '2025-11-01 15:11:00', 'pending', 1, NULL, 0, NULL),
(113, 'viraj', '1812312821', 'sanikaparitkar@gmail.com', 'HR', 'Digital Vi', 'mdcnwm', 66, 'xsaxmasxs', 'host', 3, '2025-11-02 15:13:00', 'pending', 1, NULL, 0, NULL),
(117, 'xyz', '8273284320', 'sanikaparitkar@gmail.com', 'HR', 'Digital Vi', 'nxbsn', 44, 'project', 'host', 2, '2025-11-01 17:07:00', 'pending', 0, NULL, 0, NULL),
(118, 'mahi', '9999999999', 'sanikaparitkar@gmail.com', 'IT', 'pdvs', 'prajakta', 44, 'work', 'host', 8, '2025-11-01 16:32:00', 'pending', 0, NULL, 0, NULL),
(119, 'Prajakta', '9370293723', 'sanikaparitkar@gmail.com', 'HR', 'PDVS', 'sanika', 44, 'Meeting', 'host', 4, '2025-11-05 04:14:00', 'pending', 0, 22, 0, NULL),
(126, 'Prajakta', '9370293723', 'shirkeprajakta62@gmail.com', 'IT', 'PDVS', 'sanika', 44, 'bvcb', 'host', 1, '2025-11-05 07:57:00', 'pending', 1, NULL, 0, NULL),
(128, 'Prajakta', '9370293723', 'shirkeprajakta62@gmail.com', 'IT', 'PDVS', 'sanika', 44, 'bvcb', 'host', 1, '2025-11-05 07:57:00', 'pending', 1, NULL, 0, NULL),
(129, 'Prajakta', '9370293723', 'shirkeprajakta62@gmail.com', 'IT', 'PDVS', 'sanika', 44, 'vxcvfd', 'host', 2, '2025-11-06 07:03:00', 'pending', 0, NULL, 0, NULL),
(130, 'siddhu', '9370293723', 'shirkeprajakta62@gmail.com', 'HR', 'PDVS', 'sanika', 44, 'vxcvfd', 'host', 2, '2025-11-06 07:03:00', 'pending', 0, NULL, 0, NULL),
(131, 'Prajakta', '9370293723', 'shirkeprajakta62@gmail.com', 'IT', 'PDVS', 'sanika', 44, 'fdfb', 'host', 2, '2025-11-06 07:15:00', 'pending', 0, NULL, 0, NULL),
(132, 'siddhu', '9370293723', 'shirkeprajakta62@gmail.com', 'Sales', 'PDVS', 'sanika', 44, 'jjm,m', 'host', 1, '2025-11-06 07:26:00', 'pending', 0, NULL, 0, NULL),
(133, 'Prajakta', '9370293723', 'sanikaparitkar@gmail.com', 'HR', 'PDVS', 'sanika', 44, 'cccd', 'host', 1, '2025-11-06 23:23:00', 'pending', 0, NULL, 0, NULL),
(134, 'Prajakta', '9370293723', 'sanikaparitkar@gmail.com', 'Sales', 'PDVS', 'sanika', 44, 'bbbb', 'host', 2, '2025-11-06 12:29:00', 'pending', 0, NULL, 0, NULL),
(135, 'Omkar', '7410769240', 'omkar@pdvspl.in', 'Testing', 'PDVS', 'sagar', 44, 'bvcbng', 'host', 1, '2025-11-06 15:54:00', 'pending', 0, NULL, 0, NULL),
(136, 'Prajakta', '7410769240', 'sanikaparitkar@gmail.com', 'HR', 'PDVS', 'sanika', 44, 'vbnfhn', 'host', 2, '2025-11-06 14:07:00', 'pending', 0, NULL, 0, NULL),
(140, 'kjh', '5555555555', 'sanikaparitkar@gmail.com', 'Testing', 'asd', 'qwe', 44, 'poi', 'host', 1, '2025-11-06 11:27:00', 'pending', 0, NULL, 0, NULL),
(144, 'zszwszwszz', '6666666666', 'sanikaparitkar@gmail.com', 'IT', 'jbmghn', 'wz', 66, 'dsa', 'host', 1, '2025-11-06 12:56:00', 'pending', 0, NULL, 0, NULL),
(145, 'hbjm', '3999999999', 'sanikaparitkar@gmail.com', 'sales', 'asd', 'fdfs', 66, 'gfdsf', 'host', 3, '2025-11-06 14:57:00', 'pending', 0, NULL, 0, NULL),
(146, 'uyt', '5555555555', 'sanikaparitkar@gmail.com', 'HR', 'nbv', 'nhjn', 66, 'uty', 'host', 4, '2025-11-06 13:59:00', 'pending', 1, NULL, 0, NULL),
(147, 'AAAbg', '1234566778', '0', 'Acceptance', 'PDVSv', 'XYZas', 44, 'gjhgbn', 'host', 3, '2025-11-11 14:06:00', 'pending', 0, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(10) NOT NULL,
  `name` varchar(10) NOT NULL,
  `location` varchar(10) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `location`, `status`, `created_at`) VALUES
(24, 'ABC Pvt Lt', 'Mumbai', 'Active', '2025-10-30 10:00:31'),
(25, 'XYZ Soluti', 'Pune', 'Active', '2025-10-30 10:00:31'),
(26, 'ABC Pvt Lt', 'Mumbai', 'Active', '2025-10-30 10:12:47'),
(27, 'XYZ Soluti', 'Pune', 'Active', '2025-10-30 10:12:47');

-- --------------------------------------------------------

--
-- Table structure for table `company_themes`
--

CREATE TABLE `company_themes` (
  `id` int(10) NOT NULL,
  `company_id` int(10) DEFAULT NULL,
  `primary_color` varchar(20) DEFAULT '#007bff',
  `secondary_color` varchar(20) DEFAULT '#0056b3',
  `logo_path` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(10) NOT NULL,
  `name` varchar(10) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `status`, `created_at`) VALUES
(4, 'Testing', 'Active', '2025-10-03 14:09:42'),
(6, 'Acceptance', 'Active', '2025-10-03 18:46:59'),
(7, 'Coding', 'Active', '2025-10-03 18:34:41'),
(16, 'deployment', 'Active', '2025-10-06 19:18:46'),
(20, 'IT', 'Active', '2025-11-06 10:52:56'),
(21, 'Sales', 'Active', '2025-11-06 10:53:08'),
(22, 'HR', 'Active', '2025-11-06 10:53:20');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `visitor_name` varchar(255) DEFAULT NULL,
  `mobile` int(10) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `host_id`, `message`, `is_read`, `created_at`, `visitor_name`, `mobile`, `purpose`, `status`) VALUES
(1, 0, 'Array checked out', 0, '2025-11-07 12:10:47', 'Prajakta', 2147483647, 'vbnfhn', 'read'),
(2, 0, 'Array checked in', 0, '2025-11-07 12:11:10', 'Omkar', 2147483647, 'bvcbng', 'read'),
(3, 0, 'Array checked out', 0, '2025-11-07 12:31:34', 'Omkar', 2147483647, 'bvcbng', 'read'),
(4, 0, 'Array checked in', 0, '2025-11-07 12:31:47', 'Prajakta', 2147483647, 'bbbb', 'read'),
(5, 0, 'Array checked in', 0, '2025-11-07 12:31:48', 'Prajakta', 2147483647, 'bbbb', 'read'),
(6, 0, 'Array checked out', 0, '2025-11-07 12:32:16', 'Prajakta', 2147483647, 'bbbb', 'read'),
(7, 0, 'Prajakta checked in', 0, '2025-11-07 15:09:26', 'Prajakta', 2147483647, 'cccd', 'read'),
(8, 0, 'siddhu checked in', 0, '2025-11-08 11:03:12', 'siddhu', 2147483647, 'jjm,m', 'unread'),
(9, 0, 'Prajakta checked out', 0, '2025-11-08 11:03:36', 'Prajakta', 2147483647, 'cccd', 'unread');

-- --------------------------------------------------------

--
-- Table structure for table `passes`
--

CREATE TABLE `passes` (
  `id` int(10) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `pass_number` varchar(20) DEFAULT NULL,
  `qr_code` text DEFAULT NULL,
  `status` enum('waiting','inside','checked_out') DEFAULT 'waiting',
  `checkin_time` datetime DEFAULT NULL,
  `checkout_time` datetime DEFAULT NULL,
  `material` text NOT NULL,
  `time_spent` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passes`
--

INSERT INTO `passes` (`id`, `appointment_id`, `pass_number`, `qr_code`, `status`, `checkin_time`, `checkout_time`, `material`, `time_spent`) VALUES
(17, 20, 'VP00020', '../assets/qrcodes/VP00020.png', '', '2025-10-03 12:50:11', '2025-10-03 12:50:41', 'laptop', '0 hrs 0 mins 30 secs'),
(26, 29, 'VP00029', '../assets/qrcodes/VP00029.png', '', '2025-10-06 14:20:08', NULL, 'laptop', NULL),
(28, 31, 'VP00031', '../assets/qrcodes/VP00031.png', '', '2025-10-06 15:13:05', '2025-10-06 15:20:04', 'laptop', '0h 6m'),
(29, 32, 'VP00032', '../assets/qrcodes/VP00032.png', '', '2025-10-06 16:57:23', '2025-10-06 16:58:04', 'laptop', '0h 0m'),
(30, 33, 'VP00033', '../assets/qrcodes/VP00033.png', '', '2025-10-06 20:11:53', '2025-10-06 20:12:06', 'document', '0h 0m'),
(32, 35, 'VP00035', '../assets/qrcodes/VP00035.png', '', '2025-10-08 13:48:17', '2025-10-08 13:51:33', 'document', '0h 3m'),
(33, 36, 'VP00036', '../assets/qrcodes/VP00036.png', '', '2025-10-08 15:42:50', '2025-10-08 15:42:55', 'laptop', '0h 0m'),
(34, 37, 'VP00037', '../assets/qrcodes/VP00037.png', '', '2025-10-08 15:51:11', '2025-10-10 16:12:40', 'document', '0h 21m'),
(35, 38, 'VP00038', '../assets/qrcodes/VP00038.png', '', '2025-10-10 12:38:29', '2025-10-10 12:38:38', 'laptop', '0h 0m'),
(36, 39, 'VP00039', '../assets/qrcodes/VP00039.png', '', '2025-10-11 11:06:21', '2025-10-11 13:36:51', 'bag', '2h 30m'),
(38, 41, 'VP00041', '../assets/qrcodes/VP00041.png', '', '2025-10-27 05:14:12', '2025-10-27 05:17:05', '', '0h 2m'),
(39, 48, 'VP00048', '../assets/qrcodes/VP00048.png', '', '2025-10-17 15:12:16', '2025-10-27 05:18:35', 'document', '231h 6m'),
(42, 51, 'VP00051', '../assets/qrcodes/VP00051.png', '', '2025-10-17 15:11:12', '2025-10-17 15:12:03', 'laptop', '0h 0m'),
(45, 54, 'VP00054', '../assets/qrcodes/VP00054.png', '', '2025-10-26 07:21:20', '2025-10-26 07:24:57', '', '0h 3m'),
(46, 55, 'VP00055', '../assets/qrcodes/VP00055.png', '', '2025-10-25 06:37:26', '2025-10-25 06:38:28', '', '0h 1m'),
(47, 56, 'VP00056', '../assets/qrcodes/VP00056.png', '', '2025-10-25 06:41:21', '2025-10-26 06:59:53', '', '25h 18m'),
(48, 57, 'VP00057', '../assets/qrcodes/VP00057.png', '', '2025-10-25 11:48:39', '2025-10-27 05:50:21', '', '43h 1m'),
(49, 58, 'VP00058', '../assets/qrcodes/VP00058.png', '', '2025-10-25 11:49:38', '2025-10-27 05:50:23', '', '43h 0m'),
(50, 59, 'VP00059', '../assets/qrcodes/VP00059.png', '', '2025-10-25 10:46:18', '2025-10-27 10:39:22', '', '48h 53m'),
(51, 60, 'VP00060', '../assets/qrcodes/VP00060.png', '', '2025-10-27 05:50:28', '2025-10-27 07:09:12', '', '1h 18m'),
(52, 61, 'VP00061', '../assets/qrcodes/VP00061.png', '', '2025-10-27 05:59:24', '2025-10-27 07:08:56', '', '1h 9m'),
(53, 62, 'VP00062', '../assets/qrcodes/VP00062.png', '', '2025-10-27 07:05:25', '2025-10-27 07:05:48', '', '0h 0m'),
(54, 63, 'VP00063', '../assets/qrcodes/VP00063.png', '', '2025-10-26 06:51:55', '2025-10-27 06:57:29', '', '24h 5m'),
(55, 64, 'VP00064', '../assets/qrcodes/VP00064.png', '', '2025-10-29 12:28:55', '2025-10-31 16:40:04', '', '52h 11m'),
(56, 65, 'VP00065', '../assets/qrcodes/VP00065.png', '', '2025-10-27 09:31:51', '2025-10-27 09:32:27', '', '0h 0m'),
(57, 66, 'VP00066', '../assets/qrcodes/VP00066.png', '', '2025-10-27 09:38:10', '2025-10-27 09:42:16', '', '0h 4m'),
(58, 67, 'VP00067', '../assets/qrcodes/VP00067.png', '', '2025-10-27 09:53:11', '2025-10-27 09:56:08', '', '0h 2m'),
(59, 68, 'VP00068', '../assets/qrcodes/VP00068.png', '', '2025-10-28 11:19:45', '2025-10-28 11:20:20', '', '0h 0m'),
(60, 69, 'VP00069', '../assets/qrcodes/VP00069.png', '', '2025-10-27 16:08:37', '2025-10-27 16:08:58', '', '0h 0m'),
(62, 71, 'VP00071', '../assets/qrcodes/VP00071.png', '', '2025-10-27 11:34:19', '2025-10-27 11:34:42', '', '0h 0m'),
(63, 72, 'VP00072', '../assets/qrcodes/VP00072.png', '', '2025-10-27 10:41:17', '2025-10-27 11:33:53', '', '0h 52m'),
(64, 73, 'VP00073', '../assets/qrcodes/VP00073.png', '', '2025-10-27 10:39:43', '2025-10-27 10:40:56', '', '0h 1m'),
(66, 75, 'VP00075', '../assets/qrcodes/VP00075.png', '', '2025-10-29 12:33:48', '2025-11-05 03:49:08', '', '159h 15m'),
(67, 76, 'VP00076', '../assets/qrcodes/VP00076.png', 'waiting', NULL, NULL, '', NULL),
(68, 77, 'VP00077', '../assets/qrcodes/VP00077.png', '', '2025-10-29 12:17:06', '2025-10-29 12:17:41', '', '0h 0m'),
(69, 78, 'VP00078', '../assets/qrcodes/VP00078.png', '', '2025-10-29 12:17:20', '2025-10-29 12:17:50', '', '0h 0m'),
(71, 80, 'VP00080', '../assets/qrcodes/VP00080.png', '', '2025-10-28 11:39:55', '2025-10-28 15:33:22', '', '3h 53m'),
(72, 81, 'VP00081', '../assets/qrcodes/VP00081.png', 'waiting', NULL, NULL, '', NULL),
(74, 83, 'VP00083', '../assets/qrcodes/VP00083.png', '', '2025-10-28 15:32:27', '2025-10-28 15:33:34', '', '0h 1m'),
(75, 84, 'VP00084', '../assets/qrcodes/VP00084.png', '', '2025-10-28 15:32:43', '2025-11-05 03:52:19', '', '180h 19m'),
(76, 85, 'VP00085', '../assets/qrcodes/VP00085.png', '', '2025-10-29 11:40:49', '2025-10-29 11:42:14', '', '0h 1m'),
(77, 86, 'VP00086', '../assets/qrcodes/VP00086.png', '', '2025-10-29 14:50:04', '2025-10-29 14:51:21', '', '0h 1m'),
(78, 87, 'VP00087', '../assets/qrcodes/VP00087.png', 'waiting', NULL, NULL, '', NULL),
(79, 88, 'VP00088', '../assets/qrcodes/VP00088.png', 'inside', '2025-10-28 15:32:52', NULL, '', NULL),
(80, 89, 'VP00089', '../assets/qrcodes/VP00089.png', 'inside', '2025-10-29 14:50:14', NULL, '', NULL),
(81, 90, 'VP00090', '../assets/qrcodes/VP00090.png', 'inside', '2025-10-29 14:50:27', NULL, '', NULL),
(82, 91, 'VP00091', '../assets/qrcodes/VP00091.png', 'inside', '2025-10-29 14:50:47', NULL, '', NULL),
(83, 92, 'VP00092', '../assets/qrcodes/VP00092.png', 'inside', '2025-10-29 14:50:58', NULL, '', NULL),
(84, 93, 'VP00093', '../assets/qrcodes/VP00093.png', 'waiting', NULL, NULL, '', NULL),
(87, 96, 'VP00096', '../assets/qrcodes/VP00096.png', 'waiting', NULL, NULL, '', NULL),
(88, 97, 'VP00097', '../assets/qrcodes/VP00097.png', 'waiting', NULL, NULL, '', NULL),
(89, 98, 'VP00098', '../assets/qrcodes/VP00098.png', 'waiting', NULL, NULL, '', NULL),
(91, 100, 'VP00100', '../assets/qrcodes/VP00100.png', 'waiting', NULL, NULL, '', NULL),
(92, 101, 'VP00101', '../assets/qrcodes/VP00101.png', 'waiting', NULL, NULL, '', NULL),
(94, 103, 'VP00103', '../assets/qrcodes/VP00103.png', '', '2025-10-29 16:25:25', '2025-10-29 16:25:35', '', '0h 0m'),
(95, 104, 'VP00104', '../assets/qrcodes/VP00104.png', 'waiting', NULL, NULL, '', NULL),
(96, 105, 'VP00105', '../assets/qrcodes/VP00105.png', '', '2025-10-30 16:09:38', '2025-10-30 16:09:57', '', '0h 0m'),
(97, 106, 'VP00106', '../assets/qrcodes/VP00106.png', 'waiting', NULL, NULL, '', NULL),
(100, 109, 'VP00109', '../assets/qrcodes/VP00109.png', 'waiting', NULL, NULL, '', NULL),
(101, 110, 'VP00110', '../assets/qrcodes/VP00110.png', 'waiting', NULL, NULL, '', NULL),
(102, 111, 'VP00111', '../assets/qrcodes/VP00111.png', 'waiting', NULL, NULL, '', NULL),
(108, 117, 'VP00117', '../assets/qrcodes/VP00117.png', 'inside', '2025-11-05 04:08:53', NULL, '', NULL),
(109, 118, 'VP00118', '../assets/qrcodes/VP00118.png', '', '2025-11-01 14:34:24', '2025-11-01 14:34:44', '', '0h 0m'),
(110, 119, 'VP00119', '../assets/qrcodes/VP00119.png', 'waiting', NULL, NULL, '', NULL),
(120, 129, 'VP00129', '../assets/qrcodes/VP00129.png', 'waiting', NULL, NULL, '', NULL),
(121, 130, 'VP00130', '../assets/qrcodes/VP00130.png', 'waiting', NULL, NULL, '', NULL),
(122, 131, 'VP00131', '../assets/qrcodes/VP00131.png', 'waiting', NULL, NULL, '', NULL),
(123, 132, 'VP00132', '../assets/qrcodes/VP00132.png', 'inside', '2025-11-08 11:03:12', NULL, '', NULL),
(124, 133, 'VP00133', '../assets/qrcodes/VP00133.png', '', '2025-11-07 15:09:26', '2025-11-08 11:03:36', '', '19h 54m'),
(125, 134, 'VP00134', '../assets/qrcodes/VP00134.png', '', '2025-11-07 12:31:48', '2025-11-07 12:32:16', '', '0h 0m'),
(126, 135, 'VP00135', '../assets/qrcodes/VP00135.png', '', '2025-11-07 12:11:10', '2025-11-07 12:31:34', '', '0h 20m'),
(127, 136, 'VP00136', '../assets/qrcodes/VP00136.png', '', '2025-11-07 11:46:05', '2025-11-07 12:10:47', '', '0h 24m'),
(131, 140, 'VP00140', '../assets/qrcodes/VP00140.png', '', '2025-11-07 11:16:15', '2025-11-07 11:19:58', '', '0h 3m'),
(135, 144, 'VP00144', '../assets/qrcodes/VP00144.png', '', '2025-11-07 09:48:23', '2025-11-07 09:56:42', '', '0h 8m'),
(136, 145, 'VP00145', '../assets/qrcodes/VP00145.png', '', '2025-11-06 17:16:49', '2025-11-07 09:51:29', '', '16h 34m'),
(138, 147, 'VP00147', '../assets/qrcodes/VP00147.png', 'waiting', NULL, NULL, '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `system_theme`
--

CREATE TABLE `system_theme` (
  `id` int(10) NOT NULL,
  `primary_color` varchar(20) DEFAULT '#007bff',
  `secondary_color` varchar(20) DEFAULT '#0056b3',
  `logo_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_theme`
--

INSERT INTO `system_theme` (`id`, `primary_color`, `secondary_color`, `logo_path`) VALUES
(1, '#eaa4e4', '#95d5e4', 'assets/themes/system_logo.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `username` varchar(10) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(20) NOT NULL,
  `email` varchar(60) NOT NULL,
  `contact` varchar(10) NOT NULL,
  `role` enum('admin','security','host') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `department` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `email`, `contact`, `role`, `status`, `department`) VALUES
(3, 'host', 'host@123', 'sanika', 'host@123', '7643359876', 'host', 'active', 'IT'),
(33, 'viraj', 'viraj123', 'Viraj', 'viraj@example.com', '1234567890', 'admin', 'active', 'Testing'),
(36, 'sanika', 'sanika123', 'Sanika', 'sanikaparitkar@gmail', '1234567890', 'host', 'active', 'IT'),
(44, 'host', '$2y$10$G4adydQ7qAifnN/ucUP9qOM6QBkf9iCmuaY7uhjK./yJQ5d7mc8bm', 'Host User', 'host@example.com', '9876543212', 'host', 'active', 'HR'),
(64, 'admin', '$2y$10$.6pJUQ/6A6Otls6x4Biq5.2F68nRljXH4ib/uSooB.80gvIRrdGNe', 'Admin User', 'admin@example.com', '9876543210', 'admin', 'active', 'HR'),
(65, 'security', '$2y$10$3Cv/CQgvA0j3VrKqGs/neejYjNJ0eWiIUsuYo/hk32CMsbSHwZ276', 'Security User', 'security@example.com', '9876543211', 'security', 'active', 'HR'),
(66, 'prajakta', '$2y$10$Q9mI7kxC.NY0H5W.yfBPO.VXsbHK/yInf9Sf1cddn.ymbvHadZPlK', '', 'ps@gmail.com', '9370293723', 'host', 'active', 'Coding');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `host_id` (`host_id`),
  ADD KEY `fk_department` (`department_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company_themes`
--
ALTER TABLE `company_themes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `passes`
--
ALTER TABLE `passes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `system_theme`
--
ALTER TABLE `system_theme`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `company_themes`
--
ALTER TABLE `company_themes`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `passes`
--
ALTER TABLE `passes`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `system_theme`
--
ALTER TABLE `system_theme`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `company_themes`
--
ALTER TABLE `company_themes`
  ADD CONSTRAINT `company_themes_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `passes`
--
ALTER TABLE `passes`
  ADD CONSTRAINT `passes_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
