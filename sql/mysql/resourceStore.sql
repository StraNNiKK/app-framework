drop table if exists `resourceStore`;
create table `resourceStore` (
  `id` bigint not null auto_increment,
  `file` varchar(255) not null,
  `version` varchar(255) not null, 
  `expire` int(11),
  primary key (`id`)
) engine=memory;