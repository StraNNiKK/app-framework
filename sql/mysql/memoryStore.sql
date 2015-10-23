drop table if exists `memoryStore`;
create table `memoryStore` (
  `key` varchar(255) not null,
  `value` varbinary(4096) not null, 
  `expire` int unsigned
) engine=memory;