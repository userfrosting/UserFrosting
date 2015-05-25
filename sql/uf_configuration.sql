-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 25, 2015 at 04:12 AM
-- Server version: 5.5.31
-- PHP Version: 5.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `dbslim`
--

-- --------------------------------------------------------

--
-- Table structure for table `uf_configuration`
--

CREATE TABLE IF NOT EXISTS `uf_configuration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plugin` varchar(50) NOT NULL COMMENT 'The name of the plugin that manages this setting (set to ''userfrosting'' for core settings)',
  `name` varchar(150) NOT NULL COMMENT 'The name of the setting.',
  `value` longtext NOT NULL COMMENT 'The current value of the setting.',
  `description` text NOT NULL COMMENT 'A brief description of this setting.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='A configuration table, mapping global configuration options to their values.' AUTO_INCREMENT=15 ;

--
-- Dumping data for table `uf_configuration`
--

INSERT INTO `uf_configuration` (`id`, `plugin`, `name`, `value`, `description`) VALUES
(1, 'userfrosting', 'site_title', 'UserFrosting', 'The title of the site.  By default, displayed in the title tag, as well as the upper left corner of every user page.'),
(2, 'userfrosting', 'admin_email', 'admin@userfrosting.com', 'The administrative email for the site.  Automated emails, such as activation emails and password reset links, will come from this address.'),
(3, 'userfrosting', 'email_login', '1', 'Specify whether users can login via email address or username instead of just username.'),
(4, 'userfrosting', 'can_register', '1', 'Specify whether public registration of new accounts is enabled.  Enable if you have a service that users can sign up for, disable if you only want accounts to be created by you or an admin.'),
(5, 'userfrosting', 'enable_captcha', '1', 'Specify whether new users must complete a captcha code when registering for an account.'),
(6, 'userfrosting', 'require_activation', '0', 'Specify whether email activation is required for newly registered accounts.  Accounts created on the admin side never need to be activated.'),
(7, 'userfrosting', 'resend_activation_threshold', '0', 'The time, in seconds, that a user must wait before requesting that the activation email be resent.'),
(8, 'userfrosting', 'reset_password_timeout', '10800', 'The time, in seconds, before a user''s password reminder email expires.'),
(9, 'userfrosting', 'default_locale', 'en_US', 'The default language for newly registered users.'),
(10, 'userfrosting', 'version', '0.3.0', 'The current version of UserFrosting.'),
(11, 'userfrosting', 'author', 'Alex Weissman', 'The author of the site.  Will be used in the site''s author meta tag.');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
