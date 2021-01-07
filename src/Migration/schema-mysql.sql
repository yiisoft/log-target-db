/**
 * Database schema required by \Yiisoft\Log\Target\Db.
 *
 * The indexes declared are not required. They are mainly used to improve the performance
 * of some queries about message levels and categories. Depending on your actual needs, you may
 * want to create additional indexes (e.g. index on `log_time`).
 */

drop table if exists `log`;

create table `log`
(
   `id`          bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
   `level`       varchar(16),
   `category`    varchar(255),
   `log_time`    double,
   `message`     text,
   key `idx_log_level` (`level`),
   key `idx_log_category` (`category`)
) engine InnoDB;
