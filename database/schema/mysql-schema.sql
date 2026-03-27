/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `abilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `abilities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `abilities_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `advisory_memberships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `advisory_memberships` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned DEFAULT NULL,
  `advisor_name` varchar(255) NOT NULL,
  `license_number` varchar(50) NOT NULL COMMENT 'Nº colegiado / licencia del asesor',
  `specialty` enum('phytosanitary','agronomy','oenology','sustainability','other') NOT NULL DEFAULT 'phytosanitary',
  `company_name` varchar(255) DEFAULT NULL COMMENT 'Empresa/asesoría',
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `advisory_memberships_campaign_id_foreign` (`campaign_id`),
  KEY `advisory_memberships_viticulturist_id_active_index` (`viticulturist_id`,`active`),
  CONSTRAINT `advisory_memberships_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `advisory_memberships_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `agri_insurances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agri_insurances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `policy_number` varchar(255) DEFAULT NULL,
  `insurance_company` varchar(255) NOT NULL,
  `coverage_type` enum('frost','hail','drought','flood','fire','pest','comprehensive','other') NOT NULL DEFAULT 'comprehensive',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `insured_amount` decimal(12,2) DEFAULT NULL,
  `premium` decimal(10,2) DEFAULT NULL,
  `subsidy_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('active','expired','cancelled','pending') NOT NULL DEFAULT 'pending',
  `agent_name` varchar(255) DEFAULT NULL,
  `agent_phone` varchar(255) DEFAULT NULL,
  `covered_plots` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agri_insurances_viticulturist_id_status_index` (`viticulturist_id`,`status`),
  KEY `agri_insurances_end_date_index` (`end_date`),
  CONSTRAINT `agri_insurances_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `agricultural_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agricultural_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plot_id` bigint(20) unsigned DEFAULT NULL,
  `plot_planting_id` bigint(20) unsigned DEFAULT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `phenological_stage` varchar(50) DEFAULT NULL COMMENT 'Estadio fenológico del cultivo (brotación, floración, envero, maduración, etc.)',
  `activity_date` date NOT NULL,
  `crew_id` bigint(20) unsigned DEFAULT NULL,
  `crew_member_id` bigint(20) unsigned DEFAULT NULL,
  `machinery_id` bigint(20) unsigned DEFAULT NULL,
  `weather_conditions` varchar(255) DEFAULT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `locked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `locked_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agricultural_activities_crew_id_foreign` (`crew_id`),
  KEY `agricultural_activities_plot_id_index` (`plot_id`),
  KEY `agricultural_activities_viticulturist_id_index` (`viticulturist_id`),
  KEY `agricultural_activities_activity_type_index` (`activity_type`),
  KEY `agricultural_activities_activity_date_index` (`activity_date`),
  KEY `agricultural_activities_campaign_id_index` (`campaign_id`),
  KEY `idx_aa_plot` (`plot_id`),
  KEY `idx_aa_type` (`activity_type`),
  KEY `idx_aa_date` (`activity_date`),
  KEY `idx_aa_plot_date` (`plot_id`,`activity_date`),
  KEY `agri_crew_member_idx` (`crew_member_id`),
  KEY `agricultural_activities_plot_planting_id_index` (`plot_planting_id`),
  KEY `agricultural_activities_locked_by_foreign` (`locked_by`),
  KEY `agricultural_activities_is_locked_index` (`is_locked`),
  KEY `idx_activities_vit_date` (`viticulturist_id`,`activity_date`),
  KEY `idx_activities_vit_created` (`viticulturist_id`,`created_at`),
  KEY `activities_plot_campaign_idx` (`plot_id`,`campaign_id`),
  KEY `activities_campaign_type_idx` (`campaign_id`,`activity_type`),
  KEY `activities_date_type_idx` (`activity_date`,`activity_type`),
  KEY `agricultural_activities_machinery_id_foreign` (`machinery_id`),
  CONSTRAINT `agricultural_activities_crew_id_foreign` FOREIGN KEY (`crew_id`) REFERENCES `crews` (`id`) ON DELETE SET NULL,
  CONSTRAINT `agricultural_activities_crew_member_id_foreign` FOREIGN KEY (`crew_member_id`) REFERENCES `crew_members` (`id`) ON DELETE SET NULL,
  CONSTRAINT `agricultural_activities_locked_by_foreign` FOREIGN KEY (`locked_by`) REFERENCES `users` (`id`),
  CONSTRAINT `agricultural_activities_machinery_id_foreign` FOREIGN KEY (`machinery_id`) REFERENCES `machinery` (`id`) ON DELETE SET NULL,
  CONSTRAINT `agricultural_activities_plot_id_foreign` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`),
  CONSTRAINT `agricultural_activities_plot_planting_id_foreign` FOREIGN KEY (`plot_planting_id`) REFERENCES `plot_plantings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agricultural_activities_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `agricultural_activity_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agricultural_activity_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `action` varchar(50) NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `agricultural_activity_audit_logs_activity_id_created_at_index` (`activity_id`,`created_at`),
  KEY `agricultural_activity_audit_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `agricultural_activity_audit_logs_action_index` (`action`),
  CONSTRAINT `agricultural_activity_audit_logs_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `agricultural_activities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agricultural_activity_audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `auditable_type` varchar(255) NOT NULL,
  `auditable_id` bigint(20) unsigned NOT NULL,
  `event` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  KEY `audit_logs_user_id_index` (`user_id`),
  KEY `audit_logs_event_index` (`event`),
  KEY `audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `autonomous_communities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `autonomous_communities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `autonomous_communities_code_unique` (`code`),
  KEY `autonomous_communities_code_index` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bottling_authorizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bottling_authorizations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `authorization_number` varchar(100) NOT NULL,
  `authorization_type` varchar(50) NOT NULL DEFAULT 'standard',
  `wine_id` bigint(20) unsigned DEFAULT NULL,
  `authorized_volume_liters` decimal(12,2) DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `issuing_authority` varchar(200) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `conditions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bottling_authorizations_user_id_foreign` (`user_id`),
  KEY `bottling_authorizations_wine_id_foreign` (`wine_id`),
  CONSTRAINT `bottling_authorizations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bottling_authorizations_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `campaign_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaign_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Nombre descriptivo del documento',
  `document_type` enum('invoice','certificate','lab_report','authorization','map','analysis','other') NOT NULL DEFAULT 'other',
  `file_path` varchar(255) NOT NULL COMMENT 'Ruta del archivo en storage',
  `original_filename` varchar(255) DEFAULT NULL COMMENT 'Nombre original del archivo',
  `mime_type` varchar(50) DEFAULT NULL,
  `file_size_kb` int(11) DEFAULT NULL COMMENT 'Tamaño en KB',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_documents_viticulturist_id_foreign` (`viticulturist_id`),
  KEY `campaign_documents_campaign_id_index` (`campaign_id`),
  CONSTRAINT `campaign_documents_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `campaign_documents_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaigns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `year` int(11) NOT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `mid_validation_signed` tinyint(1) NOT NULL DEFAULT 0,
  `mid_validation_date` datetime DEFAULT NULL,
  `mid_validation_user_id` bigint(20) unsigned DEFAULT NULL,
  `final_validation_signed` tinyint(1) NOT NULL DEFAULT 0,
  `final_validation_date` datetime DEFAULT NULL,
  `final_validation_user_id` bigint(20) unsigned DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL COMMENT 'Cuando se cierra definitivamente la campaña — bloquea edición',
  `pdf_path` varchar(255) DEFAULT NULL COMMENT 'Ruta del PDF generado del cuaderno completo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaigns_viticulturist_id_index` (`viticulturist_id`),
  KEY `campaigns_year_index` (`year`),
  KEY `campaigns_active_index` (`active`),
  KEY `campaigns_user_year_idx` (`viticulturist_id`,`year`),
  KEY `campaigns_mid_validation_user_id_foreign` (`mid_validation_user_id`),
  KEY `campaigns_final_validation_user_id_foreign` (`final_validation_user_id`),
  CONSTRAINT `campaigns_final_validation_user_id_foreign` FOREIGN KEY (`final_validation_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `campaigns_mid_validation_user_id_foreign` FOREIGN KEY (`mid_validation_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `campaigns_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cellar_operations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cellar_operations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `operation_type` varchar(50) NOT NULL,
  `operation_date` date NOT NULL,
  `source_container_id` bigint(20) unsigned DEFAULT NULL,
  `target_container_id` bigint(20) unsigned DEFAULT NULL,
  `volume_liters` decimal(12,2) DEFAULT NULL,
  `responsible_person` varchar(150) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'planned',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cellar_operations_user_id_foreign` (`user_id`),
  KEY `cellar_operations_source_container_id_foreign` (`source_container_id`),
  KEY `cellar_operations_target_container_id_foreign` (`target_container_id`),
  CONSTRAINT `cellar_operations_source_container_id_foreign` FOREIGN KEY (`source_container_id`) REFERENCES `containers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cellar_operations_target_container_id_foreign` FOREIGN KEY (`target_container_id`) REFERENCES `containers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cellar_operations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `certification_type` enum('ecologico','produccion_integrada','globalgap','rainforest','denominacion_origen','indicacion_geografica','otro') NOT NULL,
  `certifying_body` varchar(255) NOT NULL,
  `certificate_number` varchar(100) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `scope` varchar(500) DEFAULT NULL,
  `audit_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `certifications_viticulturist_id_foreign` (`viticulturist_id`),
  CONSTRAINT `certifications_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `client_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL COMMENT 'Cargo/Posición',
  `address` text DEFAULT NULL,
  `autonomous_community_id` bigint(20) unsigned DEFAULT NULL,
  `province_id` bigint(20) unsigned DEFAULT NULL,
  `municipality_id` bigint(20) unsigned DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_addresses_autonomous_community_id_foreign` (`autonomous_community_id`),
  KEY `client_addresses_province_id_foreign` (`province_id`),
  KEY `client_addresses_municipality_id_foreign` (`municipality_id`),
  KEY `client_addresses_client_id_index` (`client_id`),
  KEY `client_addresses_is_default_index` (`is_default`),
  CONSTRAINT `client_addresses_autonomous_community_id_foreign` FOREIGN KEY (`autonomous_community_id`) REFERENCES `autonomous_communities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `client_addresses_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `client_addresses_municipality_id_foreign` FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `client_addresses_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `client_type` enum('individual','company') NOT NULL DEFAULT 'individual',
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_document` varchar(255) DEFAULT NULL COMMENT 'CIF/NIF empresa',
  `particular_document` varchar(255) DEFAULT NULL COMMENT 'DNI/NIE particular',
  `default_discount` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Descuento por defecto %',
  `payment_method` enum('cash','transfer','check','other') DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL COMMENT 'Número de cuenta para transferencias',
  `has_cae` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Tiene CAE (Canarias)',
  `cae_number` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `balance` decimal(20,2) NOT NULL DEFAULT 0.00 COMMENT 'Saldo pendiente',
  `avatar` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clients_user_id_index` (`user_id`),
  KEY `clients_client_type_index` (`client_type`),
  KEY `clients_active_index` (`active`),
  CONSTRAINT `clients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commercial_authorizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commercial_authorizations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `exploitation_id` bigint(20) unsigned DEFAULT NULL,
  `authorization_type` enum('do_registration','organic_certification','planting_right','replanting_right','integrated_production','other') NOT NULL,
  `authorization_code` varchar(100) DEFAULT NULL COMMENT 'Código o número de expediente',
  `description` varchar(255) DEFAULT NULL,
  `issuing_body` varchar(255) DEFAULT NULL COMMENT 'Organismo emisor (CCAA, MAPA, Consejo Regulador...)',
  `issue_date` date NOT NULL COMMENT 'Fecha de concesión',
  `expiry_date` date DEFAULT NULL COMMENT 'Fecha de caducidad (null = indefinido)',
  `document_file` varchar(255) DEFAULT NULL COMMENT 'Ruta del documento PDF',
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commercial_authorizations_exploitation_id_foreign` (`exploitation_id`),
  KEY `commercial_authorizations_viticulturist_id_active_index` (`viticulturist_id`,`active`),
  KEY `commercial_authorizations_expiry_date_index` (`expiry_date`),
  CONSTRAINT `commercial_authorizations_exploitation_id_foreign` FOREIGN KEY (`exploitation_id`) REFERENCES `exploitations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `commercial_authorizations_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `container_additive_supplies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `container_additive_supplies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `container_id` bigint(20) unsigned NOT NULL,
  `container_current_state_id` bigint(20) unsigned DEFAULT NULL,
  `winery_supply_id` bigint(20) unsigned DEFAULT NULL,
  `additive_name` varchar(255) DEFAULT NULL COMMENT 'Nombre libre si no está en el catálogo',
  `quantity` decimal(12,3) NOT NULL,
  `unit_of_measurement_id` bigint(20) unsigned DEFAULT NULL,
  `additive_date` date NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `container_additive_supplies_winery_supply_id_foreign` (`winery_supply_id`),
  KEY `container_additive_supplies_unit_of_measurement_id_foreign` (`unit_of_measurement_id`),
  KEY `container_additive_supplies_created_by_foreign` (`created_by`),
  KEY `container_additive_supplies_container_id_additive_date_index` (`container_id`,`additive_date`),
  KEY `container_additive_supplies_container_current_state_id_index` (`container_current_state_id`),
  CONSTRAINT `container_additive_supplies_container_current_state_id_foreign` FOREIGN KEY (`container_current_state_id`) REFERENCES `container_current_states` (`id`) ON DELETE SET NULL,
  CONSTRAINT `container_additive_supplies_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `container_additive_supplies_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `container_additive_supplies_unit_of_measurement_id_foreign` FOREIGN KEY (`unit_of_measurement_id`) REFERENCES `units_of_measurement` (`id`) ON DELETE SET NULL,
  CONSTRAINT `container_additive_supplies_winery_supply_id_foreign` FOREIGN KEY (`winery_supply_id`) REFERENCES `winery_supplies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `container_current_states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `container_current_states` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `container_id` bigint(20) unsigned NOT NULL,
  `wine_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID de vino (tabla futura)',
  `wine_process_detail_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Proceso de vinificación',
  `harvest_id` bigint(20) unsigned DEFAULT NULL,
  `external_grape_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Uva comprada externa',
  `has_subproducts` tinyint(1) NOT NULL DEFAULT 0,
  `current_quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Cantidad actual en kg',
  `available_qty` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Cantidad disponible',
  `reserved_qty` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Cantidad reservada',
  `sold_qty` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Cantidad vendida',
  `location` varchar(255) DEFAULT NULL COMMENT 'Ubicación del contenedor',
  `last_movement_at` timestamp NULL DEFAULT NULL,
  `last_movement_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_container_harvest` (`container_id`,`harvest_id`),
  KEY `container_current_states_container_id_index` (`container_id`),
  KEY `container_current_states_harvest_id_index` (`harvest_id`),
  KEY `container_current_states_wine_id_index` (`wine_id`),
  KEY `container_current_states_last_movement_by_foreign` (`last_movement_by`),
  CONSTRAINT `container_current_states_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `container_current_states_harvest_id_foreign` FOREIGN KEY (`harvest_id`) REFERENCES `harvests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `container_current_states_last_movement_by_foreign` FOREIGN KEY (`last_movement_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `container_current_states_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `container_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `container_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `container_id` bigint(20) unsigned NOT NULL,
  `wine_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID de vino (tabla futura)',
  `wine_process_detail_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Proceso de vinificación',
  `harvest_id` bigint(20) unsigned DEFAULT NULL,
  `external_grape_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Uva comprada externa',
  `has_subproducts` tinyint(1) NOT NULL DEFAULT 0,
  `field_activity_id` bigint(20) unsigned DEFAULT NULL,
  `operation_type` enum('fill','empty','transfer','sale','adjustment','maintenance','wine_transfer_out','wine_transfer_in','wine_transfer_revert_out','wine_transfer_revert_in','wine_loss','wine_loss_revert','bottling','bottling_revert') NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL COMMENT 'Cantidad en kg (+ entrada, - salida)',
  `start_date` datetime NOT NULL COMMENT 'Fecha inicio de la operación',
  `end_date` datetime DEFAULT NULL COMMENT 'Fecha fin de la operación',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `container_histories_field_activity_id_foreign` (`field_activity_id`),
  KEY `container_histories_container_id_index` (`container_id`),
  KEY `container_histories_harvest_id_index` (`harvest_id`),
  KEY `container_histories_wine_id_index` (`wine_id`),
  KEY `container_histories_operation_type_index` (`operation_type`),
  KEY `container_histories_start_date_index` (`start_date`),
  KEY `container_histories_created_by_index` (`created_by`),
  CONSTRAINT `container_histories_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `container_histories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `container_histories_field_activity_id_foreign` FOREIGN KEY (`field_activity_id`) REFERENCES `agricultural_activities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `container_histories_harvest_id_foreign` FOREIGN KEY (`harvest_id`) REFERENCES `harvests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `container_histories_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `container_maintenance_supplies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `container_maintenance_supplies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `container_maintenance_id` bigint(20) unsigned NOT NULL,
  `winery_supply_id` bigint(20) unsigned DEFAULT NULL,
  `supply_name` varchar(255) DEFAULT NULL COMMENT 'Nombre libre si no está en el catálogo',
  `quantity_used` decimal(12,3) NOT NULL,
  `unit_of_measurement_id` bigint(20) unsigned DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `container_maintenance_supplies_winery_supply_id_foreign` (`winery_supply_id`),
  KEY `container_maintenance_supplies_unit_of_measurement_id_foreign` (`unit_of_measurement_id`),
  KEY `container_maintenance_supplies_container_maintenance_id_index` (`container_maintenance_id`),
  CONSTRAINT `container_maintenance_supplies_container_maintenance_id_foreign` FOREIGN KEY (`container_maintenance_id`) REFERENCES `container_maintenances` (`id`) ON DELETE CASCADE,
  CONSTRAINT `container_maintenance_supplies_unit_of_measurement_id_foreign` FOREIGN KEY (`unit_of_measurement_id`) REFERENCES `units_of_measurement` (`id`) ON DELETE SET NULL,
  CONSTRAINT `container_maintenance_supplies_winery_supply_id_foreign` FOREIGN KEY (`winery_supply_id`) REFERENCES `winery_supplies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `container_maintenance_wastes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `container_maintenance_wastes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `container_maintenance_id` bigint(20) unsigned NOT NULL,
  `container_waste_type_id` bigint(20) unsigned DEFAULT NULL,
  `custom_waste_type` varchar(255) DEFAULT NULL COMMENT 'Descripción libre cuando tipo = Otro',
  `waste_date` date NOT NULL,
  `quantity` decimal(12,3) DEFAULT NULL,
  `unit_of_measurement_id` bigint(20) unsigned DEFAULT NULL,
  `disposal_method` varchar(255) DEFAULT NULL COMMENT 'Ej: gestor autorizado, vertedero, compostaje',
  `cost` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `container_maintenance_wastes_container_waste_type_id_foreign` (`container_waste_type_id`),
  KEY `container_maintenance_wastes_unit_of_measurement_id_foreign` (`unit_of_measurement_id`),
  KEY `container_maintenance_wastes_container_maintenance_id_index` (`container_maintenance_id`),
  CONSTRAINT `container_maintenance_wastes_container_maintenance_id_foreign` FOREIGN KEY (`container_maintenance_id`) REFERENCES `container_maintenances` (`id`) ON DELETE CASCADE,
  CONSTRAINT `container_maintenance_wastes_container_waste_type_id_foreign` FOREIGN KEY (`container_waste_type_id`) REFERENCES `container_waste_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `container_maintenance_wastes_unit_of_measurement_id_foreign` FOREIGN KEY (`unit_of_measurement_id`) REFERENCES `units_of_measurement` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `container_maintenances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `container_maintenances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `container_id` bigint(20) unsigned NOT NULL,
  `maintenance_type` enum('cleaning','sulfuring','inspection','repair','tartrate_removal','other') NOT NULL DEFAULT 'cleaning',
  `maintenance_name` varchar(255) NOT NULL,
  `scheduled_date` date NOT NULL,
  `performed_date` date DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `cost` decimal(10,2) DEFAULT NULL,
  `performed_by` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `container_maintenances_container_id_status_index` (`container_id`,`status`),
  KEY `container_maintenances_container_id_scheduled_date_index` (`container_id`,`scheduled_date`),
  CONSTRAINT `container_maintenances_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `container_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `container_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `container_materials_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `container_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `container_rooms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL COMMENT 'Número máximo de contenedores',
  `temperature` decimal(5,2) DEFAULT NULL COMMENT 'Temperatura en °C',
  `humidity` decimal(5,2) DEFAULT NULL COMMENT 'Humedad relativa %',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `container_rooms_user_id_index` (`user_id`),
  CONSTRAINT `container_rooms_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `container_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `container_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `container_types_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `container_waste_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `container_waste_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `containers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `containers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `container_room_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID de la sala (tabla futura)',
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`photos`)),
  `thumbnail_img` varchar(255) DEFAULT NULL,
  `capacity` decimal(10,2) NOT NULL COMMENT 'Capacidad total en kg',
  `unit` enum('kg','litros') NOT NULL DEFAULT 'kg' COMMENT 'Unidad de la capacidad: kg para uva/mosto, litros para vino',
  `used_capacity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Capacidad usada en kg',
  `wine_volume_liters` decimal(12,3) NOT NULL DEFAULT 0.000 COMMENT 'Litros de vino elaborado actualmente en este contenedor',
  `quantity` int(11) NOT NULL DEFAULT 1 COMMENT 'Cantidad de unidades',
  `serial_number` varchar(255) DEFAULT NULL COMMENT 'Número de serie',
  `unit_of_measurement_id` bigint(20) unsigned NOT NULL DEFAULT 1 COMMENT 'Unidad de medida (kg, L, etc.)',
  `type_id` bigint(20) unsigned NOT NULL DEFAULT 1 COMMENT 'Tipo de contenedor (barril, tanque, etc.)',
  `material_id` bigint(20) unsigned NOT NULL DEFAULT 1 COMMENT 'Material (acero, roble, etc.)',
  `oak_type` varchar(255) DEFAULT NULL COMMENT 'Tipo de roble',
  `toast_type` varchar(255) DEFAULT NULL COMMENT 'Tipo de tostado',
  `purchase_date` date DEFAULT NULL COMMENT 'Fecha de compra',
  `next_maintenance_date` datetime DEFAULT NULL COMMENT 'Próximo mantenimiento',
  `supplier_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del proveedor',
  `x_position` int(11) DEFAULT NULL COMMENT 'Posición X en almacén',
  `y_position` int(11) DEFAULT NULL COMMENT 'Posición Y en almacén',
  `archived` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Archivado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `containers_user_id_index` (`user_id`),
  KEY `containers_capacity_index` (`capacity`),
  KEY `containers_used_capacity_index` (`used_capacity`),
  KEY `containers_archived_index` (`archived`),
  KEY `containers_user_id_archived_index` (`user_id`,`archived`),
  CONSTRAINT `containers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `crew_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crew_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `crew_id` bigint(20) unsigned DEFAULT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  `phytosanitary_license_number` varchar(255) DEFAULT NULL,
  `license_expiry_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `crew_members_assigned_by_foreign` (`assigned_by`),
  KEY `crew_members_crew_id_index` (`crew_id`),
  KEY `crew_members_viticulturist_id_index` (`viticulturist_id`),
  KEY `idx_viticulturist_crew` (`viticulturist_id`,`crew_id`),
  CONSTRAINT `crew_members_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `crew_members_crew_id_foreign` FOREIGN KEY (`crew_id`) REFERENCES `crews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crew_members_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `crews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `winery_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `crews_viticulturist_id_index` (`viticulturist_id`),
  KEY `crews_winery_id_index` (`winery_id`),
  CONSTRAINT `crews_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `crews_winery_id_foreign` FOREIGN KEY (`winery_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cue_exports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cue_exports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `exploitation_id` bigint(20) unsigned NOT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `campaign_year` int(11) NOT NULL,
  `period_type` enum('quarterly','annual') NOT NULL DEFAULT 'annual',
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `status` enum('draft','generated','sent','accepted','rejected') NOT NULL DEFAULT 'draft',
  `payload_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON del payload enviado al MAPA' CHECK (json_valid(`payload_json`)),
  `response_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Respuesta del MAPA' CHECK (json_valid(`response_json`)),
  `generated_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL COMMENT 'Ruta del fichero XML/JSON generado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cue_exports_viticulturist_id_foreign` (`viticulturist_id`),
  KEY `cue_exports_exploitation_id_campaign_year_index` (`exploitation_id`,`campaign_year`),
  KEY `cue_exports_status_index` (`status`),
  CONSTRAINT `cue_exports_exploitation_id_foreign` FOREIGN KEY (`exploitation_id`) REFERENCES `exploitations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cue_exports_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cultural_works`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cultural_works` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) unsigned NOT NULL,
  `work_type` varchar(100) DEFAULT NULL,
  `pruning_type` varchar(50) DEFAULT NULL COMMENT 'Tipo de poda: guyot, doble_guyot, vaso, cordon, other',
  `productive_buds_per_hectare` int(11) DEFAULT NULL COMMENT 'Yemas productivas/ha resultantes de la poda',
  `residue_management` varchar(30) DEFAULT NULL COMMENT 'Gestión del ramón de poda — BCAM 6 PAC: triturado_incorporado, triturado_superficie, retirado, quemado, otro',
  `defoliation_face` varchar(10) DEFAULT NULL,
  `topping_height_cm` smallint(5) unsigned DEFAULT NULL,
  `hours_worked` decimal(5,2) DEFAULT NULL,
  `workers_count` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cultural_works_activity_id_foreign` (`activity_id`),
  CONSTRAINT `cultural_works_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `agricultural_activities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `digital_signatures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `digital_signatures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `signature_password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `digital_signatures_user_id_unique` (`user_id`),
  KEY `digital_signatures_user_id_index` (`user_id`),
  CONSTRAINT `digital_signatures_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `do_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `do_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supervisor_id` bigint(20) unsigned NOT NULL,
  `type` enum('pliego','reglamento') NOT NULL,
  `title` varchar(255) NOT NULL,
  `version` varchar(30) DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `content` text DEFAULT NULL,
  `status` enum('draft','active','archived') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `do_documents_supervisor_id_foreign` (`supervisor_id`),
  CONSTRAINT `do_documents_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `do_inspections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `do_inspections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supervisor_id` bigint(20) unsigned NOT NULL,
  `subject_type` enum('winery','viticulturist') NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `inspection_date` date NOT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `result` enum('compliant','non_compliant','pending') DEFAULT NULL,
  `findings` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `do_inspections_subject_id_foreign` (`subject_id`),
  KEY `do_inspections_supervisor_id_status_index` (`supervisor_id`,`status`),
  KEY `do_inspections_supervisor_id_inspection_date_index` (`supervisor_id`,`inspection_date`),
  CONSTRAINT `do_inspections_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `do_inspections_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `do_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `do_labels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supervisor_id` bigint(20) unsigned NOT NULL,
  `winery_id` bigint(20) unsigned NOT NULL,
  `vintage` year(4) NOT NULL,
  `batch_number` varchar(255) DEFAULT NULL,
  `quantity_requested` int(10) unsigned NOT NULL DEFAULT 0,
  `quantity_issued` int(10) unsigned NOT NULL DEFAULT 0,
  `quantity_stock` int(10) unsigned NOT NULL DEFAULT 0,
  `status` enum('pending','approved','issued','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `issued_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `do_labels_issued_by_foreign` (`issued_by`),
  KEY `do_labels_supervisor_id_status_index` (`supervisor_id`,`status`),
  KEY `do_labels_supervisor_id_vintage_index` (`supervisor_id`,`vintage`),
  KEY `do_labels_winery_id_index` (`winery_id`),
  CONSTRAINT `do_labels_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `do_labels_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `do_labels_winery_id_foreign` FOREIGN KEY (`winery_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `do_qualifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `do_qualifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supervisor_id` bigint(20) unsigned NOT NULL,
  `winery_id` bigint(20) unsigned NOT NULL,
  `vintage` year(4) NOT NULL,
  `wine_name` varchar(255) NOT NULL,
  `color` enum('tinto','blanco','rosado','espumoso','dulce','otro') DEFAULT NULL,
  `alcohol_percentage` decimal(4,2) DEFAULT NULL,
  `brix_degree` decimal(5,2) DEFAULT NULL,
  `acidity_level` decimal(5,2) DEFAULT NULL,
  `ph_level` decimal(4,2) DEFAULT NULL,
  `visual_score` tinyint(3) unsigned DEFAULT NULL,
  `aroma_score` tinyint(3) unsigned DEFAULT NULL,
  `taste_score` tinyint(3) unsigned DEFAULT NULL,
  `overall_score` tinyint(3) unsigned DEFAULT NULL,
  `result` enum('qualified','disqualified','pending') NOT NULL DEFAULT 'pending',
  `tasting_notes` text DEFAULT NULL,
  `qualification_date` date DEFAULT NULL,
  `qualified_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `do_qualifications_winery_id_foreign` (`winery_id`),
  KEY `do_qualifications_qualified_by_foreign` (`qualified_by`),
  KEY `do_qualifications_supervisor_id_result_index` (`supervisor_id`,`result`),
  KEY `do_qualifications_supervisor_id_vintage_index` (`supervisor_id`,`vintage`),
  CONSTRAINT `do_qualifications_qualified_by_foreign` FOREIGN KEY (`qualified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `do_qualifications_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `do_qualifications_winery_id_foreign` FOREIGN KEY (`winery_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eco_certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eco_certifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `certification_type` varchar(50) NOT NULL DEFAULT 'organic',
  `certifying_body` varchar(200) DEFAULT NULL,
  `certificate_number` varchar(100) DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eco_certifications_user_id_foreign` (`user_id`),
  CONSTRAINT `eco_certifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `energy_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `energy_usages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `activity_id` bigint(20) unsigned DEFAULT NULL,
  `machinery_id` bigint(20) unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `energy_type` enum('diesel','gasoline','electricity','lpg','natural_gas','water_pump','other') NOT NULL COMMENT 'Tipo de energía consumida',
  `unit` enum('liters','kwh','m3','kg') NOT NULL DEFAULT 'liters',
  `quantity` decimal(10,3) NOT NULL COMMENT 'Cantidad consumida',
  `cost_per_unit` decimal(10,4) DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `co2_kg_equivalent` decimal(10,3) DEFAULT NULL COMMENT 'kg CO₂ equivalente calculado',
  `usage_description` varchar(255) DEFAULT NULL COMMENT 'Descripción de la operación',
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `energy_usages_activity_id_foreign` (`activity_id`),
  KEY `energy_usages_machinery_id_foreign` (`machinery_id`),
  KEY `energy_usages_campaign_id_date_index` (`campaign_id`,`date`),
  KEY `energy_usages_viticulturist_id_active_index` (`viticulturist_id`,`active`),
  CONSTRAINT `energy_usages_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `agricultural_activities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `energy_usages_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `energy_usages_machinery_id_foreign` FOREIGN KEY (`machinery_id`) REFERENCES `machinery` (`id`) ON DELETE SET NULL,
  CONSTRAINT `energy_usages_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estimated_yields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estimated_yields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plot_planting_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `estimated_by` bigint(20) unsigned DEFAULT NULL,
  `estimated_yield_per_hectare` decimal(10,3) DEFAULT NULL,
  `estimated_total_yield` decimal(10,3) DEFAULT NULL,
  `estimation_date` date NOT NULL COMMENT 'Fecha de la estimación',
  `estimation_method` enum('visual','sampling','historical','satellite','other') NOT NULL DEFAULT 'visual' COMMENT 'Método de estimación',
  `status` enum('draft','confirmed','archived') NOT NULL DEFAULT 'draft' COMMENT 'Estado de la estimación',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `actual_yield_per_hectare` decimal(10,3) DEFAULT NULL,
  `actual_total_yield` decimal(10,3) DEFAULT NULL,
  `variance_percentage` decimal(5,2) DEFAULT NULL COMMENT 'Diferencia porcentual entre estimado y real',
  `notes` text DEFAULT NULL COMMENT 'Notas sobre la estimación',
  `thumbs_per_vine` int(11) DEFAULT NULL COMMENT 'Yemas/brotes por cepa (dato de poda)',
  `bunches_per_plant` decimal(8,2) DEFAULT NULL COMMENT 'Racimos contados por planta (muestreo de campo)',
  `bunch_weight_grams` decimal(8,2) DEFAULT NULL COMMENT 'Peso medio del racimo en gramos (muestreo)',
  `total_plants_sampled` int(11) DEFAULT NULL COMMENT 'Número de plantas muestreadas',
  `sampling_area_pct` decimal(5,2) DEFAULT NULL COMMENT 'Porcentaje del viñedo muestreado (0-100)',
  `health_percentage` decimal(5,2) DEFAULT NULL COMMENT 'Porcentaje de racimos sanos (0-100)',
  `health_status` varchar(255) DEFAULT NULL,
  `other_wineries` tinyint(1) NOT NULL DEFAULT 0,
  `potential_alcohol` decimal(5,2) DEFAULT NULL COMMENT 'Grado alcohólico probable (%)',
  `vintage` year(4) DEFAULT NULL COMMENT 'Añada estimada',
  `auto_calculated_yield` decimal(10,2) DEFAULT NULL COMMENT 'Rendimiento calculado automáticamente desde los datos de muestreo (kg)',
  `estimation_round` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'Ronda de estimación: 1=pre-envero, 2=envero, 3=pre-vendimia, 4=revisión',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_planting_campaign_round` (`plot_planting_id`,`campaign_id`,`estimation_round`),
  KEY `estimated_yields_campaign_id_foreign` (`campaign_id`),
  KEY `estimated_yields_estimated_by_foreign` (`estimated_by`),
  KEY `estimated_yields_plot_planting_id_campaign_id_index` (`plot_planting_id`,`campaign_id`),
  KEY `estimated_yields_estimation_date_index` (`estimation_date`),
  KEY `estimated_yields_status_index` (`status`),
  CONSTRAINT `estimated_yields_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `estimated_yields_estimated_by_foreign` FOREIGN KEY (`estimated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `estimated_yields_plot_planting_id_foreign` FOREIGN KEY (`plot_planting_id`) REFERENCES `plot_plantings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exploitation_dgcs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exploitation_dgcs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `exploitation_id` bigint(20) unsigned NOT NULL,
  `plot_id` bigint(20) unsigned NOT NULL,
  `plot_planting_id` bigint(20) unsigned DEFAULT NULL,
  `dgc_code` varchar(30) DEFAULT NULL COMMENT 'Código DGC declarado',
  `dgc_area_ha` decimal(10,4) NOT NULL COMMENT 'Superficie declarada en ha',
  `system_of_exploitation` varchar(50) DEFAULT NULL COMMENT 'Secano/Regadío',
  `system_of_cultivation` varchar(50) DEFAULT NULL COMMENT 'Convencional/Ecológico/Integrado',
  `irrigation_system_type` varchar(50) DEFAULT NULL,
  `planting_year` int(11) DEFAULT NULL,
  `geometry` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'GeoJSON de la geometría' CHECK (json_valid(`geometry`)),
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exploitation_dgcs_plot_planting_id_foreign` (`plot_planting_id`),
  KEY `exploitation_dgcs_exploitation_id_active_index` (`exploitation_id`,`active`),
  KEY `exploitation_dgcs_plot_id_index` (`plot_id`),
  CONSTRAINT `exploitation_dgcs_exploitation_id_foreign` FOREIGN KEY (`exploitation_id`) REFERENCES `exploitations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exploitation_dgcs_plot_id_foreign` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exploitation_dgcs_plot_planting_id_foreign` FOREIGN KEY (`plot_planting_id`) REFERENCES `plot_plantings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exploitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exploitations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `exploitation_name` varchar(255) NOT NULL,
  `rea_code` varchar(30) DEFAULT NULL COMMENT 'Código REA - Registro Explotaciones Agrarias',
  `siex_exploitation_id` varchar(50) DEFAULT NULL COMMENT 'ID SIEX del MAPA',
  `exploitation_type` varchar(50) DEFAULT NULL,
  `holder_name` varchar(255) NOT NULL,
  `holder_nif` varchar(15) NOT NULL,
  `representative_name` varchar(255) DEFAULT NULL,
  `representative_nif` varchar(15) DEFAULT NULL,
  `is_ecological` tinyint(1) NOT NULL DEFAULT 0,
  `is_integrated_production` tinyint(1) NOT NULL DEFAULT 0,
  `is_quality_scheme` tinyint(1) NOT NULL DEFAULT 0,
  `quality_scheme_desc` varchar(255) DEFAULT NULL COMMENT 'Descripción DO/IGP/etc.',
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exploitations_viticulturist_id_active_index` (`viticulturist_id`,`active`),
  CONSTRAINT `exploitations_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `external_grapes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `external_grapes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `grape_type` enum('grapes','must','bulk_wine') NOT NULL DEFAULT 'grapes',
  `grape_variety_id` bigint(20) unsigned DEFAULT NULL,
  `color` enum('white','red','rose','other') DEFAULT NULL,
  `protection_level` varchar(255) DEFAULT NULL,
  `geographic_origin` varchar(255) DEFAULT NULL,
  `vintage_year` smallint(5) unsigned DEFAULT NULL,
  `alcohol_pct` decimal(4,2) DEFAULT NULL,
  `total_weight_kg` decimal(10,3) NOT NULL,
  `used_weight_kg` decimal(10,3) NOT NULL DEFAULT 0.000,
  `entry_date` date NOT NULL,
  `harvest_date` date DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `container_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('available','used','archived') NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `external_grapes_grape_variety_id_foreign` (`grape_variety_id`),
  KEY `external_grapes_container_id_foreign` (`container_id`),
  KEY `external_grapes_user_id_status_index` (`user_id`,`status`),
  KEY `external_grapes_user_id_vintage_year_index` (`user_id`,`vintage_year`),
  CONSTRAINT `external_grapes_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `external_grapes_grape_variety_id_foreign` FOREIGN KEY (`grape_variety_id`) REFERENCES `grape_varieties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `external_grapes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fertilization_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fertilization_plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `plan_year` smallint(6) NOT NULL,
  `nitrate_zone` tinyint(1) NOT NULL DEFAULT 0,
  `prepared_by` varchar(255) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `total_surface_ha` decimal(8,4) DEFAULT NULL,
  `total_n_kg_ha` decimal(8,3) DEFAULT NULL,
  `total_p_kg_ha` decimal(8,3) DEFAULT NULL,
  `total_k_kg_ha` decimal(8,3) DEFAULT NULL,
  `plan_lines` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`plan_lines`)),
  `status` enum('draft','active','archived') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fertilization_plans_viticulturist_id_foreign` (`viticulturist_id`),
  KEY `fertilization_plans_campaign_id_foreign` (`campaign_id`),
  CONSTRAINT `fertilization_plans_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fertilization_plans_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fertilizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fertilizations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) unsigned NOT NULL,
  `fertilizer_type` varchar(100) DEFAULT NULL,
  `fertilizer_name` varchar(255) DEFAULT NULL,
  `quantity` decimal(10,3) DEFAULT NULL,
  `npk_ratio` varchar(50) DEFAULT NULL,
  `application_method` varchar(50) DEFAULT NULL,
  `area_applied` decimal(10,3) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `nitrogen_uf` decimal(8,3) DEFAULT NULL COMMENT 'Unidades Fertilizantes N / ha',
  `phosphorus_uf` decimal(8,3) DEFAULT NULL COMMENT 'Unidades Fertilizantes P / ha',
  `potassium_uf` decimal(8,3) DEFAULT NULL COMMENT 'Unidades Fertilizantes K / ha',
  `manure_type` varchar(100) DEFAULT NULL COMMENT 'Tipo de estiércol',
  `burial_date` date DEFAULT NULL COMMENT 'Fecha de enterrado',
  `emission_reduction_method` varchar(100) DEFAULT NULL COMMENT 'Método de reducción de emisiones',
  PRIMARY KEY (`id`),
  KEY `fertilizations_activity_id_foreign` (`activity_id`),
  CONSTRAINT `fertilizations_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `agricultural_activities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `field_applicators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `field_applicators` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `ropo_number` varchar(30) NOT NULL COMMENT 'Número ROPO (Registro Oficial de Productores y Operadores) — obligatorio para aplicar fitosanitarios',
  `ropo_category` enum('basic','qualified','fumigator','pilot') NOT NULL DEFAULT 'qualified' COMMENT 'Categoría ROPO: básico, cualificado, fumigador, piloto',
  `ropo_expiry_date` date DEFAULT NULL COMMENT 'Fecha de caducidad del carné ROPO',
  `is_advisor` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'También actúa como asesor técnico',
  `advisor_license` varchar(50) DEFAULT NULL COMMENT 'Número de colegiado/licencia del asesor',
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `field_applicators_campaign_id_foreign` (`campaign_id`),
  KEY `field_applicators_viticulturist_id_active_index` (`viticulturist_id`,`active`),
  KEY `field_applicators_viticulturist_id_campaign_id_index` (`viticulturist_id`,`campaign_id`),
  CONSTRAINT `field_applicators_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `field_applicators_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `field_equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `field_equipment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `equipment_type` enum('sprayer','spreader','irrigation','tractor','harvester','pruner','mower','other') NOT NULL DEFAULT 'sprayer',
  `registration_number` varchar(50) DEFAULT NULL COMMENT 'Matrícula, serie o número de registro del equipo',
  `purchase_date` date DEFAULT NULL,
  `last_inspection_date` date DEFAULT NULL COMMENT 'Última inspección técnica obligatoria (ITB para pulverizadores)',
  `next_inspection_date` date DEFAULT NULL COMMENT 'Próxima inspección programada',
  `inspection_entity` varchar(100) DEFAULT NULL COMMENT 'Entidad que realizó/realizará la inspección',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `field_equipment_viticulturist_id_active_index` (`viticulturist_id`,`active`),
  KEY `field_equipment_viticulturist_id_equipment_type_index` (`viticulturist_id`,`equipment_type`),
  CONSTRAINT `field_equipment_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `grape_reception_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grape_reception_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `winery_id` bigint(20) unsigned NOT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `plot_planting_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `vintage_year` smallint(6) NOT NULL,
  `total_weight_kg` decimal(12,3) NOT NULL DEFAULT 0.000,
  `designation_of_origin` varchar(255) DEFAULT NULL,
  `status` enum('open','closed','invoiced') NOT NULL DEFAULT 'open',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grb_unique_winery_planting_campaign` (`winery_id`,`plot_planting_id`,`campaign_id`),
  KEY `grape_reception_batches_plot_planting_id_foreign` (`plot_planting_id`),
  KEY `grape_reception_batches_campaign_id_foreign` (`campaign_id`),
  KEY `grape_reception_batches_winery_id_vintage_year_index` (`winery_id`,`vintage_year`),
  KEY `grape_reception_batches_viticulturist_id_vintage_year_index` (`viticulturist_id`,`vintage_year`),
  CONSTRAINT `grape_reception_batches_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grape_reception_batches_plot_planting_id_foreign` FOREIGN KEY (`plot_planting_id`) REFERENCES `plot_plantings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grape_reception_batches_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grape_reception_batches_winery_id_foreign` FOREIGN KEY (`winery_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `grape_varieties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grape_varieties` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Nombre de la variedad de uva',
  `code` varchar(10) DEFAULT NULL COMMENT 'Código de la variedad',
  `color` enum('red','white','rose') DEFAULT NULL COMMENT 'Color de la uva',
  `description` text DEFAULT NULL COMMENT 'Descripción y características',
  `active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Si está activa',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grape_varieties_name_unique` (`name`),
  UNIQUE KEY `grape_varieties_code_unique` (`code`),
  KEY `grape_varieties_active_index` (`active`),
  KEY `grape_varieties_color_index` (`color`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `harvest_byproducts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `harvest_byproducts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `byproduct_type` enum('orujo','raspon','lia','otro') NOT NULL,
  `quantity_kg` decimal(10,3) NOT NULL,
  `destination_type` enum('cooperativa','bodega','destileria','compostaje','vertedero_autorizado','otro') NOT NULL,
  `destination_name` varchar(255) NOT NULL,
  `document_reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `harvest_byproducts_viticulturist_id_foreign` (`viticulturist_id`),
  KEY `harvest_byproducts_campaign_id_foreign` (`campaign_id`),
  CONSTRAINT `harvest_byproducts_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `harvest_byproducts_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `harvest_containers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `harvest_containers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `harvest_id` bigint(20) unsigned DEFAULT NULL,
  `container_type` enum('caja','pallet','contenedor','saco','cuba','other') NOT NULL DEFAULT 'caja' COMMENT 'Tipo de contenedor',
  `container_number` varchar(255) DEFAULT NULL COMMENT 'Número o identificador del contenedor',
  `quantity` int(11) NOT NULL DEFAULT 1 COMMENT 'Cantidad de contenedores',
  `weight` decimal(10,3) DEFAULT NULL,
  `weight_per_unit` decimal(10,3) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL COMMENT 'Ubicación del contenedor (almacén, campo, etc.)',
  `status` enum('filled','in_transit','delivered','stored','empty') NOT NULL DEFAULT 'filled' COMMENT 'Estado del contenedor',
  `filled_date` date DEFAULT NULL COMMENT 'Fecha de llenado',
  `delivery_date` date DEFAULT NULL COMMENT 'Fecha de entrega',
  `notes` text DEFAULT NULL COMMENT 'Notas adicionales',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `harvest_containers_harvest_id_index` (`harvest_id`),
  KEY `harvest_containers_container_type_index` (`container_type`),
  KEY `harvest_containers_status_index` (`status`),
  CONSTRAINT `harvest_containers_harvest_id_foreign` FOREIGN KEY (`harvest_id`) REFERENCES `harvests` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `harvest_declarations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `harvest_declarations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `declaration_year` smallint(5) unsigned NOT NULL COMMENT 'Año de campaña declarado',
  `declaration_date` date NOT NULL COMMENT 'Fecha de elaboración de la declaración',
  `submission_date` date DEFAULT NULL COMMENT 'Fecha de presentación ante el organismo',
  `authority` varchar(255) NOT NULL COMMENT 'Organismo receptor (CCAA, DO, MAPA, etc.)',
  `reference_number` varchar(255) DEFAULT NULL COMMENT 'Número de referencia oficial asignado',
  `total_surface_ha` decimal(10,4) DEFAULT NULL COMMENT 'Superficie total declarada (ha)',
  `total_kg` decimal(12,2) DEFAULT NULL COMMENT 'Producción total declarada (kg)',
  `declaration_lines` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Detalle por variedad: [{variety, plot_name, surface_ha, kg, destination, rega_code, buyer}]' CHECK (json_valid(`declaration_lines`)),
  `status` enum('draft','submitted','accepted','rejected') NOT NULL DEFAULT 'draft',
  `rejection_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `harvest_declarations_viticulturist_id_declaration_year_index` (`viticulturist_id`,`declaration_year`),
  KEY `harvest_declarations_campaign_id_status_index` (`campaign_id`,`status`),
  CONSTRAINT `harvest_declarations_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `harvest_declarations_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `harvest_deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `harvest_deliveries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `plot_planting_id` bigint(20) unsigned DEFAULT NULL,
  `harvest_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('pending','matched','disputed','resolved') NOT NULL DEFAULT 'pending',
  `discrepancy_kg` decimal(10,2) DEFAULT NULL,
  `dispute_note` text DEFAULT NULL,
  `dispute_submitted_at` timestamp NULL DEFAULT NULL,
  `dispute_resolution_note` text DEFAULT NULL,
  `dispute_resolved_at` timestamp NULL DEFAULT NULL,
  `vintage_year` smallint(5) unsigned NOT NULL,
  `buyer_name` varchar(255) NOT NULL,
  `delivered_kg` decimal(10,2) NOT NULL,
  `price_per_kg` decimal(10,3) DEFAULT NULL,
  `total_price` decimal(12,3) DEFAULT NULL,
  `delivery_date` date NOT NULL,
  `harvest_time` time DEFAULT NULL,
  `ticket_number` varchar(100) DEFAULT NULL,
  `destination_rega_code` varchar(20) DEFAULT NULL,
  `vehicle_plate` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `disqualified` tinyint(1) NOT NULL DEFAULT 0,
  `disqualified_reason` varchar(500) DEFAULT NULL,
  `baume_degree` decimal(5,2) DEFAULT NULL,
  `brix_degree` decimal(5,2) DEFAULT NULL,
  `potential_alcohol` decimal(5,2) DEFAULT NULL,
  `acidity_level` decimal(5,2) DEFAULT NULL,
  `ph_level` decimal(4,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `harvest_deliveries_viticulturist_id_foreign` (`viticulturist_id`),
  KEY `harvest_deliveries_plot_planting_id_foreign` (`plot_planting_id`),
  KEY `harvest_deliveries_harvest_id_foreign` (`harvest_id`),
  CONSTRAINT `harvest_deliveries_harvest_id_foreign` FOREIGN KEY (`harvest_id`) REFERENCES `harvests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvest_deliveries_plot_planting_id_foreign` FOREIGN KEY (`plot_planting_id`) REFERENCES `plot_plantings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvest_deliveries_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `harvest_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `harvest_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `harvest_id` bigint(20) unsigned NOT NULL,
  `container_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_item_id` bigint(20) unsigned DEFAULT NULL,
  `movement_type` enum('initial','adjustment','reserve','sale','unreserve','gift','loss','return') NOT NULL DEFAULT 'initial',
  `quantity_change` decimal(10,3) NOT NULL COMMENT 'Cambio en cantidad (+ o -)',
  `quantity_after` decimal(10,3) NOT NULL COMMENT 'Cantidad total después del movimiento',
  `available_qty` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Disponible para venta',
  `reserved_qty` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Reservado (pendiente factura)',
  `sold_qty` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Vendido (facturado)',
  `gifted_qty` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Regalado',
  `lost_qty` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Pérdidas/mermas',
  `notes` text DEFAULT NULL COMMENT 'Razón del movimiento',
  `reference_number` varchar(100) DEFAULT NULL COMMENT 'Número de referencia externo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `harvest_stocks_harvest_id_index` (`harvest_id`),
  KEY `harvest_stocks_container_id_index` (`container_id`),
  KEY `harvest_stocks_user_id_index` (`user_id`),
  KEY `harvest_stocks_invoice_item_id_index` (`invoice_item_id`),
  KEY `harvest_stocks_movement_type_index` (`movement_type`),
  KEY `harvest_stocks_created_at_index` (`created_at`),
  KEY `harvest_stocks_harvest_created_idx` (`harvest_id`,`created_at`),
  KEY `harvest_stocks_invoice_item_idx` (`invoice_item_id`),
  KEY `harvest_stocks_container_idx` (`container_id`),
  CONSTRAINT `harvest_stocks_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvest_stocks_harvest_id_foreign` FOREIGN KEY (`harvest_id`) REFERENCES `harvests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `harvest_stocks_invoice_item_id_foreign` FOREIGN KEY (`invoice_item_id`) REFERENCES `invoice_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvest_stocks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `harvests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `harvests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) unsigned DEFAULT NULL,
  `notebook_harvest_id` bigint(20) unsigned DEFAULT NULL,
  `winery_id` bigint(20) unsigned DEFAULT NULL,
  `batch_id` bigint(20) unsigned DEFAULT NULL,
  `plot_planting_id` bigint(20) unsigned DEFAULT NULL,
  `container_id` bigint(20) unsigned DEFAULT NULL,
  `harvest_start_date` date NOT NULL COMMENT 'Fecha inicio de la vendimia',
  `harvest_time` time DEFAULT NULL COMMENT 'Hora de descarga',
  `harvest_end_date` date DEFAULT NULL COMMENT 'Fecha fin de la vendimia (opcional)',
  `vintage` smallint(5) unsigned DEFAULT NULL COMMENT 'Año de cosecha / añada (auto-calculado desde harvest_start_date)',
  `total_weight` decimal(10,3) DEFAULT NULL,
  `yield_per_hectare` decimal(10,3) DEFAULT NULL,
  `baume_degree` decimal(5,3) DEFAULT NULL,
  `brix_degree` decimal(5,3) DEFAULT NULL,
  `acidity_level` decimal(5,3) DEFAULT NULL,
  `ph_level` decimal(4,3) DEFAULT NULL,
  `potential_alcohol` decimal(5,2) DEFAULT NULL COMMENT 'Grado alcohólico potencial (%)',
  `color_rating` enum('excelente','bueno','aceptable','deficiente') DEFAULT NULL,
  `aroma_rating` enum('excelente','bueno','aceptable','deficiente') DEFAULT NULL,
  `health_status` enum('sano','daño_leve','daño_moderado','daño_grave') DEFAULT NULL,
  `destination_type` enum('winery','direct_sale','cooperative','self_consumption','other') DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL COMMENT 'Nombre específico del destino (bodega, comprador, etc.)',
  `buyer_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del comprador',
  `price_per_kg` decimal(10,4) DEFAULT NULL COMMENT 'Precio por kilogramo (€/kg)',
  `total_value` decimal(12,3) DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `edited_by` bigint(20) unsigned DEFAULT NULL,
  `edit_notes` text DEFAULT NULL COMMENT 'Motivo de edición',
  `status` enum('active','cancelled','draft') NOT NULL DEFAULT 'active',
  `disqualified` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Uva descartada/rechazada (no apta para vinificación)',
  `disqualified_reason` varchar(255) DEFAULT NULL COMMENT 'Motivo del descarte',
  `notes` text DEFAULT NULL,
  `harvest_ticket_number` varchar(50) DEFAULT NULL COMMENT 'Número de albarán / ticket de vendimia',
  `sanitary_state_grapes` decimal(5,2) DEFAULT NULL COMMENT '% uva sana sobre el total cosechado',
  `sanitary_state_agraces` decimal(5,2) DEFAULT NULL COMMENT '% agraces (uva verde/inmadura) sobre el total',
  `sanitary_state_botrytis` decimal(5,2) DEFAULT NULL COMMENT '% afectado por botritis (podredumbre gris)',
  `sanitary_state_oidium` decimal(5,2) DEFAULT NULL COMMENT '% afectado por oídio (ceniza)',
  `sanitary_state_mildew` decimal(5,2) DEFAULT NULL COMMENT '% afectado por mildiu',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `transport_document_number` varchar(50) DEFAULT NULL COMMENT 'Nº Documento de Acompañamiento',
  `destination_rega_code` varchar(20) DEFAULT NULL COMMENT 'Código REGA de destino',
  `vehicle_plate` varchar(20) DEFAULT NULL COMMENT 'Matrícula del vehículo',
  PRIMARY KEY (`id`),
  KEY `harvests_edited_by_foreign` (`edited_by`),
  KEY `harvests_plot_planting_id_index` (`plot_planting_id`),
  KEY `harvests_harvest_start_date_index` (`harvest_start_date`),
  KEY `harvests_status_index` (`status`),
  KEY `harvests_container_id_foreign` (`container_id`),
  KEY `harvests_vintage_index` (`vintage`),
  KEY `harvests_activity_id_foreign` (`activity_id`),
  KEY `harvests_winery_id_foreign` (`winery_id`),
  KEY `harvests_batch_id_foreign` (`batch_id`),
  KEY `harvests_notebook_harvest_id_foreign` (`notebook_harvest_id`),
  CONSTRAINT `harvests_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `agricultural_activities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvests_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `grape_reception_batches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvests_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvests_edited_by_foreign` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvests_notebook_harvest_id_foreign` FOREIGN KEY (`notebook_harvest_id`) REFERENCES `harvests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvests_plot_planting_id_foreign` FOREIGN KEY (`plot_planting_id`) REFERENCES `plot_plantings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvests_winery_id_foreign` FOREIGN KEY (`winery_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invoice_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_audit_logs_invoice_id_index` (`invoice_id`),
  KEY `invoice_audit_logs_user_id_index` (`user_id`),
  KEY `invoice_audit_logs_action_index` (`action`),
  KEY `invoice_audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `invoice_audit_logs_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invoice_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_groups_user_id_index` (`user_id`),
  CONSTRAINT `invoice_groups_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `harvest_id` bigint(20) unsigned DEFAULT NULL,
  `container_id` bigint(20) unsigned DEFAULT NULL,
  `marketed_harvest_id` bigint(20) unsigned DEFAULT NULL,
  `wine_lot_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Nombre del concepto',
  `description` text DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `concept_type` enum('harvest','service','product','other','wine') DEFAULT 'harvest',
  `quantity` decimal(10,3) NOT NULL DEFAULT 1.000 COMMENT 'Cantidad (kg, unidades, etc.)',
  `unit` varchar(20) NOT NULL DEFAULT 'kg',
  `unit_price` decimal(10,4) NOT NULL COMMENT 'Precio unitario',
  `discount_percentage` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Descuento %',
  `discount_amount` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Monto de descuento',
  `tax_id` bigint(20) unsigned DEFAULT NULL,
  `tax_name` varchar(255) DEFAULT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tax_base` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Base imponible del item',
  `tax_amount` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Monto de impuesto del item',
  `subtotal` decimal(10,3) NOT NULL COMMENT 'Subtotal sin impuestos',
  `total` decimal(10,3) NOT NULL COMMENT 'Total con impuestos',
  `status` enum('active','cancelled') NOT NULL DEFAULT 'active',
  `payment_status` enum('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
  `delivery_status` enum('pending','in_transit','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `variations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Opciones/variaciones del item' CHECK (json_valid(`variations`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_tax_id_foreign` (`tax_id`),
  KEY `invoice_items_invoice_id_index` (`invoice_id`),
  KEY `invoice_items_harvest_id_index` (`harvest_id`),
  KEY `invoice_items_concept_type_index` (`concept_type`),
  KEY `invoice_items_harvest_idx` (`harvest_id`),
  KEY `invoice_items_deleted_idx` (`deleted_at`),
  KEY `invoice_items_invoice_idx` (`invoice_id`),
  KEY `invoice_items_wine_lot_id_foreign` (`wine_lot_id`),
  KEY `invoice_items_container_id_foreign` (`container_id`),
  KEY `invoice_items_marketed_harvest_id_foreign` (`marketed_harvest_id`),
  CONSTRAINT `invoice_items_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `harvest_containers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoice_items_harvest_id_foreign` FOREIGN KEY (`harvest_id`) REFERENCES `harvests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_items_marketed_harvest_id_foreign` FOREIGN KEY (`marketed_harvest_id`) REFERENCES `marketed_harvests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoice_items_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoice_items_wine_lot_id_foreign` FOREIGN KEY (`wine_lot_id`) REFERENCES `wine_lots` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invoice_stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `invoice_item_id` bigint(20) unsigned DEFAULT NULL,
  `wine_lot_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `qty` decimal(10,3) NOT NULL COMMENT 'Cantidad movida',
  `action` enum('create','deliver','cancel') NOT NULL COMMENT 'Tipo de movimiento',
  `from_bucket` enum('available','reserved','sold') DEFAULT NULL,
  `to_bucket` enum('available','reserved','sold') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_stock_movements_invoice_item_id_foreign` (`invoice_item_id`),
  KEY `invoice_stock_movements_invoice_id_index` (`invoice_id`),
  KEY `invoice_stock_movements_wine_lot_id_index` (`wine_lot_id`),
  KEY `invoice_stock_movements_user_id_index` (`user_id`),
  CONSTRAINT `invoice_stock_movements_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_stock_movements_invoice_item_id_foreign` FOREIGN KEY (`invoice_item_id`) REFERENCES `invoice_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoice_stock_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_stock_movements_wine_lot_id_foreign` FOREIGN KEY (`wine_lot_id`) REFERENCES `wine_lots` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `invoice_type` varchar(20) DEFAULT NULL COMMENT 'wine_sale | grape_purchase',
  `client_id` bigint(20) unsigned DEFAULT NULL,
  `viticulturist_id` bigint(20) unsigned DEFAULT NULL,
  `client_address_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_number` varchar(255) DEFAULT NULL,
  `delivery_note_code` varchar(255) DEFAULT NULL COMMENT 'Código de albarán',
  `current_invoice_code` int(11) NOT NULL DEFAULT 1 COMMENT 'Contador interno',
  `current_delivery_note_code` int(11) NOT NULL DEFAULT 1,
  `invoice_code_generated_at` datetime DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `delivery_note_date` datetime DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `order_date` datetime NOT NULL DEFAULT '2026-03-27 11:00:57',
  `billing_address` text DEFAULT NULL,
  `billing_first_name` varchar(255) DEFAULT NULL,
  `billing_last_name` varchar(255) DEFAULT NULL,
  `billing_email` varchar(255) DEFAULT NULL,
  `billing_phone` varchar(255) DEFAULT NULL,
  `billing_company_name` varchar(255) DEFAULT NULL,
  `billing_company_document` varchar(255) DEFAULT NULL,
  `billing_postal_code` varchar(255) DEFAULT NULL,
  `billing_city` varchar(255) DEFAULT NULL,
  `billing_state` varchar(255) DEFAULT NULL,
  `billing_country` varchar(255) DEFAULT NULL,
  `subtotal` decimal(12,3) NOT NULL DEFAULT 0.000 COMMENT 'Base imponible',
  `discount_amount` decimal(12,3) NOT NULL DEFAULT 0.000,
  `tax_base` decimal(12,3) NOT NULL DEFAULT 0.000 COMMENT 'Base después de descuentos',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Tasa de impuesto aplicada',
  `tax_amount` decimal(12,3) NOT NULL DEFAULT 0.000 COMMENT 'Monto de impuesto',
  `total_amount` decimal(12,3) NOT NULL DEFAULT 0.000,
  `status` enum('draft','sent','paid','cancelled','corrective') NOT NULL DEFAULT 'draft',
  `payment_status` enum('unpaid','partial','paid','overdue') NOT NULL DEFAULT 'unpaid',
  `payment_type` enum('cash','transfer','check','other') DEFAULT NULL,
  `payment_details` text DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_account_name` varchar(255) DEFAULT NULL,
  `bank_account_number` varchar(255) DEFAULT NULL,
  `bank_routing_number` varchar(255) DEFAULT NULL,
  `bank_payment_status` tinyint(1) NOT NULL DEFAULT 0,
  `delivery_status` enum('pending','in_transit','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `tracking_code` varchar(255) DEFAULT NULL,
  `sif_status` enum('pendiente','enviado','aceptado','error') NOT NULL DEFAULT 'pendiente',
  `sif_uuid` varchar(255) DEFAULT NULL,
  `sif_hash` varchar(255) DEFAULT NULL,
  `sif_sent_at` datetime DEFAULT NULL,
  `sif_response` text DEFAULT NULL,
  `sif_excluded` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified_aet` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Verificado AET',
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `viewed` tinyint(1) NOT NULL DEFAULT 0,
  `delivery_viewed` tinyint(1) NOT NULL DEFAULT 1,
  `payment_status_viewed` tinyint(1) NOT NULL DEFAULT 1,
  `corrective` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Factura rectificativa',
  `corrected_invoice_id` bigint(20) unsigned DEFAULT NULL,
  `gift` tinyint(1) NOT NULL DEFAULT 0,
  `observations` text DEFAULT NULL COMMENT 'Observaciones generales',
  `observations_invoice` text DEFAULT NULL COMMENT 'Observaciones en factura',
  `invoice_group_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_user_invoice_number_unique` (`user_id`,`invoice_number`),
  KEY `invoices_client_address_id_foreign` (`client_address_id`),
  KEY `invoices_invoice_group_id_foreign` (`invoice_group_id`),
  KEY `invoices_user_id_index` (`user_id`),
  KEY `invoices_client_id_index` (`client_id`),
  KEY `invoices_invoice_number_index` (`invoice_number`),
  KEY `invoices_status_index` (`status`),
  KEY `invoices_payment_status_index` (`payment_status`),
  KEY `invoices_invoice_date_index` (`invoice_date`),
  KEY `invoices_user_status_created_idx` (`user_id`,`status`,`created_at`),
  KEY `invoices_delivery_note_idx` (`delivery_note_code`),
  KEY `invoices_invoice_number_idx` (`invoice_number`),
  KEY `invoices_status_created_idx` (`status`,`created_at`),
  KEY `invoices_corrected_invoice_id_foreign` (`corrected_invoice_id`),
  KEY `invoices_viticulturist_id_foreign` (`viticulturist_id`),
  CONSTRAINT `invoices_client_address_id_foreign` FOREIGN KEY (`client_address_id`) REFERENCES `client_addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `invoices_corrected_invoice_id_foreign` FOREIGN KEY (`corrected_invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_invoice_group_id_foreign` FOREIGN KEY (`invoice_group_id`) REFERENCES `invoice_groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invoicing_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoicing_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `issuer_legal_name` varchar(150) DEFAULT NULL COMMENT 'Nombre/Razón social fiscal del emisor — override de users.name para facturas',
  `invoice_prefix` varchar(255) NOT NULL DEFAULT 'FAC-' COMMENT 'Prefijo para facturas',
  `invoice_padding` int(11) NOT NULL DEFAULT 4 COMMENT 'Número de dígitos (ej: 4 = 0023)',
  `invoice_counter` int(11) NOT NULL DEFAULT 1 COMMENT 'Contador actual',
  `invoice_year_reset` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Resetear cada año',
  `invoice_last_reset_year` int(11) NOT NULL DEFAULT 2026 COMMENT 'Último año de reseteo del contador de facturas',
  `delivery_note_prefix` varchar(255) NOT NULL DEFAULT 'ALB-' COMMENT 'Prefijo para albaranes',
  `delivery_note_padding` int(11) NOT NULL DEFAULT 4 COMMENT 'Número de dígitos',
  `delivery_note_counter` int(11) NOT NULL DEFAULT 1 COMMENT 'Contador actual',
  `delivery_note_year_reset` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Resetear cada año',
  `delivery_note_last_reset_year` int(11) NOT NULL DEFAULT 2026 COMMENT 'Último año de reseteo del contador de albaranes',
  `last_reset_year` int(11) NOT NULL DEFAULT 2026 COMMENT 'Último año de reseteo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoicing_settings_user_id_unique` (`user_id`),
  CONSTRAINT `invoicing_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `irrigation_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `irrigation_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `irrigation_types_user_id_foreign` (`user_id`),
  CONSTRAINT `irrigation_types_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `irrigations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `irrigations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) unsigned NOT NULL,
  `water_volume` decimal(10,3) DEFAULT NULL,
  `water_volume_unit` varchar(3) NOT NULL DEFAULT 'L',
  `irrigation_method` varchar(50) DEFAULT NULL,
  `water_source` varchar(100) DEFAULT NULL COMMENT 'Origen del agua: pozo, embalse, acequia, río, etc.',
  `water_concession` varchar(100) DEFAULT NULL COMMENT 'Número de concesión o autorización de agua',
  `flow_rate` decimal(10,2) DEFAULT NULL COMMENT 'Caudal de riego en litros/hora',
  `is_fertirrigation` tinyint(1) NOT NULL DEFAULT 0,
  `fertilizer_product` varchar(150) DEFAULT NULL,
  `fertilizer_dose_per_ha` decimal(8,2) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `soil_moisture_before` decimal(5,2) DEFAULT NULL,
  `soil_moisture_after` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `irrigations_activity_id_foreign` (`activity_id`),
  CONSTRAINT `irrigations_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `agricultural_activities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `label_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `label_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `wine_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `source` enum('own','do_assigned','other') NOT NULL DEFAULT 'own',
  `start_number` bigint(20) unsigned NOT NULL,
  `end_number` bigint(20) unsigned NOT NULL,
  `total_quantity` int(10) unsigned NOT NULL,
  `used_quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `wasted_quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `label_batches_user_id_foreign` (`user_id`),
  KEY `label_batches_wine_id_foreign` (`wine_id`),
  CONSTRAINT `label_batches_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `label_batches_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `label_wastes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `label_wastes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `label_batch_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `from_number` bigint(20) unsigned DEFAULT NULL,
  `to_number` bigint(20) unsigned DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `waste_date` date NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `label_wastes_label_batch_id_foreign` (`label_batch_id`),
  KEY `label_wastes_user_id_foreign` (`user_id`),
  KEY `label_wastes_created_by_foreign` (`created_by`),
  CONSTRAINT `label_wastes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `label_wastes_label_batch_id_foreign` FOREIGN KEY (`label_batch_id`) REFERENCES `label_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `label_wastes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `machinery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `machinery` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `machinery_type_id` bigint(20) unsigned DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL,
  `current_value` decimal(10,2) DEFAULT NULL,
  `roma_registration` varchar(255) DEFAULT NULL,
  `is_rented` tinyint(1) NOT NULL DEFAULT 0,
  `capacity` varchar(255) DEFAULT NULL,
  `last_revision_date` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `machinery_viticulturist_id_active_index` (`viticulturist_id`,`active`),
  KEY `machinery_type_index` (`type`),
  KEY `machinery_type_idx` (`machinery_type_id`),
  CONSTRAINT `machinery_machinery_type_id_foreign` FOREIGN KEY (`machinery_type_id`) REFERENCES `machinery_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `machinery_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `machinery_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `machinery_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `machinery_types_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `marketed_harvests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketed_harvests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `harvest_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `delivery_date` date NOT NULL COMMENT 'Fecha de la entrega/albarán',
  `quantity_kg` decimal(10,2) NOT NULL COMMENT 'Kilogramos entregados en esta entrega',
  `destination_type` enum('own_winery','cooperative','third_party','other') NOT NULL DEFAULT 'cooperative',
  `buyer_name` varchar(255) DEFAULT NULL COMMENT 'Nombre de la bodega/cooperativa/comprador',
  `buyer_rega_code` varchar(30) DEFAULT NULL COMMENT 'Código REGA de la bodega receptora',
  `transport_document` varchar(50) DEFAULT NULL COMMENT 'Nº albarán de transporte',
  `vehicle_plate` varchar(15) DEFAULT NULL COMMENT 'Matrícula del vehículo',
  `price_per_kg` decimal(8,4) DEFAULT NULL COMMENT 'Precio €/kg en esta entrega',
  `total_value` decimal(12,2) DEFAULT NULL COMMENT 'Valor total calculado (€)',
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marketed_harvests_viticulturist_id_foreign` (`viticulturist_id`),
  KEY `marketed_harvests_harvest_id_active_index` (`harvest_id`,`active`),
  KEY `marketed_harvests_campaign_id_delivery_date_index` (`campaign_id`,`delivery_date`),
  KEY `marketed_harvests_invoice_id_index` (`invoice_id`),
  CONSTRAINT `marketed_harvests_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `marketed_harvests_harvest_id_foreign` FOREIGN KEY (`harvest_id`) REFERENCES `harvests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `marketed_harvests_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `marketed_harvests_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `multipart_plot_sigpac`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `multipart_plot_sigpac` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plot_id` bigint(20) unsigned NOT NULL,
  `sigpac_code_id` bigint(20) unsigned NOT NULL,
  `plot_geometry_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_plot_sigpac_geometry` (`plot_id`,`sigpac_code_id`,`plot_geometry_id`),
  KEY `plot_id` (`plot_id`),
  KEY `sigpac_code_id` (`sigpac_code_id`),
  KEY `plot_geometry_id` (`plot_geometry_id`),
  KEY `idx_plot_geometry` (`plot_id`,`plot_geometry_id`),
  CONSTRAINT `fk_multiple_plot_sigpac_geometry` FOREIGN KEY (`plot_geometry_id`) REFERENCES `plot_geometry` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_multiple_plot_sigpac_plot` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_multiple_plot_sigpac_sigpac` FOREIGN KEY (`sigpac_code_id`) REFERENCES `sigpac_code` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `municipalities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `municipalities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `province_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `municipalities_code_unique` (`code`),
  KEY `municipalities_code_index` (`code`),
  KEY `municipalities_province_id_index` (`province_id`),
  CONSTRAINT `municipalities_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notebook_access_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notebook_access_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `winery_id` bigint(20) unsigned DEFAULT NULL,
  `supervisor_id` bigint(20) unsigned DEFAULT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `requested_at` timestamp NOT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuaderno_access_requests_winery_id_viticulturist_id_unique` (`winery_id`,`viticulturist_id`),
  UNIQUE KEY `nar_supervisor_viticulturist_unique` (`supervisor_id`,`viticulturist_id`),
  KEY `cuaderno_access_requests_viticulturist_id_foreign` (`viticulturist_id`),
  CONSTRAINT `cuaderno_access_requests_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cuaderno_access_requests_winery_id_foreign` FOREIGN KEY (`winery_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notebook_access_requests_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `observations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `observations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) unsigned NOT NULL,
  `pest_id` bigint(20) unsigned DEFAULT NULL,
  `observation_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`photos`)),
  `severity` varchar(20) DEFAULT NULL,
  `affected_area_percentage` decimal(5,2) DEFAULT NULL COMMENT '% de superficie afectada — IPM PAC',
  `threshold_exceeded` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Umbral de daño económico superado — IPM PAC',
  `follow_up_date` date DEFAULT NULL COMMENT 'Fecha de revisión programada',
  `action_taken` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `observations_activity_id_foreign` (`activity_id`),
  KEY `observations_pest_id_index` (`pest_id`),
  CONSTRAINT `observations_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `agricultural_activities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `observations_pest_id_foreign` FOREIGN KEY (`pest_id`) REFERENCES `pests` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oenologists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oenologists` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `surname` varchar(150) DEFAULT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oenologists_user_id_active_index` (`user_id`,`active`),
  CONSTRAINT `oenologists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `official_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `official_reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `report_type` enum('phytosanitary_treatments','full_digital_notebook') NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `signature_hash` varchar(64) NOT NULL,
  `signed_at` timestamp NOT NULL,
  `signed_ip` varchar(45) NOT NULL,
  `signature_metadata` text DEFAULT NULL,
  `verification_code` varchar(64) NOT NULL,
  `verification_count` int(11) NOT NULL DEFAULT 0,
  `last_verified_at` timestamp NULL DEFAULT NULL,
  `report_metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_metadata`)),
  `pdf_path` varchar(255) DEFAULT NULL,
  `pdf_size` int(11) DEFAULT NULL,
  `pdf_filename` varchar(255) DEFAULT NULL,
  `csv_path` varchar(255) DEFAULT NULL,
  `xml_path` varchar(255) DEFAULT NULL,
  `is_valid` tinyint(1) NOT NULL DEFAULT 1,
  `invalidation_reason` text DEFAULT NULL,
  `invalidated_at` timestamp NULL DEFAULT NULL,
  `invalidated_by` bigint(20) unsigned DEFAULT NULL,
  `processing_status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'completed' COMMENT 'Estado del procesamiento en cola',
  `processing_error` text DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `official_reports_signature_hash_unique` (`signature_hash`),
  UNIQUE KEY `official_reports_verification_code_unique` (`verification_code`),
  KEY `official_reports_invalidated_by_foreign` (`invalidated_by`),
  KEY `official_reports_user_id_report_type_index` (`user_id`,`report_type`),
  KEY `official_reports_period_start_period_end_index` (`period_start`,`period_end`),
  KEY `official_reports_verification_code_index` (`verification_code`),
  KEY `official_reports_is_valid_created_at_index` (`is_valid`,`created_at`),
  KEY `official_reports_user_id_index` (`user_id`),
  KEY `official_reports_report_type_index` (`report_type`),
  KEY `official_reports_created_at_index` (`created_at`),
  CONSTRAINT `official_reports_invalidated_by_foreign` FOREIGN KEY (`invalidated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `official_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `onboarding_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `onboarding_progress` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `step` varchar(50) NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `skipped` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `onboarding_progress_user_id_step_unique` (`user_id`,`step`),
  KEY `onboarding_progress_user_id_completed_at_index` (`user_id`,`completed_at`),
  CONSTRAINT `onboarding_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organizations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` enum('winery','denomination_of_origin') NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `vat_number` varchar(255) DEFAULT NULL,
  `reovi_number` varchar(50) DEFAULT NULL COMMENT 'Número de registro en el REOVI (Registro de Operadores Vitivinícolas)',
  `nidpb` varchar(50) DEFAULT NULL COMMENT 'Número de Identificación del Depósito o Punto de Bodega (instalación INFOVI)',
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `province_id` bigint(20) unsigned DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `owner_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organizations_slug_unique` (`slug`),
  KEY `organizations_province_id_foreign` (`province_id`),
  KEY `organizations_owner_user_id_foreign` (`owner_user_id`),
  KEY `organizations_type_active_index` (`type`,`active`),
  KEY `organizations_parent_id_index` (`parent_id`),
  CONSTRAINT `organizations_owner_user_id_foreign` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `organizations_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `organizations_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `orientations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orientations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `abbreviation` varchar(3) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pac_declaration_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pac_declaration_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `declaration_id` bigint(20) unsigned NOT NULL,
  `plot_id` bigint(20) unsigned NOT NULL,
  `declared_area` decimal(10,3) NOT NULL,
  `eligible_area` decimal(10,3) NOT NULL DEFAULT 0.000,
  `eco_schemes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array de eco-regímenes declarados' CHECK (json_valid(`eco_schemes`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pac_declaration_items_declaration_id_plot_id_unique` (`declaration_id`,`plot_id`),
  KEY `pac_declaration_items_plot_id_foreign` (`plot_id`),
  CONSTRAINT `pac_declaration_items_declaration_id_foreign` FOREIGN KEY (`declaration_id`) REFERENCES `pac_declarations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pac_declaration_items_plot_id_foreign` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pac_declarations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pac_declarations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL COMMENT 'Referencia FEGA/organismo pagador',
  `status` enum('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `total_declared_area` decimal(10,3) NOT NULL DEFAULT 0.000,
  `total_eligible_area` decimal(10,3) NOT NULL DEFAULT 0.000,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pac_declarations_vitic_year_unique` (`viticulturist_id`,`year`),
  KEY `pac_declarations_viticulturist_id_year_index` (`viticulturist_id`,`year`),
  CONSTRAINT `pac_declarations_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pac_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pac_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `declaration_id` bigint(20) unsigned DEFAULT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `payment_type` enum('basic_payment','eco_scheme','associated_aid','transitional','other') NOT NULL DEFAULT 'basic_payment',
  `amount` decimal(12,2) NOT NULL,
  `payment_date` date NOT NULL,
  `reference` varchar(100) DEFAULT NULL COMMENT 'Referencia FEGA/organismo pagador',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pac_payments_declaration_id_foreign` (`declaration_id`),
  KEY `pac_payments_viticulturist_id_year_index` (`viticulturist_id`,`year`),
  CONSTRAINT `pac_payments_declaration_id_foreign` FOREIGN KEY (`declaration_id`) REFERENCES `pac_declarations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pac_payments_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `subscription_id` bigint(20) unsigned DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'EUR',
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `paypal_payment_id` varchar(255) DEFAULT NULL,
  `paypal_order_id` varchar(255) DEFAULT NULL,
  `paypal_response` text DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_paypal_payment_id_unique` (`paypal_payment_id`),
  KEY `payments_subscription_id_foreign` (`subscription_id`),
  KEY `payments_user_id_index` (`user_id`),
  KEY `payments_status_index` (`status`),
  KEY `payments_paypal_order_id_index` (`paypal_order_id`),
  CONSTRAINT `payments_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pest_product_effectiveness`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pest_product_effectiveness` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pest_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `effectiveness_rating` tinyint(4) NOT NULL DEFAULT 3 COMMENT 'Eficacia 1-5 estrellas',
  `notes` text DEFAULT NULL COMMENT 'Notas adicionales',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pest_product_effectiveness_pest_id_product_id_unique` (`pest_id`,`product_id`),
  KEY `pest_product_effectiveness_product_id_foreign` (`product_id`),
  KEY `pest_product_effectiveness_effectiveness_rating_index` (`effectiveness_rating`),
  CONSTRAINT `pest_product_effectiveness_pest_id_foreign` FOREIGN KEY (`pest_id`) REFERENCES `pests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pest_product_effectiveness_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `phytosanitary_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('pest','disease') NOT NULL COMMENT 'Tipo: plaga o enfermedad',
  `name` varchar(100) NOT NULL COMMENT 'Nombre común',
  `scientific_name` varchar(150) DEFAULT NULL COMMENT 'Nombre científico',
  `description` text DEFAULT NULL COMMENT 'Descripción detallada',
  `symptoms` text DEFAULT NULL COMMENT 'Síntomas y signos',
  `lifecycle` text DEFAULT NULL COMMENT 'Ciclo de vida',
  `risk_months` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Meses de mayor riesgo (1-12)' CHECK (json_valid(`risk_months`)),
  `threshold` varchar(255) DEFAULT NULL COMMENT 'Umbral de tratamiento',
  `prevention_methods` text DEFAULT NULL COMMENT 'Métodos de prevención',
  `control_methods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Métodos de control IPM ordenados por prioridad PAC: biologico, cultural, fisico, quimico' CHECK (json_valid(`control_methods`)),
  `photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Rutas de fotos' CHECK (json_valid(`photos`)),
  `active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Activo/Inactivo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pests_type_index` (`type`),
  KEY `pests_active_index` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `phenology_observations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phenology_observations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plot_planting_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `event` enum('budbreak','shoot_growth','flowering','fruit_set','veraison','pre_harvest','harvest') NOT NULL COMMENT 'Estadio fenológico observado',
  `obs_date` date NOT NULL COMMENT 'Fecha de la observación',
  `source` enum('manual','sensor','model','auto') NOT NULL DEFAULT 'manual' COMMENT 'Origen del dato: manual=observación en campo, sensor=sensor IoT, model=modelo predictivo, auto=satélite',
  `confidence` tinyint(4) NOT NULL DEFAULT 100 COMMENT 'Nivel de confianza 0-100 (100=certeza absoluta)',
  `degree_days_accumulated` decimal(8,2) DEFAULT NULL COMMENT 'Grados-día acumulados desde brotación hasta este evento (suma de temperaturas > base)',
  `bbch_code` int(11) DEFAULT NULL COMMENT 'Código BBCH correspondiente al estadio (escala internacional)',
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_phenology_event` (`plot_planting_id`,`campaign_id`,`event`),
  KEY `phenology_observations_campaign_id_foreign` (`campaign_id`),
  KEY `phenology_observations_viticulturist_id_foreign` (`viticulturist_id`),
  KEY `phenology_observations_plot_planting_id_campaign_id_index` (`plot_planting_id`,`campaign_id`),
  KEY `phenology_observations_obs_date_index` (`obs_date`),
  CONSTRAINT `phenology_observations_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phenology_observations_plot_planting_id_foreign` FOREIGN KEY (`plot_planting_id`) REFERENCES `plot_plantings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phenology_observations_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `phytosanitary_container_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phytosanitary_container_returns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `phytosanitary_product_id` bigint(20) unsigned DEFAULT NULL,
  `date` date NOT NULL COMMENT 'Fecha de entrega en punto de recogida',
  `product_name` varchar(255) NOT NULL COMMENT 'Nombre del producto (libre, puede diferir del catálogo)',
  `registration_number` varchar(255) DEFAULT NULL COMMENT 'Nº registro MAPA del producto',
  `container_type` enum('plastic','glass','metal','cardboard','flexible','other') NOT NULL COMMENT 'Material del envase',
  `container_size_liters` decimal(8,3) DEFAULT NULL COMMENT 'Capacidad unitaria del envase (L)',
  `containers_quantity` int(10) unsigned NOT NULL COMMENT 'Número de envases entregados',
  `total_weight_kg` decimal(8,3) DEFAULT NULL COMMENT 'Peso total de envases vacíos (kg)',
  `collection_system` enum('sigfito','field','other') NOT NULL DEFAULT 'sigfito' COMMENT 'Sistema de gestión de envases',
  `collection_point` varchar(255) NOT NULL COMMENT 'Nombre o código del punto de recogida',
  `transport_document` varchar(255) DEFAULT NULL COMMENT 'Nº albarán o documento de transporte',
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phytosanitary_container_returns_phytosanitary_product_id_foreign` (`phytosanitary_product_id`),
  KEY `phytosanitary_container_returns_viticulturist_id_active_index` (`viticulturist_id`,`active`),
  KEY `phytosanitary_container_returns_campaign_id_date_index` (`campaign_id`,`date`),
  CONSTRAINT `phytosanitary_container_returns_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phytosanitary_container_returns_phytosanitary_product_id_foreign` FOREIGN KEY (`phytosanitary_product_id`) REFERENCES `phytosanitary_products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `phytosanitary_container_returns_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `phytosanitary_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phytosanitary_products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `active_ingredient` varchar(255) DEFAULT NULL,
  `safety_interval_days` int(10) unsigned DEFAULT NULL COMMENT 'Plazo de seguridad en días',
  `registration_number` varchar(100) NOT NULL,
  `registration_expiry_date` date DEFAULT NULL,
  `registration_status` enum('active','expired','revoked') NOT NULL DEFAULT 'active',
  `manufacturer` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `toxicity_class` varchar(20) DEFAULT NULL,
  `withdrawal_period_days` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phytosanitary_products_user_id_foreign` (`user_id`),
  CONSTRAINT `phytosanitary_products_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `phytosanitary_treatments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phytosanitary_treatments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) unsigned NOT NULL,
  `field_applicator_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `pest_id` bigint(20) unsigned DEFAULT NULL,
  `dose_per_hectare` decimal(10,3) DEFAULT NULL,
  `total_dose` decimal(10,3) DEFAULT NULL,
  `area_treated` decimal(10,3) DEFAULT NULL,
  `treatment_justification` text DEFAULT NULL COMMENT 'Justificación del tratamiento: plaga o enfermedad detectada (obligatorio PAC)',
  `applicator_ropo_number` varchar(50) DEFAULT NULL COMMENT 'Número de Registro Oficial de Productores y Operadores del aplicador',
  `reentry_period_days` int(11) DEFAULT NULL COMMENT 'Días sin acceso a la parcela tras aplicación (obligatorio PAC)',
  `spray_volume` decimal(10,2) DEFAULT NULL COMMENT 'Volumen total de caldo aplicado en litros',
  `water_volume_liters_ha` decimal(8,2) DEFAULT NULL COMMENT 'Volumen de agua por hectárea (L/ha) — volumen de caldo',
  `buffer_zone_respected` tinyint(1) DEFAULT NULL,
  `distance_to_water_m` decimal(6,2) DEFAULT NULL,
  `under_advisory` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'El tratamiento se realiza bajo asesoramiento técnico cualificado (RD 1311/2012)',
  `advisory_recommendation_date` date DEFAULT NULL COMMENT 'Fecha de la recomendación del asesor técnico',
  `prior_non_chemical_methods` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'IPM: Se aplicaron métodos no químicos previos al tratamiento',
  `plague_monitoring` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'IPM: Se realizó seguimiento/monitoreo de la plaga antes de tratar',
  `manual_mechanical_control` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'IPM: Se usó control manual o mecánico como alternativa o complemento',
  `biological_control` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'IPM: Se usó control biológico (depredadores, parásitos, etc.)',
  `cultural_preventions` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'IPM: Se aplicaron medidas culturales preventivas (poda, eliminación de focos, etc.)',
  `application_method` varchar(50) DEFAULT NULL,
  `target_pest` varchar(255) DEFAULT NULL,
  `wind_speed` decimal(5,2) DEFAULT NULL,
  `humidity` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phytosanitary_treatments_activity_id_foreign` (`activity_id`),
  KEY `phytosanitary_treatments_pest_id_index` (`pest_id`),
  KEY `phytosanitary_treatments_product_id_index` (`product_id`),
  KEY `phytosanitary_treatments_field_applicator_id_foreign` (`field_applicator_id`),
  CONSTRAINT `phytosanitary_treatments_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `agricultural_activities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phytosanitary_treatments_field_applicator_id_foreign` FOREIGN KEY (`field_applicator_id`) REFERENCES `field_applicators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `phytosanitary_treatments_pest_id_foreign` FOREIGN KEY (`pest_id`) REFERENCES `pests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `phytosanitary_treatments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `phytosanitary_products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `planting_certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `planting_certifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plot_planting_id` bigint(20) unsigned NOT NULL,
  `type` enum('ecologico','do','doca','igp','vino_pago') NOT NULL,
  `certification_number` varchar(255) NOT NULL,
  `certifying_body` varchar(255) NOT NULL,
  `certification_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('active','expired','suspended','pending') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `planting_certifications_plot_planting_id_status_index` (`plot_planting_id`,`status`),
  KEY `planting_certifications_expiry_date_index` (`expiry_date`),
  CONSTRAINT `planting_certifications_plot_planting_id_foreign` FOREIGN KEY (`plot_planting_id`) REFERENCES `plot_plantings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plot_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plot_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plot_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `plot_audit_logs_plot_id_created_at_index` (`plot_id`,`created_at`),
  KEY `plot_audit_logs_user_id_index` (`user_id`),
  KEY `plot_audit_logs_action_index` (`action`),
  CONSTRAINT `plot_audit_logs_plot_id_foreign` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plot_audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plot_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plot_costs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `plot_id` bigint(20) unsigned DEFAULT NULL,
  `campaign_id` bigint(20) unsigned DEFAULT NULL,
  `category` enum('labor','machinery','materials','phytosanitary','fertilizer','water','insurance','transport','subcontracting','other') NOT NULL DEFAULT 'other',
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `cost_date` date NOT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `invoice_reference` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plot_costs_plot_id_foreign` (`plot_id`),
  KEY `plot_costs_campaign_id_foreign` (`campaign_id`),
  KEY `plot_costs_viticulturist_id_campaign_id_index` (`viticulturist_id`,`campaign_id`),
  KEY `plot_costs_viticulturist_id_plot_id_index` (`viticulturist_id`,`plot_id`),
  KEY `plot_costs_cost_date_index` (`cost_date`),
  CONSTRAINT `plot_costs_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plot_costs_plot_id_foreign` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plot_costs_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plot_environments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plot_environments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `plot_id` bigint(20) unsigned NOT NULL,
  `plot_planting_id` bigint(20) unsigned DEFAULT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `water_intake_nearby` tinyint(1) NOT NULL DEFAULT 0 COMMENT '¿Hay captación de agua cercana?',
  `water_intake_distance_m` decimal(8,2) DEFAULT NULL COMMENT 'Distancia a captación (m)',
  `protected_zone_total` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Zona de exclusión total',
  `protected_zone_partial` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Zona de exclusión parcial',
  `protection_zone_type` varchar(100) DEFAULT NULL COMMENT 'Tipo: N2000, LIC, ZEPA...',
  `buffer_zone_m` decimal(8,2) DEFAULT NULL COMMENT 'Zona tampón requerida (m)',
  `slope_pct` decimal(5,2) DEFAULT NULL COMMENT 'Pendiente media de la parcela (%)',
  `erosion_risk` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Riesgo de erosión significativo',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_campaign_plot_env` (`campaign_id`,`plot_id`),
  KEY `plot_environments_plot_id_foreign` (`plot_id`),
  KEY `plot_environments_plot_planting_id_foreign` (`plot_planting_id`),
  KEY `plot_environments_viticulturist_id_index` (`viticulturist_id`),
  CONSTRAINT `plot_environments_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plot_environments_plot_id_foreign` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plot_environments_plot_planting_id_foreign` FOREIGN KEY (`plot_planting_id`) REFERENCES `plot_plantings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plot_environments_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plot_geometry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plot_geometry` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `coordinates` geometry NOT NULL,
  `centroid` geometry DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  SPATIAL KEY `spatial_coordinates` (`coordinates`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plot_planting_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plot_planting_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plot_planting_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `plot_planting_audit_logs_plot_planting_id_created_at_index` (`plot_planting_id`,`created_at`),
  KEY `plot_planting_audit_logs_user_id_index` (`user_id`),
  KEY `plot_planting_audit_logs_action_index` (`action`),
  CONSTRAINT `plot_planting_audit_logs_plot_planting_id_foreign` FOREIGN KEY (`plot_planting_id`) REFERENCES `plot_plantings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plot_planting_audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plot_plantings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plot_plantings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plot_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT 'Nombre identificativo de la plantación para diferenciarla (ej: "Parcela Norte - Tempranillo", "Bloque A", etc.)',
  `grape_variety_id` bigint(20) unsigned DEFAULT NULL,
  `area_planted` decimal(10,3) NOT NULL COMMENT 'Superficie dedicada a esta variedad (hectáreas)',
  `harvest_limit_kg` decimal(10,3) DEFAULT NULL,
  `planting_year` int(11) DEFAULT NULL COMMENT 'Año de plantación',
  `vine_count` int(11) DEFAULT NULL COMMENT 'Número de cepas',
  `density` int(11) DEFAULT NULL COMMENT 'Cepas por hectárea',
  `row_spacing` decimal(10,3) DEFAULT NULL COMMENT 'Distancia entre filas (metros)',
  `vine_spacing` decimal(10,3) DEFAULT NULL COMMENT 'Distancia entre cepas (metros)',
  `rootstock` varchar(255) DEFAULT NULL COMMENT 'Portainjerto utilizado',
  `training_system` varchar(255) DEFAULT NULL COMMENT 'Sistema de conducción (espaldera, vaso, etc.)',
  `training_system_id` bigint(20) unsigned DEFAULT NULL,
  `irrigated` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si tiene riego',
  `status` enum('active','removed','experimental','replanting') NOT NULL DEFAULT 'active' COMMENT 'Estado de la plantación',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL COMMENT 'Observaciones adicionales',
  `planting_authorization` varchar(255) DEFAULT NULL COMMENT 'Número de autorización de plantación vitícola',
  `authorization_date` date DEFAULT NULL COMMENT 'Fecha de concesión de la autorización',
  `right_type` enum('nueva','replantacion','conversion','transferencia') DEFAULT NULL,
  `uprooting_date` date DEFAULT NULL COMMENT 'Fecha de arranque (solo para replantaciones)',
  `designation_of_origin` varchar(255) DEFAULT NULL COMMENT 'DO, DOCa o IGP (ej: DO Rioja, DOCa Priorat)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plot_plantings_plot_id_grape_variety_id_index` (`plot_id`,`grape_variety_id`),
  KEY `plot_plantings_status_index` (`status`),
  KEY `plot_plantings_training_system_idx` (`training_system_id`),
  KEY `plot_plantings_planting_authorization_index` (`planting_authorization`),
  KEY `plot_plantings_authorization_date_index` (`authorization_date`),
  CONSTRAINT `plot_plantings_plot_id_foreign` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plot_plantings_training_system_id_foreign` FOREIGN KEY (`training_system_id`) REFERENCES `training_systems` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plot_remote_sensing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plot_remote_sensing` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plot_id` bigint(20) unsigned NOT NULL,
  `multipart_plot_sigpac_id` bigint(20) unsigned DEFAULT NULL,
  `image_date` date NOT NULL,
  `ndvi_mean` decimal(6,4) DEFAULT NULL,
  `ndvi_min` decimal(6,4) DEFAULT NULL,
  `ndvi_max` decimal(6,4) DEFAULT NULL,
  `ndvi_stddev` decimal(6,4) DEFAULT NULL,
  `ndwi_mean` decimal(6,4) DEFAULT NULL,
  `ndwi_min` decimal(6,4) DEFAULT NULL,
  `ndwi_max` decimal(6,4) DEFAULT NULL,
  `evi_mean` decimal(6,4) DEFAULT NULL,
  `lai` decimal(5,2) DEFAULT NULL COMMENT 'Leaf Area Index - predicts yield',
  `fpar` decimal(5,3) DEFAULT NULL COMMENT 'Fraction of PAR (0-1)',
  `lai_quality` int(11) DEFAULT NULL COMMENT 'LAI quality flag from MODIS',
  `gndvi` decimal(7,4) DEFAULT NULL COMMENT 'Green NDVI - chlorophyll content',
  `ndre` decimal(7,4) DEFAULT NULL COMMENT 'Normalized Difference Red Edge',
  `msr` decimal(6,4) DEFAULT NULL COMMENT 'Modified Simple Ratio',
  `ci_green` decimal(6,4) DEFAULT NULL COMMENT 'Chlorophyll Index Green',
  `arvi` decimal(6,4) DEFAULT NULL COMMENT 'Atmospherically Resistant VI',
  `chlorophyll_content` decimal(5,2) DEFAULT NULL COMMENT 'Relative chlorophyll content (0-100%)',
  `maturity_index` decimal(5,2) DEFAULT NULL COMMENT 'Maturity index (0-100)',
  `predicted_brix` decimal(5,2) DEFAULT NULL COMMENT 'Predicted sugar content (°Brix)',
  `days_to_harvest` int(11) DEFAULT NULL COMMENT 'Estimated days to optimal harvest',
  `anomaly_detected` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether anomaly was detected',
  `anomaly_severity` varchar(20) DEFAULT NULL COMMENT 'Severity: none, low, medium, high, critical',
  `anomaly_type` varchar(50) DEFAULT NULL COMMENT 'Type of anomaly detected',
  `cloud_coverage` int(11) DEFAULT NULL COMMENT 'Porcentaje de nubes 0-100',
  `image_source` varchar(50) NOT NULL DEFAULT 'sentinel2',
  `data_source` varchar(50) NOT NULL DEFAULT 'point' COMMENT 'point, area, or timeseries',
  `satellite` varchar(20) NOT NULL DEFAULT 'MODIS' COMMENT 'MODIS, VIIRS, or other',
  `pixel_reliability` int(11) DEFAULT NULL COMMENT 'Pixel reliability flag (0=good, 1=marginal, 2-3=poor)',
  `red_band` decimal(6,4) DEFAULT NULL COMMENT 'Red band reflectance',
  `nir_band` decimal(6,4) DEFAULT NULL COMMENT 'NIR band reflectance',
  `green_band` decimal(6,4) DEFAULT NULL COMMENT 'Green band reflectance',
  `blue_band` decimal(6,4) DEFAULT NULL COMMENT 'Blue band reflectance',
  `tile_id` varchar(100) DEFAULT NULL COMMENT 'ID del tile de Sentinel-2',
  `tile_path` varchar(255) DEFAULT NULL COMMENT 'Ruta al tile NDVI procesado',
  `health_status` enum('excellent','good','moderate','poor','critical') DEFAULT NULL,
  `health_notes` text DEFAULT NULL,
  `ndvi_change` decimal(6,4) DEFAULT NULL COMMENT 'Cambio vs periodo anterior',
  `trend` enum('increasing','stable','decreasing') DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `temperature` decimal(5,2) DEFAULT NULL COMMENT '°C',
  `temperature_min` decimal(5,2) DEFAULT NULL COMMENT '°C',
  `temperature_max` decimal(5,2) DEFAULT NULL COMMENT '°C',
  `lst_day` decimal(6,2) DEFAULT NULL COMMENT 'Land Surface Temperature Day (°C) from MODIS',
  `lst_night` decimal(6,2) DEFAULT NULL COMMENT 'Land Surface Temperature Night (°C)',
  `lst_diff` decimal(6,2) DEFAULT NULL COMMENT 'Day-Night Temperature Difference (DTR)',
  `cwsi` decimal(5,3) DEFAULT NULL COMMENT 'Crop Water Stress Index (0-1, higher = more stress)',
  `area_statistics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Statistics from area request: min, max, mean, stddev, percentiles' CHECK (json_valid(`area_statistics`)),
  `precipitation` decimal(6,2) DEFAULT NULL COMMENT 'mm',
  `humidity` decimal(5,2) DEFAULT NULL COMMENT '%',
  `wind_speed` decimal(5,2) DEFAULT NULL COMMENT 'km/h',
  `soil_moisture` decimal(5,2) DEFAULT NULL COMMENT '% volumétrico',
  `soil_moisture_surface_smap` decimal(5,2) DEFAULT NULL COMMENT 'SMAP surface soil moisture (%)',
  `soil_moisture_rootzone_smap` decimal(5,2) DEFAULT NULL COMMENT 'SMAP rootzone soil moisture (%)',
  `soil_temperature` decimal(5,2) DEFAULT NULL COMMENT '°C',
  `solar_radiation` decimal(8,2) DEFAULT NULL COMMENT 'W/m²',
  `et0` decimal(5,2) DEFAULT NULL COMMENT 'mm/día - Evapotranspiración referencia',
  `et_nasa` decimal(6,2) DEFAULT NULL COMMENT 'NASA official ET (mm/day)',
  `pet_nasa` decimal(6,2) DEFAULT NULL COMMENT 'NASA Potential ET (mm/day)',
  `sunshine_hours` decimal(4,1) DEFAULT NULL COMMENT 'horas',
  `water_stress_status` varchar(20) DEFAULT NULL COMMENT 'optimal, mild, moderate, severe',
  PRIMARY KEY (`id`),
  UNIQUE KEY `prs_plot_sigpac_date_source_unique` (`plot_id`,`multipart_plot_sigpac_id`,`image_date`,`image_source`),
  KEY `plot_remote_sensing_plot_id_image_date_index` (`plot_id`,`image_date`),
  KEY `plot_remote_sensing_health_status_index` (`health_status`),
  KEY `idx_anomalies` (`anomaly_detected`,`anomaly_severity`),
  KEY `idx_maturity` (`maturity_index`),
  KEY `idx_lai` (`lai`),
  KEY `idx_lst_day` (`lst_day`),
  KEY `idx_cwsi` (`cwsi`),
  KEY `idx_data_source` (`data_source`,`satellite`),
  KEY `idx_fpar` (`fpar`),
  KEY `idx_satellite_date` (`satellite`,`image_date`),
  KEY `plot_remote_sensing_multipart_plot_sigpac_id_foreign` (`multipart_plot_sigpac_id`),
  KEY `prs_plot_recinto_date_idx` (`plot_id`,`multipart_plot_sigpac_id`,`image_date`),
  CONSTRAINT `plot_remote_sensing_multipart_plot_sigpac_id_foreign` FOREIGN KEY (`multipart_plot_sigpac_id`) REFERENCES `multipart_plot_sigpac` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plot_remote_sensing_plot_id_foreign` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plot_sigpac_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plot_sigpac_code` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plot_id` bigint(20) unsigned NOT NULL,
  `sigpac_code_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plot_sigpac_code_plot_id_sigpac_code_id_unique` (`plot_id`,`sigpac_code_id`),
  KEY `plot_sigpac_code_plot_idx` (`plot_id`),
  KEY `plot_sigpac_code_code_idx` (`sigpac_code_id`),
  CONSTRAINT `plot_sigpac_code_plot_id_foreign` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plot_sigpac_code_sigpac_code_id_foreign` FOREIGN KEY (`sigpac_code_id`) REFERENCES `sigpac_code` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plot_sigpac_use`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plot_sigpac_use` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plot_id` bigint(20) unsigned NOT NULL,
  `sigpac_use_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plot_sigpac_use_plot_id_sigpac_use_id_unique` (`plot_id`,`sigpac_use_id`),
  KEY `plot_sigpac_use_plot_idx` (`plot_id`),
  KEY `plot_sigpac_use_use_idx` (`sigpac_use_id`),
  CONSTRAINT `plot_sigpac_use_plot_id_foreign` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plot_sigpac_use_sigpac_use_id_foreign` FOREIGN KEY (`sigpac_use_id`) REFERENCES `sigpac_use` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `site_id` bigint(20) unsigned DEFAULT NULL,
  `training_system_id` bigint(20) unsigned DEFAULT NULL,
  `planting_pattern` varchar(50) DEFAULT NULL COMMENT 'Marco de plantación: cuadrado, tresbolillo...',
  `slope` decimal(5,2) DEFAULT NULL COMMENT 'Pendiente en %',
  `number_of_vines` int(11) DEFAULT NULL COMMENT 'Número total de cepas en la parcela',
  `valley_id` bigint(20) unsigned DEFAULT NULL,
  `code_parcel` varchar(50) DEFAULT NULL COMMENT 'Código catastral de la parcela',
  `enclosure` varchar(100) DEFAULT NULL COMMENT 'Referencia de recinto/enclave',
  `soil_type_id` bigint(20) unsigned DEFAULT NULL,
  `irrigation_type_id` bigint(20) unsigned DEFAULT NULL,
  `topography_id` bigint(20) unsigned DEFAULT NULL,
  `degree_day_base` decimal(4,1) DEFAULT NULL,
  `plantation_year` smallint(6) DEFAULT NULL COMMENT 'Año de plantación del viñedo',
  `is_organic` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Producción ecológica certificada',
  `viticulturist_id` bigint(20) unsigned DEFAULT NULL,
  `owner_id` bigint(20) unsigned DEFAULT NULL,
  `area` decimal(10,3) DEFAULT NULL,
  `cadastral_area` decimal(10,4) DEFAULT NULL COMMENT 'Superficie catastral registrada',
  `pac_eligible_area` decimal(10,3) DEFAULT NULL,
  `non_eligible_area` decimal(10,3) DEFAULT NULL,
  `eligibility_coefficient` decimal(5,4) DEFAULT NULL,
  `property_type_id` bigint(20) unsigned DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `locked_at` timestamp NULL DEFAULT NULL,
  `autonomous_community_id` bigint(20) unsigned DEFAULT NULL,
  `province_id` bigint(20) unsigned DEFAULT NULL,
  `municipality_id` bigint(20) unsigned DEFAULT NULL,
  `ndvi_alert_threshold` decimal(3,2) NOT NULL DEFAULT 0.30,
  `alert_email_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `locked_by` bigint(20) unsigned DEFAULT NULL,
  `lock_reason` varchar(255) DEFAULT NULL,
  `orientation_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plots_autonomous_community_id_foreign` (`autonomous_community_id`),
  KEY `plots_viticulturist_id_index` (`viticulturist_id`),
  KEY `plots_active_index` (`active`),
  KEY `plots_municipality_id_index` (`municipality_id`),
  KEY `idx_plots_viticulturist_active` (`viticulturist_id`,`active`),
  KEY `idx_plots_name` (`name`),
  KEY `idx_plots_viticulturist` (`viticulturist_id`),
  KEY `idx_plots_active` (`active`),
  KEY `idx_plots_municipality` (`municipality_id`),
  KEY `plots_locked_by_foreign` (`locked_by`),
  KEY `plots_is_locked_index` (`is_locked`),
  KEY `plots_province_id_index` (`province_id`),
  KEY `plots_name_index` (`name`),
  KEY `plots_viticulturist_active_idx` (`viticulturist_id`,`active`),
  KEY `plots_soil_type_id_foreign` (`soil_type_id`),
  KEY `plots_irrigation_type_id_foreign` (`irrigation_type_id`),
  KEY `plots_topography_id_foreign` (`topography_id`),
  KEY `plots_property_type_id_foreign` (`property_type_id`),
  KEY `plots_valley_id_foreign` (`valley_id`),
  KEY `plots_site_id_foreign` (`site_id`),
  KEY `plots_training_system_id_foreign` (`training_system_id`),
  KEY `plots_owner_id_foreign` (`owner_id`),
  KEY `plots_orientation_id_foreign` (`orientation_id`),
  CONSTRAINT `plots_autonomous_community_id_foreign` FOREIGN KEY (`autonomous_community_id`) REFERENCES `autonomous_communities` (`id`),
  CONSTRAINT `plots_irrigation_type_id_foreign` FOREIGN KEY (`irrigation_type_id`) REFERENCES `irrigation_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plots_locked_by_foreign` FOREIGN KEY (`locked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plots_municipality_id_foreign` FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`),
  CONSTRAINT `plots_orientation_id_foreign` FOREIGN KEY (`orientation_id`) REFERENCES `orientations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plots_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plots_property_type_id_foreign` FOREIGN KEY (`property_type_id`) REFERENCES `property_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plots_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`),
  CONSTRAINT `plots_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plots_soil_type_id_foreign` FOREIGN KEY (`soil_type_id`) REFERENCES `soil_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plots_topography_id_foreign` FOREIGN KEY (`topography_id`) REFERENCES `topographies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plots_training_system_id_foreign` FOREIGN KEY (`training_system_id`) REFERENCES `training_systems` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plots_valley_id_foreign` FOREIGN KEY (`valley_id`) REFERENCES `valleys` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plots_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_harvest_treatments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_harvest_treatments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `application_type` enum('copper_treatment','sulfur_treatment','wound_sealing','foliar_application','trunk_treatment','other') NOT NULL DEFAULT 'foliar_application',
  `treated_area_ha` decimal(10,4) NOT NULL COMMENT 'Superficie tratada (ha)',
  `dose_per_hectare` decimal(10,3) DEFAULT NULL COMMENT 'Dosis por hectárea',
  `dose_unit` varchar(20) DEFAULT NULL COMMENT 'Unidad de la dosis (kg/ha, L/ha, g/ha)',
  `water_volume_liters` decimal(8,2) DEFAULT NULL COMMENT 'Volumen de agua/caldo (L)',
  `reentry_interval_hours` smallint(5) unsigned DEFAULT NULL COMMENT 'Plazo de reentrada en horas — seguridad laboral PAC (0 = sin restricción)',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_harvest_treatments_activity_id_foreign` (`activity_id`),
  KEY `post_harvest_treatments_product_id_foreign` (`product_id`),
  CONSTRAINT `post_harvest_treatments_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `agricultural_activities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `post_harvest_treatments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `phytosanitary_products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stock_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `treatment_id` bigint(20) unsigned DEFAULT NULL,
  `movement_type` varchar(255) NOT NULL,
  `quantity_change` decimal(10,3) NOT NULL,
  `quantity_before` decimal(10,3) NOT NULL,
  `quantity_after` decimal(10,3) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_stock_movements_user_id_foreign` (`user_id`),
  KEY `product_stock_movements_treatment_id_foreign` (`treatment_id`),
  KEY `product_stock_movements_stock_id_movement_type_index` (`stock_id`,`movement_type`),
  KEY `product_stock_movements_created_at_index` (`created_at`),
  CONSTRAINT `product_stock_movements_stock_id_foreign` FOREIGN KEY (`stock_id`) REFERENCES `product_stocks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_stock_movements_treatment_id_foreign` FOREIGN KEY (`treatment_id`) REFERENCES `phytosanitary_treatments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_stock_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `warehouse_id` bigint(20) unsigned DEFAULT NULL,
  `batch_number` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `manufacturing_date` date DEFAULT NULL,
  `quantity` decimal(10,3) NOT NULL DEFAULT 0.000,
  `minimum_stock` decimal(10,3) DEFAULT NULL COMMENT 'Umbral mínimo de stock para alertas',
  `unit` varchar(20) NOT NULL DEFAULT 'L',
  `unit_price` decimal(10,2) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `invoice_number` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_stocks_user_id_foreign` (`user_id`),
  KEY `product_stocks_warehouse_id_foreign` (`warehouse_id`),
  KEY `product_stocks_product_id_user_id_active_index` (`product_id`,`user_id`,`active`),
  KEY `product_stocks_expiry_date_index` (`expiry_date`),
  CONSTRAINT `product_stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `phytosanitary_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_stocks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_stocks_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `property_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `property_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `property_types_user_id_foreign` (`user_id`),
  CONSTRAINT `property_types_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `provinces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provinces` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `autonomous_community_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provinces_code_unique` (`code`),
  KEY `provinces_code_index` (`code`),
  KEY `provinces_autonomous_community_id_index` (`autonomous_community_id`),
  CONSTRAINT `provinces_autonomous_community_id_foreign` FOREIGN KEY (`autonomous_community_id`) REFERENCES `autonomous_communities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `residue_analyses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `residue_analyses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `plot_planting_id` bigint(20) unsigned DEFAULT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `analysis_date` date NOT NULL COMMENT 'Fecha del análisis/resultado',
  `sample_date` date DEFAULT NULL COMMENT 'Fecha de toma de muestra',
  `laboratory_name` varchar(255) NOT NULL COMMENT 'Nombre del laboratorio',
  `laboratory_accreditation` varchar(50) DEFAULT NULL COMMENT 'Nº acreditación del laboratorio (ENAC)',
  `sample_type` varchar(50) DEFAULT NULL COMMENT 'Tipo de muestra: uva, mosto, vino',
  `results` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array de resultados por producto analizado' CHECK (json_valid(`results`)),
  `overall_compliant` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'true si todos los resultados cumplen LMR',
  `certificate_file` varchar(255) DEFAULT NULL COMMENT 'Ruta del certificado de análisis PDF',
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `residue_analyses_plot_planting_id_foreign` (`plot_planting_id`),
  KEY `residue_analyses_viticulturist_id_foreign` (`viticulturist_id`),
  KEY `residue_analyses_campaign_id_active_index` (`campaign_id`,`active`),
  KEY `residue_analyses_analysis_date_index` (`analysis_date`),
  CONSTRAINT `residue_analyses_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `residue_analyses_plot_planting_id_foreign` FOREIGN KEY (`plot_planting_id`) REFERENCES `plot_plantings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `residue_analyses_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `residue_managements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `residue_managements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `plot_id` bigint(20) unsigned DEFAULT NULL,
  `plot_planting_id` bigint(20) unsigned DEFAULT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `practice_type` enum('incorporation','removal','burning','composting','biogas','sale','other') NOT NULL COMMENT 'Práctica de gestión aplicada',
  `material_type` enum('pruning_wood','grape_marc','vine_leaves','grass','other') NOT NULL COMMENT 'Tipo de residuo/material gestionado',
  `estimated_quantity` decimal(10,2) DEFAULT NULL COMMENT 'Cantidad estimada (kg o t)',
  `quantity_unit` varchar(10) DEFAULT NULL COMMENT 'Unidad: kg, t',
  `justification` text DEFAULT NULL COMMENT 'Justificación (obligatoria para quema)',
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `residue_managements_plot_id_foreign` (`plot_id`),
  KEY `residue_managements_plot_planting_id_foreign` (`plot_planting_id`),
  KEY `residue_managements_viticulturist_id_foreign` (`viticulturist_id`),
  KEY `residue_managements_campaign_id_active_index` (`campaign_id`,`active`),
  KEY `residue_managements_date_index` (`date`),
  CONSTRAINT `residue_managements_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `residue_managements_plot_id_foreign` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE SET NULL,
  CONSTRAINT `residue_managements_plot_planting_id_foreign` FOREIGN KEY (`plot_planting_id`) REFERENCES `plot_plantings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `residue_managements_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sanitary_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sanitary_registrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `registration_number` varchar(100) NOT NULL,
  `registration_type` varchar(50) NOT NULL DEFAULT 'rgseaa',
  `activity_description` varchar(300) DEFAULT NULL,
  `registration_date` date DEFAULT NULL,
  `renewal_date` date DEFAULT NULL,
  `issuing_authority` varchar(200) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sanitary_registrations_user_id_foreign` (`user_id`),
  CONSTRAINT `sanitary_registrations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
DROP TABLE IF EXISTS `sif_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sif_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `tipo_registro` enum('ALTA','ANULACION') NOT NULL DEFAULT 'ALTA',
  `csv` varchar(100) DEFAULT NULL COMMENT 'Código Seguro de Verificación AEAT',
  `huella` char(64) DEFAULT NULL COMMENT 'SHA-256 del registro — usada para encadenamiento',
  `registro_aeat` varchar(100) DEFAULT NULL,
  `hash_registro` varchar(64) DEFAULT NULL,
  `request_xml` mediumtext DEFAULT NULL,
  `response_xml` mediumtext DEFAULT NULL,
  `status` enum('WD','OK','ER') NOT NULL DEFAULT 'WD' COMMENT 'WD=Waiting, OK=Success, ER=Error',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sif_records_invoice_id_index` (`invoice_id`),
  KEY `sif_records_status_index` (`status`),
  CONSTRAINT `sif_records_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sigpac_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sigpac_code` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code_autonomous_community` varchar(10) DEFAULT NULL,
  `code_polygon` varchar(10) DEFAULT NULL,
  `code_plot` varchar(10) DEFAULT NULL,
  `code_enclosure` varchar(10) DEFAULT NULL,
  `code_aggregate` varchar(10) DEFAULT NULL,
  `code_province` varchar(10) DEFAULT NULL,
  `code_zone` varchar(10) DEFAULT NULL,
  `code_municipality` varchar(10) DEFAULT NULL,
  `code` varchar(30) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sigpac_code_code_index` (`code`),
  KEY `sigpac_code_code_municipality_index` (`code_municipality`),
  KEY `sigpac_code_code_autonomous_community_index` (`code_autonomous_community`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sigpac_use`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sigpac_use` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sigpac_use_code_unique` (`code`),
  KEY `sigpac_use_code_index` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `municipality_id` bigint(20) unsigned DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sites_user_id_foreign` (`user_id`),
  CONSTRAINT `sites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `soil_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `soil_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `soil_types_user_id_foreign` (`user_id`),
  CONSTRAINT `soil_types_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subcontractings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subcontractings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `plot_id` bigint(20) unsigned DEFAULT NULL,
  `campaign_id` bigint(20) unsigned DEFAULT NULL,
  `service_type` enum('harvesting','pruning','treatment','fertilization','irrigation','soil_work','transport','analysis','other') NOT NULL DEFAULT 'other',
  `company_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(255) DEFAULT NULL,
  `service_date` date NOT NULL,
  `service_end_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `invoiced` tinyint(1) NOT NULL DEFAULT 0,
  `invoice_number` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subcontractings_plot_id_foreign` (`plot_id`),
  KEY `subcontractings_campaign_id_foreign` (`campaign_id`),
  KEY `subcontractings_viticulturist_id_campaign_id_index` (`viticulturist_id`,`campaign_id`),
  KEY `subcontractings_service_date_index` (`service_date`),
  CONSTRAINT `subcontractings_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `subcontractings_plot_id_foreign` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE SET NULL,
  CONSTRAINT `subcontractings_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `plan_type` enum('monthly','yearly') NOT NULL DEFAULT 'monthly',
  `amount` decimal(10,2) NOT NULL,
  `status` enum('active','cancelled','expired') NOT NULL DEFAULT 'active',
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `paypal_subscription_id` varchar(255) DEFAULT NULL,
  `paypal_plan_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscriptions_paypal_subscription_id_unique` (`paypal_subscription_id`),
  KEY `subscriptions_user_id_index` (`user_id`),
  KEY `subscriptions_status_index` (`status`),
  KEY `subscriptions_ends_at_index` (`ends_at`),
  KEY `subscriptions_user_status_idx` (`user_id`,`status`),
  CONSTRAINT `subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `supervisor_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supervisor_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supervisor_id` bigint(20) unsigned NOT NULL,
  `winery_id` bigint(20) unsigned NOT NULL,
  `type` varchar(50) NOT NULL,
  `status` enum('draft','pending','in_review','approved','rejected','archived') NOT NULL DEFAULT 'draft',
  `title` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `response_notes` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supervisor_requests_supervisor_id_status_index` (`supervisor_id`,`status`),
  KEY `supervisor_requests_winery_id_status_index` (`winery_id`,`status`),
  CONSTRAINT `supervisor_requests_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supervisor_requests_winery_id_foreign` FOREIGN KEY (`winery_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `supervisor_viticulturist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supervisor_viticulturist` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supervisor_id` bigint(20) unsigned NOT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  `cuaderno_access` tinyint(1) NOT NULL DEFAULT 0,
  `cuaderno_granted_at` timestamp NULL DEFAULT NULL,
  `cuaderno_revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supervisor_viticulturist_supervisor_id_viticulturist_id_unique` (`supervisor_id`,`viticulturist_id`),
  KEY `supervisor_viticulturist_assigned_by_foreign` (`assigned_by`),
  KEY `supervisor_viticulturist_supervisor_id_index` (`supervisor_id`),
  KEY `supervisor_viticulturist_viticulturist_id_index` (`viticulturist_id`),
  CONSTRAINT `supervisor_viticulturist_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supervisor_viticulturist_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supervisor_viticulturist_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `supervisor_winery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supervisor_winery` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supervisor_id` bigint(20) unsigned NOT NULL,
  `winery_id` bigint(20) unsigned NOT NULL,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supervisor_winery_supervisor_id_winery_id_unique` (`supervisor_id`,`winery_id`),
  KEY `supervisor_winery_assigned_by_foreign` (`assigned_by`),
  KEY `supervisor_winery_supervisor_id_index` (`supervisor_id`),
  KEY `supervisor_winery_winery_id_index` (`winery_id`),
  CONSTRAINT `supervisor_winery_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supervisor_winery_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supervisor_winery_winery_id_foreign` FOREIGN KEY (`winery_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `vat_number` varchar(50) DEFAULT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'other',
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `suppliers_user_id_foreign` (`user_id`),
  CONSTRAINT `suppliers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `supplies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `warehouse_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Nombre del producto en el almacén',
  `commercial_name` varchar(255) DEFAULT NULL,
  `registration_number` varchar(50) DEFAULT NULL COMMENT 'Nº Registro MAPA del producto',
  `supply_type` enum('fertilizer','seed','postharvest','other') NOT NULL DEFAULT 'other',
  `unit_of_measurement` varchar(20) NOT NULL DEFAULT 'L' COMMENT 'L, kg, g, mL, ud',
  `initial_stock` decimal(10,3) NOT NULL DEFAULT 0.000,
  `current_stock` decimal(10,3) NOT NULL DEFAULT 0.000,
  `min_stock_alert` decimal(10,3) DEFAULT NULL COMMENT 'Alerta de stock mínimo',
  `expiry_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplies_viticulturist_id_active_index` (`viticulturist_id`,`active`),
  KEY `supplies_supply_type_index` (`supply_type`),
  KEY `supplies_warehouse_id_foreign` (`warehouse_id`),
  CONSTRAINT `supplies_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplies_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `supply_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supply_purchases` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supply_id` bigint(20) unsigned NOT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `quantity` decimal(10,3) NOT NULL COMMENT 'Cantidad comprada',
  `unit_of_measurement` varchar(20) NOT NULL COMMENT 'L, kg, g, mL, ud',
  `price_per_unit` decimal(10,4) DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supply_purchases_campaign_id_foreign` (`campaign_id`),
  KEY `supply_purchases_supply_id_index` (`supply_id`),
  KEY `supply_purchases_viticulturist_id_invoice_date_index` (`viticulturist_id`,`invoice_date`),
  CONSTRAINT `supply_purchases_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supply_purchases_supply_id_foreign` FOREIGN KEY (`supply_id`) REFERENCES `supplies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supply_purchases_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `support_ticket_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_ticket_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `comment` text NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Solo visible para admins',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_ticket_comments_user_id_foreign` (`user_id`),
  KEY `support_ticket_comments_ticket_id_index` (`ticket_id`),
  CONSTRAINT `support_ticket_comments_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_ticket_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_tickets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `type` enum('bug','feature','improvement','question') NOT NULL DEFAULT 'question',
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_tickets_assigned_to_foreign` (`assigned_to`),
  KEY `support_tickets_user_id_status_index` (`user_id`,`status`),
  KEY `support_tickets_type_index` (`type`),
  KEY `support_tickets_status_index` (`status`),
  CONSTRAINT `support_tickets_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `taxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `taxes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'IVA, IGIC, etc.',
  `code` varchar(255) NOT NULL COMMENT 'IVA, IGIC',
  `rate` decimal(5,2) NOT NULL COMMENT 'Tasa porcentual (21.00, 7.00, etc.)',
  `region` varchar(255) DEFAULT NULL COMMENT 'España, Canarias, etc.',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tax_code_rate_region_unique` (`code`,`rate`,`region`),
  KEY `taxes_code_index` (`code`),
  KEY `taxes_active_index` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `topographies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `topographies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topographies_user_id_foreign` (`user_id`),
  CONSTRAINT `topographies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `training_systems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_systems` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `training_systems_name_unique` (`name`),
  KEY `training_systems_user_id_foreign` (`user_id`),
  CONSTRAINT `training_systems_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `category` varchar(20) NOT NULL DEFAULT 'other',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `units_of_measurement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `units_of_measurement` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `type` varchar(255) NOT NULL COMMENT 'volume, weight, etc.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `units_of_measurement_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_abilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_abilities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `ability_id` bigint(20) unsigned NOT NULL,
  `granted_by` bigint(20) unsigned DEFAULT NULL,
  `granted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_abilities_user_id_ability_id_unique` (`user_id`,`ability_id`),
  KEY `user_abilities_ability_id_foreign` (`ability_id`),
  KEY `user_abilities_granted_by_foreign` (`granted_by`),
  KEY `user_abilities_user_id_index` (`user_id`),
  CONSTRAINT `user_abilities_ability_id_foreign` FOREIGN KEY (`ability_id`) REFERENCES `abilities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_abilities_granted_by_foreign` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_abilities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_catalog_hidden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_catalog_hidden` (
  `user_id` bigint(20) unsigned NOT NULL,
  `catalog_type` varchar(50) NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`catalog_type`,`item_id`),
  CONSTRAINT `user_catalog_hidden_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `province_id` bigint(20) unsigned DEFAULT NULL,
  `country` varchar(255) NOT NULL DEFAULT 'España',
  `phone` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_profiles_user_id_unique` (`user_id`),
  KEY `user_profiles_province_id_foreign` (`province_id`),
  CONSTRAINT `user_profiles_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_taxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_taxes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `tax_id` bigint(20) unsigned NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_taxes_user_id_tax_id_unique` (`user_id`,`tax_id`),
  KEY `user_taxes_tax_id_foreign` (`tax_id`),
  KEY `user_taxes_user_id_index` (`user_id`),
  CONSTRAINT `user_taxes_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_taxes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wine_sale_seq` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Contador para numeración de facturas de venta de vino',
  `grape_purchase_seq` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Contador para numeración de liquidaciones de vendimia',
  `harvest_sale_seq` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Contador para numeración de facturas de venta de uva (viticultor)',
  `name` varchar(255) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `is_beta_user` tinyint(1) NOT NULL DEFAULT 1,
  `beta_ends_at` timestamp NULL DEFAULT NULL,
  `beta_access_granted` tinyint(1) NOT NULL DEFAULT 0,
  `invitation_sent_at` timestamp NULL DEFAULT NULL,
  `invitation_token` varchar(80) DEFAULT NULL,
  `invitation_expires_at` timestamp NULL DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `password_must_reset` tinyint(1) NOT NULL DEFAULT 0,
  `can_login` tinyint(1) NOT NULL DEFAULT 1,
  `role` varchar(255) NOT NULL DEFAULT 'viticulturist',
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `compra_uva_externa` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_dni_unique` (`dni`),
  UNIQUE KEY `users_invitation_token_unique` (`invitation_token`),
  KEY `users_role_index` (`role`),
  KEY `users_email_index` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_role_email` (`role`,`email`),
  KEY `users_organization_id_index` (`organization_id`),
  CONSTRAINT `users_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `valleys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `valleys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description_valley` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `valleys_user_id_foreign` (`user_id`),
  CONSTRAINT `valleys_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `viticultor_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `viticultor_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticultor_id` bigint(20) unsigned NOT NULL,
  `organization_id` bigint(20) unsigned NOT NULL,
  `assigned_by_org_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `cuaderno_access` tinyint(1) NOT NULL DEFAULT 0,
  `cuaderno_granted_at` timestamp NULL DEFAULT NULL,
  `cuaderno_revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `viticultor_assignments_viticultor_id_organization_id_unique` (`viticultor_id`,`organization_id`),
  KEY `viticultor_assignments_organization_id_foreign` (`organization_id`),
  KEY `viticultor_assignments_assigned_by_org_id_foreign` (`assigned_by_org_id`),
  KEY `viticultor_assignments_assigned_by_user_id_foreign` (`assigned_by_user_id`),
  CONSTRAINT `viticultor_assignments_assigned_by_org_id_foreign` FOREIGN KEY (`assigned_by_org_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `viticultor_assignments_assigned_by_user_id_foreign` FOREIGN KEY (`assigned_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `viticultor_assignments_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `viticultor_assignments_viticultor_id_foreign` FOREIGN KEY (`viticultor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `viticulturist_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `viticulturist_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `default_limit_kg_per_ha` decimal(10,3) DEFAULT NULL,
  `degree_day_base` decimal(4,1) NOT NULL DEFAULT 10.0 COMMENT 'Temperatura base para cálculo de grados-día (°C)',
  `document_prefix_activity` varchar(20) NOT NULL DEFAULT 'ACT' COMMENT 'Prefijo numeración actividades',
  `document_prefix_harvest` varchar(20) NOT NULL DEFAULT 'VND' COMMENT 'Prefijo numeración vendimias',
  `legal_text_fieldbook` text DEFAULT NULL COMMENT 'Texto legal al pie del PDF del cuaderno',
  `notify_harvest_alerts` tinyint(1) NOT NULL DEFAULT 1,
  `notify_activity_alerts` tinyint(1) NOT NULL DEFAULT 1,
  `default_irpf_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `viticulturist_settings_viticulturist_id_unique` (`viticulturist_id`),
  CONSTRAINT `viticulturist_settings_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `warehouses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `warehouses_user_id_active_index` (`user_id`,`active`),
  CONSTRAINT `warehouses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `water_concessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `water_concessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned DEFAULT NULL,
  `concession_type` enum('superficial','subterranea','comunidad_regantes','otro') NOT NULL,
  `concession_number` varchar(100) DEFAULT NULL,
  `water_body` varchar(255) NOT NULL,
  `authority` varchar(255) NOT NULL,
  `concession_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `max_volume_m3` decimal(12,3) NOT NULL,
  `used_volume_m3` decimal(12,3) DEFAULT NULL,
  `surface_ha` decimal(8,4) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `water_concessions_viticulturist_id_foreign` (`viticulturist_id`),
  KEY `water_concessions_campaign_id_foreign` (`campaign_id`),
  CONSTRAINT `water_concessions_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `water_concessions_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_additives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_additives` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wine_id` bigint(20) unsigned NOT NULL,
  `wine_process_detail_id` bigint(20) unsigned DEFAULT NULL,
  `winery_supply_id` bigint(20) unsigned DEFAULT NULL,
  `oenologist_id` bigint(20) unsigned DEFAULT NULL,
  `unit_of_measurement_id` bigint(20) unsigned DEFAULT NULL,
  `additive_name` varchar(200) NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `application_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wine_additives_winery_supply_id_foreign` (`winery_supply_id`),
  KEY `wine_additives_oenologist_id_foreign` (`oenologist_id`),
  KEY `wine_additives_unit_of_measurement_id_foreign` (`unit_of_measurement_id`),
  KEY `wine_additives_created_by_foreign` (`created_by`),
  KEY `wine_additives_wine_id_application_date_index` (`wine_id`,`application_date`),
  KEY `wine_additives_wine_process_detail_id_index` (`wine_process_detail_id`),
  CONSTRAINT `wine_additives_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_additives_oenologist_id_foreign` FOREIGN KEY (`oenologist_id`) REFERENCES `oenologists` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_additives_unit_of_measurement_id_foreign` FOREIGN KEY (`unit_of_measurement_id`) REFERENCES `units_of_measurement` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_additives_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_additives_wine_process_detail_id_foreign` FOREIGN KEY (`wine_process_detail_id`) REFERENCES `wine_process_details` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_additives_winery_supply_id_foreign` FOREIGN KEY (`winery_supply_id`) REFERENCES `winery_supplies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_analyses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_analyses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `laboratory` varchar(200) DEFAULT NULL,
  `sample_reference` varchar(100) DEFAULT NULL,
  `alcoholic_strength` decimal(5,2) DEFAULT NULL,
  `free_so2` decimal(6,1) DEFAULT NULL,
  `total_so2` decimal(6,1) DEFAULT NULL,
  `wine_id` bigint(20) unsigned DEFAULT NULL,
  `container_id` bigint(20) unsigned DEFAULT NULL,
  `oenologist_id` bigint(20) unsigned DEFAULT NULL,
  `analysis_date` date NOT NULL,
  `analysis_type` enum('standard','complete','organic','custom') NOT NULL DEFAULT 'standard',
  `laboratory_name` varchar(255) DEFAULT NULL,
  `alcohol` decimal(5,2) DEFAULT NULL COMMENT '% vol',
  `residual_sugar` decimal(6,2) DEFAULT NULL COMMENT 'g/L',
  `total_acidity` decimal(5,2) DEFAULT NULL COMMENT 'g/L tartárico',
  `volatile_acidity` decimal(5,2) DEFAULT NULL COMMENT 'g/L acético',
  `ph` decimal(4,2) DEFAULT NULL,
  `so2_free` decimal(6,2) DEFAULT NULL COMMENT 'mg/L SO₂ libre',
  `so2_total` decimal(6,2) DEFAULT NULL COMMENT 'mg/L SO₂ total',
  `density` decimal(7,4) DEFAULT NULL COMMENT 'g/cm³',
  `turbidity` decimal(6,2) DEFAULT NULL COMMENT 'NTU',
  `color_intensity` decimal(5,3) DEFAULT NULL,
  `malic_acid` decimal(5,2) DEFAULT NULL COMMENT 'g/L - seguimiento FML',
  `document` varchar(255) DEFAULT NULL COMMENT 'Ruta PDF del análisis',
  `notes` text DEFAULT NULL,
  `result` varchar(20) NOT NULL DEFAULT 'pending',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wine_analyses_created_by_foreign` (`created_by`),
  KEY `wine_analyses_wine_id_analysis_date_index` (`wine_id`,`analysis_date`),
  KEY `wine_analyses_container_id_index` (`container_id`),
  KEY `wine_analyses_oenologist_id_foreign` (`oenologist_id`),
  KEY `wine_analyses_user_id_index` (`user_id`),
  CONSTRAINT `wine_analyses_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_analyses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_analyses_oenologist_id_foreign` FOREIGN KEY (`oenologist_id`) REFERENCES `oenologists` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_analyses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_analyses_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_bottling_supplies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_bottling_supplies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wine_bottling_id` bigint(20) unsigned NOT NULL,
  `winery_supply_id` bigint(20) unsigned DEFAULT NULL,
  `supply_name` varchar(255) NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit_of_measurement_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wine_bottling_supplies_wine_bottling_id_foreign` (`wine_bottling_id`),
  KEY `wine_bottling_supplies_winery_supply_id_foreign` (`winery_supply_id`),
  KEY `wine_bottling_supplies_unit_of_measurement_id_foreign` (`unit_of_measurement_id`),
  CONSTRAINT `wine_bottling_supplies_unit_of_measurement_id_foreign` FOREIGN KEY (`unit_of_measurement_id`) REFERENCES `units_of_measurement` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_bottling_supplies_wine_bottling_id_foreign` FOREIGN KEY (`wine_bottling_id`) REFERENCES `wine_bottlings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_bottling_supplies_winery_supply_id_foreign` FOREIGN KEY (`winery_supply_id`) REFERENCES `winery_supplies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_bottlings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_bottlings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `wine_id` bigint(20) unsigned NOT NULL,
  `container_id` bigint(20) unsigned DEFAULT NULL,
  `wine_process_detail_id` bigint(20) unsigned DEFAULT NULL,
  `product_lot_id` bigint(20) unsigned DEFAULT NULL,
  `oenologist_id` bigint(20) unsigned DEFAULT NULL,
  `bottling_date` date NOT NULL,
  `bottle_format` varchar(20) NOT NULL,
  `quantity_bottles` int(10) unsigned NOT NULL,
  `quantity_liters` decimal(10,3) NOT NULL,
  `lot_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wine_bottlings_user_id_foreign` (`user_id`),
  KEY `wine_bottlings_wine_id_foreign` (`wine_id`),
  KEY `wine_bottlings_wine_process_detail_id_foreign` (`wine_process_detail_id`),
  KEY `wine_bottlings_product_lot_id_foreign` (`product_lot_id`),
  KEY `wine_bottlings_oenologist_id_foreign` (`oenologist_id`),
  KEY `wine_bottlings_created_by_foreign` (`created_by`),
  KEY `wine_bottlings_container_id_foreign` (`container_id`),
  CONSTRAINT `wine_bottlings_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_bottlings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_bottlings_oenologist_id_foreign` FOREIGN KEY (`oenologist_id`) REFERENCES `oenologists` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_bottlings_product_lot_id_foreign` FOREIGN KEY (`product_lot_id`) REFERENCES `wine_lots` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_bottlings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_bottlings_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_bottlings_wine_process_detail_id_foreign` FOREIGN KEY (`wine_process_detail_id`) REFERENCES `wine_process_details` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_fermentation_controls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_fermentation_controls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wine_id` bigint(20) unsigned NOT NULL,
  `container_id` bigint(20) unsigned NOT NULL,
  `control_date` datetime NOT NULL,
  `temperature` decimal(5,2) DEFAULT NULL COMMENT '°C',
  `brix_degree` decimal(5,2) DEFAULT NULL COMMENT '°Brix',
  `baume_degree` decimal(5,2) DEFAULT NULL COMMENT '°Baumé',
  `density` decimal(7,4) DEFAULT NULL COMMENT 'g/L',
  `ph` decimal(4,2) DEFAULT NULL,
  `volatile_acidity` decimal(5,2) DEFAULT NULL COMMENT 'g/L ác. acético',
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wine_fermentation_controls_container_id_foreign` (`container_id`),
  KEY `wine_fermentation_controls_created_by_foreign` (`created_by`),
  KEY `wine_fermentation_controls_wine_id_container_id_index` (`wine_id`,`container_id`),
  KEY `wine_fermentation_controls_wine_id_control_date_index` (`wine_id`,`control_date`),
  CONSTRAINT `wine_fermentation_controls_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`),
  CONSTRAINT `wine_fermentation_controls_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_fermentation_controls_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_harvests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_harvests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wine_id` bigint(20) unsigned NOT NULL,
  `harvest_id` bigint(20) unsigned NOT NULL,
  `quantity_kg` decimal(12,3) NOT NULL COMMENT 'Kg aportados a este lote',
  `percentage` decimal(5,2) DEFAULT NULL COMMENT '% sobre el total del lote',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wine_harvests_wine_id_harvest_id_unique` (`wine_id`,`harvest_id`),
  KEY `wine_harvests_wine_id_index` (`wine_id`),
  KEY `wine_harvests_harvest_id_index` (`harvest_id`),
  CONSTRAINT `wine_harvests_harvest_id_foreign` FOREIGN KEY (`harvest_id`) REFERENCES `harvests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_harvests_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_labelings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_labelings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `wine_id` bigint(20) unsigned NOT NULL,
  `wine_bottling_id` bigint(20) unsigned DEFAULT NULL,
  `label_batch_id` bigint(20) unsigned DEFAULT NULL,
  `labeling_date` date NOT NULL,
  `quantity_labeled` int(10) unsigned NOT NULL,
  `from_number` bigint(20) unsigned DEFAULT NULL,
  `to_number` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wine_labelings_user_id_foreign` (`user_id`),
  KEY `wine_labelings_wine_id_foreign` (`wine_id`),
  KEY `wine_labelings_wine_bottling_id_foreign` (`wine_bottling_id`),
  KEY `wine_labelings_label_batch_id_foreign` (`label_batch_id`),
  KEY `wine_labelings_created_by_foreign` (`created_by`),
  CONSTRAINT `wine_labelings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_labelings_label_batch_id_foreign` FOREIGN KEY (`label_batch_id`) REFERENCES `label_batches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_labelings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_labelings_wine_bottling_id_foreign` FOREIGN KEY (`wine_bottling_id`) REFERENCES `wine_bottlings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_labelings_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_losses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_losses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wine_id` bigint(20) unsigned NOT NULL,
  `container_id` bigint(20) unsigned DEFAULT NULL,
  `loss_type` enum('evaporation','filtration','sampling','spillage','other') NOT NULL DEFAULT 'evaporation',
  `loss_authorization` enum('authorized','processing','extraordinary','quality') NOT NULL DEFAULT 'authorized',
  `regulatory_reference` varchar(100) DEFAULT NULL COMMENT 'Nº expediente / referencia documental SILICIE',
  `quantity` decimal(12,3) NOT NULL,
  `unit_of_measurement_id` bigint(20) unsigned NOT NULL,
  `loss_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wine_losses_unit_of_measurement_id_foreign` (`unit_of_measurement_id`),
  KEY `wine_losses_created_by_foreign` (`created_by`),
  KEY `wine_losses_wine_id_index` (`wine_id`),
  KEY `wine_losses_container_id_index` (`container_id`),
  KEY `wine_losses_loss_date_index` (`loss_date`),
  CONSTRAINT `wine_losses_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_losses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_losses_unit_of_measurement_id_foreign` FOREIGN KEY (`unit_of_measurement_id`) REFERENCES `units_of_measurement` (`id`),
  CONSTRAINT `wine_losses_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_lot_grape_varieties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_lot_grape_varieties` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wine_lot_id` bigint(20) unsigned NOT NULL,
  `grape_variety_id` bigint(20) unsigned NOT NULL,
  `percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wine_lot_grape_varieties_wine_lot_id_grape_variety_id_unique` (`wine_lot_id`,`grape_variety_id`),
  KEY `wine_lot_grape_varieties_grape_variety_id_foreign` (`grape_variety_id`),
  KEY `wine_lot_grape_varieties_wine_lot_id_index` (`wine_lot_id`),
  CONSTRAINT `wine_lot_grape_varieties_grape_variety_id_foreign` FOREIGN KEY (`grape_variety_id`) REFERENCES `grape_varieties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_lot_grape_varieties_wine_lot_id_foreign` FOREIGN KEY (`wine_lot_id`) REFERENCES `wine_lots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_lot_taxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_lot_taxes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wine_lot_id` bigint(20) unsigned NOT NULL,
  `tax_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wine_lot_taxes_wine_lot_id_tax_id_unique` (`wine_lot_id`,`tax_id`),
  KEY `wine_lot_taxes_tax_id_foreign` (`tax_id`),
  KEY `wine_lot_taxes_wine_lot_id_index` (`wine_lot_id`),
  CONSTRAINT `wine_lot_taxes_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_lot_taxes_wine_lot_id_foreign` FOREIGN KEY (`wine_lot_id`) REFERENCES `wine_lots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_lots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_lots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `wine_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `vintage` smallint(5) unsigned DEFAULT NULL COMMENT 'Año de vendimia',
  `wine_type` enum('tinto','blanco','rosado','espumoso','otro') NOT NULL DEFAULT 'tinto',
  `aging_type` varchar(30) DEFAULT NULL,
  `agingtime` smallint(5) unsigned DEFAULT NULL,
  `alcohol` decimal(5,2) DEFAULT NULL,
  `residual_sugar` decimal(6,2) DEFAULT NULL,
  `total_acidity` decimal(5,2) DEFAULT NULL,
  `volatile_acidity` decimal(5,2) DEFAULT NULL,
  `ph` decimal(4,2) DEFAULT NULL,
  `ean` varchar(14) DEFAULT NULL,
  `bottle_format` varchar(20) DEFAULT NULL,
  `units_per_case` tinyint(3) unsigned DEFAULT NULL,
  `cost_price` decimal(10,4) DEFAULT NULL,
  `winemaker` varchar(255) DEFAULT NULL,
  `harvest_method` varchar(20) DEFAULT NULL,
  `fermentation_vessel` varchar(255) DEFAULT NULL,
  `oak_type` varchar(255) DEFAULT NULL,
  `oak_months` smallint(5) unsigned DEFAULT NULL,
  `vine_age` smallint(5) unsigned DEFAULT NULL,
  `altitude` smallint(5) unsigned DEFAULT NULL,
  `soil_type` varchar(255) DEFAULT NULL,
  `is_vegan` tinyint(1) NOT NULL DEFAULT 0,
  `is_biodynamic` tinyint(1) NOT NULL DEFAULT 0,
  `sulfites` tinyint(1) NOT NULL DEFAULT 0,
  `ecological` tinyint(1) NOT NULL DEFAULT 0,
  `awards_notes` text DEFAULT NULL,
  `production_quantity` int(10) unsigned DEFAULT NULL,
  `bottling_date` date DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `quantity` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Cantidad total producida',
  `initial_quantity` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Stock inicial al crear el lote. Inmutable.',
  `unit` enum('litros','botellas','cajas') NOT NULL DEFAULT 'litros',
  `available_quantity` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Cantidad disponible para venta',
  `reserved_quantity` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Comprometido en facturas activas, pendiente de entrega',
  `sold_quantity` decimal(10,3) NOT NULL DEFAULT 0.000 COMMENT 'Vendido y entregado definitivamente',
  `price_per_unit` decimal(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'Precio unitario por defecto',
  `notes` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `pairing` text DEFAULT NULL,
  `tasting_notes` text DEFAULT NULL,
  `consumption_recommendation` text DEFAULT NULL,
  `recommended_temperature_min` decimal(4,1) DEFAULT NULL,
  `recommended_temperature_max` decimal(4,1) DEFAULT NULL,
  `tags` varchar(500) DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wine_lots_user_id_index` (`user_id`),
  KEY `wine_lots_archived_index` (`archived`),
  KEY `wine_lots_wine_id_foreign` (`wine_id`),
  CONSTRAINT `wine_lots_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_lots_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_process_detail_containers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_process_detail_containers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wine_process_detail_id` bigint(20) unsigned NOT NULL,
  `container_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(12,3) DEFAULT NULL COMMENT 'Cantidad en este contenedor',
  `unit_of_measurement_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_process_container` (`wine_process_detail_id`,`container_id`),
  KEY `wine_process_detail_containers_unit_of_measurement_id_foreign` (`unit_of_measurement_id`),
  KEY `wine_process_detail_containers_container_id_index` (`container_id`),
  CONSTRAINT `wine_process_detail_containers_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_process_detail_containers_unit_of_measurement_id_foreign` FOREIGN KEY (`unit_of_measurement_id`) REFERENCES `units_of_measurement` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_process_detail_containers_wine_process_detail_id_foreign` FOREIGN KEY (`wine_process_detail_id`) REFERENCES `wine_process_details` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_process_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_process_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wine_id` bigint(20) unsigned NOT NULL,
  `container_id` bigint(20) unsigned DEFAULT NULL,
  `process_type` enum('destemming_crushing','pressing','settling','fermentation','maceration','malolactic','aging','racking','blending','fining','filtration','cold_stabilization','bottling','enrichment','concentration','acidification','desacidification','sulfitation','other') NOT NULL DEFAULT 'fermentation',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `quantity` decimal(12,3) DEFAULT NULL COMMENT 'Volumen/peso procesado',
  `unit_of_measurement_id` bigint(20) unsigned DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `oenologist_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wine_process_details_container_id_foreign` (`container_id`),
  KEY `wine_process_details_unit_of_measurement_id_foreign` (`unit_of_measurement_id`),
  KEY `wine_process_details_created_by_foreign` (`created_by`),
  KEY `wine_process_details_wine_id_process_type_index` (`wine_id`,`process_type`),
  KEY `wine_process_details_wine_id_start_date_index` (`wine_id`,`start_date`),
  KEY `wine_process_details_oenologist_id_foreign` (`oenologist_id`),
  CONSTRAINT `wine_process_details_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_process_details_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_process_details_oenologist_id_foreign` FOREIGN KEY (`oenologist_id`) REFERENCES `oenologists` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_process_details_unit_of_measurement_id_foreign` FOREIGN KEY (`unit_of_measurement_id`) REFERENCES `units_of_measurement` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_process_details_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_stock_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_stock_snapshots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `wine_id` bigint(20) unsigned NOT NULL,
  `snapshot_date` date NOT NULL,
  `quantity_liters` decimal(12,3) NOT NULL DEFAULT 0.000,
  `container_count` smallint(5) unsigned NOT NULL DEFAULT 0,
  `alcohol_percentage` decimal(5,2) DEFAULT NULL,
  `vintage` varchar(4) DEFAULT NULL,
  `wine_type` varchar(30) DEFAULT NULL,
  `is_must` tinyint(1) NOT NULL DEFAULT 0,
  `observations` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wine_stock_snapshots_user_id_snapshot_date_wine_id_unique` (`user_id`,`snapshot_date`,`wine_id`),
  KEY `wine_stock_snapshots_created_by_foreign` (`created_by`),
  KEY `wine_stock_snapshots_user_id_snapshot_date_index` (`user_id`,`snapshot_date`),
  KEY `wine_stock_snapshots_wine_id_index` (`wine_id`),
  CONSTRAINT `wine_stock_snapshots_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_stock_snapshots_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_stock_snapshots_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_subproducts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_subproducts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `wine_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(30) NOT NULL,
  `subproduct_date` date NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit_of_measurement_id` bigint(20) unsigned DEFAULT NULL,
  `destination` varchar(30) NOT NULL,
  `destination_name` varchar(200) DEFAULT NULL,
  `lot_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wine_subproducts_wine_id_foreign` (`wine_id`),
  KEY `wine_subproducts_unit_of_measurement_id_foreign` (`unit_of_measurement_id`),
  KEY `wine_subproducts_created_by_foreign` (`created_by`),
  KEY `wine_subproducts_user_id_subproduct_date_index` (`user_id`,`subproduct_date`),
  KEY `wine_subproducts_user_id_type_index` (`user_id`,`type`),
  CONSTRAINT `wine_subproducts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `wine_subproducts_unit_of_measurement_id_foreign` FOREIGN KEY (`unit_of_measurement_id`) REFERENCES `units_of_measurement` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_subproducts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_subproducts_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_tasting_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_tasting_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `wine_id` bigint(20) unsigned NOT NULL,
  `oenologist_id` bigint(20) unsigned DEFAULT NULL,
  `evaluation_date` date NOT NULL,
  `evaluator_name` varchar(255) DEFAULT NULL,
  `visual_color` varchar(100) DEFAULT NULL,
  `visual_clarity` enum('brilliant','clear','slightly_hazy','hazy') DEFAULT NULL,
  `visual_intensity` enum('pale','medium','deep','very_deep') DEFAULT NULL,
  `aroma_intensity` enum('light','medium','pronounced','complex') DEFAULT NULL,
  `aroma_descriptors` text DEFAULT NULL,
  `palate_acidity` enum('low','medium_minus','medium','medium_plus','high') DEFAULT NULL,
  `palate_tannins` enum('low','medium_minus','medium','medium_plus','high') DEFAULT NULL,
  `palate_body` enum('light','medium','full') DEFAULT NULL,
  `palate_finish` enum('short','medium','long') DEFAULT NULL,
  `overall_score` decimal(4,1) DEFAULT NULL,
  `overall_conclusion` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wine_tasting_notes_user_id_foreign` (`user_id`),
  KEY `wine_tasting_notes_wine_id_foreign` (`wine_id`),
  KEY `wine_tasting_notes_oenologist_id_foreign` (`oenologist_id`),
  KEY `wine_tasting_notes_created_by_foreign` (`created_by`),
  CONSTRAINT `wine_tasting_notes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_tasting_notes_oenologist_id_foreign` FOREIGN KEY (`oenologist_id`) REFERENCES `oenologists` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_tasting_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wine_tasting_notes_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wine_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wine_transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wine_id` bigint(20) unsigned NOT NULL,
  `from_container_id` bigint(20) unsigned DEFAULT NULL,
  `to_container_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `unit_of_measurement_id` bigint(20) unsigned NOT NULL,
  `transfer_type` enum('racking','blending','top_up','other') NOT NULL DEFAULT 'racking',
  `transfer_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `oenologist_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wine_transfers_unit_of_measurement_id_foreign` (`unit_of_measurement_id`),
  KEY `wine_transfers_created_by_foreign` (`created_by`),
  KEY `wine_transfers_wine_id_index` (`wine_id`),
  KEY `wine_transfers_from_container_id_index` (`from_container_id`),
  KEY `wine_transfers_to_container_id_index` (`to_container_id`),
  KEY `wine_transfers_transfer_date_index` (`transfer_date`),
  KEY `wine_transfers_oenologist_id_foreign` (`oenologist_id`),
  CONSTRAINT `wine_transfers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_transfers_from_container_id_foreign` FOREIGN KEY (`from_container_id`) REFERENCES `containers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_transfers_oenologist_id_foreign` FOREIGN KEY (`oenologist_id`) REFERENCES `oenologists` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wine_transfers_to_container_id_foreign` FOREIGN KEY (`to_container_id`) REFERENCES `containers` (`id`),
  CONSTRAINT `wine_transfers_unit_of_measurement_id_foreign` FOREIGN KEY (`unit_of_measurement_id`) REFERENCES `units_of_measurement` (`id`),
  CONSTRAINT `wine_transfers_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `winery_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `winery_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `document_type` varchar(50) NOT NULL DEFAULT 'other',
  `reference_number` varchar(100) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `issuing_authority` varchar(200) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `winery_documents_user_id_foreign` (`user_id`),
  CONSTRAINT `winery_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `winery_supplies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `winery_supplies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `commercial_name` varchar(255) DEFAULT NULL,
  `supply_type` enum('cleaning','sulfiting','fining','filtration','yeast','nutrient','enzyme','tannin','acid','sugar','analysis','packaging','other') NOT NULL DEFAULT 'other',
  `unit_of_measurement_id` bigint(20) unsigned DEFAULT NULL,
  `current_stock` decimal(12,3) DEFAULT NULL,
  `min_stock_alert` decimal(12,3) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `winery_supplies_unit_of_measurement_id_foreign` (`unit_of_measurement_id`),
  KEY `winery_supplies_user_id_active_index` (`user_id`,`active`),
  KEY `winery_supplies_user_id_supply_type_index` (`user_id`,`supply_type`),
  CONSTRAINT `winery_supplies_unit_of_measurement_id_foreign` FOREIGN KEY (`unit_of_measurement_id`) REFERENCES `units_of_measurement` (`id`) ON DELETE SET NULL,
  CONSTRAINT `winery_supplies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `winery_viticulturist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `winery_viticulturist` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `winery_id` bigint(20) unsigned DEFAULT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  `source` varchar(50) NOT NULL DEFAULT 'own',
  `supervisor_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `cuaderno_access` tinyint(1) NOT NULL DEFAULT 0,
  `cuaderno_granted_at` timestamp NULL DEFAULT NULL,
  `cuaderno_revoked_at` timestamp NULL DEFAULT NULL,
  `parent_viticulturist_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `winery_viticulturist_winery_id_viticulturist_id_unique` (`winery_id`,`viticulturist_id`),
  KEY `winery_viticulturist_winery_id_index` (`winery_id`),
  KEY `winery_viticulturist_viticulturist_id_index` (`viticulturist_id`),
  KEY `winery_viticulturist_source_index` (`source`),
  KEY `idx_winery_viticulturist_parent` (`parent_viticulturist_id`),
  KEY `idx_winery_viticulturist_source` (`source`),
  KEY `idx_winery_viticulturist_assigned_by` (`assigned_by`),
  KEY `idx_winery_viticulturist_supervisor` (`supervisor_id`),
  KEY `idx_winery_viticulturist_created` (`viticulturist_id`,`source`,`assigned_by`),
  KEY `idx_winery_viticulturist_visibility` (`viticulturist_id`,`winery_id`,`source`),
  KEY `idx_wv_winery` (`winery_id`),
  KEY `idx_wv_viticulturist` (`viticulturist_id`),
  KEY `idx_wv_supervisor` (`supervisor_id`),
  KEY `idx_wv_parent` (`parent_viticulturist_id`),
  KEY `idx_wv_source` (`source`),
  KEY `wv_winery_idx` (`winery_id`),
  KEY `wv_viticulturist_idx` (`viticulturist_id`),
  KEY `wv_source_idx` (`source`),
  KEY `wv_parent_viticulturist_idx` (`parent_viticulturist_id`),
  KEY `wv_supervisor_idx` (`supervisor_id`),
  KEY `idx_wv_parent_source` (`parent_viticulturist_id`,`source`),
  KEY `idx_wv_supervisor_source` (`supervisor_id`,`source`),
  KEY `idx_wv_winery_source` (`winery_id`,`source`),
  CONSTRAINT `winery_viticulturist_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `winery_viticulturist_parent_viticulturist_id_foreign` FOREIGN KEY (`parent_viticulturist_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `winery_viticulturist_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `winery_viticulturist_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `winery_viticulturist_winery_id_foreign` FOREIGN KEY (`winery_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `winery_yield_forecasts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `winery_yield_forecasts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `winery_id` bigint(20) unsigned NOT NULL,
  `viticulturist_id` bigint(20) unsigned NOT NULL,
  `plot_planting_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `vintage_year` smallint(5) unsigned NOT NULL,
  `estimated_kg` decimal(10,3) NOT NULL,
  `estimation_date` date NOT NULL,
  `status` enum('draft','confirmed') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wyf_unique_winery_planting_campaign` (`winery_id`,`plot_planting_id`,`campaign_id`),
  KEY `winery_yield_forecasts_viticulturist_id_foreign` (`viticulturist_id`),
  KEY `winery_yield_forecasts_plot_planting_id_foreign` (`plot_planting_id`),
  KEY `winery_yield_forecasts_campaign_id_foreign` (`campaign_id`),
  KEY `winery_yield_forecasts_winery_id_vintage_year_index` (`winery_id`,`vintage_year`),
  CONSTRAINT `winery_yield_forecasts_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `winery_yield_forecasts_plot_planting_id_foreign` FOREIGN KEY (`plot_planting_id`) REFERENCES `plot_plantings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `winery_yield_forecasts_viticulturist_id_foreign` FOREIGN KEY (`viticulturist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `winery_yield_forecasts_winery_id_foreign` FOREIGN KEY (`winery_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `oenologist_id` bigint(20) unsigned DEFAULT NULL,
  `is_must` tinyint(1) NOT NULL DEFAULT 0,
  `is_organic` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `vintage` smallint(6) DEFAULT NULL COMMENT 'Añada',
  `wine_type` enum('red','white','rose','sparkling','fortified','sweet','semi_sweet','other') NOT NULL DEFAULT 'red',
  `aging_type` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `status` enum('in_progress','aged','bottled','sold','cancelled') NOT NULL DEFAULT 'in_progress',
  `variety` varchar(255) DEFAULT NULL COMMENT 'Variedad o descripción del coupage',
  `volume_liters` decimal(12,3) DEFAULT NULL COMMENT 'Volumen total en litros',
  `initial_quantity_kg` decimal(12,3) DEFAULT NULL COMMENT 'Kg de uva de entrada (trazabilidad)',
  `internal_code` varchar(255) DEFAULT NULL COMMENT 'Código interno de bodega',
  `trace_token` char(36) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wines_trace_token_unique` (`trace_token`),
  KEY `wines_user_id_status_index` (`user_id`,`status`),
  KEY `wines_user_id_vintage_index` (`user_id`,`vintage`),
  KEY `wines_oenologist_id_foreign` (`oenologist_id`),
  CONSTRAINT `wines_oenologist_id_foreign` FOREIGN KEY (`oenologist_id`) REFERENCES `oenologists` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wines_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2025_12_15_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2025_12_15_000100_create_autonomous_communities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2025_12_15_000200_create_provinces_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_12_15_000300_create_municipalities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_12_15_000400_create_crews_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_12_15_000500_create_crew_members_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_12_15_000600_create_winery_viticulturist_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_12_16_125727_create_supervisor_winery_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_12_16_125737_create_supervisor_viticulturist_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_12_16_125800_create_sigpac_code_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_12_16_181532_create_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_12_16_181533_create_plot_sigpac_code_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_12_16_185034_create_sigpac_use_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_12_16_185132_create_plot_sigpac_use_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_12_17_095107_create_phytosanitary_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_12_17_095118_create_agricultural_activities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_12_17_095127_create_phytosanitary_treatments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_12_17_095140_create_fertilizations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_12_17_095149_create_irrigations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_12_17_095157_create_cultural_works_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_12_17_095204_create_observations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_12_17_103333_create_campaigns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_12_17_103345_add_campaign_id_to_agricultural_activities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_12_17_122825_make_crew_id_nullable_in_crew_members_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_12_17_131633_make_winery_id_nullable_in_crews_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_12_17_133655_make_winery_id_nullable_in_winery_viticulturist_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_12_17_140000_add_indexes_to_winery_viticulturist_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_12_17_140100_fix_unique_constraint_winery_viticulturist',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_12_17_150000_add_composite_indexes_to_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_12_17_163820_create_machinery_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_12_17_190804_create_user_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_12_17_190851_create_subscriptions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_12_17_190917_create_payments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_12_17_220419_add_performance_indexes_to_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_12_17_220930_add_profile_image_to_user_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_12_17_222020_change_province_to_province_id_in_user_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_12_17_225451_create_plot_plantings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_12_17_225641_create_grape_varieties_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_12_17_233647_add_password_must_reset_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_12_18_000001_add_can_login_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_12_18_000010_optimize_pivot_indexes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_12_18_101200_drop_winery_id_from_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_12_18_130500_make_winery_id_nullable_in_viticulturist_hierarchy_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_12_18_140500_add_crew_member_id_to_agricultural_activities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_12_18_150500_create_machinery_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_12_18_151000_add_machinery_type_id_to_machinery_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_12_18_152500_create_training_systems_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2025_12_18_153000_add_training_system_id_to_plot_plantings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_12_19_111747_drop_viticulturist_hierarchy_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_12_19_173822_update_sigpac_code_table_add_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2025_12_19_173849_create_plot_geometry_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2025_12_19_173850_create_multiple_plot_sigpac_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2025_12_19_175414_rename_multiple_plot_sigpac_to_multipart',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2025_12_19_185955_add_safety_interval_to_phytosanitary_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2025_12_19_203000_create_harvests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2025_12_19_213419_create_harvest_containers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2025_12_19_213429_create_estimated_yields_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2025_12_19_221052_add_harvest_limit_kg_to_plot_plantings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2025_12_19_223202_change_harvest_decimal_precision_to_3',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2025_12_19_223743_add_name_to_plot_plantings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2025_12_19_225143_make_harvest_id_nullable_in_harvest_containers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2025_12_19_225150_add_container_id_to_harvests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2025_12_19_232235_create_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2025_12_19_232254_create_client_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2025_12_19_232309_create_taxes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2025_12_19_232318_create_user_taxes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2025_12_19_232335_create_invoice_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2025_12_19_232345_create_invoices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2025_12_19_232353_create_invoice_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2025_12_19_233102_fix_taxes_unique_constraint',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2025_12_20_013117_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2025_12_20_013117_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2025_12_20_104109_add_invitation_sent_at_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2025_12_20_104500_create_harvest_stocks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2025_12_20_104501_create_container_states_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2025_12_20_104502_migrate_existing_harvests_to_stock',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2025_12_20_120000_create_invoicing_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2025_12_20_143000_create_support_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2025_12_20_143001_create_support_ticket_comments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2025_12_20_144742_add_image_to_support_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2025_12_20_150503_make_invoice_number_nullable_in_invoices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2025_12_21_130125_add_code_autonomous_community_to_sigpac_code_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2025_12_21_145538_add_phytosanitary_license_to_crew_members',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2025_12_21_201047_add_beta_fields_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2025_12_21_221919_add_plantation_id_to_agricultural_activities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2025_12_21_233756_create_digital_signatures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2025_12_22_174818_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2025_12_22_231943_add_soft_deletes_to_invoice_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2025_12_22_232305_create_invoice_audit_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2025_12_22_235224_add_performance_indexes_to_invoicing_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2025_12_23_110824_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2025_12_23_113257_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2025_12_23_115920_add_export_paths_to_official_reports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2025_12_23_133240_add_pac_fields_to_phytosanitary_treatments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2025_12_23_134440_add_phenological_stage_to_agricultural_activities',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2025_12_23_134445_add_pac_fields_to_irrigations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2025_12_23_135814_add_pac_fields_to_fertilizations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2025_12_23_141018_add_processing_status_to_official_reports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2025_12_23_141328_add_traceability_fields_to_harvests',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2025_12_23_225133_remove_name_from_client_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2025_12_23_230539_remove_is_delivery_note_address_from_client_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2025_12_24_000001_remove_due_date_from_invoices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2025_12_24_164400_create_pests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2025_12_24_164401_create_pest_product_effectiveness_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2025_12_24_164404_add_pest_id_to_observations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2025_12_24_164407_add_pest_id_to_phytosanitary_treatments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2025_12_24_170000_create_containers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2025_12_24_170001_create_container_current_states_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2025_12_24_170002_create_container_histories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2025_12_24_170003_update_foreign_keys_to_containers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2025_12_24_170004_fix_container_current_states_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2025_12_25_101600_make_registration_mandatory_in_phytosanitary_products',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2025_12_25_101700_create_agricultural_activity_audit_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2025_12_25_105238_create_plot_audit_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2025_12_25_111215_add_pac_fields_to_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2025_12_25_111222_add_planting_authorization_to_plot_plantings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2025_12_25_112244_create_plot_planting_audit_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2025_12_25_112247_add_locking_fields_to_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2025_12_25_113618_create_planting_certifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2025_12_25_190716_add_active_to_estimated_yields_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2025_12_25_191234_add_active_to_plot_plantings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2025_12_25_193614_add_active_to_phytosanitary_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2025_12_26_083613_add_spatial_index_to_plot_geometry_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2025_12_26_084545_add_index_to_multipart_plot_sigpac_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2025_12_26_095602_fix_municipality_codes_length',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2025_12_27_100000_create_plot_remote_sensing_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2025_12_28_230919_add_additional_indices_to_plot_remote_sensing_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2025_12_29_114258_add_stock_fields_to_container_current_states_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2025_12_29_114408_migrate_container_states_to_container_current_states',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2025_12_29_114611_drop_container_states_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2025_12_29_191028_add_alert_settings_to_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2025_12_29_201507_create_warehouses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2025_12_29_201518_create_product_stocks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2025_12_29_201528_create_product_stock_movements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2025_12_29_210338_add_minimum_stock_to_product_stocks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2025_12_30_184502_create_container_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2025_12_30_184620_create_container_materials_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2025_12_30_184627_create_container_rooms_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2025_12_30_184630_create_units_of_measurement_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2025_12_30_193025_cleanup_containers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2025_12_30_194549_create_unit_of_measurements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2025_12_31_165000_add_viticulturist_query_indexes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2025_12_31_165100_create_onboarding_progress_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2026_01_01_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2026_01_15_000000_create_official_reports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2026_01_15_000001_add_export_paths_to_official_reports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2026_02_15_174423_add_database_indexes_for_performance',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2026_02_15_174455_create_audit_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2026_02_15_180000_add_advanced_remote_sensing_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2026_02_15_205839_add_lst_and_area_data_to_plot_remote_sensing',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2026_02_15_211249_add_ultra_enriched_columns_to_plot_remote_sensing',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2026_02_22_100000_enhance_estimated_yields_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2026_02_22_100000_split_last_reset_year_in_invoicing_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2026_02_22_100001_add_ipm_fields_to_phytosanitary_treatments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2026_02_22_100002_create_field_applicators_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2026_02_22_100003_create_field_equipment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2026_02_22_100004_create_phenology_observations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2026_02_22_120000_change_container_current_states_unique_key',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2026_02_22_200000_add_sanitary_fields_to_harvests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2026_02_22_200001_create_post_harvest_treatments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2026_02_22_200002_create_marketed_harvests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2026_02_22_200003_create_residue_analyses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2026_02_22_200004_create_residue_managements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2026_02_22_200005_create_advisory_memberships_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2026_02_22_200006_create_campaign_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2026_02_22_200007_add_validation_fields_to_campaigns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2026_02_22_204858_add_pruning_fields_to_cultural_works_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2026_02_22_205301_create_viticulturist_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2026_02_22_300000_create_exploitations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2026_02_22_300001_create_exploitation_dgcs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2026_02_22_300002_create_cue_exports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2026_02_22_300003_create_supplies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2026_02_22_300004_create_supply_purchases_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2026_02_22_300005_create_energy_usages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2026_02_22_300006_create_commercial_authorizations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2026_02_22_300007_create_plot_environments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2026_02_22_300008_add_fields_to_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2026_02_23_000001_add_soil_type_and_orientation_to_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2026_02_25_000001_add_invoice_id_to_marketed_harvests',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2026_02_25_000001_add_transferencia_to_right_type_enum_in_plot_plantings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2026_02_28_000001_add_warehouse_id_to_supplies_and_fix_supply_type',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2026_02_28_000002_drop_dead_columns_from_supplies',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2026_02_28_000003_add_user_id_to_phytosanitary_products',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2026_02_28_000005_create_units_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (185,'2026_02_28_000006_add_toneladas_to_units_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (186,'2026_02_28_000007_drop_unit_of_measurements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (187,'2026_03_06_000001_add_corrected_invoice_id_to_invoices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (188,'2026_03_06_000001_add_dni_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (189,'2026_03_06_000001_add_harvest_fields_to_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (190,'2026_03_06_000001_create_wine_lots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (191,'2026_03_06_000002_add_vintage_to_harvests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (192,'2026_03_06_000002_add_winery_billing_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (193,'2026_03_06_000002_create_plot_lookup_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (194,'2026_03_06_000003_add_lookup_fields_to_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (195,'2026_03_06_000003_add_three_bucket_stock_system',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (196,'2026_03_06_000004_drop_legacy_text_columns_from_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (197,'2026_03_06_000005_refactor_orientation_tenure_on_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (198,'2026_03_06_000006_add_user_id_to_catalog_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (199,'2026_03_06_000007_create_user_catalog_hidden_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (200,'2026_03_06_000008_add_description_to_training_systems_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (201,'2026_03_06_000009_make_degree_day_base_nullable_on_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (202,'2026_03_06_222126_drop_planting_date_from_plot_plantings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (203,'2026_03_06_224301_drop_limit_kg_from_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (204,'2026_03_06_224457_update_yield_decimals_to_3',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (205,'2026_03_06_231648_drop_maximum_yield_kg_ha_from_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (206,'2026_03_07_000001_add_potential_alcohol_and_harvest_time_to_harvests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (207,'2026_03_07_000002_add_disqualified_to_harvests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (208,'2026_03_07_020707_add_invitation_expires_at_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (209,'2026_03_07_200000_create_grape_reception_batches_and_update_harvests',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (210,'2026_03_07_210000_create_winery_yield_forecasts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (211,'2026_03_07_220000_add_invitation_token_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (212,'2026_03_07_230000_add_health_status_other_wineries_to_estimated_yields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (213,'2026_03_07_231000_create_external_grapes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (214,'2026_03_07_232000_create_container_maintenances_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (215,'2026_03_07_234000_create_pac_declarations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (216,'2026_03_07_235000_create_pac_payments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (217,'2026_03_07_240001_create_winery_supplies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (218,'2026_03_07_240002_create_container_maintenance_supplies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (219,'2026_03_07_240003_create_container_additive_supplies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (220,'2026_03_07_240004_create_container_waste_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (221,'2026_03_07_240005_create_container_maintenance_wastes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (222,'2026_03_07_240006_create_wines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (223,'2026_03_07_240007_create_wine_process_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (224,'2026_03_07_240008_create_wine_process_detail_containers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (225,'2026_03_08_235000_add_wine_fields_to_wine_lots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (226,'2026_03_09_000001_add_harvest_sale_seq_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (227,'2026_03_09_000001_fix_invoice_number_unique_per_user',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (228,'2026_03_09_000002_add_container_and_marketed_harvest_to_invoice_items',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (229,'2026_03_09_000002_make_invoice_date_nullable_in_invoices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (230,'2026_03_09_000003_add_default_irpf_rate_to_viticulturist_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (231,'2026_03_09_100000_add_fields_to_wine_lots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (232,'2026_03_09_100001_create_wine_lot_grape_varieties_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (233,'2026_03_09_100002_create_wine_lot_taxes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (234,'2026_03_09_200000_add_unit_to_invoice_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (235,'2026_03_09_200001_add_initial_quantity_to_wines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (236,'2026_03_09_200002_create_wine_harvests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (237,'2026_03_09_200003_create_wine_transfers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (238,'2026_03_09_200004_create_wine_losses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (239,'2026_03_09_200005_create_wine_fermentation_controls_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (240,'2026_03_09_200006_create_wine_analyses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (241,'2026_03_10_000001_create_oenologists_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (242,'2026_03_10_000002_add_fields_to_wines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (243,'2026_03_10_000003_create_wine_additives_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (244,'2026_03_10_093438_rebuild_sigpac_code_column_to_24_digits',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (245,'2026_03_10_100537_add_oenologist_to_wine_operations_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (246,'2026_03_10_120000_add_foreign_key_machinery_id_to_agricultural_activities',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (247,'2026_03_10_171757_make_pac_fields_nullable_on_plots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (248,'2026_03_11_100000_create_harvest_deliveries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (249,'2026_03_11_102326_add_traceability_to_harvest_deliveries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (250,'2026_03_11_103824_add_quality_fields_to_harvest_deliveries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (251,'2026_03_11_134437_add_matching_fields_to_harvest_deliveries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (252,'2026_03_11_135456_add_dispute_fields_to_harvest_deliveries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (253,'2026_03_11_143656_change_price_decimals_on_harvest_deliveries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (254,'2026_03_11_143701_change_price_decimals_on_harvest_deliveries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (255,'2026_03_11_191023_change_image_to_images_on_support_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (256,'2026_03_11_200000_add_dispute_resolution_to_harvest_deliveries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (257,'2026_03_12_200049_add_compra_uva_externa_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (258,'2026_03_13_000001_add_initial_quantity_to_wine_lots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (259,'2026_03_13_000002_create_sif_records_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (260,'2026_03_13_112702_add_huella_to_sif_records_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (261,'2026_03_13_200000_add_issuer_legal_name_to_invoicing_settings',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (262,'2026_03_13_210000_create_wine_bottlings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (263,'2026_03_13_210001_create_wine_bottling_supplies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (264,'2026_03_13_220000_create_label_batches_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (265,'2026_03_13_220001_create_wine_labelings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (266,'2026_03_13_220002_create_label_wastes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (267,'2026_03_13_230000_create_wine_tasting_notes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (268,'2026_03_13_240000_create_wine_subproducts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (269,'2026_03_14_111235_add_wine_id_to_wine_lots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (270,'2026_03_14_112544_add_container_id_to_wine_bottlings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (271,'2026_03_14_123512_add_trace_token_to_wines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (272,'2026_03_15_001455_add_recinto_id_to_plot_remote_sensing',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (273,'2026_03_15_021431_update_plot_remote_sensing_unique_constraint_add_sigpac_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (274,'2026_03_16_090654_add_notes_to_winery_viticulturist_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (275,'2026_03_16_094929_create_do_inspections_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (276,'2026_03_16_094930_create_do_labels_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (277,'2026_03_16_094931_create_do_qualifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (278,'2026_03_16_200000_add_cuaderno_access_to_winery_viticulturist_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (279,'2026_03_16_210000_create_cuaderno_access_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (280,'2026_03_16_220000_rename_cuaderno_access_requests_to_notebook_access_requests',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (281,'2026_03_17_193215_create_supervisor_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (282,'2026_03_17_193829_create_abilities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (283,'2026_03_17_193830_create_user_abilities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (284,'2026_03_17_200000_add_loss_authorization_to_wine_losses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (285,'2026_03_17_200001_create_wine_stock_snapshots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (286,'2026_03_17_200002_add_enrichment_types_to_wine_process_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (287,'2026_03_18_000001_create_plot_costs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (288,'2026_03_18_000002_create_subcontractings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (289,'2026_03_18_000003_create_agri_insurances_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (290,'2026_03_18_000010_create_organizations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (291,'2026_03_18_000011_add_organization_id_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (292,'2026_03_18_000020_create_suppliers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (293,'2026_03_18_000021_create_winery_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (294,'2026_03_18_000022_extend_wine_analyses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (295,'2026_03_18_000023_create_eco_certifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (296,'2026_03_18_000023_fix_wine_analyses_analysis_type_enum',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (297,'2026_03_18_000024_create_sanitary_registrations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (298,'2026_03_18_000025_create_bottling_authorizations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (299,'2026_03_18_000030_create_cellar_operations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (300,'2026_03_19_175642_add_wine_volume_to_containers_and_activate_wine_fk',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (301,'2026_03_21_000001_extend_container_histories_operation_type_enum',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (302,'2026_03_21_000002_add_unit_to_containers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (303,'2026_03_21_000003_add_notebook_harvest_id_to_harvests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (304,'2026_03_21_100001_create_phytosanitary_container_returns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (305,'2026_03_21_100002_create_harvest_declarations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (306,'2026_03_21_200001_create_harvest_byproducts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (307,'2026_03_21_200002_create_water_concessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (308,'2026_03_21_200003_create_fertilization_plans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (309,'2026_03_21_200004_create_certifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (310,'2026_03_22_000001_improve_phytosanitary_treatments_pac_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (311,'2026_03_22_000002_add_fertirrigation_and_unit_to_irrigations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (312,'2026_03_22_000003_add_defoliation_and_topping_to_cultural_works_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (313,'2026_03_22_201936_add_ipm_fields_to_observations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (314,'2026_03_22_203512_add_residue_management_to_cultural_works_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (315,'2026_03_22_210116_add_reentry_interval_to_post_harvest_treatments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (316,'2026_03_22_211400_add_control_methods_to_pests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (317,'2026_03_24_131136_add_activated_at_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (318,'2026_03_25_000001_add_cuaderno_to_supervisor_viticulturist',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (319,'2026_03_25_000002_add_supervisor_to_notebook_access_requests',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (320,'2026_03_25_110000_create_do_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (321,'2026_03_27_094145_drop_viticultor_assignments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (322,'2099_99_99_999999_make_fields_nullable_for_testing',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (323,'2026_03_27_120000_recreate_viticultor_assignments_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (324,'2026_03_27_140000_add_infovi_fields_to_organizations_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (325,'2026_03_27_160000_add_is_must_to_wine_stock_snapshots_table',4);
