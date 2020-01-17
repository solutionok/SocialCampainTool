-- --------------------------------------------------------

--
-- Table structure for table `creator_tools`
--

CREATE TABLE IF NOT EXISTS `creator_tools` (
  `tool_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `tool_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`tool_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `delete_log`
--

CREATE TABLE IF NOT EXISTS `delete_log` (
  `post_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `site` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` datetime NOT NULL,
  UNIQUE KEY `post_id` (`post_id`,`site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `error_msg`
--

CREATE TABLE IF NOT EXISTS `error_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `social_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `added_at` datetime NOT NULL,
  `site` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `fb_accounts`
--

CREATE TABLE IF NOT EXISTS `fb_accounts` (
  `fb_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `first_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_pic` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sex` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_at` datetime NOT NULL,
  `account_status` tinyint(1) NOT NULL,
  UNIQUE KEY `fb_id` (`fb_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fb_events`
--

CREATE TABLE IF NOT EXISTS `fb_events` (
  `user_id` bigint(20) NOT NULL,
  `fb_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` date NOT NULL,
  `access_token` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_post_at` datetime NOT NULL,
  `last_post_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_update` datetime NOT NULL,
  `account_status` tinyint(1) NOT NULL,
  `manual_entry` tinyint(1) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`,`fb_id`,`event_id`),
  KEY `user_id_2` (`user_id`),
  KEY `fb_id` (`fb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fb_groups`
--

CREATE TABLE IF NOT EXISTS `fb_groups` (
  `user_id` bigint(20) NOT NULL,
  `fb_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `privacy` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_post_at` datetime NOT NULL,
  `last_post_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_update` datetime NOT NULL,
  `account_status` tinyint(1) NOT NULL,
  `manual_entry` tinyint(1) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`,`fb_id`,`group_id`),
  KEY `user_id_2` (`user_id`),
  KEY `fb_id` (`fb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fb_pages`
--

CREATE TABLE IF NOT EXISTS `fb_pages` (
  `user_id` bigint(20) NOT NULL,
  `fb_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `likes` int(11) NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_post_at` datetime NOT NULL,
  `last_post_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_update` datetime NOT NULL,
  `account_status` tinyint(1) NOT NULL,
  `manual_entry` tinyint(1) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`,`fb_id`,`page_id`),
  KEY `user_id_2` (`user_id`),
  KEY `fb_id` (`fb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `user_id` bigint(20) NOT NULL,
  `folder_id` bigint(20) NOT NULL,
  `file_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `filename` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tags` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `privacy` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `added_at` datetime NOT NULL,
  `watermarked` tinyint(1) NOT NULL,
  `position` int(11) NOT NULL,
  `duration` float NOT NULL,
  PRIMARY KEY (`file_id`),
  KEY `user_id` (`user_id`),
  KEY `folder_id` (`folder_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `file_meta`
--

CREATE TABLE IF NOT EXISTS `file_meta` (
  `file_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tags` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `privacy` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  UNIQUE KEY `file_id` (`file_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `file_id_2` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `folders`
--

CREATE TABLE IF NOT EXISTS `folders` (
  `user_id` bigint(20) NOT NULL,
  `folder_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `folder_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_count` int(11) NOT NULL,
  `thumb` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`folder_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `global_config`
--

CREATE TABLE IF NOT EXISTS `global_config` (
  `site_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `site_url` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `site_theme` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fb_app_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fb_app_secret` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fb_app_token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fb_scope` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tw_app_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tw_app_secret` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `yt_client_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `yt_client_secret` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `yt_dev_token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fb_enabled` tinyint(1) NOT NULL,
  `tw_enabled` tinyint(1) NOT NULL,
  `yt_enabled` tinyint(1) NOT NULL,
  `ffmpeg` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seo_url` tinyint(1) NOT NULL,
  `media_plugin_enabled` tinyint(1) NOT NULL,
  `downloader_plugin_enabled` tinyint(1) NOT NULL,
  `image_watermarking_enabled` tinyint(1) NOT NULL,
  `video_watermarking_enabled` tinyint(1) NOT NULL,
  `image_editor_enabled` tinyint(1) NOT NULL,
  `video_editor_enabled` tinyint(1) NOT NULL,
  `disable_all_crons` tinyint(1) NOT NULL,
  `disable_poster_cron` tinyint(1) NOT NULL,
  `disable_hide_delete_cron` tinyint(1) NOT NULL,
  `disable_insights_cron` tinyint(1) NOT NULL,
  `disable_videoeditor_bumping_cron` tinyint(1) NOT NULL,
  `enable_signup` tinyint(1) NOT NULL,
  `enable_maintenance_mode` tinyint(1) NOT NULL,
  `maintenance_message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `paypal_email` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_email` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  UNIQUE KEY `site_name` (`site_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `link_meta`
--

CREATE TABLE IF NOT EXISTS `link_meta` (
  `file_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `link_title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_desc` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_image` text COLLATE utf8mb4_unicode_ci NOT NULL,
  UNIQUE KEY `file_id` (`file_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membership_plans`
--

CREATE TABLE IF NOT EXISTS `membership_plans` (
  `plan_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_price` float NOT NULL,
  `plan_price_currency_code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `folder_limit` int(11) NOT NULL,
  `schedule_limit` int(11) NOT NULL,
  `schedule_group_limit` int(11) NOT NULL,
  `social_profile_limit_per_site` int(11) NOT NULL,
  `page_group_event_limit` int(11) NOT NULL,
  `rss_feed_limit` int(11) NOT NULL,
  `use_feed_cleaner` int(11) NOT NULL,
  `allowed_storage` bigint(20) NOT NULL,
  `use_advanced_scheduling` int(11) NOT NULL,
  `use_image_editor` int(11) NOT NULL,
  `use_video_editor` int(11) NOT NULL,
  `use_image_watermark` int(11) NOT NULL,
  `use_video_watermark` int(11) NOT NULL,
  `use_image_downloader` int(11) NOT NULL,
  `use_video_downloader` int(11) NOT NULL,
  `use_meme_generator` int(11) NOT NULL,
  `use_html_image_creator` int(11) NOT NULL,
  `use_album_post` int(11) NOT NULL,
  `use_slideshow` int(11) NOT NULL,
  `post_per_day` int(11) NOT NULL,
  `facebook_post_per_day` int(11) NOT NULL,
  `twitter_post_per_day` int(11) NOT NULL,
  `youtube_post_per_day` int(11) NOT NULL,
  `use_facebook` int(11) NOT NULL,
  `use_twitter` int(11) NOT NULL,
  `use_youtube` int(11) NOT NULL,
  `use_group_event_importer` int(11) NOT NULL,
  `plan_features` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_on_site` tinyint(1) NOT NULL,
  `plan_subtitle` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_preferred` tinyint(1) NOT NULL,
  PRIMARY KEY (`plan_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_paid` datetime NOT NULL,
  `status` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `buyer_email` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `social_email` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `social_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `txn_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_paid` float NOT NULL,
  `currency` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recurring` tinyint(1) NOT NULL,
  `subs_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `raw_data` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `post_counter`
--

CREATE TABLE IF NOT EXISTS `post_counter` (
  `user_id` bigint(20) NOT NULL,
  `today` date NOT NULL,
  `post_count` int(11) NOT NULL,
  `site` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  UNIQUE KEY `user_id` (`user_id`,`today`,`site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_log`
--

CREATE TABLE IF NOT EXISTS `post_log` (
  `post_log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `post_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `social_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `site` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `schedule_group_id` bigint(20) NOT NULL,
  `schedule_id` bigint(20) NOT NULL,
  `folder_id` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_blind` tinyint(1) NOT NULL,
  `is_hidden` tinyint(1) NOT NULL,
  `posted_at` datetime NOT NULL,
  `delete_at` datetime NOT NULL,
  `hid_action` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hid_status` tinyint(1) NOT NULL,
  `next_insight` datetime NOT NULL,
  `insights` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_bump` datetime NOT NULL,
  `next_bump` datetime NOT NULL,
  `last_bump_message` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`post_log_id`),
  KEY `user_id` (`user_id`),
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `rss_feeds`
--

CREATE TABLE IF NOT EXISTS `rss_feeds` (
  `rss_feed_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `rss_url` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `feed_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`rss_feed_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE IF NOT EXISTS `schedules` (
  `schedule_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `schedule_group_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `social_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `site` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_done` tinyint(1) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `added_at` datetime NOT NULL,
  `completed_at` datetime NOT NULL,
  `next_post` datetime NOT NULL,
  `rate_limited` tinyint(1) NOT NULL,
  `rate_limited_at` datetime NOT NULL,
  `notes` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_locked` tinyint(1) NOT NULL,
  `locked_at` datetime NOT NULL,
  `comment_bumping_freq` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment_bumps` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `bump_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stats_settings` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`schedule_id`),
  UNIQUE KEY `schedule_group_id` (`schedule_group_id`,`user_id`,`social_id`,`page_id`,`site`),
  KEY `user_id` (`user_id`),
  KEY `schedule_group_id_2` (`schedule_group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `schedule_groups`
--

CREATE TABLE IF NOT EXISTS `schedule_groups` (
  `schedule_group_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `folder_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `schedule_group_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `schedule_interval` int(11) NOT NULL,
  `post_freq` int(11) NOT NULL,
  `post_freq_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_only_from` int(11) NOT NULL,
  `post_only_to` int(11) NOT NULL,
  `post_start_from` datetime NOT NULL,
  `post_end_at` datetime NOT NULL,
  `do_repeat` tinyint(1) NOT NULL,
  `repeat_campaign` tinyint(1) NOT NULL,
  `post_delete_after` bigint(20) NOT NULL,
  `post_delete_freq` int(11) NOT NULL,
  `post_delete_freq_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_delete_action` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_done` tinyint(1) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `auto_delete_file` int(11) NOT NULL,
  `watermark` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `watermark_position` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_sequence` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `next_post` datetime NOT NULL,
  `last_post` datetime NOT NULL,
  `total_schedules` int(11) NOT NULL,
  `last_update` timestamp NOT NULL,
  `comment_bumping_freq` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment_bumps` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `bump_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stats_settings` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `onetime_post` tinyint(1) NOT NULL,
  `sync_post` tinyint(1) NOT NULL,
  PRIMARY KEY (`schedule_group_id`),
  KEY `user_id` (`user_id`),
  KEY `folder_id` (`folder_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `token_expiry`
--

CREATE TABLE IF NOT EXISTS `token_expiry` (
  `user_id` bigint(20) NOT NULL,
  `social_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `site` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mail_sent` tinyint(1) NOT NULL,
  `expired_at` datetime NOT NULL,
  UNIQUE KEY `user_id_2` (`user_id`,`social_id`,`page_id`,`site`),
  KEY `user_id` (`user_id`),
  KEY `social_id_2` (`social_id`),
  KEY `page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tw_accounts`
--

CREATE TABLE IF NOT EXISTS `tw_accounts` (
  `tw_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `tw_username` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_pic` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sex` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `followers` int(11) NOT NULL,
  `friends` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `access_token` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_at` datetime NOT NULL,
  `account_status` tinyint(1) NOT NULL,
  UNIQUE KEY `tw_id` (`tw_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_zone` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_id` bigint(20) NOT NULL,
  `storage` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `used_storage` bigint(20) NOT NULL,
  `fb_app_config` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `tw_app_config` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `yt_app_config` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` tinyint(1) NOT NULL,
  `last_login_ip` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_login_time` datetime NOT NULL,
  `login_required` tinyint(1) NOT NULL,
  `account_status` tinyint(1) NOT NULL,
  `fb_posting` tinyint(1) NOT NULL,
  `tw_posting` tinyint(1) NOT NULL,
  `yt_posting` tinyint(1) NOT NULL,
  `email_noti` tinyint(1) NOT NULL,
  `fb_noti` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `v_code` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `membership_expiry_time` datetime NOT NULL,
  `membership_is_recurring` tinyint(1) NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `plan_id` (`plan_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_categories`
--

CREATE TABLE IF NOT EXISTS `user_categories` (
  `category_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `category_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `selected_pages` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`category_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `video_editor_queue`
--

CREATE TABLE IF NOT EXISTS `video_editor_queue` (
  `queue_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` tinyint(1) NOT NULL,
  `video_file` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `download_file` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `tasks` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `chunks` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `delete_source` tinyint(1) NOT NULL,
  `is_locked` tinyint(1) NOT NULL,
  `locked_at` datetime NOT NULL,
  `is_done` tinyint(1) NOT NULL,
  `added_at` datetime NOT NULL,
  `notes` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `yt_accounts`
--

CREATE TABLE IF NOT EXISTS `yt_accounts` (
  `yt_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `yt_username` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_pic` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sex` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `followers` int(11) NOT NULL,
  `comments` int(11) NOT NULL,
  `views` int(11) NOT NULL,
  `videos` int(11) NOT NULL,
  `access_token` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_at` datetime NOT NULL,
  `account_status` tinyint(1) NOT NULL,
  UNIQUE KEY `yt_id` (`yt_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
