-- phpMyAdmin SQL Dump
-- version 2.9.0
-- http://www.phpmyadmin.net

-- --------------------------------------------------------

-- 
-- Структура таблицы `pfx_dialog`
-- 

CREATE TABLE `pfx_dialog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `public` int(11) NOT NULL,
  `hash` varchar(50) NOT NULL,
  `userid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `pfx_dialog` (`id`, `public`, `hash`, `userid`) VALUES
(4, 1364454719, 'zPcfYXgp', 2);
-- --------------------------------------------------------

-- 
-- Структура таблицы `pfx_message`
-- 

CREATE TABLE `pfx_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `public` int(11) NOT NULL,
  `senderid` int(11) NOT NULL,
  `dialogid` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `pfx_message_to_user`
-- 

CREATE TABLE `pfx_message_to_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `messageid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `pfx_user`
-- 

CREATE TABLE `pfx_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catid` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `login` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(32) NOT NULL,
  `icq` varchar(15) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `job` text NOT NULL,
  `image` varchar(256) NOT NULL,
  `perm` varchar(50) NOT NULL DEFAULT '0|1|1|0|1|1',
  `public` int(11) NOT NULL,
  `lastvisit` int(11) NOT NULL,
  `active` enum('yes','no') NOT NULL DEFAULT 'yes',
  `access` int(11) NOT NULL,
  `salt` varchar(100) NOT NULL,
  `method_notification` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `pfx_user_to_dialog`
-- 

CREATE TABLE `pfx_user_to_dialog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `dialogid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
