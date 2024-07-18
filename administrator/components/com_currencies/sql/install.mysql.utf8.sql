
DROP TABLE IF EXISTS `#__currencies_all_currencies`;

CREATE TABLE `#__currencies_all_currencies` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `main` tinyint(4) NOT NULL,
  `continent` varchar(15) NOT NULL,
  `code` varchar(3) NOT NULL,
  `symbol` varchar(5) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `version` int(1) NOT NULL,
  `article_id` int(11) NOT NULL,
  `iso_alpha2` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `#__currencies_all_currencies` (`id`, `name`, `main`, `continent`, `code`, `symbol`, `published`, `ordering`, `created`, `modified`, `modified_by`, `version`, `article_id`, `iso_alpha2`) VALUES
(1, 'US Dollar', 1, 'America', 'USD', '$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'US'),
(2, 'Canadian Dollar', 1, 'America', 'CAD', 'CA$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'CA'),
(3, 'Euro', 1, 'Europe', 'EUR', '€', 1, 0, '0000-00-00 00:00:00', '2020-08-17 11:33:00', 798, 1, 1, 'DE'),
(4, 'United Arab Emirates Dirham', 0, 'MEA', 'AED', 'AED', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'AE'),
(5, 'Afghan Afghani', 0, 'Asia', 'AFN', '؋', 1, 0, '0000-00-00 00:00:00', '2020-08-17 12:34:00', 798, 9, 2, 'AF'),
(6, 'Albanian Lek', 0, 'Europe', 'ALL', 'L', 1, 0, '0000-00-00 00:00:00', '2020-08-17 12:34:00', 798, 1, 1, 'AL'),
(7, 'Armenian Dram', 0, 'Asia', 'AMD', 'AMD', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'AM'),
(8, 'Argentine Peso', 0, 'America', 'ARS', 'AR$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'AR'),
(9, 'Australian Dollar', 1, 'Pacific', 'AUD', 'AU$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'AU'),
(10, 'Azerbaijani Manat', 0, 'Asia', 'AZN', 'man.', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'AZ'),
(11, 'Bosnia-Herzegovina Convertible Mark', 0, 'Europe', 'BAM', 'KM', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'BA'),
(12, 'Bangladeshi Taka', 0, 'Asia', 'BDT', 'Tk', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'BD'),
(13, 'Bulgarian Lev', 0, 'Europe', 'BGN', 'лв.', 1, 0, '0000-00-00 00:00:00', '2017-08-20 16:39:24', 798, 1, 1, 'BG'),
(14, 'Bahraini Dinar', 0, 'MEA', 'BHD', 'BD', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'BH'),
(15, 'Burundian Franc', 0, 'Africa', 'BIF', 'FBu', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'BI'),
(16, 'Brunei Dollar', 0, 'Asia', 'BND', 'BN$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'BN'),
(17, 'Bolivian Boliviano', 0, 'America', 'BOB', 'Bs', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'BO'),
(18, 'Brazilian Real', 1, 'America', 'BRL', 'R$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'BR'),
(19, 'Botswanan Pula', 0, 'Africa', 'BWP', 'P', 1, 0, '0000-00-00 00:00:00', '2017-08-20 16:37:05', 798, 1, 1, 'BW'),
(20, 'Belarusian Ruble', 0, 'Europe', 'BYN', 'p.', 1, 0, '0000-00-00 00:00:00', '2017-08-20 16:38:30', 798, 1, 1, 'BY'),
(21, 'Belize Dollar', 0, 'America', 'BZD', 'BZ$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'BZ'),
(22, 'Congolese Franc', 0, 'Africa', 'CDF', 'FC', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'CD'),
(23, 'Swiss Franc', 1, 'Europe', 'CHF', 'Fr', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'CH'),
(24, 'Chilean Peso', 0, 'America', 'CLP', 'CL$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'CL'),
(25, 'Chinese Yuan Renminbi', 1, 'Asia', 'CNY', 'CNĽ', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'CN'),
(26, 'Colombian Peso', 0, 'America', 'COP', 'CO$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'CO'),
(27, 'Costa Rican Colón', 0, 'America', 'CRC', '₡', 1, 0, '0000-00-00 00:00:00', '2020-08-17 12:09:00', 798, 1, 1, 'CR'),
(28, 'Cape Verdean Escudo', 0, 'Africa', 'CVE', 'CV$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'CV'),
(29, 'Czech Koruna', 0, 'Europe', 'CZK', 'Kc', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'CZ'),
(30, 'Djiboutian Franc', 0, 'Africa', 'DJF', 'Fdj', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'DJ'),
(31, 'Danish Krone', 1, 'Europe', 'DKK', 'Dkr', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'DK'),
(32, 'Dominican Peso', 0, 'America', 'DOP', 'RD$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'DO'),
(33, 'Algerian Dinar', 0, 'Africa', 'DZD', 'دج', 1, 0, '0000-00-00 00:00:00', '2020-08-17 12:32:00', 798, 1, 1, 'DZ'),
(35, 'Egyptian Pound', 0, 'Africa', 'EGP', 'EGP', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'EG'),
(36, 'Eritrean Nakfa', 0, 'Africa', 'ERN', 'Nfk', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'ER'),
(37, 'Ethiopian Birr', 0, 'Africa', 'ETB', 'Br', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'ET'),
(38, 'British Pound Sterling', 1, 'Europe', 'GBP', '£', 1, 0, '0000-00-00 00:00:00', '2020-08-17 11:32:00', 798, 1, 1, 'GB'),
(39, 'Georgian Lari', 0, 'Asia', 'GEL', 'GEL', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'GE'),
(40, 'Ghanaian Cedi', 0, 'Africa', 'GHS', 'GH₵', 1, 0, '0000-00-00 00:00:00', '2020-08-17 12:09:00', 798, 1, 1, 'GH'),
(41, 'Guinean Franc', 0, 'Africa', 'GNF', 'FG', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'GN'),
(42, 'Guatemalan Quetzal', 0, 'America', 'GTQ', 'GTQ', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'GT'),
(43, 'Hong Kong Dollar', 1, 'Asia', 'HKD', 'HK$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'HK'),
(44, 'Honduran Lempira', 0, 'America', 'HNL', 'HNL', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'HN'),
(45, 'Croatian Kuna', 0, 'Europe', 'HRK', 'kn', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'HR'),
(46, 'Hungarian Forint', 0, 'Europe', 'HUF', 'Ft', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'HU'),
(47, 'Indonesian Rupiah', 0, 'Asia', 'IDR', 'Rp', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'ID'),
(48, 'Israeli New Sheqel', 0, 'Asia', 'ILS', '₪', 1, 0, '0000-00-00 00:00:00', '2020-08-17 12:10:00', 798, 1, 1, 'IL'),
(49, 'Indian Rupee', 1, 'Asia', 'INR', 'Rs', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'IN'),
(50, 'Iraqi Dinar', 0, 'MEA', 'IQD', 'IQD', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'IQ'),
(51, 'Iranian Rial', 0, 'MEA', 'IRR', 'IRR', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'IR'),
(52, 'Icelandic Króna', 0, 'Europe', 'ISK', 'Ikr', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'IS'),
(53, 'Jamaican Dollar', 0, 'America', 'JMD', 'J$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'JM'),
(54, 'Jordanian Dinar', 0, 'MEA', 'JOD', 'JD', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'JO'),
(55, 'Japanese Yen', 1, 'Asia', 'JPY', 'Ľ', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'JP'),
(56, 'Kenyan Shilling', 0, 'Africa', 'KES', 'Ksh', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'KE'),
(57, 'Cambodian Riel', 0, 'Asia', 'KHR', 'KHR', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'KH'),
(58, 'Comorian Franc', 0, 'Africa', 'KMF', 'CF', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'KM'),
(59, 'South Korean Won', 1, 'Asia', 'KRW', '₩', 1, 0, '0000-00-00 00:00:00', '2020-08-17 12:08:00', 798, 1, 1, 'KR'),
(60, 'Kuwaiti Dinar', 1, 'MEA', 'KWD', 'KD', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'KW'),
(61, 'Kazakhstani Tenge', 0, 'Asia', 'KZT', 'KZT', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'KZ'),
(62, 'Lebanese Pound', 0, 'MEA', 'LBP', 'LBŁ', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'LB'),
(63, 'Sri Lankan Rupee', 0, 'Asia', 'LKR', 'SLRs', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'LK'),
(66, 'Libyan Dinar', 0, 'MEA', 'LYD', 'LD', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'LY'),
(67, 'Moroccan Dirham', 0, 'Africa', 'MAD', 'MAD', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'MA'),
(68, 'Moldovan Leu', 0, 'Europe', 'MDL', 'MDL', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'MD'),
(69, 'Malagasy Ariary', 0, 'Asia', 'MGA', 'MGA', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'MG'),
(70, 'Macedonian Denar', 0, 'Europe', 'MKD', 'MKD', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'MK'),
(71, 'Myanma Kyat', 0, 'Asia', 'MMK', 'MMK', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'MM'),
(72, 'Macanese Pataca', 0, 'Asia', 'MOP', 'MOP$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'MO'),
(73, 'Mauritian Rupee', 0, 'Africa', 'MUR', 'MURs', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'MU'),
(74, 'Mexican Peso', 1, 'America', 'MXN', 'MX$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'MX'),
(75, 'Malaysian Ringgit', 1, 'Asia', 'MYR', 'RM', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'MY'),
(76, 'Mozambican Metical', 0, 'Africa', 'MZN', 'MTn', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'MZ'),
(77, 'Namibian Dollar', 0, 'Africa', 'NAD', 'N$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'NA'),
(78, 'Nigerian Naira', 0, 'Africa', 'NGN', '₦', 1, 0, '0000-00-00 00:00:00', '2020-08-17 12:10:00', 798, 1, 1, 'NG'),
(79, 'Nicaraguan Córdoba', 0, 'America', 'NIO', 'C$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'NI'),
(80, 'Norwegian Krone', 1, 'Europe', 'NOK', 'Nkr', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'NO'),
(81, 'Nepalese Rupee', 0, 'Asia', 'NPR', 'NPRs', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'NP'),
(82, 'New Zealand Dollar', 1, 'Pacific', 'NZD', 'NZ$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'NZ'),
(83, 'Omani Rial', 0, 'MEA', 'OMR', 'OMR', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'OM'),
(84, 'Panamanian Balboa', 0, 'America', 'PAB', 'B/.', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'PA'),
(85, 'Peruvian Nuevo Sol', 0, 'America', 'PEN', 'S/.', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'PE'),
(86, 'Philippine Peso', 0, 'Asia', 'PHP', '₱', 1, 0, '0000-00-00 00:00:00', '2020-08-17 12:11:00', 798, 1, 1, 'PH'),
(87, 'Pakistani Rupee', 0, 'Asia', 'PKR', 'PKRs', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'PK'),
(88, 'Polish Zloty', 1, 'Europe', 'PLN', 'zł', 1, 0, '0000-00-00 00:00:00', '2020-08-17 12:11:00', 798, 1, 1, 'PL'),
(89, 'Paraguayan Guarani', 0, 'America', 'PYG', '₲', 1, 0, '0000-00-00 00:00:00', '2020-08-17 12:11:00', 798, 1, 1, 'PY'),
(90, 'Qatari Rial', 0, 'MEA', 'QAR', 'QR', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'QA'),
(91, 'Romanian Leu', 0, 'Europe', 'RON', 'RON', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'RO'),
(92, 'Serbian Dinar', 0, 'Europe', 'RSD', 'din.', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'CS'),
(93, 'Russian Ruble', 1, 'Europe', 'RUB', 'руб', 1, 0, '0000-00-00 00:00:00', '2017-08-20 16:38:47', 798, 1, 1, 'RU'),
(94, 'Rwandan Franc', 0, 'Africa', 'RWF', 'RWF', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'RW'),
(95, 'Saudi Riyal', 0, 'MEA', 'SAR', 'SR', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'SA'),
(96, 'Sudanese Pound', 0, 'Africa', 'SDG', 'SDG', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'SD'),
(97, 'Swedish Krona', 1, 'Europe', 'SEK', 'Skr', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'SE'),
(98, 'Singapore Dollar', 1, 'Asia', 'SGD', 'S$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'SG'),
(100, 'Syrian Pound', 0, 'MEA', 'SYP', 'SYŁ', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'SY'),
(101, 'Thai Baht', 1, 'Asia', 'THB', '฿', 1, 0, '0000-00-00 00:00:00', '2020-08-17 12:11:00', 798, 1, 1, 'TH'),
(102, 'Tunisian Dinar', 0, 'Africa', 'TND', 'DT', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'TN'),
(103, 'Tongan Pa\'anga', 0, 'Pacific', 'TOP', 'T$', 1, 0, '0000-00-00 00:00:00', '2020-08-17 10:56:00', 798, 1, 1, 'TO'),
(104, 'Turkish Lira', 1, 'Europe', 'TRY', 'TL', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'TR'),
(105, 'Trinidad and Tobago Dollar', 0, 'America', 'TTD', 'TT$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'TT'),
(106, 'New Taiwan Dollar', 1, 'Asia', 'TWD', 'NT$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'TW'),
(107, 'Tanzanian Shilling', 0, 'Africa', 'TZS', 'TSh', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'TZ'),
(108, 'Ukrainian Hryvnia', 0, 'Europe', 'UAH', '₴', 1, 0, '0000-00-00 00:00:00', '2020-08-17 12:12:00', 798, 1, 1, 'UA'),
(109, 'Ugandan Shilling', 0, 'Africa', 'UGX', 'USh', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'UG'),
(110, 'Uruguayan Peso', 0, 'America', 'UYU', '$U', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'UY'),
(111, 'Uzbekistan Som', 0, 'Asia', 'UZS', 'UZS', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'UZ'),
(112, 'Venezuelan Bolívar', 0, 'America', 'VEF', 'Bs.F.', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'VE'),
(113, 'Vietnamese Dong', 0, 'Asia', 'VND', '₫', 1, 0, '0000-00-00 00:00:00', '2012-08-17 06:48:00', 798, 2, 1, 'VN'),
(116, 'Yemeni Rial', 0, 'MEA', 'YER', 'YR', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'YE'),
(117, 'South African Rand', 1, 'Africa', 'ZAR', 'R', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'ZA'),
(119, 'Netherlands Antillean guilder', 0, 'America', 'ANG', 'NAƒ', 1, 1, '2020-08-17 11:27:00', '2020-08-17 11:28:00', 798, 2, 1, 'AN'),
(120, 'Angolan Kwanza', 0, 'Africa', 'AOA', 'Kz', 1, 2, '2020-08-17 11:29:00', '2020-08-17 11:29:00', 798, 2, 1, 'AO'),
(121, 'Aruban Florin', 0, 'America', 'AWG', 'Afl', 1, 3, '2020-08-17 11:29:00', '0000-00-00 00:00:00', 0, 1, 1, 'AW'),
(122, 'Barbadian Dollar', 0, 'America', 'BBD', 'Bds$', 1, 4, '2020-08-17 11:29:00', '0000-00-00 00:00:00', 0, 1, 1, 'BB'),
(123, 'Bermudan Dollar', 0, 'America', 'BMD', '$', 1, 5, '2020-08-17 11:30:00', '0000-00-00 00:00:00', 0, 1, 1, 'BM'),
(124, 'Zimbabwean Dollar', 0, 'Africa', 'ZWL', '$', 1, 6, '2020-08-17 11:31:00', '2020-08-17 11:32:00', 798, 2, 1, 'ZW'),
(126, 'Bhutanese Ngultrum', 0, 'Asia', 'BTN', 'Nu.', 1, 8, '2020-08-17 11:57:00', '0000-00-00 00:00:00', 0, 1, 1, 'BT'),
(127, 'Maldivian Rufiyaa', 0, 'Europe', 'MVR', 'Rf', 1, 9, '2020-08-17 12:01:00', '0000-00-00 00:00:00', 0, 1, 1, 'MV'),
(128, 'Mauritanian Ouguiya', 0, 'Africa', 'MRO', 'UM', 1, 10, '2020-08-17 12:01:00', '0000-00-00 00:00:00', 0, 1, 1, 'MR'),
(129, 'Mongolian Tugrik', 0, 'Asia', 'MNT', '₮', 1, 11, '2020-08-17 12:01:00', '0000-00-00 00:00:00', 0, 1, 1, 'MN'),
(130, 'Lesotho Loti', 0, 'Africa', 'LSL', 'L', 1, 12, '2020-08-17 12:02:00', '0000-00-00 00:00:00', 0, 1, 1, 'LS'),
(131, 'Liberian Dollar', 0, 'Africa', 'LRD', 'L$', 1, 13, '2020-08-17 12:02:00', '0000-00-00 00:00:00', 0, 1, 1, 'LR'),
(132, 'Laotian Kip', 0, 'Asia', 'LAK', '₭', 1, 14, '2020-08-17 12:02:00', '0000-00-00 00:00:00', 0, 1, 1, 'LA'),
(136, 'Fijian Dollar', 0, 'Pacific', 'FJD', 'FJ$', 1, 18, '2020-08-17 12:03:00', '0000-00-00 00:00:00', 0, 1, 1, 'FJ'),
(137, 'Falkland Islands Pound', 0, 'America', 'FKP', '£', 1, 19, '2020-08-17 12:03:00', '0000-00-00 00:00:00', 0, 1, 1, 'FK'),
(138, 'Gibraltar Pound', 0, 'Europe', 'GIP', '£', 1, 20, '2020-08-17 12:04:00', '0000-00-00 00:00:00', 0, 1, 1, 'GI'),
(139, 'Gambian Dalasi', 0, 'Africa', 'GMD', 'D', 1, 21, '2020-08-17 12:04:00', '0000-00-00 00:00:00', 0, 1, 1, 'GM'),
(140, 'Guyanese Dollar', 0, 'America', 'GYD', 'G$', 1, 22, '2020-08-17 12:04:00', '0000-00-00 00:00:00', 0, 1, 1, 'GY'),
(141, 'Haitian Gourde', 0, 'America', 'HTG', 'G', 1, 23, '2020-08-17 12:05:00', '0000-00-00 00:00:00', 0, 1, 1, 'HT'),
(142, 'Kyrgyzstani Som', 0, 'Asia', 'KGS', 'лв', 1, 24, '2020-08-17 12:06:00', '0000-00-00 00:00:00', 0, 1, 1, 'KG'),
(143, 'North Korean Won', 0, 'Asia', 'KPW', '₩', 1, 25, '2020-08-17 12:08:00', '0000-00-00 00:00:00', 0, 1, 1, 'KP'),
(144, 'Cayman Islands Dollar', 0, 'America', 'KYD', '$', 1, 26, '2020-08-17 12:12:00', '0000-00-00 00:00:00', 0, 1, 1, 'KY'),
(145, 'West African CFA Franc', 0, 'Africa', 'XOF', 'CFA', 1, 27, '2020-08-17 12:15:00', '0000-00-00 00:00:00', 0, 1, 1, 'ML'),
(146, 'CFP Franc', 0, 'Africa', 'XPF', 'CFP', 1, 28, '2020-08-17 12:16:00', '0000-00-00 00:00:00', 0, 1, 1, 'PF'),
(147, 'East Caribbean Dollar', 0, 'America', 'XCD', '$', 1, 29, '2020-08-17 12:17:00', '0000-00-00 00:00:00', 0, 1, 1, 'MS'),
(148, 'Central African CFA Franc', 0, 'Africa', 'XAF', 'FCFA', 1, 30, '2020-08-17 12:18:00', '0000-00-00 00:00:00', 0, 1, 1, 'CF'),
(149, 'Samoan Tala', 0, 'Pacific', 'WST', 'WS$', 1, 31, '2020-08-17 12:20:00', '0000-00-00 00:00:00', 0, 1, 1, 'WS'),
(150, 'Vanuatu Vatu', 0, 'Pacific', 'VUV', 'VT', 1, 32, '2020-08-17 12:20:00', '0000-00-00 00:00:00', 0, 1, 1, 'VU'),
(151, 'Turkmenistani Manat', 0, 'Asia', 'TMT', 'T', 1, 33, '2020-08-17 12:20:00', '0000-00-00 00:00:00', 0, 1, 1, 'TM'),
(152, 'Tajikistani Somoni', 0, 'Asia', 'TJS', '-', 1, 34, '2020-08-17 12:21:00', '0000-00-00 00:00:00', 0, 1, 1, 'TJ'),
(153, 'Swazi Lilangeni', 0, 'Africa', 'SZL', 'L', 1, 35, '2020-08-17 12:21:00', '0000-00-00 00:00:00', 0, 1, 1, 'SZ'),
(154, 'Salvadoran Colón', 0, 'America', 'SVC', '₡', 1, 36, '2020-08-17 12:22:00', '0000-00-00 00:00:00', 0, 1, 1, 'SV'),
(155, 'São Tomé and Príncipe Dobra', 0, 'Africa', 'STD', 'Db', 1, 37, '2020-08-17 12:22:00', '0000-00-00 00:00:00', 0, 1, 1, 'ST'),
(156, 'Surinamese Dollar', 0, 'America', 'SRD', '$', 1, 38, '2020-08-17 12:22:00', '0000-00-00 00:00:00', 0, 1, 1, 'SR'),
(157, 'Sierra Leonean Leone', 0, 'Africa', 'SLL', 'Le', 1, 39, '2020-08-17 12:22:00', '0000-00-00 00:00:00', 0, 1, 1, 'SL'),
(159, 'Saint Helena Pound', 0, 'Africa', 'SHP', '£', 1, 41, '2020-08-17 12:23:00', '0000-00-00 00:00:00', 0, 1, 1, 'SH'),
(160, 'Seychellois Rupee', 0, 'Africa', 'SCR', 'SRe', 1, 42, '2020-08-17 12:23:00', '0000-00-00 00:00:00', 0, 1, 1, 'SC'),
(161, 'Solomon Islands Dollar', 0, 'Pacific', 'SBD', 'SI$', 1, 43, '2020-08-17 12:24:00', '0000-00-00 00:00:00', 0, 1, 1, 'SB'),
(162, 'Papua New Guinean Kina', 0, 'Pacific', 'PGK', 'K', 1, 44, '2020-08-17 12:24:00', '0000-00-00 00:00:00', 0, 1, 1, 'PG'),
(163, 'Malawian Kwacha', 0, 'Africa', 'MWK', 'MK', 1, 45, '2020-08-17 12:24:00', '0000-00-00 00:00:00', 0, 1, 1, 'MW'),
(166, 'Bitcoin', 1, 'Europe', 'BTC', 'Ƀ', 1, 46, '2017-09-13 22:44:30', '0000-00-00 00:00:00', 0, 1, 1, 'WW'),
(167, 'Bahamas Dollar', 0, 'America', 'BSD', '$', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'BS'),
(168, 'Cuban Peso', 0, 'America', 'CUP', '₱', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'CU'),
(170, 'Somali shilling', 0, 'Africa', 'SOS', 'Sh', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'SO'),
(171, 'Zambian Kwacha', 0, 'Africa', 'ZMW', 'ZK', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0, 0, 'ZM');



ALTER TABLE `#__currencies_all_currencies`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `#__currencies_all_currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

DROP TABLE IF EXISTS `#__currencies_lastconversion`;

CREATE TABLE `#__currencies_lastconversion` (
  `id` int(11) NOT NULL,
  `from` varchar(3) NOT NULL,
  `to` varchar(3) NOT NULL,
  `amount` float(15,8) NOT NULL,
  `ip` text NOT NULL,
  `date_created` datetime NOT NULL,
  `is_real` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


ALTER TABLE `#__currencies_lastconversion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `date_created` (`date_created`);


ALTER TABLE `#__currencies_lastconversion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

DROP TABLE IF EXISTS `#__currencies_rates`;
CREATE TABLE `#__currencies_rates` (
  `id` int(11) NOT NULL,
  `base_currency` varchar(3) NOT NULL,
  `currency2` varchar(3) NOT NULL,
  `rate` decimal(15,8) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


ALTER TABLE `#__currencies_rates`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__currencies_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

DROP TABLE IF EXISTS `#__currencies_rates_current`;
CREATE TABLE `#__currencies_rates_current` (
  `id` int(11) NOT NULL,
  `base_currency` varchar(3) NOT NULL,
  `currency2` varchar(3) NOT NULL,
  `rate` decimal(15,8) NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


ALTER TABLE `#__currencies_rates_current`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `#__currencies_rates_current`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

DROP TABLE IF EXISTS `#__countries`;
CREATE TABLE `#__countries` (
  `id_countries` int(3) UNSIGNED NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `iso_alpha2` varchar(2) DEFAULT NULL,
  `iso_alpha3` varchar(3) DEFAULT NULL,
  `iso_numeric` int(11) DEFAULT NULL,
  `currency_code` char(3) DEFAULT NULL,
  `currency_name` varchar(32) DEFAULT NULL,
  `currrency_symbol` varchar(3) DEFAULT NULL,
  `flag` varchar(6) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `#__countries` (`id_countries`, `name`, `iso_alpha2`, `iso_alpha3`, `iso_numeric`, `currency_code`, `currency_name`, `currrency_symbol`, `flag`) VALUES
(1, 'Afghanistan', 'AF', 'AFG', 4, 'AFN', 'Afghani', '؋', 'AF.png'),
(2, 'Albania', 'AL', 'ALB', 8, 'ALL', 'Lek', 'L', 'AL.png'),
(3, 'Algeria', 'DZ', 'DZA', 12, 'DZD', 'Dinar', 'دج', 'DZ.png'),
(4, 'American Samoa', 'AS', 'ASM', 16, 'USD', 'Dollar', '$', 'AS.png'),
(5, 'Andorra', 'AD', 'AND', 20, 'EUR', 'Euro', '€', 'AD.png'),
(6, 'Angola', 'AO', 'AGO', 24, 'AOA', 'Kwanza', 'Kz', 'AO.png'),
(7, 'Anguilla', 'AI', 'AIA', 660, 'XCD', 'Dollar', '$', 'AI.png'),
(8, 'Antarctica', 'AQ', 'ATA', 10, '', '', NULL, 'AQ.png'),
(9, 'Antigua and Barbuda', 'AG', 'ATG', 28, 'XCD', 'Dollar', '$', 'AG.png'),
(10, 'Argentina', 'AR', 'ARG', 32, 'ARS', 'Peso', '$', 'AR.png'),
(11, 'Armenia', 'AM', 'ARM', 51, 'AMD', 'Dram', NULL, 'AM.png'),
(12, 'Aruba', 'AW', 'ABW', 533, 'AWG', 'Guilder', 'ƒ', 'AW.png'),
(13, 'Australia', 'AU', 'AUS', 36, 'AUD', 'Dollar', '$', 'AU.png'),
(14, 'Austria', 'AT', 'AUT', 40, 'EUR', 'Euro', '€', 'AT.png'),
(15, 'Azerbaijan', 'AZ', 'AZE', 31, 'AZN', 'Manat', 'ман', 'AZ.png'),
(16, 'Bahamas', 'BS', 'BHS', 44, 'BSD', 'Dollar', '$', 'BS.png'),
(17, 'Bahrain', 'BH', 'BHR', 48, 'BHD', 'Dinar', NULL, 'BH.png'),
(18, 'Bangladesh', 'BD', 'BGD', 50, 'BDT', 'Taka', NULL, 'BD.png'),
(19, 'Barbados', 'BB', 'BRB', 52, 'BBD', 'Dollar', '$', 'BB.png'),
(20, 'Belarus', 'BY', 'BLR', 112, 'BYN', 'Ruble', 'p.', 'BY.png'),
(21, 'Belgium', 'BE', 'BEL', 56, 'EUR', 'Euro', '€', 'BE.png'),
(22, 'Belize', 'BZ', 'BLZ', 84, 'BZD', 'Dollar', 'BZ$', 'BZ.png'),
(23, 'Benin', 'BJ', 'BEN', 204, 'XOF', 'Franc', NULL, 'BJ.png'),
(24, 'Bermuda', 'BM', 'BMU', 60, 'BMD', 'Dollar', '$', 'BM.png'),
(25, 'Bhutan', 'BT', 'BTN', 64, 'BTN', 'Ngultrum', NULL, 'BT.png'),
(26, 'Bolivia', 'BO', 'BOL', 68, 'BOB', 'Boliviano', '', 'BO.png'),
(27, 'Bosnia and Herzegovina', 'BA', 'BIH', 70, 'BAM', 'Marka', 'KM', 'BA.png'),
(28, 'Botswana', 'BW', 'BWA', 72, 'BWP', 'Pula', 'P', 'BW.png'),
(29, 'Bouvet Island', 'BV', 'BVT', 74, 'NOK', 'Krone', 'kr', 'BV.png'),
(30, 'Brazil', 'BR', 'BRA', 76, 'BRL', 'Real', 'R$', 'BR.png'),
(31, 'British Indian Ocean Territory', 'IO', 'IOT', 86, 'USD', 'Dollar', '$', 'IO.png'),
(32, 'British Virgin Islands', 'VG', 'VGB', 92, 'USD', 'Dollar', '$', 'VG.png'),
(33, 'Brunei', 'BN', 'BRN', 96, 'BND', 'Dollar', '$', 'BN.png'),
(34, 'Bulgaria', 'BG', 'BGR', 100, 'BGN', 'Lev', 'лв', 'BG.png'),
(35, 'Burkina Faso', 'BF', 'BFA', 854, 'XOF', 'Franc', NULL, 'BF.png'),
(36, 'Burundi', 'BI', 'BDI', 108, 'BIF', 'Franc', NULL, 'BI.png'),
(37, 'Cambodia', 'KH', 'KHM', 116, 'KHR', 'Riels', '៛', 'KH.png'),
(38, 'Cameroon', 'CM', 'CMR', 120, 'XAF', 'Franc', 'FCF', 'CM.png'),
(39, 'Canada', 'CA', 'CAN', 124, 'CAD', 'Dollar', '$', 'CA.png'),
(40, 'Cape Verde', 'CV', 'CPV', 132, 'CVE', 'Escudo', NULL, 'CV.png'),
(41, 'Cayman Islands', 'KY', 'CYM', 136, 'KYD', 'Dollar', '$', 'KY.png'),
(42, 'Central African Republic', 'CF', 'CAF', 140, 'XAF', 'Franc', 'FCF', 'CF.png'),
(43, 'Chad', 'TD', 'TCD', 148, 'XAF', 'Franc', NULL, 'TD.png'),
(44, 'Chile', 'CL', 'CHL', 152, 'CLP', 'Peso', NULL, 'CL.png'),
(45, 'China', 'CN', 'CHN', 156, 'CNY', 'Yuan Renminbi', '¥', 'CN.png'),
(46, 'Christmas Island', 'CX', 'CXR', 162, 'AUD', 'Dollar', '$', 'CX.png'),
(47, 'Cocos Islands', 'CC', 'CCK', 166, 'AUD', 'Dollar', '$', 'CC.png'),
(48, 'Colombia', 'CO', 'COL', 170, 'COP', 'Peso', '$', 'CO.png'),
(49, 'Comoros', 'KM', 'COM', 174, 'KMF', 'Franc', NULL, 'KM.png'),
(50, 'Cook Islands', 'CK', 'COK', 184, 'NZD', 'Dollar', '$', 'CK.png'),
(51, 'Costa Rica', 'CR', 'CRI', 188, 'CRC', 'Colon', '₡', 'CR.png'),
(52, 'Croatia', 'HR', 'HRV', 191, 'HRK', 'Kuna', 'kn', 'HR.png'),
(53, 'Cuba', 'CU', 'CUB', 192, 'CUP', 'Peso', '₱', 'CU.png'),
(54, 'Cyprus', 'CY', 'CYP', 196, 'CYP', 'Pound', NULL, 'CY.png'),
(55, 'Czech Republic', 'CZ', 'CZE', 203, 'CZK', 'Koruna', 'Kč', 'CZ.png'),
(56, 'Democratic Republic of the Congo', 'CD', 'COD', 180, 'CDF', 'Franc', 'FC', 'CD.png'),
(57, 'Denmark', 'DK', 'DNK', 208, 'DKK', 'Krone', 'kr', 'DK.png'),
(58, 'Djibouti', 'DJ', 'DJI', 262, 'DJF', 'Franc', NULL, 'DJ.png'),
(59, 'Dominica', 'DM', 'DMA', 212, 'XCD', 'Dollar', '$', 'DM.png'),
(60, 'Dominican Republic', 'DO', 'DOM', 214, 'DOP', 'Peso', 'RD$', 'DO.png'),
(61, 'East Timor', 'TL', 'TLS', 626, 'USD', 'Dollar', '$', 'TL.png'),
(62, 'Ecuador', 'EC', 'ECU', 218, 'USD', 'Dollar', '$', 'EC.png'),
(63, 'Egypt', 'EG', 'EGY', 818, 'EGP', 'Pound', '£', 'EG.png'),
(64, 'El Salvador', 'SV', 'SLV', 222, 'SVC', 'Colone', '$', 'SV.png'),
(65, 'Equatorial Guinea', 'GQ', 'GNQ', 226, 'XAF', 'Franc', 'FCF', 'GQ.png'),
(66, 'Eritrea', 'ER', 'ERI', 232, 'ERN', 'Nakfa', 'Nfk', 'ER.png'),
(67, 'Estonia', 'EE', 'EST', 233, 'EEK', 'Kroon', 'kr', 'EE.png'),
(68, 'Ethiopia', 'ET', 'ETH', 231, 'ETB', 'Birr', NULL, 'ET.png'),
(69, 'Falkland Islands', 'FK', 'FLK', 238, 'FKP', 'Pound', '£', 'FK.png'),
(70, 'Faroe Islands', 'FO', 'FRO', 234, 'DKK', 'Krone', 'kr', 'FO.png'),
(71, 'Fiji', 'FJ', 'FJI', 242, 'FJD', 'Dollar', '$', 'FJ.png'),
(72, 'Finland', 'FI', 'FIN', 246, 'EUR', 'Euro', '€', 'FI.png'),
(73, 'France', 'FR', 'FRA', 250, 'EUR', 'Euro', '€', 'FR.png'),
(74, 'French Guiana', 'GF', 'GUF', 254, 'EUR', 'Euro', '€', 'GF.png'),
(75, 'French Polynesia', 'PF', 'PYF', 258, 'XPF', 'Franc', NULL, 'PF.png'),
(76, 'French Southern Territories', 'TF', 'ATF', 260, 'EUR', 'Euro  ', '€', 'TF.png'),
(77, 'Gabon', 'GA', 'GAB', 266, 'XAF', 'Franc', 'FCF', 'GA.png'),
(78, 'Gambia', 'GM', 'GMB', 270, 'GMD', 'Dalasi', 'D', 'GM.png'),
(79, 'Georgia', 'GE', 'GEO', 268, 'GEL', 'Lari', NULL, 'GE.png'),
(80, 'Germany', 'DE', 'DEU', 276, 'EUR', 'Euro', '€', 'E1.png'),
(81, 'Ghana', 'GH', 'GHA', 288, 'GHS', 'Cedi', '¢', 'GH.png'),
(82, 'Gibraltar', 'GI', 'GIB', 292, 'GIP', 'Pound', '£', 'GI.png'),
(83, 'Greece', 'GR', 'GRC', 300, 'EUR', 'Euro', '€', 'GR.png'),
(84, 'Greenland', 'GL', 'GRL', 304, 'DKK', 'Krone', 'kr', 'GL.png'),
(85, 'Grenada', 'GD', 'GRD', 308, 'XCD', 'Dollar', '$', 'GD.png'),
(86, 'Guadeloupe', 'GP', 'GLP', 312, 'EUR', 'Euro', '€', 'GP.png'),
(87, 'Guam', 'GU', 'GUM', 316, 'USD', 'Dollar', '$', 'GU.png'),
(88, 'Guatemala', 'GT', 'GTM', 320, 'GTQ', 'Quetzal', 'Q', 'GT.png'),
(89, 'Guinea', 'GN', 'GIN', 324, 'GNF', 'Franc', NULL, 'GN.png'),
(90, 'Guinea-Bissau', 'GW', 'GNB', 624, 'XOF', 'Franc', NULL, 'GW.png'),
(91, 'Guyana', 'GY', 'GUY', 328, 'GYD', 'Dollar', '$', 'GY.png'),
(92, 'Haiti', 'HT', 'HTI', 332, 'HTG', 'Gourde', 'G', 'HT.png'),
(93, 'Heard Island and McDonald Islands', 'HM', 'HMD', 334, 'AUD', 'Dollar', '$', 'HM.png'),
(94, 'Honduras', 'HN', 'HND', 340, 'HNL', 'Lempira', 'L', 'HN.png'),
(95, 'Hong Kong', 'HK', 'HKG', 344, 'HKD', 'Dollar', '$', 'HK.png'),
(96, 'Hungary', 'HU', 'HUN', 348, 'HUF', 'Forint', 'Ft', 'HU.png'),
(97, 'Iceland', 'IS', 'ISL', 352, 'ISK', 'Krona', 'kr', 'IS.png'),
(98, 'India', 'IN', 'IND', 356, 'INR', 'Rupee', '₹', 'IN.png'),
(99, 'Indonesia', 'ID', 'IDN', 360, 'IDR', 'Rupiah', 'Rp', 'ID.png'),
(100, 'Iran', 'IR', 'IRN', 364, 'IRR', 'Rial', '﷼', 'IR.png'),
(101, 'Iraq', 'IQ', 'IRQ', 368, 'IQD', 'Dinar', NULL, 'IQ.png'),
(102, 'Ireland', 'IE', 'IRL', 372, 'EUR', 'Euro', '€', 'IE.png'),
(103, 'Israel', 'IL', 'ISR', 376, 'ILS', 'Shekel', '₪', 'IL.png'),
(104, 'Italy', 'IT', 'ITA', 380, 'EUR', 'Euro', '€', 'IT.png'),
(105, 'Ivory Coast', 'CI', 'CIV', 384, 'XOF', 'Franc', NULL, 'CI.png'),
(106, 'Jamaica', 'JM', 'JAM', 388, 'JMD', 'Dollar', '$', 'JM.png'),
(107, 'Japan', 'JP', 'JPN', 392, 'JPY', 'Yen', '¥', 'JP.png'),
(108, 'Jordan', 'JO', 'JOR', 400, 'JOD', 'Dinar', NULL, 'JO.png'),
(109, 'Kazakhstan', 'KZ', 'KAZ', 398, 'KZT', 'Tenge', 'лв', 'KZ.png'),
(110, 'Kenya', 'KE', 'KEN', 404, 'KES', 'Shilling', NULL, 'KE.png'),
(111, 'Kiribati', 'KI', 'KIR', 296, 'AUD', 'Dollar', '$', 'KI.png'),
(112, 'Kuwait', 'KW', 'KWT', 414, 'KWD', 'Dinar', NULL, 'KW.png'),
(113, 'Kyrgyzstan', 'KG', 'KGZ', 417, 'KGS', 'Som', 'лв', 'KG.png'),
(114, 'Laos', 'LA', 'LAO', 418, 'LAK', 'Kip', '₭', 'LA.png'),
(115, 'Latvia', 'LV', 'LVA', 428, 'EUR', 'Euro', 'EU', 'eu.png'),
(116, 'Lebanon', 'LB', 'LBN', 422, 'LBP', 'Pound', '£', 'LB.png'),
(117, 'Lesotho', 'LS', 'LSO', 426, 'LSL', 'Loti', 'L', 'LS.png'),
(118, 'Liberia', 'LR', 'LBR', 430, 'LRD', 'Dollar', '$', 'LR.png'),
(119, 'Libya', 'LY', 'LBY', 434, 'LYD', 'Dinar', NULL, 'LY.png'),
(120, 'Liechtenstein', 'LI', 'LIE', 438, 'CHF', 'Franc', 'Fr', 'LI.png'),
(121, 'Lithuania', 'LT', 'LTU', 440, 'EUR', 'Euro', 'EU', 'eu.png'),
(122, 'Luxembourg', 'LU', 'LUX', 442, 'EUR', 'Euro', '€', 'LU.png'),
(123, 'Macao', 'MO', 'MAC', 446, 'MOP', 'Pataca', 'MOP', 'MO.png'),
(124, 'Macedonia', 'MK', 'MKD', 807, 'MKD', 'Denar', 'ден', 'MK.png'),
(125, 'Madagascar', 'MG', 'MDG', 450, 'MGA', 'Ariary', NULL, 'MG.png'),
(126, 'Malawi', 'MW', 'MWI', 454, 'MWK', 'Kwacha', 'MK', 'MW.png'),
(127, 'Malaysia', 'MY', 'MYS', 458, 'MYR', 'Ringgit', 'RM', 'MY.png'),
(128, 'Maldives', 'MV', 'MDV', 462, 'MVR', 'Rufiyaa', 'Rf', 'MV.png'),
(129, 'Mali', 'ML', 'MLI', 466, 'XOF', 'Franc', NULL, 'ML.png'),
(130, 'Malta', 'MT', 'MLT', 470, 'MTL', 'Lira', NULL, 'MT.png'),
(131, 'Marshall Islands', 'MH', 'MHL', 584, 'USD', 'Dollar', '$', 'MH.png'),
(132, 'Martinique', 'MQ', 'MTQ', 474, 'EUR', 'Euro', '€', 'MQ.png'),
(133, 'Mauritania', 'MR', 'MRT', 478, 'MRO', 'Ouguiya', 'UM', 'MR.png'),
(134, 'Mauritius', 'MU', 'MUS', 480, 'MUR', 'Rupee', '₨', 'MU.png'),
(135, 'Mayotte', 'YT', 'MYT', 175, 'EUR', 'Euro', '€', 'YT.png'),
(136, 'Mexico', 'MX', 'MEX', 484, 'MXN', 'Peso', '$', 'MX.png'),
(137, 'Micronesia', 'FM', 'FSM', 583, 'USD', 'Dollar', '$', 'FM.png'),
(138, 'Moldova', 'MD', 'MDA', 498, 'MDL', 'Leu', NULL, 'MD.png'),
(139, 'Monaco', 'MC', 'MCO', 492, 'EUR', 'Euro', '€', 'MC.png'),
(140, 'Mongolia', 'MN', 'MNG', 496, 'MNT', 'Tugrik', '₮', 'MN.png'),
(141, 'Montserrat', 'MS', 'MSR', 500, 'XCD', 'Dollar', '$', 'MS.png'),
(142, 'Morocco', 'MA', 'MAR', 504, 'MAD', 'Dirham', NULL, 'MA.png'),
(143, 'Mozambique', 'MZ', 'MOZ', 508, 'MZN', 'Meticail', 'MT', 'MZ.png'),
(144, 'Myanmar', 'MM', 'MMR', 104, 'MMK', 'Kyat', 'K', 'MM.png'),
(145, 'Namibia', 'NA', 'NAM', 516, 'NAD', 'Dollar', '$', 'NA.png'),
(146, 'Nauru', 'NR', 'NRU', 520, 'AUD', 'Dollar', '$', 'NR.png'),
(147, 'Nepal', 'NP', 'NPL', 524, 'NPR', 'Rupee', '₨', 'NP.png'),
(148, 'Netherlands', 'NL', 'NLD', 528, 'EUR', 'Euro', '€', 'NL.png'),
(149, 'Netherlands Antilles', 'AN', 'ANT', 530, 'ANG', 'Guilder', 'ƒ', 'AN.png'),
(150, 'New Caledonia', 'NC', 'NCL', 540, 'XPF', 'Franc', NULL, 'NC.png'),
(151, 'New Zealand', 'NZ', 'NZL', 554, 'NZD', 'Dollar', '$', 'NZ.png'),
(152, 'Nicaragua', 'NI', 'NIC', 558, 'NIO', 'Cordoba', 'C$', 'NI.png'),
(153, 'Niger', 'NE', 'NER', 562, 'XOF', 'Franc', NULL, 'NE.png'),
(154, 'Nigeria', 'NG', 'NGA', 566, 'NGN', 'Naira', '₦', 'NG.png'),
(155, 'Niue', 'NU', 'NIU', 570, 'NZD', 'Dollar', '$', 'NU.png'),
(156, 'Norfolk Island', 'NF', 'NFK', 574, 'AUD', 'Dollar', '$', 'NF.png'),
(157, 'North Korea', 'KP', 'PRK', 408, 'KPW', 'Won', '₩', 'KP.png'),
(158, 'Northern Mariana Islands', 'MP', 'MNP', 580, 'USD', 'Dollar', '$', 'MP.png'),
(159, 'Norway', 'NO', 'NOR', 578, 'NOK', 'Krone', 'kr', 'NO.png'),
(160, 'Oman', 'OM', 'OMN', 512, 'OMR', 'Rial', '﷼', 'OM.png'),
(161, 'Pakistan', 'PK', 'PAK', 586, 'PKR', 'Rupee', '₨', 'PK.png'),
(162, 'Palau', 'PW', 'PLW', 585, 'USD', 'Dollar', '$', 'PW.png'),
(163, 'Palestinian Territory', 'PS', 'PSE', 275, 'ILS', 'Shekel', '₪', 'PS.png'),
(164, 'Panama', 'PA', 'PAN', 591, 'PAB', 'Balboa', 'B/.', 'PA.png'),
(165, 'Papua New Guinea', 'PG', 'PNG', 598, 'PGK', 'Kina', NULL, 'PG.png'),
(166, 'Paraguay', 'PY', 'PRY', 600, 'PYG', 'Guarani', 'Gs', 'PY.png'),
(167, 'Peru', 'PE', 'PER', 604, 'PEN', 'Sol', 'S/.', 'PE.png'),
(168, 'Philippines', 'PH', 'PHL', 608, 'PHP', 'Peso', 'Php', 'PH.png'),
(169, 'Pitcairn', 'PN', 'PCN', 612, 'NZD', 'Dollar', '$', 'PN.png'),
(170, 'Poland', 'PL', 'POL', 616, 'PLN', 'Zloty', 'zł', 'PL.png'),
(171, 'Portugal', 'PT', 'PRT', 620, 'EUR', 'Euro', '€', 'PT.png'),
(172, 'Puerto Rico', 'PR', 'PRI', 630, 'USD', 'Dollar', '$', 'PR.png'),
(173, 'Qatar', 'QA', 'QAT', 634, 'QAR', 'Rial', '﷼', 'QA.png'),
(174, 'Republic of the Congo', 'CG', 'COG', 178, 'XAF', 'Franc', 'FCF', 'CG.png'),
(175, 'Reunion', 'RE', 'REU', 638, 'EUR', 'Euro', '€', 'RE.png'),
(176, 'Romania', 'RO', 'ROU', 642, 'RON', 'Leu', 'lei', 'RO.png'),
(177, 'Russia', 'RU', 'RUS', 643, 'RUB', 'Ruble', 'руб', 'RU.png'),
(178, 'Rwanda', 'RW', 'RWA', 646, 'RWF', 'Franc', NULL, 'RW.png'),
(179, 'Saint Helena', 'SH', 'SHN', 654, 'SHP', 'Pound', '£', 'SH.png'),
(180, 'Saint Kitts and Nevis', 'KN', 'KNA', 659, 'XCD', 'Dollar', '$', 'KN.png'),
(181, 'Saint Lucia', 'LC', 'LCA', 662, 'XCD', 'Dollar', '$', 'LC.png'),
(182, 'Saint Pierre and Miquelon', 'PM', 'SPM', 666, 'EUR', 'Euro', '€', 'PM.png'),
(183, 'Saint Vincent and the Grenadines', 'VC', 'VCT', 670, 'XCD', 'Dollar', '$', 'VC.png'),
(184, 'Samoa', 'WS', 'WSM', 882, 'WST', 'Tala', 'WS$', 'WS.png'),
(185, 'San Marino', 'SM', 'SMR', 674, 'EUR', 'Euro', '€', 'SM.png'),
(186, 'Sao Tome and Principe', 'ST', 'STP', 678, 'STD', 'Dobra', 'Db', 'ST.png'),
(187, 'Saudi Arabia', 'SA', 'SAU', 682, 'SAR', 'Rial', '﷼', 'SA.png'),
(188, 'Senegal', 'SN', 'SEN', 686, 'XOF', 'Franc', NULL, 'SN.png'),
(189, 'Serbia', 'CS', 'SCG', 891, 'RSD', 'Dinar', 'Дин', 'RS.png'),
(190, 'Seychelles', 'SC', 'SYC', 690, 'SCR', 'Rupee', '₨', 'SC.png'),
(191, 'Sierra Leone', 'SL', 'SLE', 694, 'SLL', 'Leone', 'Le', 'SL.png'),
(192, 'Singapore', 'SG', 'SGP', 702, 'SGD', 'Dollar', '$', 'SG.png'),
(194, 'Slovenia', 'SI', 'SVN', 705, 'EUR', 'Euro', '€', 'SI.png'),
(195, 'Solomon Islands', 'SB', 'SLB', 90, 'SBD', 'Dollar', '$', 'SB.png'),
(196, 'Somalia', 'SO', 'SOM', 706, 'SOS', 'Shilling', 'Sh', 'SO.png'),
(197, 'South Africa', 'ZA', 'ZAF', 710, 'ZAR', 'Rand', 'R', 'ZA.png'),
(198, 'South Georgia and the South Sandwich Islands', 'GS', 'SGS', 239, 'GBP', 'Pound', '£', 'GS.png'),
(199, 'South Korea', 'KR', 'KOR', 410, 'KRW', 'Won', '₩', 'KR.png'),
(200, 'Spain', 'ES', 'ESP', 724, 'EUR', 'Euro', '€', 'ES.png'),
(201, 'Sri Lanka', 'LK', 'LKA', 144, 'LKR', 'Rupee', '₨', 'LK.png'),
(202, 'Sudan', 'SD', 'SDN', 736, 'SDG', 'Pound', NULL, 'SD.png'),
(203, 'Suriname', 'SR', 'SUR', 740, 'SRD', 'Dollar', '$', 'SR.png'),
(204, 'Svalbard and Jan Mayen', 'SJ', 'SJM', 744, 'NOK', 'Krone', 'kr', 'SJ.png'),
(205, 'Swaziland', 'SZ', 'SWZ', 748, 'SZL', 'Lilangeni', NULL, 'SZ.png'),
(206, 'Sweden', 'SE', 'SWE', 752, 'SEK', 'Krona', 'kr', 'SE.png'),
(207, 'Switzerland', 'CH', 'CHE', 756, 'CHF', 'Franc', 'Fr', 'CH.png'),
(208, 'Syria', 'SY', 'SYR', 760, 'SYP', 'Pound', '£', 'SY.png'),
(209, 'Taiwan', 'TW', 'TWN', 158, 'TWD', 'Dollar', 'NT$', 'TW.png'),
(210, 'Tajikistan', 'TJ', 'TJK', 762, 'TJS', 'Somoni', NULL, 'TJ.png'),
(211, 'Tanzania', 'TZ', 'TZA', 834, 'TZS', 'Shilling', NULL, 'TZ.png'),
(212, 'Thailand', 'TH', 'THA', 764, 'THB', 'Baht', '฿', 'TH.png'),
(213, 'Togo', 'TG', 'TGO', 768, 'XOF', 'Franc', NULL, 'TG.png'),
(214, 'Tokelau', 'TK', 'TKL', 772, 'NZD', 'Dollar', '$', 'TK.png'),
(215, 'Tonga', 'TO', 'TON', 776, 'TOP', 'Pa\'anga', 'T$', 'TO.png'),
(216, 'Trinidad and Tobago', 'TT', 'TTO', 780, 'TTD', 'Dollar', 'TT$', 'TT.png'),
(217, 'Tunisia', 'TN', 'TUN', 788, 'TND', 'Dinar', 'DT', 'TN.png'),
(218, 'Turkey', 'TR', 'TUR', 792, 'TRY', 'Lira', 'YTL', 'TR.png'),
(219, 'Turkmenistan', 'TM', 'TKM', 795, 'TMT', 'Manat', 'm', 'TM.png'),
(220, 'Turks and Caicos Islands', 'TC', 'TCA', 796, 'USD', 'Dollar', '$', 'TC.png'),
(221, 'Tuvalu', 'TV', 'TUV', 798, 'AUD', 'Dollar', '$', 'TV.png'),
(222, 'U.S. Virgin Islands', 'VI', 'VIR', 850, 'USD', 'Dollar', '$', 'VI.png'),
(223, 'Uganda', 'UG', 'UGA', 800, 'UGX', 'Shilling', 'USh', 'UG.png'),
(224, 'Ukraine', 'UA', 'UKR', 804, 'UAH', 'Hryvnia', '₴', 'UA.png'),
(225, 'United Arab Emirates', 'AE', 'ARE', 784, 'AED', 'Dirham', 'د.إ', 'AE.png'),
(226, 'United Kingdom', 'GB', 'GBR', 826, 'GBP', 'Pound', '£', 'GB.png'),
(227, 'United States', 'US', 'USA', 840, 'USD', 'Dollar', '$', 'US.png'),
(228, 'United States Minor Outlying Islands', 'UM', 'UMI', 581, 'USD', 'Dollar ', '$', 'UM.png'),
(229, 'Uruguay', 'UY', 'URY', 858, 'UYU', 'Peso', '', 'UY.png'),
(230, 'Uzbekistan', 'UZ', 'UZB', 860, 'UZS', 'Som', 'лв', 'UZ.png'),
(231, 'Vanuatu', 'VU', 'VUT', 548, 'VUV', 'Vatu', 'Vt', 'VU.png'),
(232, 'Vatican', 'VA', 'VAT', 336, 'EUR', 'Euro', '€', 'VA.png'),
(233, 'Venezuela', 'VE', 'VEN', 862, 'VEF', 'Bolivar', 'Bs', 'VE.png'),
(234, 'Vietnam', 'VN', 'VNM', 704, 'VND', 'Dong', '₫', 'VN.png'),
(235, 'Wallis and Futuna', 'WF', 'WLF', 876, 'XPF', 'Franc', NULL, 'WF.png'),
(236, 'Western Sahara', 'EH', 'ESH', 732, 'MAD', 'Dirham', NULL, 'EH.png'),
(237, 'Yemen', 'YE', 'YEM', 887, 'YER', 'Rial', '﷼', 'YE.png'),
(238, 'Zambia', 'ZM', 'ZMB', 894, 'ZMW', 'Kwacha', 'ZK', 'ZM.png'),
(239, 'Zimbabwe', 'US', 'ZWL', 716, 'USD', 'Dollar', 'Z$', 'US.png'),
(241, 'Worldwide', 'WW', 'WWW', NULL, 'BTC', 'Bitcoin', 'Ƀ', 'WW.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `countries`
--
ALTER TABLE `#__countries`
  ADD PRIMARY KEY (`id_countries`),
  ADD KEY `iso_alpha2` (`iso_alpha2`),
  ADD KEY `currency_code` (`currency_code`),
  ADD KEY `flag` (`flag`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `#__countries`
  MODIFY `id_countries` int(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=242;
COMMIT;

DROP TABLE IF EXISTS `#__rates_history`;
CREATE TABLE `#__rates_history` (
  `rid` int(11) NOT NULL COMMENT 'record id',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `currency_from` varchar(3) NOT NULL,
  `currency_to` varchar(3) NOT NULL,
  `value` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `rates_history`
--
ALTER TABLE `#__rates_history`
  ADD PRIMARY KEY (`rid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `rates_history`
--
ALTER TABLE `#__rates_history`
  MODIFY `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'record id';
COMMIT;