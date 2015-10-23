drop table if exists `session`;
create table `session` (
  `id` char(32) not null primary key, /* Session ID */
  `expires` int not null, /* Время истекания сессии */
  `sessionData` text not null, /* Данные, хранящиеся в сессии */
  index(`expires`)
) engine=innodb;