-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Tempo de geração: 28/02/2024 às 18:26
-- Versão do servidor: 5.6.51
-- Versão do PHP: 8.2.16

create database site_db;

use site_db;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `site_db`
--

DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_projects_create`(
  `pidproject` INT, 
  `pdestitle` VARCHAR(128), 
  `pdesdescription` TEXT, 
  `ptechnologies` VARCHAR(255)
)
BEGIN
  DECLARE vidproject INT;
  DECLARE vidtechnology INT;
  DECLARE vcommaposition INT;
    
  IF pidproject > 0 THEN
    UPDATE tb_projects
    SET 
      destitle = pdestitle,
      desdescription = pdesdescription
    WHERE idproject = pidproject;
      
    SET vidproject = pidproject;

    DELETE FROM tb_projectstechnologies 
    WHERE idproject = pidproject;
  ELSE
    INSERT INTO tb_projects (destitle, desdescription) 
    VALUES (pdestitle, pdesdescription);
    
    SET vidproject = LAST_INSERT_ID();
  END IF;
    
  WHILE LENGTH(ptechnologies) > 0 DO
    SET vcommaposition = LOCATE(',', ptechnologies);
    IF vcommaposition = 0 THEN
      SET vidtechnology = ptechnologies;
      SET ptechnologies = '';
    ELSE
      SET vidtechnology = SUBSTRING(ptechnologies, 1, vcommaposition - 1);
      SET ptechnologies = SUBSTRING(ptechnologies, vcommaposition + 1);
    END IF;
      
    INSERT INTO tb_projectstechnologies (idproject, idtechnology) 
    VALUES (vidproject, vidtechnology);
  END WHILE;
  
  SELECT * FROM tb_projects WHERE idproject = vidproject;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_projects_delete` (`pidproject` INT) BEGIN
  DELETE FROM tb_projectstechnologies WHERE idproject = pidproject;
  DELETE FROM tb_projects WHERE idproject = pidproject;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_userspasswordsrecoveries_create` (
  `piduser` INT, 
  `pdesip` VARCHAR(45)
)   
BEGIN  
  INSERT INTO tb_userspasswordsrecoveries (iduser, desip)
  VALUES(piduser, pdesip);
  
  SELECT * FROM tb_userspasswordsrecoveries
  WHERE idrecovery = LAST_INSERT_ID();    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_users_create` (
  `pdesperson` VARCHAR(64), 
  `pdeslogin` VARCHAR(64), 
  `pdespassword` VARCHAR(256), 
  `pdesemail` VARCHAR(128), 
  `pnrphone` VARCHAR(15), 
  `pnrcpf` VARCHAR(15), 
  `pinadmin` TINYINT
)   
BEGIN
  DECLARE vidperson INT;
    
  INSERT INTO tb_persons (desperson, desemail, nrphone, nrcpf)
  VALUES(pdesperson, pdesemail, pnrphone, pnrcpf);
  
  SET vidperson = LAST_INSERT_ID();
  
  INSERT INTO tb_users (idperson, deslogin, despassword, inadmin)
  VALUES(vidperson, pdeslogin, pdespassword, pinadmin);
  
  SELECT * FROM tb_users a 
  INNER JOIN tb_persons b 
  USING(idperson) 
  WHERE a.iduser = LAST_INSERT_ID();  
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_users_delete` (`piduser` INT) BEGIN
  DECLARE vidperson INT;
  
  SET FOREIGN_KEY_CHECKS = 0;
	
	SELECT idperson INTO vidperson
  FROM tb_users
  WHERE iduser = piduser;
  
	DELETE FROM tb_persons WHERE idperson = vidperson;    
  DELETE FROM tb_userslogs WHERE iduser = piduser;
  DELETE FROM tb_userspasswordsrecoveries WHERE iduser = piduser;
  DELETE FROM tb_users WHERE iduser = piduser;
  
  SET FOREIGN_KEY_CHECKS = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_users_update` (
  `piduser` INT, 
  `pdesperson` VARCHAR(64), 
  `pdeslogin` VARCHAR(64), 
  `pdespassword` VARCHAR(256), 
  `pdesemail` VARCHAR(128), 
  `pnrphone` VARCHAR(15), 
  `pnrcpf` VARCHAR(15), 
  `pinadmin` TINYINT
)   
BEGIN
  DECLARE vidperson INT;
    
  SELECT idperson INTO vidperson
  FROM tb_users
  WHERE iduser = piduser;
  
  UPDATE tb_persons
  SET desperson = pdesperson,
      desemail = pdesemail,
      nrphone = pnrphone,
      nrcpf = nrcpf
  WHERE idperson = vidperson;
    
  UPDATE tb_users
  SET deslogin = pdeslogin,
      despassword = pdespassword,
      inadmin = pinadmin
  WHERE iduser = piduser;
    
  SELECT * FROM tb_users a 
  INNER JOIN tb_persons b 
  USING(idperson) 
  WHERE a.iduser = piduser;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_experiences`
--

CREATE TABLE `tb_experiences` (
  `idexperience` int(11) NOT NULL,
  `destitle` varchar(128) NOT NULL,
  `desdescription` text NOT NULL,
  `dtstart` varchar(7) NOT NULL,
  `dtend` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_persons`
--

CREATE TABLE `tb_persons` (
  `idperson` int(11) NOT NULL,
  `desperson` varchar(64) NOT NULL,
  `desemail` varchar(128) NOT NULL,
  `nrphone` varchar(15) DEFAULT NULL,
  `nrcpf` varchar(15) DEFAULT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Despejando dados para a tabela `tb_persons`
--

INSERT INTO `tb_persons` (`idperson`, `desperson`, `desemail`, `nrphone`, `nrcpf`, `dtregister`) VALUES
(1, 'Admin', 'admin@email.com', NULL, NULL, '2024-01-01 00:00:00'),
(2, 'User', 'user@email.com', NULL, NULL, '2024-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_projects`
--

CREATE TABLE `tb_projects` (
  `idproject` int(11) NOT NULL,
  `destitle` varchar(128) NOT NULL,
  `desdescription` text NOT NULL,
  `desimage` varchar(255) DEFAULT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_projectstechnologies`
--

CREATE TABLE `tb_projectstechnologies` (
  `idproject` int(11) NOT NULL,
  `idtechnology` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_technologies`
--

CREATE TABLE `tb_technologies` (
  `idtechnology` int(11) NOT NULL,
  `desname` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_users`
--

CREATE TABLE `tb_users` (
  `iduser` int(11) NOT NULL,
  `idperson` int(11) NOT NULL,
  `deslogin` varchar(64) NOT NULL,
  `despassword` varchar(256) NOT NULL,
  `inadmin` tinyint(4) NOT NULL DEFAULT '0',
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Despejando dados para a tabela `tb_users`
--

INSERT INTO `tb_users` (`iduser`, `idperson`, `deslogin`, `despassword`, `inadmin`, `dtregister`) VALUES
(1, 1, 'admin', '$2y$12$YlooCyNvyTji8bPRcrfNfOKnVMmZA9ViM2A3IpFjmrpIbp5ovNmga', 1, '2021-08-11 17:32:22'),
(2, 2, 'user', '$2y$12$Hl0YFyxxuJ8c/TPpoShRZ.0r3PTP24mL7Q3iTBvC70Reou1dp4ZS6', 0, '2021-08-11 17:32:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_userslogs`
--

CREATE TABLE `tb_userslogs` (
  `idlog` int(11) NOT NULL,
  `iduser` int(11) NOT NULL,
  `deslog` varchar(128) NOT NULL,
  `desip` varchar(45) NOT NULL,
  `desuseragent` varchar(128) NOT NULL,
  `dessessionid` varchar(64) NOT NULL,
  `desurl` varchar(128) NOT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_userspasswordsrecoveries`
--

CREATE TABLE `tb_userspasswordsrecoveries` (
  `idrecovery` int(11) NOT NULL,
  `iduser` int(11) NOT NULL,
  `desip` varchar(45) NOT NULL,
  `dtrecovery` datetime DEFAULT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `tb_experiences`
--
ALTER TABLE `tb_experiences`
  ADD PRIMARY KEY (`idexperience`);

--
-- Índices de tabela `tb_persons`
--
ALTER TABLE `tb_persons`
  ADD PRIMARY KEY (`idperson`);

--
-- Índices de tabela `tb_projects`
--
ALTER TABLE `tb_projects`
  ADD PRIMARY KEY (`idproject`);

--
-- Índices de tabela `tb_projectstechnologies`
--
ALTER TABLE `tb_projectstechnologies`
  ADD PRIMARY KEY (`idproject`,`idtechnology`),
  ADD KEY `idtechnology` (`idtechnology`);

--
-- Índices de tabela `tb_technologies`
--
ALTER TABLE `tb_technologies`
  ADD PRIMARY KEY (`idtechnology`);

--
-- Índices de tabela `tb_users`
--
ALTER TABLE `tb_users`
  ADD PRIMARY KEY (`iduser`),
  ADD KEY `FK_users_persons_idx` (`idperson`);

--
-- Índices de tabela `tb_userslogs`
--
ALTER TABLE `tb_userslogs`
  ADD PRIMARY KEY (`idlog`),
  ADD KEY `fk_userslogs_users_idx` (`iduser`);

--
-- Índices de tabela `tb_userspasswordsrecoveries`
--
ALTER TABLE `tb_userspasswordsrecoveries`
  ADD PRIMARY KEY (`idrecovery`),
  ADD KEY `fk_userspasswordsrecoveries_users_idx` (`iduser`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `tb_experiences`
--
ALTER TABLE `tb_experiences`
  MODIFY `idexperience` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_persons`
--
ALTER TABLE `tb_persons`
  MODIFY `idperson` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `tb_projects`
--
ALTER TABLE `tb_projects`
  MODIFY `idproject` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `tb_technologies`
--
ALTER TABLE `tb_technologies`
  MODIFY `idtechnology` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `tb_users`
--
ALTER TABLE `tb_users`
  MODIFY `iduser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `tb_userslogs`
--
ALTER TABLE `tb_userslogs`
  MODIFY `idlog` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_userspasswordsrecoveries`
--
ALTER TABLE `tb_userspasswordsrecoveries`
  MODIFY `idrecovery` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `tb_projectstechnologies`
--
ALTER TABLE `tb_projectstechnologies`
  ADD CONSTRAINT `tb_projectstechnologies_ibfk_1` FOREIGN KEY (`idproject`) REFERENCES `tb_projects` (`idproject`),
  ADD CONSTRAINT `tb_projectstechnologies_ibfk_2` FOREIGN KEY (`idtechnology`) REFERENCES `tb_technologies` (`idtechnology`);

--
-- Restrições para tabelas `tb_users`
--
ALTER TABLE `tb_users`
  ADD CONSTRAINT `fk_users_persons` FOREIGN KEY (`idperson`) REFERENCES `tb_persons` (`idperson`);

--
-- Restrições para tabelas `tb_userslogs`
--
ALTER TABLE `tb_userslogs`
  ADD CONSTRAINT `fk_userslogs_users` FOREIGN KEY (`iduser`) REFERENCES `tb_users` (`iduser`);

--
-- Restrições para tabelas `tb_userspasswordsrecoveries`
--
ALTER TABLE `tb_userspasswordsrecoveries`
  ADD CONSTRAINT `fk_userspasswordsrecoveries_users` FOREIGN KEY (`iduser`) REFERENCES `tb_users` (`iduser`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
