''' Adding fields for statement 4.12.2013 '''
Alter table `volunteers` 
Add column `statement_file` varchar(255) NULL DEFAULT NULL after rank,
Add column `statement_downloaded` binary(1) NOT NULL DEFAULT 0 after statement_file,
Add column `statement_downloaded_timestamp` timestamp NULL DEFAULT NULL after statement_downloaded;