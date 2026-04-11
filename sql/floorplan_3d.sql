-- 3D Floorplan layouts table
CREATE TABLE IF NOT EXISTS `floorplan_3d_layouts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL DEFAULT 'Untitled Layout',
  `layout_json` LONGTEXT NOT NULL,
  `saved_by` INT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `saved_by` (`saved_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Active layout pointer (only one layout is "active" / shown to viewers)
CREATE TABLE IF NOT EXISTS `floorplan_3d_active` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `layout_id` INT NOT NULL,
  `set_by` INT NULL,
  `set_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
