CREATE TABLE `atlanta_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` varchar(100) NOT NULL,
  `receiver` varchar(100) NOT NULL DEFAULT '',
  `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('new','exist','del') CHARACTER SET utf8mb4 NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_receiver__users_id__fk` (`receiver`),
  KEY `message_sender__users_id__fk` (`sender`),
  CONSTRAINT `message_sender__users_id__fk` FOREIGN KEY (`sender`) REFERENCES `atlanta_users` (`sess`),
  CONSTRAINT `messages_receiver__users_id__fk` FOREIGN KEY (`receiver`) REFERENCES `atlanta_users` (`sess`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8;