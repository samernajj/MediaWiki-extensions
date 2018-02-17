CREATE TABLE `google_custome_search_api` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL DEFAULT '' ,
  `decription` text NOT NULL DEFAULT '',
  `user_id` int(10) NOT NULL ,
  `comment` text 
);
