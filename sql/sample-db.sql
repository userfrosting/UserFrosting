-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 22, 2015 at 07:14 AM
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
CREATE DATABASE IF NOT EXISTS `dbslim` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `dbslim`;

-- --------------------------------------------------------

--
-- Table structure for table `uf_authorize_group`
--

CREATE TABLE IF NOT EXISTS `uf_authorize_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `hook` varchar(200) NOT NULL COMMENT 'A code that references a specific action or URI that the group has access to.',
  `conditions` text NOT NULL COMMENT 'The conditions under which members of this group have access to this hook.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `uf_authorize_group`
--

INSERT INTO `uf_authorize_group` (`id`, `group_id`, `hook`, `conditions`) VALUES
(1, 2, 'uri_users', 'always()'),
(2, 1, 'uri_dashboard', 'always()');

-- --------------------------------------------------------

--
-- Table structure for table `uf_authorize_user`
--

CREATE TABLE IF NOT EXISTS `uf_authorize_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `hook` varchar(200) NOT NULL COMMENT 'A code that references a specific action or URI that the user has access to.',
  `conditions` text NOT NULL COMMENT 'The conditions under which the user has access to this action.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `uf_authorize_user`
--

INSERT INTO `uf_authorize_user` (`id`, `user_id`, `hook`, `conditions`) VALUES
(1, 2, 'uri_site_settings', 'always()');

-- --------------------------------------------------------

--
-- Table structure for table `uf_configuration`
--

CREATE TABLE IF NOT EXISTS `uf_configuration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `value` longtext NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='A configuration table, mapping global configuration options to their values.' AUTO_INCREMENT=11 ;

--
-- Dumping data for table `uf_configuration`
--

INSERT INTO `uf_configuration` (`id`, `name`, `value`, `description`) VALUES
(1, 'site_title', 'UserFrosting', 'The title of the site.  By default, displayed in the title tag, as well as the upper left corner of every user page.'),
(2, 'admin_email', 'admin@userfrosting.com', 'The administrative email for the site.  Automated emails, such as activation emails and password reset links, will come from this address.'),
(3, 'email_login', '1', 'Set to 1 to enable login with an email address, 0 to disable.'),
(4, 'can_register', '1', 'Set to 1 to enable public registration, 0 to disable.'),
(5, 'enable_captcha', '1', 'Set to 1 to enable captcha on the registration page, 0 to disable.'),
(6, 'require_activation', '0', 'Set to 1 to require email activation for newly registered accounts (recommended), 0 to disable.  Accounts created on the admin side never need to be activated.'),
(7, 'resend_activation_threshold', '0', 'The time, in seconds, that a user must wait before requesting that the activation email be resent.'),
(8, 'reset_password_timeout', '10800', 'The time, in seconds, before a user''s password reminder email expires.'),
(9, 'default_locale', 'en_US', 'The default language for newly registered users.'),
(10, 'version', '0.3.0', 'The current version of UserFrosting.');

-- --------------------------------------------------------

--
-- Table structure for table `uf_group`
--

CREATE TABLE IF NOT EXISTS `uf_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Specifies whether this permission is a default setting for new accounts.',
  `can_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Specifies whether this permission can be deleted from the control panel.',
  `theme` varchar(100) NOT NULL DEFAULT 'default' COMMENT 'The theme assigned to primary users in this group.',
  `landing_page` varchar(200) NOT NULL DEFAULT 'account' COMMENT 'The page to take primary members to when they first log in.',
  `new_user_title` varchar(200) NOT NULL DEFAULT 'New User' COMMENT 'The default title to assign to new primary users.',
  `icon` varchar(100) NOT NULL DEFAULT 'fa fa-user' COMMENT 'The icon representing primary users in this group.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `uf_group`
--

INSERT INTO `uf_group` (`id`, `name`, `is_default`, `can_delete`, `theme`, `landing_page`, `new_user_title`, `icon`) VALUES
(1, 'User', 1, 0, 'default', 'dashboard', 'New User', 'fa fa-user'),
(2, 'Administrator', 0, 0, 'nyx', 'dashboard', 'New User', 'fa fa-user'),
(3, 'Hydralisks', 2, 1, 'nyx', 'dashboard', 'New User', 'sc sc-hydralisk'),
(4, 'Zerglings', 0, 1, 'default', 'dashboard', 'New User', 'sc sc-zergling');

-- --------------------------------------------------------

--
-- Table structure for table `uf_group_user`
--

CREATE TABLE IF NOT EXISTS `uf_group_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Maps users to their group(s)' AUTO_INCREMENT=20 ;

--
-- Dumping data for table `uf_group_user`
--

INSERT INTO `uf_group_user` (`id`, `user_id`, `group_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 2, 1),
(12, 2, 2),
(18, 10, 3),
(19, 10, 1);

-- --------------------------------------------------------

--
-- Table structure for table `uf_user`
--

CREATE TABLE IF NOT EXISTS `uf_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `display_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `activation_token` varchar(225) NOT NULL,
  `last_activation_request` datetime NOT NULL,
  `lost_password_request` tinyint(1) NOT NULL DEFAULT '0',
  `lost_password_timestamp` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `title` varchar(150) NOT NULL,
  `sign_up_stamp` datetime NOT NULL,
  `last_sign_in_stamp` datetime DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Specifies if the account is enabled.  Disabled accounts cannot be logged in to, but they retain all of their data and settings.',
  `primary_group_id` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Specifies the primary group for the user.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `uf_user`
--

INSERT INTO `uf_user` (`id`, `user_name`, `display_name`, `password`, `email`, `activation_token`, `last_activation_request`, `lost_password_request`, `lost_password_timestamp`, `active`, `title`, `sign_up_stamp`, `last_sign_in_stamp`, `enabled`, `primary_group_id`) VALUES
(1, 'admin', 'Admin', '$2y$10$ssRwANKk/cj7XXyjmZSj9OvmfKawWzIHu1yNMP6AM2Ans8.g/Nau.', 'alex@bloomingtontutors.com', '1f9668f3acacc6e693fdf47be7e41872', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, 'Master Account', '0000-00-00 00:00:00', '2015-05-22 00:43:58', 1, 2),
(2, 'patch', 'Patch Adams', '$2y$10$Bcdl3ajiBLwOsX5rLXl7R.5Vt9Iksvg.TfnzMmGv8EAAOiIYqkDrq', 'duh@duh.com', '0fb0c5f64ccecc3c2281467b118d325a', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, 'RIP', '0000-00-00 00:00:00', '2015-05-22 00:52:18', 1, 2),
(3, 'tweety', 'Tweetyboid', '$2y$10$Mu8g2TAoVb.FzGKbRvmZIOGnrshdNTkFShCtQqV8Sv/H0fLnGlwbm', 'abweissman@gmail.com', '5dd2a3dca2ad5e888941cbbfd95eaebb', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, 'New Member', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 1),
(10, 'derp', 'derp', '$2y$10$eJ3XUf11G8r48kWoQhrAE.G/GLhGhoSFVksSKDL5xytrXLs.pf8EG', 'derp@derp.com', 'c7ebb6f1a66628bf422d86764f1d2d2f', '2015-05-21 22:31:17', 0, NULL, 1, 'New User', '2015-05-21 22:31:17', '2015-05-22 00:52:56', 1, 3);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
