-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2026 at 02:56 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `edulink`
--

-- --------------------------------------------------------

--
-- Table structure for table `badge`
--

-- CREATE TABLE `badge` (
--   `id` int(11) NOT NULL,
--   `name` varchar(100) NOT NULL,
--   `min_points` int(11) NOT NULL,
--   `icon` varchar(255) DEFAULT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

-- CREATE TABLE `category` (
--   `id` int(11) NOT NULL,
--   `name` varchar(100) NOT NULL,
--   `color` varchar(20) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`, `color`) VALUES
(1, 'Note personnelle', '#bb76db'),
(2, 'Note de cours', '#4ECDC4'),
(3, 'revision', '#78c030');

-- --------------------------------------------------------

--
-- Table structure for table `doctrine_migration_versions`
--

-- CREATE TABLE `doctrine_migration_versions` (
--   `version` varchar(191) NOT NULL,
--   `executed_at` datetime DEFAULT NULL,
--   `execution_time` int(11) DEFAULT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260202065044', '2026-02-02 06:50:50', 241),
('DoctrineMigrations\\Version20260202103244', '2026-02-02 10:32:52', 47),
('DoctrineMigrations\\Version20260202104552', '2026-02-02 10:46:11', 5),
('DoctrineMigrations\\Version20260208163059', '2026-02-08 17:31:05', 193),
('DoctrineMigrations\\Version20260209000000', '2026-02-09 15:15:53', NULL),
('DoctrineMigrations\\Version20260212091723', '2026-02-12 10:20:22', 263);

-- --------------------------------------------------------

--
-- Table structure for table `messenger_messages`
--

-- CREATE TABLE `messenger_messages` (
--   `id` bigint(20) NOT NULL,
--   `body` longtext NOT NULL,
--   `headers` longtext NOT NULL,
--   `queue_name` varchar(190) NOT NULL,
--   `created_at` datetime NOT NULL,
--   `available_at` datetime NOT NULL,
--   `delivered_at` datetime DEFAULT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

-- CREATE TABLE `notes` (
--   `id` int(11) NOT NULL,
--   `title` varchar(255) NOT NULL,
--   `content` longtext NOT NULL,
--   `created_at` datetime NOT NULL,
--   `updated_at` datetime NOT NULL,
--   `user_id` int(11) NOT NULL,
--   `category_id` int(11) DEFAULT NULL,
--   `attachment` varchar(255) DEFAULT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `title`, `content`, `created_at`, `updated_at`, `user_id`, `category_id`, `attachment`) VALUES
(1, 'z', 'bonjour', '2026-02-08 17:41:31', '2026-02-08 17:41:31', 6, NULL, NULL),
(2, 'nnnn', '445', '2026-02-09 15:21:29', '2026-02-09 15:21:29', 6, 1, NULL),
(3, '445', 'thuu', '2026-02-09 15:21:46', '2026-02-09 15:21:46', 6, 2, NULL),
(4, 'aaaaaaaaaaaaaaaaaaaaaaaa', ';,', '2026-02-11 19:58:16', '2026-02-12 12:23:18', 7, 1, NULL),
(5, 'nnnn', 'ojihh', '2026-02-11 19:58:52', '2026-02-11 19:58:52', 7, 2, NULL),
(6, 'c dcdc', 'CDCDCDCD', '2026-02-11 20:26:36', '2026-02-12 11:37:38', 7, 3, NULL),
(7, 'de', 'defefe', '2026-02-11 20:32:52', '2026-02-11 20:32:52', 8, 1, NULL),
(8, 'aaze', 'dzdzdz', '2026-02-11 20:33:10', '2026-02-11 20:33:10', 8, 1, NULL),
(9, 'note1', 'eeeee', '2026-02-12 10:22:19', '2026-02-12 10:22:19', 7, 1, 'topologie-698d9bcb6f5a0.png'),
(10, '1515', 'nnnnnnnnnnnnnnnn', '2026-02-12 14:25:51', '2026-02-12 14:25:51', 8, 2, NULL),
(12, 'nnnn', 'kkk', '2026-02-12 14:35:53', '2026-02-12 14:35:53', 8, 3, 'reso1-698dd7399bb7d.png');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

-- CREATE TABLE `notification` (
--   `id` int(11) NOT NULL,
--   `message` varchar(255) NOT NULL,
--   `created_at` datetime NOT NULL,
--   `is_read` tinyint(4) NOT NULL,
--   `link` varchar(255) DEFAULT NULL,
--   `user_id` int(11) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

-- CREATE TABLE `reminders` (
--   `id` int(11) NOT NULL,
--   `title` varchar(255) NOT NULL,
--   `description` longtext DEFAULT NULL,
--   `reminder_time` datetime NOT NULL,
--   `status` varchar(50) NOT NULL,
--   `created_at` datetime NOT NULL,
--   `user_id` int(11) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reminders`
--

INSERT INTO `reminders` (`id`, `title`, `description`, `reminder_time`, `status`, `created_at`, `user_id`) VALUES
(4, '11122', '111', '2026-02-08 18:13:00', 'pending', '2026-02-08 18:12:34', 6),
(5, '112', NULL, '2026-02-08 18:51:00', 'pending', '2026-02-08 18:20:19', 6),
(7, 'ccccss', NULL, '2026-02-11 20:34:00', 'notified', '2026-02-11 20:33:29', 8),
(8, '1111', 'huhuhuhuu', '2026-02-11 20:50:00', 'notified', '2026-02-11 20:49:18', 8),
(9, '111', 'fe', '2026-02-12 09:50:00', 'pending', '2026-02-12 09:49:25', 7),
(11, 'deuxieme', 'eeeee', '2026-02-12 11:36:00', 'pending', '2026-02-12 11:35:55', 7),
(12, 'taki', 'dz', '2026-02-12 14:38:00', 'notified', '2026-02-12 14:37:15', 8);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

-- CREATE TABLE `tasks` (
--   `id` int(11) NOT NULL,
--   `title` varchar(255) NOT NULL,
--   `is_completed` tinyint(4) NOT NULL,
--   `created_at` datetime NOT NULL,
--   `completed_at` datetime DEFAULT NULL,
--   `user_id` int(11) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `is_completed`, `created_at`, `completed_at`, `user_id`) VALUES
(1, 'haha', 1, '2026-02-08 17:41:40', '2026-02-08 21:26:56', 6),
(2, 'haha11', 1, '2026-02-08 17:44:29', '2026-02-08 21:26:52', 6),
(3, 'haha11', 0, '2026-02-08 17:50:01', NULL, 6),
(4, '11111', 1, '2026-02-08 21:11:43', '2026-02-09 14:50:50', 6),
(5, 'haha', 1, '2026-02-11 19:58:27', '2026-02-11 19:58:30', 7),
(6, 'haha11', 1, '2026-02-11 20:49:37', '2026-02-12 14:19:59', 8),
(7, '111111111111111', 0, '2026-02-12 14:22:42', NULL, 8);

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

-- CREATE TABLE `transaction` (
--   `id` int(11) NOT NULL,
--   `amount` int(11) NOT NULL,
--   `type` varchar(50) DEFAULT NULL,
--   `date` datetime DEFAULT NULL,
--   `user_id` int(11) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`id`, `amount`, `type`, `date`, `user_id`) VALUES
(1, -1000, 'TRANSFER_SENT', '2026-02-02 09:44:31', 2),
(2, 1000, 'TRANSFER_RECEIVED', '2026-02-02 09:44:31', 4),
(3, 100, 'GRANT', '2026-02-02 11:29:30', 2);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

-- CREATE TABLE `user` (
--   `id` int(11) NOT NULL,
--   `email` varchar(150) NOT NULL,
--   `password` varchar(255) NOT NULL,
--   `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
--   `wallet_balance` int(11) NOT NULL,
--   `full_name` varchar(150) DEFAULT NULL,
--   `face_descriptor` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`face_descriptor`)),
--   `reset_otp` varchar(10) DEFAULT NULL,
--   `reset_otp_expires_at` datetime DEFAULT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `email`, `password`, `roles`, `wallet_balance`, `full_name`, `face_descriptor`, `reset_otp`, `reset_otp_expires_at`) VALUES
(2, 'taki.mejri001@gmail.com', '$2y$13$MnU0emcTc9IeDH4.CsXuxu5Oje3Y2D2CX3o1JvyuWAG/jc0Vetq6q', '[]', 5000, NULL, NULL, NULL, NULL),
(3, 'taki.mejri@esprit.tn', '$2y$13$eGTZ2DrRnJwM.kaFEt3J7OWMBgKYaGLJkW7cZ.aO3sTalkpwFhnri', '[\"ROLE_ADMIN\"]', 5000, NULL, NULL, NULL, NULL),
(4, 'taki@gmail.com', '$2y$13$ZBzazCGrCAkhhCdYfL3SG.I8q45GUfHIaDk6v..OmBFx04xT/TY7u', '[\"ROLE_STUDENT\"]', 1000, 'taki', NULL, NULL, NULL),
(5, 'ttatt@gmail.com', '$2y$13$rQHfpCwXlflmBFJDjE77suD41fsYEsTntnz9hpTnIfGLuJ0TyJXWm', '[\"ROLE_STUDENT\"]', 0, 'test', '[-0.10089247673749924,0.07892113924026489,0.11243534088134766,0.015558293089270592,-0.06074829027056694,-0.014880023896694183,-0.050874464213848114,-0.022067897021770477,0.1557360142469406,-0.07369371503591537,0.19106318056583405,0.008625841699540615,-0.22963273525238037,-0.002192470245063305,-0.02776874601840973,0.08743026107549667,-0.13657213747501373,-0.09493522346019745,-0.03416574373841286,-0.00187426689080894,0.06765211373567581,0.07565603405237198,-0.004256933927536011,0.07717473804950714,-0.09387151896953583,-0.37974491715431213,-0.09828793257474899,-0.11591487377882004,-0.040740448981523514,-0.10532883554697037,-0.09103938937187195,0.0716530904173851,-0.136968195438385,-0.07240157574415207,-0.0002692180569283664,0.12320554256439209,-0.03513742983341217,-0.096285380423069,0.18020054697990417,0.033121466636657715,-0.09280211478471756,-0.002625606954097748,-0.01288564596325159,0.3251461386680603,0.12954500317573547,0.023884322494268417,0.04250641167163849,-0.0161576010286808,0.08009915798902512,-0.25786736607551575,0.045306943356990814,0.18301314115524292,0.037776004523038864,0.1178090050816536,0.07776855677366257,-0.11083520203828812,0.03848644718527794,0.07060333341360092,-0.14191676676273346,0.09998620301485062,0.01727866195142269,-0.11206835508346558,0.03670680150389671,-0.03236564248800278,0.20994220674037933,0.09443053603172302,-0.07588335126638412,-0.0697660744190216,0.08178958296775818,-0.15922267735004425,-0.1031111553311348,-0.022395819425582886,-0.13900642096996307,-0.17813949286937714,-0.2552708685398102,0.05779299512505531,0.3503335118293762,0.15966635942459106,-0.20965689420700073,0.0650564432144165,-0.08482779562473297,-0.05804220214486122,0.11422590166330338,0.09326530992984772,-0.020399918779730797,0.05500834062695503,-0.13696399331092834,0.06980768591165543,0.23532100021839142,0.0134871331974864,-0.009866034612059593,0.24456928670406342,0.0072755999863147736,0.011165635660290718,0.11519254744052887,0.034621745347976685,-0.043881043791770935,-0.038027722388505936,-0.15696296095848083,0.031147627159953117,0.06055867299437523,-0.1025698333978653,-0.05365738645195961,0.07607831805944443,-0.18560557067394257,0.06428212672472,0.0031993682496249676,-0.0349910631775856,0.004570951219648123,-0.029452377930283546,-0.20430295169353485,-0.04544466733932495,0.18383868038654327,-0.2175254076719284,0.16914261877536774,0.157853364944458,0.01631927117705345,0.17056895792484283,0.04738358408212662,0.06822632253170013,-0.033112939447164536,-0.07823103666305542,-0.12021426856517792,-0.08286803215742111,0.08042585849761963,0.10109798610210419,0.016267025843262672,0.022051602602005005]', NULL, NULL),
(6, 'delizar.chaaraoui@esprit.tn', '$2y$13$S6Im9RaSAe03rMIv9uvlmOiFOIOsEflizGPluqJdSj7RWlMWkCeoC', '[\"ROLE_ADMIN\"]', 0, 'delizar chaaraoui', '[-0.04322679340839386,0.06458383798599243,0.03914767876267433,-0.01810825802385807,-0.06688709557056427,0.009734652005136013,-0.06193959340453148,-0.04651978984475136,0.2511874735355377,-0.05217815563082695,0.21385493874549866,0.05472762510180473,-0.3075045347213745,-0.029756637290120125,0.018853945657610893,0.10907123237848282,-0.1147044450044632,-0.12177315354347229,-0.07453592121601105,0.05714593827724457,0.06899747252464294,0.007516656536608934,0.06636600941419601,0.09524960815906525,-0.14840328693389893,-0.3891546428203583,-0.05720125883817673,-0.07618739455938339,-0.062224190682172775,-0.0559060238301754,-0.06681996583938599,0.03597994148731232,-0.22783921658992767,-0.033743731677532196,0.0021209961269050837,0.05347428470849991,-0.09725718200206757,-0.10482580959796906,0.23183420300483704,0.05114471912384033,-0.2458253800868988,-0.08047781139612198,-0.03703955188393593,0.2030266374349594,0.14900310337543488,0.01864982210099697,0.0680878683924675,-0.012113446369767189,0.08930608630180359,-0.3390266001224518,0.08121100813150406,0.23517397046089172,0.049631841480731964,0.019787469878792763,0.11507806926965714,-0.12772119045257568,-0.022248907014727592,0.16369803249835968,-0.2191036343574524,0.03243463113903999,0.02297091670334339,-0.007226519752293825,-0.05363546311855316,-0.10407669842243195,0.20036987960338593,0.18581649661064148,-0.12589441239833832,-0.08662749826908112,0.14361047744750977,-0.13581816852092743,-0.037663400173187256,0.0824957937002182,-0.1469334363937378,-0.2947971820831299,-0.2738775908946991,0.03272087126970291,0.40376779437065125,0.15523739159107208,-0.12028545141220093,0.04309321194887161,-0.07408441603172302,-0.027753304690122604,0.010706516914069653,0.002790318103507161,-0.06111937016248703,0.08377210050821304,-0.07661490142345428,0.08456065505743027,0.20279261469841003,0.022962134331464767,-0.0036126563791185617,0.27174630761146545,-0.02724386192858219,-0.03497898951172829,0.03866467997431755,0.05687445402145386,0.0014677640283480287,-0.03691120445728302,-0.16338349878787994,0.03217938169836998,0.06240738555788994,-0.06602153182029724,0.018921157345175743,0.059977248311042786,-0.20411837100982666,0.03610720857977867,0.0028375054243952036,-0.049753520637750626,0.03544216603040695,0.0016356655396521091,-0.2164331078529358,-0.07485001534223557,0.13091643154621124,-0.2927529513835907,0.19082355499267578,0.09340149164199829,0.019132761284708977,0.11567272245883942,0.0032052183523774147,0.052758652716875076,-0.016216062009334564,-0.09901805967092514,-0.1467449963092804,-0.10564391314983368,0.0971432775259018,0.04728681594133377,0.056359391659498215,-0.0022402829490602016]', NULL, NULL),
(7, 'laila@gmail.com', '$2y$13$8VkOYEDLI5abrL6pZTw8HemdEEgFXcMMMGpLyhBn4sfe0B.govFQe', '[\"ROLE_STUDENT\"]', 0, 'laila', NULL, NULL, NULL),
(8, 'hejer@gmail.com', '$2y$13$DhkGZuom2c.08FeZd2.0e.4xHBfvicgNPXXZj99EIgxo92tIMM96C', '[\"ROLE_STUDENT\"]', 0, 'hejer', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_badge`
--

-- CREATE TABLE `user_badge` (
--   `id` int(11) NOT NULL,
--   `unlocked_at` datetime DEFAULT NULL,
--   `user_id` int(11) NOT NULL,
--   `badge_id` int(11) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_matiere_stat`
--

-- CREATE TABLE `user_matiere_stat` (
--   `id` int(11) NOT NULL,
--   `points_earned` int(11) NOT NULL,
--   `level` int(11) NOT NULL,
--   `user_id` int(11) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `badge`
--
-- ALTER TABLE `badge`
--   ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
-- ALTER TABLE `category`
--   ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctrine_migration_versions`
--
-- ALTER TABLE `doctrine_migration_versions`
--   ADD PRIMARY KEY (`version`);

--
-- Indexes for table `messenger_messages`
--
-- ALTER TABLE `messenger_messages`
--   ADD PRIMARY KEY (`id`),
--   ADD KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`);

--
-- Indexes for table `notes`
--
-- ALTER TABLE `notes`
--   ADD PRIMARY KEY (`id`),
--   ADD KEY `IDX_11BA68CA76ED395` (`user_id`),
--   ADD KEY `IDX_11BA68C12469DE2` (`category_id`);

--
-- Indexes for table `notification`
--
-- ALTER TABLE `notification`
--   ADD PRIMARY KEY (`id`),
--   ADD KEY `IDX_BF5476CAA76ED395` (`user_id`);

--
-- Indexes for table `reminders`
--
-- ALTER TABLE `reminders`
--   ADD PRIMARY KEY (`id`),
--   ADD KEY `IDX_6D92B9D4A76ED395` (`user_id`);

--
-- Indexes for table `tasks`
--
-- ALTER TABLE `tasks`
--   ADD PRIMARY KEY (`id`),
--   ADD KEY `IDX_50586597A76ED395` (`user_id`);

--
-- Indexes for table `transaction`
--
-- ALTER TABLE `transaction`
--   ADD PRIMARY KEY (`id`),
--   ADD KEY `IDX_723705D1A76ED395` (`user_id`);

--
-- Indexes for table `user`
--
-- ALTER TABLE `user`
--   ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_badge`
--
-- ALTER TABLE `user_badge`
--   ADD PRIMARY KEY (`id`),
--   ADD KEY `IDX_1C32B345A76ED395` (`user_id`),
--   ADD KEY `IDX_1C32B345F7A2C2FC` (`badge_id`);

--
-- Indexes for table `user_matiere_stat`
--
-- ALTER TABLE `user_matiere_stat`
--   ADD PRIMARY KEY (`id`),
--   ADD KEY `IDX_D3A6057DA76ED395` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `badge`
--
-- ALTER TABLE `badge`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
-- ALTER TABLE `category`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `messenger_messages`
--
-- ALTER TABLE `messenger_messages`
--   MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notes`
--
-- ALTER TABLE `notes`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notification`
--
-- ALTER TABLE `notification`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reminders`
--
-- ALTER TABLE `reminders`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tasks`
--
-- ALTER TABLE `tasks`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transaction`
--
-- ALTER TABLE `transaction`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
-- ALTER TABLE `user`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_badge`
--
-- ALTER TABLE `user_badge`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_matiere_stat`
--
-- ALTER TABLE `user_matiere_stat`
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notes`
--
-- ALTER TABLE `notes`
--   ADD CONSTRAINT `FK_11BA68CA12469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`),
--   ADD CONSTRAINT `FK_11BA68CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `notification`
--
-- ALTER TABLE `notification`
--   ADD CONSTRAINT `FK_BF5476CAA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `reminders`
--
-- ALTER TABLE `reminders`
--   ADD CONSTRAINT `FK_6D92B9D4A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `tasks`
--
-- ALTER TABLE `tasks`
--   ADD CONSTRAINT `FK_50586597A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `transaction`
--
-- ALTER TABLE `transaction`
--   ADD CONSTRAINT `FK_723705D1A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `user_badge`
--
-- ALTER TABLE `user_badge`
--   ADD CONSTRAINT `FK_1C32B345A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
--   ADD CONSTRAINT `FK_1C32B345F7A2C2FC` FOREIGN KEY (`badge_id`) REFERENCES `badge` (`id`);

--
-- Constraints for table `user_matiere_stat`
--
-- ALTER TABLE `user_matiere_stat`
--   ADD CONSTRAINT `FK_D3A6057DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
