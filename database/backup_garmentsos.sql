-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: garmentsos
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event` varchar(255) NOT NULL,
  `subject_type` varchar(255) NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`properties`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `activity_logs_created_by_foreign` (`created_by`),
  KEY `activity_logs_updated_by_foreign` (`updated_by`),
  CONSTRAINT `activity_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activity_logs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,'created','App\\Models\\YarnStockMovement',1,'{\"before\":[],\"after\":{\"yarn_id\":3,\"type\":\"purchase\",\"quantity_kg\":\"453.592\",\"movement_date\":\"2026-11-06 00:00:00\",\"ref_type\":null,\"ref_id\":null,\"price_per_kg\":\"429.901411\",\"note\":\"Opening purchase\",\"updated_by\":1,\"created_by\":1,\"updated_at\":\"2026-11-06 18:42:33\",\"created_at\":\"2026-11-06 18:42:33\",\"id\":1}}','2026-11-06 12:42:33','2026-11-06 12:42:33',NULL,1,1),(2,'created','App\\Models\\YarnStockMovement',2,'{\"before\":[],\"after\":{\"yarn_id\":4,\"type\":\"purchase\",\"quantity_kg\":\"544.311\",\"movement_date\":\"2026-11-06 00:00:00\",\"ref_type\":null,\"ref_id\":null,\"price_per_kg\":\"429.901411\",\"note\":\"Opening purchase\",\"updated_by\":1,\"created_by\":1,\"updated_at\":\"2026-11-06 18:44:27\",\"created_at\":\"2026-11-06 18:44:27\",\"id\":2}}','2026-11-06 12:44:27','2026-11-06 12:44:27',NULL,1,1),(3,'created','App\\Models\\KnittingJob',1,'{\"before\":[],\"after\":{\"lot_id\":1,\"company_id\":\"2\",\"entry_date\":\"2026-11-06 00:00:00\",\"delivery_date\":\"2026-11-06 00:00:00\",\"input_unit\":\"lb\",\"notes\":null,\"updated_by\":1,\"created_by\":1,\"updated_at\":\"2026-11-06 18:45:37\",\"created_at\":\"2026-11-06 18:45:37\",\"id\":1}}','2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(4,'created','App\\Models\\YarnStockMovement',3,'{\"before\":[],\"after\":{\"yarn_id\":4,\"type\":\"knitting_issue\",\"quantity_kg\":\"-453.592\",\"movement_date\":\"2026-11-06 00:00:00\",\"ref_type\":\"App\\\\Models\\\\KnittingJob\",\"ref_id\":1,\"price_per_kg\":null,\"note\":null,\"updated_by\":1,\"created_by\":1,\"updated_at\":\"2026-11-06 18:45:37\",\"created_at\":\"2026-11-06 18:45:37\",\"id\":3}}','2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(5,'created','App\\Models\\DyeingJob',1,'{\"before\":[],\"after\":{\"lot_id\":1,\"company_id\":\"1\",\"entry_date\":\"2026-11-06 00:00:00\",\"return_date\":\"2026-11-06 00:00:00\",\"notes\":null,\"updated_by\":1,\"created_by\":1,\"updated_at\":\"2026-11-06 19:09:01\",\"created_at\":\"2026-11-06 19:09:01\",\"id\":1}}','2026-11-06 13:09:01','2026-11-06 13:09:01',NULL,1,1),(6,'created','App\\Models\\YarnStockMovement',4,'{\"before\":[],\"after\":{\"yarn_id\":5,\"type\":\"purchase\",\"quantity_kg\":\"1000.000\",\"movement_date\":\"2026-05-01 00:00:00\",\"ref_type\":null,\"ref_id\":null,\"price_per_kg\":null,\"note\":null,\"updated_by\":null,\"created_by\":null,\"updated_at\":\"2026-11-06 19:33:20\",\"created_at\":\"2026-11-06 19:33:20\",\"id\":4}}','2026-11-06 13:33:20','2026-11-06 13:33:20',NULL,NULL,NULL),(7,'created','App\\Models\\KnittingJob',2,'{\"before\":[],\"after\":{\"lot_id\":2,\"company_id\":3,\"entry_date\":\"2026-11-06 00:00:00\",\"delivery_date\":null,\"input_unit\":\"kg\",\"notes\":null,\"updated_by\":null,\"created_by\":null,\"updated_at\":\"2026-11-06 19:33:20\",\"created_at\":\"2026-11-06 19:33:20\",\"id\":2}}','2026-11-06 13:33:20','2026-11-06 13:33:20',NULL,NULL,NULL),(8,'created','App\\Models\\YarnStockMovement',5,'{\"before\":[],\"after\":{\"yarn_id\":5,\"type\":\"knitting_issue\",\"quantity_kg\":\"-100.000\",\"movement_date\":\"2026-11-06 00:00:00\",\"ref_type\":\"App\\\\Models\\\\KnittingJob\",\"ref_id\":2,\"price_per_kg\":null,\"note\":null,\"updated_by\":null,\"created_by\":null,\"updated_at\":\"2026-11-06 19:33:20\",\"created_at\":\"2026-11-06 19:33:20\",\"id\":5}}','2026-11-06 13:33:20','2026-11-06 13:33:20',NULL,NULL,NULL),(9,'created','App\\Models\\DyeingJob',2,'{\"before\":[],\"after\":{\"lot_id\":2,\"company_id\":3,\"entry_date\":\"2026-11-06 00:00:00\",\"return_date\":null,\"notes\":null,\"updated_by\":null,\"created_by\":null,\"updated_at\":\"2026-11-06 19:33:20\",\"created_at\":\"2026-11-06 19:33:20\",\"id\":2}}','2026-11-06 13:33:20','2026-11-06 13:33:20',NULL,NULL,NULL),(10,'deleted','App\\Models\\DyeingJob',2,'{\"before\":{\"id\":2,\"lot_id\":2,\"company_id\":3,\"entry_date\":\"2026-11-06\",\"return_date\":null,\"notes\":null,\"created_at\":\"2026-11-06 19:33:20\",\"updated_at\":\"2026-11-06 20:14:45\",\"deleted_at\":\"2026-11-06 20:14:45\",\"created_by\":null,\"updated_by\":1},\"after\":{\"id\":2,\"lot_id\":2,\"company_id\":3,\"entry_date\":\"2026-11-06\",\"return_date\":null,\"notes\":null,\"created_at\":\"2026-11-06 19:33:20\",\"updated_at\":\"2026-11-06 20:14:45\",\"deleted_at\":\"2026-11-06 20:14:45\",\"created_by\":null,\"updated_by\":1}}','2026-11-06 14:14:45','2026-11-06 14:14:45',NULL,1,1),(11,'created','App\\Models\\YarnStockMovement',6,'{\"before\":[],\"after\":{\"yarn_id\":5,\"type\":\"reversal\",\"quantity_kg\":\"100.000\",\"movement_date\":\"2026-11-06 00:00:00\",\"ref_type\":\"App\\\\Models\\\\KnittingJob\",\"ref_id\":2,\"price_per_kg\":null,\"note\":\"Reconciled previous knitting allocation\",\"updated_by\":1,\"created_by\":1,\"updated_at\":\"2026-11-06 20:15:10\",\"created_at\":\"2026-11-06 20:15:10\",\"id\":6}}','2026-11-06 14:15:10','2026-11-06 14:15:10',NULL,1,1),(12,'deleted','App\\Models\\KnittingJob',2,'{\"before\":{\"id\":2,\"lot_id\":2,\"company_id\":3,\"entry_date\":\"2026-11-06\",\"delivery_date\":null,\"input_unit\":\"kg\",\"notes\":null,\"created_at\":\"2026-11-06 19:33:20\",\"updated_at\":\"2026-11-06 20:15:10\",\"deleted_at\":\"2026-11-06 20:15:10\",\"created_by\":null,\"updated_by\":1},\"after\":{\"id\":2,\"lot_id\":2,\"company_id\":3,\"entry_date\":\"2026-11-06\",\"delivery_date\":null,\"input_unit\":\"kg\",\"notes\":null,\"created_at\":\"2026-11-06 19:33:20\",\"updated_at\":\"2026-11-06 20:15:10\",\"deleted_at\":\"2026-11-06 20:15:10\",\"created_by\":null,\"updated_by\":1}}','2026-11-06 14:15:10','2026-11-06 14:15:10',NULL,1,1),(13,'deleted','App\\Models\\CuttingJob',1,'{\"before\":{\"id\":1,\"cutting_type\":\"body\",\"cutting_date\":\"2026-11-05T18:00:00.000000Z\",\"staff_id\":2,\"notes\":null,\"created_at\":\"2026-11-06T13:33:20.000000Z\",\"updated_at\":\"2026-11-06T13:33:20.000000Z\",\"deleted_at\":null,\"created_by\":null,\"updated_by\":null,\"rows\":[]},\"after\":null}','2026-11-06 14:15:35','2026-11-06 14:15:35',NULL,1,1),(14,'created','App\\Models\\CuttingJob',2,'{\"before\":null,\"after\":{\"id\":2,\"cutting_type\":\"body\",\"cutting_date\":\"2026-11-05T18:00:00.000000Z\",\"staff_id\":1,\"notes\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"deleted_at\":null,\"created_by\":1,\"updated_by\":1,\"rows\":[{\"id\":1,\"cutting_job_id\":2,\"roll_id\":1,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":32,\"roll_weight_kg\":\"22.452\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":2,\"cutting_job_id\":2,\"roll_id\":3,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":34,\"roll_weight_kg\":\"22.452\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":3,\"cutting_job_id\":2,\"roll_id\":4,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":34,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":4,\"cutting_job_id\":2,\"roll_id\":5,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":36,\"roll_weight_kg\":\"22.452\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":5,\"cutting_job_id\":2,\"roll_id\":6,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":36,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":6,\"cutting_job_id\":2,\"roll_id\":7,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":38,\"roll_weight_kg\":\"22.452\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":7,\"cutting_job_id\":2,\"roll_id\":8,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":38,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":8,\"cutting_job_id\":2,\"roll_id\":9,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":40,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":9,\"cutting_job_id\":2,\"roll_id\":10,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":40,\"roll_weight_kg\":\"10.000\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":10,\"cutting_job_id\":2,\"roll_id\":11,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":32,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":11,\"cutting_job_id\":2,\"roll_id\":13,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":34,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":12,\"cutting_job_id\":2,\"roll_id\":14,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":34,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":13,\"cutting_job_id\":2,\"roll_id\":15,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":36,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":14,\"cutting_job_id\":2,\"roll_id\":16,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":36,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":15,\"cutting_job_id\":2,\"roll_id\":17,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":38,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":16,\"cutting_job_id\":2,\"roll_id\":18,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":38,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":17,\"cutting_job_id\":2,\"roll_id\":19,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":40,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":18,\"cutting_job_id\":2,\"roll_id\":20,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":40,\"roll_weight_kg\":\"10.000\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false}]}}','2026-11-06 14:16:23','2026-11-06 14:16:23',NULL,1,1),(15,'updated','App\\Models\\CuttingJob',2,'{\"before\":{\"id\":2,\"cutting_type\":\"body\",\"cutting_date\":\"2026-11-05T18:00:00.000000Z\",\"staff_id\":1,\"notes\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"deleted_at\":null,\"created_by\":1,\"updated_by\":1,\"rows\":[{\"id\":1,\"cutting_job_id\":2,\"roll_id\":1,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":32,\"roll_weight_kg\":\"22.452\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":2,\"cutting_job_id\":2,\"roll_id\":3,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":34,\"roll_weight_kg\":\"22.452\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":3,\"cutting_job_id\":2,\"roll_id\":4,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":34,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":4,\"cutting_job_id\":2,\"roll_id\":5,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":36,\"roll_weight_kg\":\"22.452\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":5,\"cutting_job_id\":2,\"roll_id\":6,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":36,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":6,\"cutting_job_id\":2,\"roll_id\":7,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":38,\"roll_weight_kg\":\"22.452\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":7,\"cutting_job_id\":2,\"roll_id\":8,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":38,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":8,\"cutting_job_id\":2,\"roll_id\":9,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":40,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":9,\"cutting_job_id\":2,\"roll_id\":10,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":40,\"roll_weight_kg\":\"10.000\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":10,\"cutting_job_id\":2,\"roll_id\":11,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":32,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":11,\"cutting_job_id\":2,\"roll_id\":13,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":34,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":12,\"cutting_job_id\":2,\"roll_id\":14,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":34,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":13,\"cutting_job_id\":2,\"roll_id\":15,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":36,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":14,\"cutting_job_id\":2,\"roll_id\":16,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":36,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":15,\"cutting_job_id\":2,\"roll_id\":17,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":38,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":16,\"cutting_job_id\":2,\"roll_id\":18,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":38,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":17,\"cutting_job_id\":2,\"roll_id\":19,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":40,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":18,\"cutting_job_id\":2,\"roll_id\":20,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":40,\"roll_weight_kg\":\"10.000\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":null,\"weight_is_automatic\":false}]},\"after\":{\"id\":2,\"cutting_type\":\"body\",\"cutting_date\":\"2026-11-05T18:00:00.000000Z\",\"staff_id\":1,\"notes\":null,\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T14:16:23.000000Z\",\"deleted_at\":null,\"created_by\":1,\"updated_by\":1,\"rows\":[{\"id\":1,\"cutting_job_id\":2,\"roll_id\":1,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":32,\"roll_weight_kg\":\"22.452\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.452\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020545\",\"weight_per_dozen_kg\":\"1.02054545\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":2,\"cutting_job_id\":2,\"roll_id\":3,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":34,\"roll_weight_kg\":\"22.452\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.452\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020545\",\"weight_per_dozen_kg\":\"1.02054545\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":3,\"cutting_job_id\":2,\"roll_id\":4,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":34,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":4,\"cutting_job_id\":2,\"roll_id\":5,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":36,\"roll_weight_kg\":\"22.452\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.452\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020545\",\"weight_per_dozen_kg\":\"1.02054545\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":5,\"cutting_job_id\":2,\"roll_id\":6,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":36,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":6,\"cutting_job_id\":2,\"roll_id\":7,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":38,\"roll_weight_kg\":\"22.452\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.452\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020545\",\"weight_per_dozen_kg\":\"1.02054545\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":7,\"cutting_job_id\":2,\"roll_id\":8,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":38,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":8,\"cutting_job_id\":2,\"roll_id\":9,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":40,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":9,\"cutting_job_id\":2,\"roll_id\":10,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"Black\",\"dia\":40,\"roll_weight_kg\":\"10.000\",\"dozens\":\"10.000\",\"cut_weight_kg\":\"10.000\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.000000\",\"weight_per_dozen_kg\":\"1.00000000\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"120.000\",\"weight_is_automatic\":true},{\"id\":10,\"cutting_job_id\":2,\"roll_id\":11,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":32,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":11,\"cutting_job_id\":2,\"roll_id\":13,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":34,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":12,\"cutting_job_id\":2,\"roll_id\":14,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":34,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":13,\"cutting_job_id\":2,\"roll_id\":15,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":36,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":14,\"cutting_job_id\":2,\"roll_id\":16,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":36,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":15,\"cutting_job_id\":2,\"roll_id\":17,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":38,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":16,\"cutting_job_id\":2,\"roll_id\":18,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":38,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":17,\"cutting_job_id\":2,\"roll_id\":19,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":40,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":18,\"cutting_job_id\":2,\"roll_id\":20,\"cutting_type\":\"body\",\"lot_id\":1,\"color\":\"White\",\"dia\":40,\"roll_weight_kg\":\"10.000\",\"dozens\":\"10.000\",\"cut_weight_kg\":\"10.000\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.000000\",\"weight_per_dozen_kg\":\"1.00000000\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T14:16:23.000000Z\",\"updated_at\":\"2026-11-06T15:06:30.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"body\",\"pieces\":\"120.000\",\"weight_is_automatic\":true}]}}','2026-11-06 15:06:30','2026-11-06 15:06:30',NULL,1,1),(16,'created','App\\Models\\CuttingJob',3,'{\"before\":null,\"after\":{\"id\":3,\"cutting_type\":\"neck\",\"cutting_date\":\"2026-11-05T18:00:00.000000Z\",\"staff_id\":1,\"notes\":null,\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"deleted_at\":null,\"created_by\":1,\"updated_by\":1,\"rows\":[{\"id\":19,\"cutting_job_id\":3,\"roll_id\":1,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"Black\",\"dia\":32,\"roll_weight_kg\":\"22.452\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.452\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020545\",\"weight_per_dozen_kg\":\"1.02054545\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":1,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":20,\"cutting_job_id\":3,\"roll_id\":3,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"Black\",\"dia\":34,\"roll_weight_kg\":\"22.452\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.452\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020545\",\"weight_per_dozen_kg\":\"1.02054545\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":2,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":21,\"cutting_job_id\":3,\"roll_id\":4,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"Black\",\"dia\":34,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":3,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":22,\"cutting_job_id\":3,\"roll_id\":5,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"Black\",\"dia\":36,\"roll_weight_kg\":\"22.452\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.452\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020545\",\"weight_per_dozen_kg\":\"1.02054545\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":4,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":23,\"cutting_job_id\":3,\"roll_id\":6,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"Black\",\"dia\":36,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":5,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":24,\"cutting_job_id\":3,\"roll_id\":7,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"Black\",\"dia\":38,\"roll_weight_kg\":\"22.452\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.452\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020545\",\"weight_per_dozen_kg\":\"1.02054545\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":6,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":25,\"cutting_job_id\":3,\"roll_id\":8,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"Black\",\"dia\":38,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":7,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":26,\"cutting_job_id\":3,\"roll_id\":9,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"Black\",\"dia\":40,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":8,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":27,\"cutting_job_id\":3,\"roll_id\":10,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"Black\",\"dia\":40,\"roll_weight_kg\":\"10.000\",\"dozens\":\"10.000\",\"cut_weight_kg\":\"10.000\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.000000\",\"weight_per_dozen_kg\":\"1.00000000\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":9,\"exclusive_cutting_type\":null,\"pieces\":\"120.000\",\"weight_is_automatic\":true},{\"id\":28,\"cutting_job_id\":3,\"roll_id\":11,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"White\",\"dia\":32,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":10,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":29,\"cutting_job_id\":3,\"roll_id\":13,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"White\",\"dia\":34,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":11,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":30,\"cutting_job_id\":3,\"roll_id\":14,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"White\",\"dia\":34,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":12,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":31,\"cutting_job_id\":3,\"roll_id\":15,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"White\",\"dia\":36,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":13,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":32,\"cutting_job_id\":3,\"roll_id\":16,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"White\",\"dia\":36,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":14,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":33,\"cutting_job_id\":3,\"roll_id\":17,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"White\",\"dia\":38,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":15,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":34,\"cutting_job_id\":3,\"roll_id\":18,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"White\",\"dia\":38,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":16,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":35,\"cutting_job_id\":3,\"roll_id\":19,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"White\",\"dia\":40,\"roll_weight_kg\":\"22.453\",\"dozens\":\"22.000\",\"cut_weight_kg\":\"22.453\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.020591\",\"weight_per_dozen_kg\":\"1.02059091\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":17,\"exclusive_cutting_type\":null,\"pieces\":\"264.000\",\"weight_is_automatic\":true},{\"id\":36,\"cutting_job_id\":3,\"roll_id\":20,\"cutting_type\":\"neck\",\"lot_id\":1,\"color\":\"White\",\"dia\":40,\"roll_weight_kg\":\"10.000\",\"dozens\":\"10.000\",\"cut_weight_kg\":\"10.000\",\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":\"0.000\",\"waste_kg\":\"0.000\",\"weight_per_dozen\":\"1.000000\",\"weight_per_dozen_kg\":\"1.00000000\",\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":\"0.00\",\"created_at\":\"2026-11-06T15:06:46.000000Z\",\"updated_at\":\"2026-11-06T15:06:46.000000Z\",\"source_body_row_id\":18,\"exclusive_cutting_type\":null,\"pieces\":\"120.000\",\"weight_is_automatic\":true}]}}','2026-11-06 15:06:46','2026-11-06 15:06:46',NULL,1,1),(17,'created','App\\Models\\CuttingJob',4,'{\"before\":null,\"after\":{\"id\":4,\"cutting_type\":\"rib\",\"cutting_date\":\"2026-11-10T18:00:00.000000Z\",\"staff_id\":2,\"notes\":null,\"created_at\":\"2026-11-11T09:30:48.000000Z\",\"updated_at\":\"2026-11-11T09:30:48.000000Z\",\"deleted_at\":null,\"created_by\":1,\"updated_by\":1,\"rows\":[{\"id\":37,\"cutting_job_id\":4,\"roll_id\":2,\"cutting_type\":\"rib\",\"lot_id\":1,\"color\":\"Black\",\"dia\":32,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-11T09:30:48.000000Z\",\"updated_at\":\"2026-11-11T09:30:48.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"rib\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":38,\"cutting_job_id\":4,\"roll_id\":10,\"cutting_type\":\"rib\",\"lot_id\":1,\"color\":\"Black\",\"dia\":40,\"roll_weight_kg\":\"12.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-11T09:30:48.000000Z\",\"updated_at\":\"2026-11-11T09:30:48.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"rib\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":39,\"cutting_job_id\":4,\"roll_id\":12,\"cutting_type\":\"rib\",\"lot_id\":1,\"color\":\"White\",\"dia\":32,\"roll_weight_kg\":\"22.453\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-11T09:30:48.000000Z\",\"updated_at\":\"2026-11-11T09:30:48.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"rib\",\"pieces\":null,\"weight_is_automatic\":false},{\"id\":40,\"cutting_job_id\":4,\"roll_id\":20,\"cutting_type\":\"rib\",\"lot_id\":1,\"color\":\"White\",\"dia\":40,\"roll_weight_kg\":\"12.454\",\"dozens\":null,\"cut_weight_kg\":null,\"remaining_fabric\":\"0.000\",\"remaining_fabric_kg\":\"0.000\",\"cutting_scrap_kg\":null,\"waste_kg\":null,\"weight_per_dozen\":null,\"weight_per_dozen_kg\":null,\"weight_unit\":\"kg\",\"remaining_fabric_unit\":\"kg\",\"waste_percent\":null,\"created_at\":\"2026-11-11T09:30:48.000000Z\",\"updated_at\":\"2026-11-11T09:30:48.000000Z\",\"source_body_row_id\":null,\"exclusive_cutting_type\":\"rib\",\"pieces\":null,\"weight_is_automatic\":false}]}}','2026-11-11 09:30:48','2026-11-11 09:30:48',NULL,1,1);
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('garmentos-cache-owner@garmentos.test|127.0.0.1','i:2;',1793968660),('garmentos-cache-owner@garmentos.test|127.0.0.1:timer','i:1793968660;',1793968660);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `colors`
--

DROP TABLE IF EXISTS `colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `colors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `name_key` varchar(255) NOT NULL,
  `hex` varchar(7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `colors_name_key_unique` (`name_key`),
  KEY `colors_created_by_foreign` (`created_by`),
  KEY `colors_updated_by_foreign` (`updated_by`),
  CONSTRAINT `colors_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `colors_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `colors`
--

LOCK TABLES `colors` WRITE;
/*!40000 ALTER TABLE `colors` DISABLE KEYS */;
INSERT INTO `colors` VALUES (1,'Black','black',NULL,'2026-11-01 08:30:20','2026-11-01 08:30:20',NULL,1,1),(2,'GREY','grey',NULL,'2026-11-01 08:30:20','2026-11-01 08:30:20',NULL,1,1),(3,'White','white',NULL,'2026-11-01 08:30:20','2026-11-01 08:30:20',NULL,1,1),(9,'Navy','navy',NULL,'2026-11-06 13:33:20','2026-11-06 13:33:20',NULL,NULL,NULL);
/*!40000 ALTER TABLE `colors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `name_key` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `is_knitting` tinyint(1) NOT NULL DEFAULT 0,
  `is_dyeing` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_name_key_unique` (`name_key`),
  KEY `companies_created_by_foreign` (`created_by`),
  KEY `companies_updated_by_foreign` (`updated_by`),
  KEY `companies_is_active_index` (`is_active`),
  CONSTRAINT `companies_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `companies_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,'Fatullah Dyeing LTD','fatullah dyeing ltd','2/5 Allahma Iqbal Road ,Chasara\r\nNarayanganj Sadar',0,1,1,NULL,'2026-11-01 06:19:09','2026-11-01 06:19:51',NULL,1,1),(2,'Spinnex Knitting','spinnex knitting','Shop 018, New Industrial Area - 2',1,0,1,NULL,'2026-11-01 06:20:28','2026-11-01 06:20:28',NULL,1,1),(3,'Torphy, Weimann and Schuppe','torphy, weimann and schuppe','6308 Bosco Field\nWillmsberg, NM 44476',1,1,1,NULL,'2026-11-06 13:33:20','2026-11-06 13:33:20',NULL,NULL,NULL);
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_contacts`
--

DROP TABLE IF EXISTS `company_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_contacts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `role` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_contacts_company_id_role_unique` (`company_id`,`role`),
  KEY `company_contacts_created_by_foreign` (`created_by`),
  KEY `company_contacts_updated_by_foreign` (`updated_by`),
  CONSTRAINT `company_contacts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `company_contacts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `company_contacts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_contacts`
--

LOCK TABLES `company_contacts` WRITE;
/*!40000 ALTER TABLE `company_contacts` DISABLE KEYS */;
INSERT INTO `company_contacts` VALUES (1,1,'manager','ABUL KASHEM','+8801982069807','2026-11-01 06:19:09','2026-11-01 06:19:51','2026-11-01 06:19:51',1,1),(2,1,'operator','AHMED','+971553209940','2026-11-01 06:19:09','2026-11-01 06:19:51','2026-11-01 06:19:51',1,1),(3,1,'dyeing_master','Ahmed','+971553209940','2026-11-01 06:19:51','2026-11-01 06:19:51',NULL,1,1),(4,2,'manager','ABUL KASHEM','+971553209940','2026-11-01 06:20:28','2026-11-01 06:20:28',NULL,1,1),(5,2,'operator','Rahim','+8801982069807','2026-11-01 06:20:28','2026-11-01 06:20:28',NULL,1,1),(6,3,'manager','Cyril Turner','+8801700000001','2026-11-06 13:33:20','2026-11-06 13:33:20',NULL,NULL,NULL),(7,3,'operator','Reggie Simonis V','+8801700000001','2026-11-06 13:33:20','2026-11-06 13:33:20',NULL,NULL,NULL),(8,3,'dyeing_master','Prof. Shad Hodkiewicz II','+8801700000001','2026-11-06 13:33:20','2026-11-06 13:33:20',NULL,NULL,NULL);
/*!40000 ALTER TABLE `company_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cutting_job_rolls`
--

DROP TABLE IF EXISTS `cutting_job_rolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cutting_job_rolls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cutting_job_id` bigint(20) unsigned NOT NULL,
  `roll_id` bigint(20) unsigned NOT NULL,
  `cutting_type` varchar(8) NOT NULL,
  `lot_id` bigint(20) unsigned NOT NULL,
  `color` varchar(255) NOT NULL,
  `dia` int(10) unsigned NOT NULL,
  `roll_weight_kg` decimal(18,3) NOT NULL,
  `dozens` decimal(18,3) DEFAULT NULL,
  `cut_weight_kg` decimal(18,3) DEFAULT NULL,
  `remaining_fabric` decimal(18,3) NOT NULL DEFAULT 0.000,
  `remaining_fabric_kg` decimal(18,3) NOT NULL DEFAULT 0.000,
  `cutting_scrap_kg` decimal(18,3) DEFAULT NULL,
  `waste_kg` decimal(18,3) DEFAULT NULL,
  `weight_per_dozen` decimal(18,6) DEFAULT NULL,
  `weight_per_dozen_kg` decimal(18,8) DEFAULT NULL,
  `weight_unit` varchar(2) NOT NULL,
  `remaining_fabric_unit` varchar(2) NOT NULL,
  `waste_percent` decimal(7,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `source_body_row_id` bigint(20) unsigned DEFAULT NULL,
  `exclusive_cutting_type` varchar(8) GENERATED ALWAYS AS (case when `cutting_type` = 'neck' then NULL else `cutting_type` end) VIRTUAL,
  `pieces` decimal(15,3) DEFAULT NULL,
  `weight_is_automatic` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cutting_rows_job_roll_unique` (`cutting_job_id`,`roll_id`),
  KEY `cutting_job_rolls_lot_id_foreign` (`lot_id`),
  KEY `cutting_job_rolls_source_body_row_id_foreign` (`source_body_row_id`),
  KEY `cutting_rows_roll_type_index` (`roll_id`,`cutting_type`),
  CONSTRAINT `cutting_job_rolls_cutting_job_id_foreign` FOREIGN KEY (`cutting_job_id`) REFERENCES `cutting_jobs` (`id`),
  CONSTRAINT `cutting_job_rolls_lot_id_foreign` FOREIGN KEY (`lot_id`) REFERENCES `lots` (`id`),
  CONSTRAINT `cutting_job_rolls_roll_id_foreign` FOREIGN KEY (`roll_id`) REFERENCES `returned_rolls` (`id`),
  CONSTRAINT `cutting_job_rolls_source_body_row_id_foreign` FOREIGN KEY (`source_body_row_id`) REFERENCES `cutting_job_rolls` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cutting_job_rolls`
--

LOCK TABLES `cutting_job_rolls` WRITE;
/*!40000 ALTER TABLE `cutting_job_rolls` DISABLE KEYS */;
INSERT INTO `cutting_job_rolls` VALUES (1,2,1,'body',1,'Black',32,22.452,22.000,22.452,0.000,0.000,0.000,0.000,1.020545,1.02054545,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(2,2,3,'body',1,'Black',34,22.452,22.000,22.452,0.000,0.000,0.000,0.000,1.020545,1.02054545,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(3,2,4,'body',1,'Black',34,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(4,2,5,'body',1,'Black',36,22.452,22.000,22.452,0.000,0.000,0.000,0.000,1.020545,1.02054545,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(5,2,6,'body',1,'Black',36,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(6,2,7,'body',1,'Black',38,22.452,22.000,22.452,0.000,0.000,0.000,0.000,1.020545,1.02054545,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(7,2,8,'body',1,'Black',38,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(8,2,9,'body',1,'Black',40,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(9,2,10,'body',1,'Black',40,10.000,10.000,10.000,0.000,0.000,0.000,0.000,1.000000,1.00000000,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',120.000,1),(10,2,11,'body',1,'White',32,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(11,2,13,'body',1,'White',34,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(12,2,14,'body',1,'White',34,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(13,2,15,'body',1,'White',36,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(14,2,16,'body',1,'White',36,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(15,2,17,'body',1,'White',38,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(16,2,18,'body',1,'White',38,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(17,2,19,'body',1,'White',40,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',264.000,1),(18,2,20,'body',1,'White',40,10.000,10.000,10.000,0.000,0.000,0.000,0.000,1.000000,1.00000000,'kg','kg',0.00,'2026-11-06 14:16:23','2026-11-06 15:06:30',NULL,'body',120.000,1),(19,3,1,'neck',1,'Black',32,22.452,22.000,22.452,0.000,0.000,0.000,0.000,1.020545,1.02054545,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',1,NULL,264.000,1),(20,3,3,'neck',1,'Black',34,22.452,22.000,22.452,0.000,0.000,0.000,0.000,1.020545,1.02054545,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',2,NULL,264.000,1),(21,3,4,'neck',1,'Black',34,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',3,NULL,264.000,1),(22,3,5,'neck',1,'Black',36,22.452,22.000,22.452,0.000,0.000,0.000,0.000,1.020545,1.02054545,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',4,NULL,264.000,1),(23,3,6,'neck',1,'Black',36,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',5,NULL,264.000,1),(24,3,7,'neck',1,'Black',38,22.452,22.000,22.452,0.000,0.000,0.000,0.000,1.020545,1.02054545,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',6,NULL,264.000,1),(25,3,8,'neck',1,'Black',38,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',7,NULL,264.000,1),(26,3,9,'neck',1,'Black',40,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',8,NULL,264.000,1),(27,3,10,'neck',1,'Black',40,10.000,10.000,10.000,0.000,0.000,0.000,0.000,1.000000,1.00000000,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',9,NULL,120.000,1),(28,3,11,'neck',1,'White',32,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',10,NULL,264.000,1),(29,3,13,'neck',1,'White',34,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',11,NULL,264.000,1),(30,3,14,'neck',1,'White',34,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',12,NULL,264.000,1),(31,3,15,'neck',1,'White',36,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',13,NULL,264.000,1),(32,3,16,'neck',1,'White',36,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',14,NULL,264.000,1),(33,3,17,'neck',1,'White',38,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',15,NULL,264.000,1),(34,3,18,'neck',1,'White',38,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',16,NULL,264.000,1),(35,3,19,'neck',1,'White',40,22.453,22.000,22.453,0.000,0.000,0.000,0.000,1.020591,1.02059091,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',17,NULL,264.000,1),(36,3,20,'neck',1,'White',40,10.000,10.000,10.000,0.000,0.000,0.000,0.000,1.000000,1.00000000,'kg','kg',0.00,'2026-11-06 15:06:46','2026-11-06 15:06:46',18,NULL,120.000,1),(37,4,2,'rib',1,'Black',32,22.453,NULL,NULL,0.000,0.000,NULL,NULL,NULL,NULL,'kg','kg',NULL,'2026-11-11 09:30:48','2026-11-11 09:30:48',NULL,'rib',NULL,0),(38,4,10,'rib',1,'Black',40,12.453,NULL,NULL,0.000,0.000,NULL,NULL,NULL,NULL,'kg','kg',NULL,'2026-11-11 09:30:48','2026-11-11 09:30:48',NULL,'rib',NULL,0),(39,4,12,'rib',1,'White',32,22.453,NULL,NULL,0.000,0.000,NULL,NULL,NULL,NULL,'kg','kg',NULL,'2026-11-11 09:30:48','2026-11-11 09:30:48',NULL,'rib',NULL,0),(40,4,20,'rib',1,'White',40,12.454,NULL,NULL,0.000,0.000,NULL,NULL,NULL,NULL,'kg','kg',NULL,'2026-11-11 09:30:48','2026-11-11 09:30:48',NULL,'rib',NULL,0);
/*!40000 ALTER TABLE `cutting_job_rolls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cutting_jobs`
--

DROP TABLE IF EXISTS `cutting_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cutting_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cutting_type` varchar(8) NOT NULL,
  `cutting_date` date NOT NULL,
  `staff_id` bigint(20) unsigned NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cutting_jobs_staff_id_foreign` (`staff_id`),
  KEY `cutting_jobs_created_by_foreign` (`created_by`),
  KEY `cutting_jobs_updated_by_foreign` (`updated_by`),
  KEY `cutting_jobs_cutting_type_index` (`cutting_type`),
  KEY `cutting_jobs_cutting_date_index` (`cutting_date`),
  CONSTRAINT `cutting_jobs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cutting_jobs_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`id`),
  CONSTRAINT `cutting_jobs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cutting_jobs`
--

LOCK TABLES `cutting_jobs` WRITE;
/*!40000 ALTER TABLE `cutting_jobs` DISABLE KEYS */;
INSERT INTO `cutting_jobs` VALUES (1,'body','2026-11-06',2,NULL,'2026-11-06 13:33:20','2026-11-06 14:15:35','2026-11-06 14:15:35',NULL,1),(2,'body','2026-11-06',1,NULL,'2026-11-06 14:16:23','2026-11-06 14:16:23',NULL,1,1),(3,'neck','2026-11-06',1,NULL,'2026-11-06 15:06:46','2026-11-06 15:06:46',NULL,1,1),(4,'rib','2026-11-11',2,NULL,'2026-11-11 09:30:48','2026-11-11 09:30:48',NULL,1,1);
/*!40000 ALTER TABLE `cutting_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dyeing_color_blocks`
--

DROP TABLE IF EXISTS `dyeing_color_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dyeing_color_blocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dyeing_job_id` bigint(20) unsigned NOT NULL,
  `color_id` bigint(20) unsigned NOT NULL,
  `sort_order` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dyeing_color_blocks_dyeing_job_id_color_id_unique` (`dyeing_job_id`,`color_id`),
  KEY `dyeing_color_blocks_color_id_foreign` (`color_id`),
  KEY `dyeing_color_blocks_created_by_foreign` (`created_by`),
  KEY `dyeing_color_blocks_updated_by_foreign` (`updated_by`),
  CONSTRAINT `dyeing_color_blocks_color_id_foreign` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`),
  CONSTRAINT `dyeing_color_blocks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dyeing_color_blocks_dyeing_job_id_foreign` FOREIGN KEY (`dyeing_job_id`) REFERENCES `dyeing_jobs` (`id`),
  CONSTRAINT `dyeing_color_blocks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dyeing_color_blocks`
--

LOCK TABLES `dyeing_color_blocks` WRITE;
/*!40000 ALTER TABLE `dyeing_color_blocks` DISABLE KEYS */;
INSERT INTO `dyeing_color_blocks` VALUES (1,1,1,0,'2026-11-06 13:09:01','2026-11-06 13:09:01',NULL,1,1),(2,1,3,1,'2026-11-06 13:09:01','2026-11-06 13:09:01',NULL,1,1),(3,2,9,0,'2026-11-06 13:33:20','2026-11-06 14:14:45','2026-11-06 14:14:45',NULL,1);
/*!40000 ALTER TABLE `dyeing_color_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dyeing_jobs`
--

DROP TABLE IF EXISTS `dyeing_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dyeing_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lot_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `entry_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dyeing_jobs_lot_id_foreign` (`lot_id`),
  KEY `dyeing_jobs_company_id_foreign` (`company_id`),
  KEY `dyeing_jobs_created_by_foreign` (`created_by`),
  KEY `dyeing_jobs_updated_by_foreign` (`updated_by`),
  KEY `dyeing_jobs_entry_date_index` (`entry_date`),
  CONSTRAINT `dyeing_jobs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `dyeing_jobs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dyeing_jobs_lot_id_foreign` FOREIGN KEY (`lot_id`) REFERENCES `lots` (`id`),
  CONSTRAINT `dyeing_jobs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dyeing_jobs`
--

LOCK TABLES `dyeing_jobs` WRITE;
/*!40000 ALTER TABLE `dyeing_jobs` DISABLE KEYS */;
INSERT INTO `dyeing_jobs` VALUES (1,1,1,'2026-11-06','2026-11-06',NULL,'2026-11-06 13:09:01','2026-11-06 13:09:01',NULL,1,1),(2,2,3,'2026-11-06',NULL,NULL,'2026-11-06 13:33:20','2026-11-06 14:14:45','2026-11-06 14:14:45',NULL,1);
/*!40000 ALTER TABLE `dyeing_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dyeing_rows`
--

DROP TABLE IF EXISTS `dyeing_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dyeing_rows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `color_block_id` bigint(20) unsigned NOT NULL,
  `dia` int(10) unsigned NOT NULL,
  `given_fabric_kg` decimal(14,3) NOT NULL,
  `returned_fabric_kg` decimal(14,3) DEFAULT NULL,
  `waste_percent` decimal(20,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `rolls` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rolls`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dyeing_rows_color_block_id_dia_unique` (`color_block_id`,`dia`),
  KEY `dyeing_rows_created_by_foreign` (`created_by`),
  KEY `dyeing_rows_updated_by_foreign` (`updated_by`),
  KEY `dyeing_rows_dia_index` (`dia`),
  CONSTRAINT `dyeing_rows_color_block_id_foreign` FOREIGN KEY (`color_block_id`) REFERENCES `dyeing_color_blocks` (`id`),
  CONSTRAINT `dyeing_rows_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dyeing_rows_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dyeing_rows`
--

LOCK TABLES `dyeing_rows` WRITE;
/*!40000 ALTER TABLE `dyeing_rows` DISABLE KEYS */;
INSERT INTO `dyeing_rows` VALUES (1,1,32,44.905,44.905,0.00,'2026-11-06 13:09:01','2026-11-06 13:09:01',NULL,1,1,'[{\"number\":\"32ROLL01\",\"weight\":\"22.452\",\"unit\":\"kg\",\"weight_kg\":\"22.452\"},{\"number\":\"32ROLL03\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"}]'),(2,1,34,44.905,44.905,0.00,'2026-11-06 13:09:01','2026-11-06 13:09:01',NULL,1,1,'[{\"number\":\"34ROLL01\",\"weight\":\"22.452\",\"unit\":\"kg\",\"weight_kg\":\"22.452\"},{\"number\":\"34ROLL03\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"}]'),(3,1,36,44.905,44.905,0.00,'2026-11-06 13:09:01','2026-11-06 13:09:01',NULL,1,1,'[{\"number\":\"36ROLL01\",\"weight\":\"22.452\",\"unit\":\"kg\",\"weight_kg\":\"22.452\"},{\"number\":\"36ROLL03\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"}]'),(4,1,38,44.905,44.905,0.00,'2026-11-06 13:09:01','2026-11-06 13:09:01',NULL,1,1,'[{\"number\":\"38ROLL01\",\"weight\":\"22.452\",\"unit\":\"kg\",\"weight_kg\":\"22.452\"},{\"number\":\"38ROLL03\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"}]'),(5,1,40,44.906,44.906,0.00,'2026-11-06 13:09:01','2026-11-06 13:09:01',NULL,1,1,'[{\"number\":\"40ROLL01\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"},{\"number\":\"40ROLL03\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"}]'),(6,2,32,44.906,44.906,0.00,'2026-11-06 13:09:01','2026-11-06 13:09:01',NULL,1,1,'[{\"number\":\"32WHITE01\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"},{\"number\":\"32WHITE02\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"}]'),(7,2,34,44.906,44.906,0.00,'2026-11-06 13:09:01','2026-11-06 13:09:01',NULL,1,1,'[{\"number\":\"34WHITE01\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"},{\"number\":\"34WHITE02\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"}]'),(8,2,36,44.906,44.906,0.00,'2026-11-06 13:09:01','2026-11-06 13:09:01',NULL,1,1,'[{\"number\":\"36WHITE01\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"},{\"number\":\"36WHITE02\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"}]'),(9,2,38,44.906,44.906,0.00,'2026-11-06 13:09:01','2026-11-06 13:09:01',NULL,1,1,'[{\"number\":\"38WHITE01\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"},{\"number\":\"38WHITE02\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"}]'),(10,2,40,44.907,44.907,0.00,'2026-11-06 13:09:01','2026-11-06 13:09:01',NULL,1,1,'[{\"number\":\"40WHITE01\",\"weight\":\"22.453\",\"unit\":\"kg\",\"weight_kg\":\"22.453\"},{\"number\":\"40WHITE02\",\"weight\":\"22.454\",\"unit\":\"kg\",\"weight_kg\":\"22.454\"}]'),(11,3,32,100.000,10.000,90.00,'2026-11-06 13:33:20','2026-11-06 14:14:45','2026-11-06 14:14:45',NULL,1,'[{\"number\":\"ROLL-8421-bfuc\",\"weight\":\"10.000\",\"unit\":\"kg\",\"weight_kg\":\"10.000\"}]');
/*!40000 ALTER TABLE `dyeing_rows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `knitting_dia_row_yarns`
--

DROP TABLE IF EXISTS `knitting_dia_row_yarns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `knitting_dia_row_yarns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dia_row_id` bigint(20) unsigned NOT NULL,
  `yarn_id` bigint(20) unsigned NOT NULL,
  `quantity_kg` decimal(14,3) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `knitting_dia_row_yarns_dia_row_id_yarn_id_unique` (`dia_row_id`,`yarn_id`),
  KEY `knitting_dia_row_yarns_yarn_id_foreign` (`yarn_id`),
  KEY `knitting_dia_row_yarns_created_by_foreign` (`created_by`),
  KEY `knitting_dia_row_yarns_updated_by_foreign` (`updated_by`),
  CONSTRAINT `knitting_dia_row_yarns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `knitting_dia_row_yarns_dia_row_id_foreign` FOREIGN KEY (`dia_row_id`) REFERENCES `knitting_dia_rows` (`id`),
  CONSTRAINT `knitting_dia_row_yarns_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `knitting_dia_row_yarns_yarn_id_foreign` FOREIGN KEY (`yarn_id`) REFERENCES `yarns` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `knitting_dia_row_yarns`
--

LOCK TABLES `knitting_dia_row_yarns` WRITE;
/*!40000 ALTER TABLE `knitting_dia_row_yarns` DISABLE KEYS */;
INSERT INTO `knitting_dia_row_yarns` VALUES (1,1,4,90.718,'2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(2,2,4,90.718,'2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(3,3,4,90.718,'2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(4,4,4,90.718,'2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(5,5,4,90.720,'2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(6,6,5,100.000,'2026-11-06 13:33:20','2026-11-06 14:15:10','2026-11-06 14:15:10',NULL,1);
/*!40000 ALTER TABLE `knitting_dia_row_yarns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `knitting_dia_rows`
--

DROP TABLE IF EXISTS `knitting_dia_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `knitting_dia_rows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `knitting_job_id` bigint(20) unsigned NOT NULL,
  `dia` int(10) unsigned NOT NULL,
  `input_kg` decimal(14,3) NOT NULL,
  `waste_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `output_fabric_kg` decimal(14,3) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `knitting_dia_rows_knitting_job_id_dia_unique` (`knitting_job_id`,`dia`),
  KEY `knitting_dia_rows_created_by_foreign` (`created_by`),
  KEY `knitting_dia_rows_updated_by_foreign` (`updated_by`),
  CONSTRAINT `knitting_dia_rows_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `knitting_dia_rows_knitting_job_id_foreign` FOREIGN KEY (`knitting_job_id`) REFERENCES `knitting_jobs` (`id`),
  CONSTRAINT `knitting_dia_rows_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `knitting_dia_rows`
--

LOCK TABLES `knitting_dia_rows` WRITE;
/*!40000 ALTER TABLE `knitting_dia_rows` DISABLE KEYS */;
INSERT INTO `knitting_dia_rows` VALUES (1,1,32,90.718,1.00,89.811,0,'2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(2,1,34,90.718,1.00,89.811,1,'2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(3,1,36,90.718,1.00,89.811,2,'2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(4,1,38,90.718,1.00,89.811,3,'2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(5,1,40,90.720,1.00,89.813,4,'2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(6,2,32,100.000,0.00,100.000,0,'2026-11-06 13:33:20','2026-11-06 14:15:10','2026-11-06 14:15:10',NULL,1);
/*!40000 ALTER TABLE `knitting_dia_rows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `knitting_job_yarns`
--

DROP TABLE IF EXISTS `knitting_job_yarns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `knitting_job_yarns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `knitting_job_id` bigint(20) unsigned NOT NULL,
  `yarn_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `knitting_job_yarns_knitting_job_id_yarn_id_unique` (`knitting_job_id`,`yarn_id`),
  KEY `knitting_job_yarns_yarn_id_foreign` (`yarn_id`),
  KEY `knitting_job_yarns_created_by_foreign` (`created_by`),
  KEY `knitting_job_yarns_updated_by_foreign` (`updated_by`),
  CONSTRAINT `knitting_job_yarns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `knitting_job_yarns_knitting_job_id_foreign` FOREIGN KEY (`knitting_job_id`) REFERENCES `knitting_jobs` (`id`),
  CONSTRAINT `knitting_job_yarns_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `knitting_job_yarns_yarn_id_foreign` FOREIGN KEY (`yarn_id`) REFERENCES `yarns` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `knitting_job_yarns`
--

LOCK TABLES `knitting_job_yarns` WRITE;
/*!40000 ALTER TABLE `knitting_job_yarns` DISABLE KEYS */;
INSERT INTO `knitting_job_yarns` VALUES (1,1,4,'2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(2,2,5,'2026-11-06 13:33:20','2026-11-06 14:15:10','2026-11-06 14:15:10',NULL,1);
/*!40000 ALTER TABLE `knitting_job_yarns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `knitting_jobs`
--

DROP TABLE IF EXISTS `knitting_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `knitting_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lot_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `entry_date` date NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `input_unit` varchar(2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `knitting_jobs_lot_id_unique` (`lot_id`),
  KEY `knitting_jobs_company_id_foreign` (`company_id`),
  KEY `knitting_jobs_created_by_foreign` (`created_by`),
  KEY `knitting_jobs_updated_by_foreign` (`updated_by`),
  KEY `knitting_jobs_entry_date_index` (`entry_date`),
  CONSTRAINT `knitting_jobs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `knitting_jobs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `knitting_jobs_lot_id_foreign` FOREIGN KEY (`lot_id`) REFERENCES `lots` (`id`),
  CONSTRAINT `knitting_jobs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `knitting_jobs`
--

LOCK TABLES `knitting_jobs` WRITE;
/*!40000 ALTER TABLE `knitting_jobs` DISABLE KEYS */;
INSERT INTO `knitting_jobs` VALUES (1,1,2,'2026-11-06','2026-11-06','lb',NULL,'2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(2,2,3,'2026-11-06',NULL,'kg',NULL,'2026-11-06 13:33:20','2026-11-06 14:15:10','2026-11-06 14:15:10',NULL,1);
/*!40000 ALTER TABLE `knitting_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lot_sequences`
--

DROP TABLE IF EXISTS `lot_sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lot_sequences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `base` varchar(255) NOT NULL,
  `next_suffix` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lot_sequences_base_unique` (`base`),
  KEY `lot_sequences_created_by_foreign` (`created_by`),
  KEY `lot_sequences_updated_by_foreign` (`updated_by`),
  CONSTRAINT `lot_sequences_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lot_sequences_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lot_sequences`
--

LOCK TABLES `lot_sequences` WRITE;
/*!40000 ALTER TABLE `lot_sequences` DISABLE KEYS */;
INSERT INTO `lot_sequences` VALUES (1,'SU-061126',2,'2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(2,'PE-061126',2,'2026-11-06 13:33:20','2026-11-06 13:33:20',NULL,NULL,NULL);
/*!40000 ALTER TABLE `lot_sequences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lots`
--

DROP TABLE IF EXISTS `lots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lot_number` varchar(255) NOT NULL,
  `lot_date` date NOT NULL,
  `current_stage` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lots_lot_number_unique` (`lot_number`),
  KEY `lots_created_by_foreign` (`created_by`),
  KEY `lots_updated_by_foreign` (`updated_by`),
  KEY `lots_current_stage_index` (`current_stage`),
  CONSTRAINT `lots_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lots_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lots`
--

LOCK TABLES `lots` WRITE;
/*!40000 ALTER TABLE `lots` DISABLE KEYS */;
INSERT INTO `lots` VALUES (1,'SU-061126','2026-11-06','sewing','2026-11-06 12:45:37','2026-11-11 13:08:05',NULL,1,1),(2,'PE-061126','2026-11-06','knitting','2026-11-06 13:33:20','2026-11-06 14:15:10','2026-11-06 14:15:10',NULL,1);
/*!40000 ALTER TABLE `lots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_04_211255_create_manufacturing_tables',1),(5,'2026_10_04_214922_add_audit_fields_to_users',1),(6,'2026_10_05_042541_widen_dyeing_waste_for_confirmed_mass_gain',1),(7,'2026_10_18_000001_add_rolls_to_dyeing_rows_table',1),(8,'2026_10_18_203707_create_staffs_table',1),(9,'2026_10_18_203901_create_cutting_tables',1),(10,'2026_10_18_220248_allow_pending_cutting_results',1),(11,'2026_10_23_211844_link_neck_cutting_to_body_batches',1),(12,'2026_10_26_013546_allow_partial_neck_cutting_allocations',1),(13,'2026_10_26_020112_add_pieces_to_cutting_job_rolls_table',1),(14,'2026_10_26_024431_add_automatic_weight_to_cutting_job_rolls_table',1),(15,'2026_11_01_171516_create_sewing_jobs_table',2),(16,'2026_11_01_171517_create_sewing_job_rows_table',2),(17,'2026_11_01_172500_add_component_sources_to_sewing_job_rows_table',3),(18,'2026_11_01_172700_add_piece_quantities_to_sewing_job_rows_table',4),(19,'2026_11_01_175900_add_sewing_ready_groups',5),(20,'2026_11_01_181600_add_current_step_to_sewing_jobs_table',6),(21,'2026_11_06_192601_allow_partial_roll_cutting_allocations',7);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `returned_rolls`
--

DROP TABLE IF EXISTS `returned_rolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `returned_rolls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dyeing_job_id` bigint(20) unsigned NOT NULL,
  `dyeing_row_id` bigint(20) unsigned NOT NULL,
  `number` varchar(255) NOT NULL,
  `number_key` varchar(255) NOT NULL,
  `weight` decimal(18,3) NOT NULL,
  `unit` varchar(2) NOT NULL,
  `weight_kg` decimal(18,3) NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `returned_rolls_dyeing_job_id_number_key_unique` (`dyeing_job_id`,`number_key`),
  KEY `returned_rolls_dyeing_row_id_foreign` (`dyeing_row_id`),
  CONSTRAINT `returned_rolls_dyeing_job_id_foreign` FOREIGN KEY (`dyeing_job_id`) REFERENCES `dyeing_jobs` (`id`),
  CONSTRAINT `returned_rolls_dyeing_row_id_foreign` FOREIGN KEY (`dyeing_row_id`) REFERENCES `dyeing_rows` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `returned_rolls`
--

LOCK TABLES `returned_rolls` WRITE;
/*!40000 ALTER TABLE `returned_rolls` DISABLE KEYS */;
INSERT INTO `returned_rolls` VALUES (1,1,1,'32ROLL01','32roll01',22.452,'kg',22.452,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(2,1,1,'32ROLL03','32roll03',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(3,1,2,'34ROLL01','34roll01',22.452,'kg',22.452,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(4,1,2,'34ROLL03','34roll03',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(5,1,3,'36ROLL01','36roll01',22.452,'kg',22.452,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(6,1,3,'36ROLL03','36roll03',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(7,1,4,'38ROLL01','38roll01',22.452,'kg',22.452,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(8,1,4,'38ROLL03','38roll03',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(9,1,5,'40ROLL01','40roll01',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(10,1,5,'40ROLL03','40roll03',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(11,1,6,'32WHITE01','32white01',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(12,1,6,'32WHITE02','32white02',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(13,1,7,'34WHITE01','34white01',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(14,1,7,'34WHITE02','34white02',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(15,1,8,'36WHITE01','36white01',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(16,1,8,'36WHITE02','36white02',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(17,1,9,'38WHITE01','38white01',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(18,1,9,'38WHITE02','38white02',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(19,1,10,'40WHITE01','40white01',22.453,'kg',22.453,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(20,1,10,'40WHITE02','40white02',22.454,'kg',22.454,1,'2026-11-06 13:09:01','2026-11-06 13:09:01'),(21,2,11,'ROLL-8421-bfuc','roll-8421-bfuc',10.000,'kg',10.000,0,'2026-11-06 13:33:20','2026-11-06 14:14:45');
/*!40000 ALTER TABLE `returned_rolls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('igcPNKfNNksrH37LZkWzvavg2MJmHqeYHOGW5foX',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJFS1lMb0R1QXJ0cE1JMUljaGpJb05aUnNQbXJCWjlMWjlPNnFHY1VnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sInVybCI6W10sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJwYXNzd29yZF9oYXNoX3dlYiI6IjJlZGU1ZGE1MGM5MTg5M2Q1NDAxMTQ5ZjdmYzU4Yjk5ZWI0YjcxMTQ0Y2U4NWE0NWU4MDk5Nzk2OTYxMmE5MTkifQ==',1794467064),('Ymk7KIcm6ffHycU6ck9gg3zJiGLnUyQlBRQv1IMT',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJHYW1TUGw1dXJGeFpGSDRLbmxZSEk4b1kyT2hkUGQ3MDZyMFM0QWlqIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9zZXdpbmctam9icyIsInJvdXRlIjoic2V3aW5nLWpvYnMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJ1cmwiOltdLCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwicGFzc3dvcmRfaGFzaF93ZWIiOiIyZWRlNWRhNTBjOTE4OTNkNTQwMTE0OWY3ZmM1OGI5OWViNGI3MTE0NGNlODVhNDVlODA5OTc5Njk2MTJhOTE5In0=',1794403483);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`),
  KEY `settings_created_by_foreign` (`created_by`),
  KEY `settings_updated_by_foreign` (`updated_by`),
  CONSTRAINT `settings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'default_weight_unit','\"lb\"','2026-11-04 01:39:43','2026-11-04 01:39:43',NULL,NULL,NULL),(2,'dia_presets','[32,34,36,40]','2026-11-04 01:39:43','2026-11-04 01:39:43',NULL,NULL,NULL),(3,'currency_label','\"BDT\"','2026-11-04 01:39:43','2026-11-04 01:39:43',NULL,NULL,NULL);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sewing_job_rows`
--

DROP TABLE IF EXISTS `sewing_job_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sewing_job_rows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sewing_job_id` bigint(20) unsigned NOT NULL,
  `group_key` varchar(255) NOT NULL,
  `source_body_row_id` bigint(20) unsigned NOT NULL,
  `source_neck_row_id` bigint(20) unsigned DEFAULT NULL,
  `source_rib_row_id` bigint(20) unsigned DEFAULT NULL,
  `lot_id` bigint(20) unsigned NOT NULL,
  `color` varchar(255) NOT NULL,
  `dia` int(10) unsigned NOT NULL,
  `fold_dozens` decimal(18,3) NOT NULL DEFAULT 0.000,
  `fold_pieces` int(10) unsigned NOT NULL DEFAULT 0,
  `lock_dozens` decimal(18,3) NOT NULL DEFAULT 0.000,
  `lock_pieces` int(10) unsigned NOT NULL DEFAULT 0,
  `down_dozens` decimal(18,3) NOT NULL DEFAULT 0.000,
  `down_pieces` int(10) unsigned NOT NULL DEFAULT 0,
  `neck_join_dozens` decimal(18,3) NOT NULL DEFAULT 0.000,
  `neck_join_pieces` int(10) unsigned NOT NULL DEFAULT 0,
  `neck_finishing_dozens` decimal(18,3) NOT NULL DEFAULT 0.000,
  `neck_finishing_pieces` int(10) unsigned NOT NULL DEFAULT 0,
  `rejected_pieces` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sewing_job_rows_sewing_job_id_source_body_row_id_unique` (`sewing_job_id`,`source_body_row_id`),
  UNIQUE KEY `sewing_job_rows_sewing_job_id_group_key_unique` (`sewing_job_id`,`group_key`),
  KEY `sewing_job_rows_source_body_row_id_foreign` (`source_body_row_id`),
  KEY `sewing_job_rows_lot_id_foreign` (`lot_id`),
  KEY `sewing_job_rows_source_neck_row_id_foreign` (`source_neck_row_id`),
  KEY `sewing_job_rows_source_rib_row_id_foreign` (`source_rib_row_id`),
  CONSTRAINT `sewing_job_rows_lot_id_foreign` FOREIGN KEY (`lot_id`) REFERENCES `lots` (`id`),
  CONSTRAINT `sewing_job_rows_sewing_job_id_foreign` FOREIGN KEY (`sewing_job_id`) REFERENCES `sewing_jobs` (`id`),
  CONSTRAINT `sewing_job_rows_source_body_row_id_foreign` FOREIGN KEY (`source_body_row_id`) REFERENCES `cutting_job_rolls` (`id`),
  CONSTRAINT `sewing_job_rows_source_neck_row_id_foreign` FOREIGN KEY (`source_neck_row_id`) REFERENCES `cutting_job_rolls` (`id`),
  CONSTRAINT `sewing_job_rows_source_rib_row_id_foreign` FOREIGN KEY (`source_rib_row_id`) REFERENCES `cutting_job_rolls` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sewing_job_rows`
--

LOCK TABLES `sewing_job_rows` WRITE;
/*!40000 ALTER TABLE `sewing_job_rows` DISABLE KEYS */;
INSERT INTO `sewing_job_rows` VALUES (1,1,'1:Black:32',1,19,NULL,1,'Black',32,10.000,0,0.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:05','2026-11-11 13:08:05'),(2,1,'1:Black:34',2,20,NULL,1,'Black',34,20.000,0,0.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:05','2026-11-11 13:08:05'),(3,1,'1:Black:36',4,22,NULL,1,'Black',36,20.000,0,0.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:05','2026-11-11 13:08:05'),(4,1,'1:Black:38',6,24,NULL,1,'Black',38,20.000,0,0.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:05','2026-11-11 13:08:05'),(5,1,'1:Black:40',8,26,NULL,1,'Black',40,20.000,0,0.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:05','2026-11-11 13:08:05'),(6,1,'1:White:32',10,28,NULL,1,'White',32,22.000,0,0.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:05','2026-11-11 13:08:05'),(7,1,'1:White:34',11,29,NULL,1,'White',34,20.000,0,0.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:05','2026-11-11 13:08:05'),(8,1,'1:White:36',13,31,NULL,1,'White',36,20.000,0,0.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:05','2026-11-11 13:08:05'),(9,1,'1:White:38',15,33,NULL,1,'White',38,20.000,0,0.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:05','2026-11-11 13:08:05'),(10,1,'1:White:40',17,35,NULL,1,'White',40,22.000,0,0.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:05','2026-11-11 13:08:05'),(11,2,'1:Black:32',1,19,NULL,1,'Black',32,0.000,0,10.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:24','2026-11-11 13:08:24'),(12,2,'1:Black:34',2,20,NULL,1,'Black',34,0.000,0,20.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:24','2026-11-11 13:08:24'),(13,2,'1:Black:36',4,22,NULL,1,'Black',36,0.000,0,20.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:24','2026-11-11 13:08:24'),(14,2,'1:Black:38',6,24,NULL,1,'Black',38,0.000,0,20.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:24','2026-11-11 13:08:24'),(15,2,'1:Black:40',8,26,NULL,1,'Black',40,0.000,0,20.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:24','2026-11-11 13:08:24'),(16,2,'1:White:32',10,28,NULL,1,'White',32,0.000,0,22.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:24','2026-11-11 13:08:24'),(17,2,'1:White:34',11,29,NULL,1,'White',34,0.000,0,20.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:24','2026-11-11 13:08:24'),(18,2,'1:White:36',13,31,NULL,1,'White',36,0.000,0,20.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:24','2026-11-11 13:08:24'),(19,2,'1:White:38',15,33,NULL,1,'White',38,0.000,0,20.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:24','2026-11-11 13:08:24'),(20,2,'1:White:40',17,35,NULL,1,'White',40,0.000,0,22.000,0,0.000,0,0.000,0,0.000,0,0,'2026-11-11 13:08:24','2026-11-11 13:08:24'),(21,3,'1:Black:32',1,19,NULL,1,'Black',32,0.000,0,0.000,0,10.000,0,0.000,0,0.000,0,0,'2026-11-11 13:17:31','2026-11-11 13:17:31'),(22,3,'1:Black:34',2,20,NULL,1,'Black',34,0.000,0,0.000,0,20.000,0,0.000,0,0.000,0,0,'2026-11-11 13:17:31','2026-11-11 13:17:31'),(23,3,'1:Black:36',4,22,NULL,1,'Black',36,0.000,0,0.000,0,20.000,0,0.000,0,0.000,0,0,'2026-11-11 13:17:31','2026-11-11 13:17:31'),(24,3,'1:Black:38',6,24,NULL,1,'Black',38,0.000,0,0.000,0,20.000,0,0.000,0,0.000,0,0,'2026-11-11 13:17:31','2026-11-11 13:17:31'),(25,3,'1:Black:40',8,26,NULL,1,'Black',40,0.000,0,0.000,0,20.000,0,0.000,0,0.000,0,0,'2026-11-11 13:17:31','2026-11-11 13:17:31'),(26,3,'1:White:32',10,28,NULL,1,'White',32,0.000,0,0.000,0,22.000,0,0.000,0,0.000,0,0,'2026-11-11 13:17:31','2026-11-11 13:17:31'),(27,3,'1:White:34',11,29,NULL,1,'White',34,0.000,0,0.000,0,20.000,0,0.000,0,0.000,0,0,'2026-11-11 13:17:31','2026-11-11 13:17:31'),(28,3,'1:White:36',13,31,NULL,1,'White',36,0.000,0,0.000,0,20.000,0,0.000,0,0.000,0,0,'2026-11-11 13:17:31','2026-11-11 13:17:31'),(29,3,'1:White:38',15,33,NULL,1,'White',38,0.000,0,0.000,0,20.000,0,0.000,0,0.000,0,0,'2026-11-11 13:17:31','2026-11-11 13:17:31'),(30,3,'1:White:40',17,35,NULL,1,'White',40,0.000,0,0.000,0,22.000,0,0.000,0,0.000,0,0,'2026-11-11 13:17:31','2026-11-11 13:17:31'),(31,4,'1:Black:32',1,19,NULL,1,'Black',32,0.000,0,0.000,0,0.000,0,10.000,0,0.000,0,0,'2026-11-11 13:18:16','2026-11-11 13:18:16'),(32,4,'1:Black:34',2,20,NULL,1,'Black',34,0.000,0,0.000,0,0.000,0,20.000,0,0.000,0,0,'2026-11-11 13:18:16','2026-11-11 13:18:16'),(33,4,'1:Black:36',4,22,NULL,1,'Black',36,0.000,0,0.000,0,0.000,0,20.000,0,0.000,0,0,'2026-11-11 13:18:16','2026-11-11 13:18:16'),(34,4,'1:Black:38',6,24,NULL,1,'Black',38,0.000,0,0.000,0,0.000,0,20.000,0,0.000,0,0,'2026-11-11 13:18:16','2026-11-11 13:18:16'),(35,4,'1:Black:40',8,26,NULL,1,'Black',40,0.000,0,0.000,0,0.000,0,20.000,0,0.000,0,0,'2026-11-11 13:18:16','2026-11-11 13:18:16'),(36,4,'1:White:32',10,28,NULL,1,'White',32,0.000,0,0.000,0,0.000,0,22.000,0,0.000,0,0,'2026-11-11 13:18:16','2026-11-11 13:18:16'),(37,4,'1:White:34',11,29,NULL,1,'White',34,0.000,0,0.000,0,0.000,0,20.000,0,0.000,0,0,'2026-11-11 13:18:16','2026-11-11 13:18:16'),(38,4,'1:White:36',13,31,NULL,1,'White',36,0.000,0,0.000,0,0.000,0,20.000,0,0.000,0,0,'2026-11-11 13:18:16','2026-11-11 13:18:16'),(39,4,'1:White:38',15,33,NULL,1,'White',38,0.000,0,0.000,0,0.000,0,20.000,0,0.000,0,0,'2026-11-11 13:18:16','2026-11-11 13:18:16'),(40,4,'1:White:40',17,35,NULL,1,'White',40,0.000,0,0.000,0,0.000,0,22.000,0,0.000,0,0,'2026-11-11 13:18:16','2026-11-11 13:18:16'),(41,5,'1:Black:32',1,19,NULL,1,'Black',32,0.000,0,0.000,0,0.000,0,0.000,0,10.000,0,0,'2026-11-11 13:18:34','2026-11-11 13:18:34'),(42,5,'1:Black:34',2,20,NULL,1,'Black',34,0.000,0,0.000,0,0.000,0,0.000,0,20.000,0,0,'2026-11-11 13:18:34','2026-11-11 13:18:34'),(43,5,'1:Black:36',4,22,NULL,1,'Black',36,0.000,0,0.000,0,0.000,0,0.000,0,20.000,0,0,'2026-11-11 13:18:34','2026-11-11 13:18:34'),(44,5,'1:Black:38',6,24,NULL,1,'Black',38,0.000,0,0.000,0,0.000,0,0.000,0,20.000,0,0,'2026-11-11 13:18:34','2026-11-11 13:18:34'),(45,5,'1:Black:40',8,26,NULL,1,'Black',40,0.000,0,0.000,0,0.000,0,0.000,0,20.000,0,0,'2026-11-11 13:18:34','2026-11-11 13:18:34'),(46,5,'1:White:32',10,28,NULL,1,'White',32,0.000,0,0.000,0,0.000,0,0.000,0,22.000,0,0,'2026-11-11 13:18:34','2026-11-11 13:18:34'),(47,5,'1:White:34',11,29,NULL,1,'White',34,0.000,0,0.000,0,0.000,0,0.000,0,20.000,0,0,'2026-11-11 13:18:34','2026-11-11 13:18:34'),(48,5,'1:White:36',13,31,NULL,1,'White',36,0.000,0,0.000,0,0.000,0,0.000,0,20.000,0,0,'2026-11-11 13:18:34','2026-11-11 13:18:34'),(49,5,'1:White:38',15,33,NULL,1,'White',38,0.000,0,0.000,0,0.000,0,0.000,0,20.000,0,0,'2026-11-11 13:18:34','2026-11-11 13:18:34'),(50,5,'1:White:40',17,35,NULL,1,'White',40,0.000,0,0.000,0,0.000,0,0.000,0,22.000,0,0,'2026-11-11 13:18:34','2026-11-11 13:18:34');
/*!40000 ALTER TABLE `sewing_job_rows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sewing_jobs`
--

DROP TABLE IF EXISTS `sewing_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sewing_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sewing_date` date NOT NULL,
  `staff_id` bigint(20) unsigned NOT NULL,
  `current_step` varchar(255) NOT NULL DEFAULT 'fold',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sewing_jobs_staff_id_foreign` (`staff_id`),
  KEY `sewing_jobs_created_by_foreign` (`created_by`),
  KEY `sewing_jobs_updated_by_foreign` (`updated_by`),
  KEY `sewing_jobs_sewing_date_index` (`sewing_date`),
  KEY `sewing_jobs_current_step_index` (`current_step`),
  CONSTRAINT `sewing_jobs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sewing_jobs_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`id`),
  CONSTRAINT `sewing_jobs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sewing_jobs`
--

LOCK TABLES `sewing_jobs` WRITE;
/*!40000 ALTER TABLE `sewing_jobs` DISABLE KEYS */;
INSERT INTO `sewing_jobs` VALUES (1,'2026-11-11',1,'fold',NULL,'2026-11-11 13:08:05','2026-11-11 13:08:05',NULL,1,1),(2,'2026-11-11',1,'lock',NULL,'2026-11-11 13:08:24','2026-11-11 13:08:24',NULL,1,1),(3,'2026-11-11',1,'down',NULL,'2026-11-11 13:17:30','2026-11-11 13:17:30',NULL,1,1),(4,'2026-11-11',1,'neck_join',NULL,'2026-11-11 13:18:16','2026-11-11 13:18:16',NULL,1,1),(5,'2026-11-11',1,'neck_finishing',NULL,'2026-11-11 13:18:34','2026-11-11 13:18:34',NULL,1,1);
/*!40000 ALTER TABLE `sewing_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staffs`
--

DROP TABLE IF EXISTS `staffs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staffs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `address` text DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'cutting_man',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staffs_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staffs`
--

LOCK TABLES `staffs` WRITE;
/*!40000 ALTER TABLE `staffs` DISABLE KEYS */;
INSERT INTO `staffs` VALUES (1,'Tipu Miah','0553209940','Shop 018, New Industrial Area - 2','cutting_man',1,'2026-11-01 10:50:57','2026-11-01 10:50:57'),(2,'Ms. Brigitte Greenfelder','01712345678',NULL,'cutting_man',1,'2026-11-06 13:33:20','2026-11-06 13:33:20');
/*!40000 ALTER TABLE `staffs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_created_by_foreign` (`created_by`),
  KEY `users_updated_by_foreign` (`updated_by`),
  CONSTRAINT `users_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@garmentos.test',NULL,'$2y$12$HK.7zQAu/7C5i2ohCLRlCOFAwlcx9TYCBHbkG7lVHUjahXH5KvWIC',NULL,'2026-11-01 06:16:33','2026-11-01 06:16:33',NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yarn_stock_movements`
--

DROP TABLE IF EXISTS `yarn_stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yarn_stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `yarn_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `quantity_kg` decimal(14,3) NOT NULL,
  `movement_date` date NOT NULL,
  `ref_type` varchar(255) DEFAULT NULL,
  `ref_id` bigint(20) unsigned DEFAULT NULL,
  `price_per_kg` decimal(18,6) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `yarn_stock_movements_yarn_id_foreign` (`yarn_id`),
  KEY `yarn_stock_movements_ref_type_ref_id_index` (`ref_type`,`ref_id`),
  KEY `yarn_stock_movements_created_by_foreign` (`created_by`),
  KEY `yarn_stock_movements_updated_by_foreign` (`updated_by`),
  KEY `yarn_stock_movements_movement_date_index` (`movement_date`),
  CONSTRAINT `yarn_stock_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `yarn_stock_movements_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `yarn_stock_movements_yarn_id_foreign` FOREIGN KEY (`yarn_id`) REFERENCES `yarns` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yarn_stock_movements`
--

LOCK TABLES `yarn_stock_movements` WRITE;
/*!40000 ALTER TABLE `yarn_stock_movements` DISABLE KEYS */;
INSERT INTO `yarn_stock_movements` VALUES (1,3,'purchase',453.592,'2026-11-06',NULL,NULL,429.901411,'Opening purchase','2026-11-06 12:42:33','2026-11-06 12:42:33',NULL,1,1),(2,4,'purchase',544.311,'2026-11-06',NULL,NULL,429.901411,'Opening purchase','2026-11-06 12:44:27','2026-11-06 12:44:27',NULL,1,1),(3,4,'knitting_issue',-453.592,'2026-11-06','App\\Models\\KnittingJob',1,NULL,NULL,'2026-11-06 12:45:37','2026-11-06 12:45:37',NULL,1,1),(4,5,'purchase',1000.000,'2026-05-01',NULL,NULL,NULL,NULL,'2026-11-06 13:33:20','2026-11-06 13:33:20',NULL,NULL,NULL),(5,5,'knitting_issue',-100.000,'2026-11-06','App\\Models\\KnittingJob',2,NULL,NULL,'2026-11-06 13:33:20','2026-11-06 13:33:20',NULL,NULL,NULL),(6,5,'reversal',100.000,'2026-11-06','App\\Models\\KnittingJob',2,NULL,'Reconciled previous knitting allocation','2026-11-06 14:15:10','2026-11-06 14:15:10',NULL,1,1);
/*!40000 ALTER TABLE `yarn_stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yarns`
--

DROP TABLE IF EXISTS `yarns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yarns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `yarn_type` varchar(255) NOT NULL,
  `country_of_origin` varchar(255) NOT NULL,
  `price_per_kg` decimal(18,6) NOT NULL,
  `price_unit` varchar(2) NOT NULL,
  `bag_size_kg` decimal(18,8) DEFAULT NULL,
  `bag_size_unit` varchar(2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `yarns_created_by_foreign` (`created_by`),
  KEY `yarns_updated_by_foreign` (`updated_by`),
  KEY `yarns_name_index` (`name`),
  CONSTRAINT `yarns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `yarns_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yarns`
--

LOCK TABLES `yarns` WRITE;
/*!40000 ALTER TABLE `yarns` DISABLE KEYS */;
INSERT INTO `yarns` VALUES (1,'Sudan','34 Single','IND',440.924524,'lb',45.35923700,'lb','2026-11-01 06:18:17','2026-11-06 12:40:29','2026-11-06 12:40:29',1,1),(2,'Cotton','36 Single','IND',440.924524,'lb',45.35923700,'lb','2026-11-01 09:07:21','2026-11-06 12:40:27','2026-11-06 12:40:27',1,1),(3,'Sudan','34 Single','IND',429.901411,'lb',45.35923700,'lb','2026-11-06 12:42:33','2026-11-06 12:44:11','2026-11-06 12:44:11',1,1),(4,'Sudan','34 Single','IND',429.901411,'lb',45.35923700,'lb','2026-11-06 12:44:27','2026-11-06 12:44:27',NULL,1,1),(5,'perferendis','34 Single','India',4.409245,'lb',45.35923700,'lb','2026-11-06 13:33:20','2026-11-06 14:15:16','2026-11-06 14:15:16',NULL,1);
/*!40000 ALTER TABLE `yarns` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-12 13:27:08
