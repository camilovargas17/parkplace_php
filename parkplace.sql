-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema parkplace
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema parkplace
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `parkplace` DEFAULT CHARACTER SET utf8mb4 ;
USE `parkplace` ;

-- -----------------------------------------------------
-- Table `parkplace`.`vehiculos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `parkplace`.`vehiculos` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `placa` VARCHAR(20) NOT NULL,
  `tipo` ENUM('carro', 'moto') NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `placa` (`placa` ASC) VISIBLE,
  INDEX `idx_vehiculos_placa` (`placa` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 12
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `parkplace`.`usuarios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `parkplace`.`usuarios` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `rol` ENUM('administrador', 'operador') NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email` (`email` ASC) VISIBLE,
  INDEX `idx_usuarios_email` (`email` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `parkplace`.`registros`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `parkplace`.`registros` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `vehiculo_id` BIGINT(20) NULL DEFAULT NULL,
  `usuario_id` BIGINT(20) NULL DEFAULT NULL,
  `hora_entrada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  `hora_salida` TIMESTAMP NULL DEFAULT NULL,
  `total_pagar` DECIMAL(10,2) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  INDEX `vehiculo_id` (`vehiculo_id` ASC) VISIBLE,
  INDEX `usuario_id` (`usuario_id` ASC) VISIBLE,
  INDEX `idx_registros_hora_entrada` (`hora_entrada` ASC) VISIBLE,
  CONSTRAINT `registros_ibfk_1`
    FOREIGN KEY (`vehiculo_id`)
    REFERENCES `parkplace`.`vehiculos` (`id`),
  CONSTRAINT `registros_ibfk_2`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `parkplace`.`usuarios` (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 12
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `parkplace`.`tarifas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `parkplace`.`tarifas` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `tipo_vehiculo` ENUM('carro', 'moto') NOT NULL,
  `valor_hora` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8mb4;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
