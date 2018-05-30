<?php

namespace CommerceGuys\Addressing\Country;

use CommerceGuys\Addressing\Locale;
use CommerceGuys\Addressing\Exception\UnknownCountryException;

/**
 * Manages countries based on JSON definitions.
 */
class CountryRepository implements CountryRepositoryInterface
{
    /**
     * The default locale.
     *
     * @var string
     */
    protected $defaultLocale;

    /**
     * The fallback locale.
     *
     * @var string
     */
    protected $fallbackLocale;

    /**
     * The path where per-locale definitions are stored.
     *
     * @var string
     */
    protected $definitionPath;

    /**
     * Base country definitions.
     *
     * Contains data common to all locales, such as the country numeric,
     * three-letter, currency codes.
     *
     * @var array
     */
    protected $baseDefinitions = [];

    /**
     * Per-locale country definitions.
     *
     * @var array
     */
    protected $definitions = [];

    /**
     * The available locales.
     *
     * @var array
     */
    protected $availableLocales = [
        'af', 'agq', 'ak', 'am', 'ar', 'ar-LY', 'ar-SA', 'as', 'asa', 'ast',
        'az', 'az-Cyrl', 'bas', 'be', 'bez', 'bg', 'bm', 'bn', 'bn-IN', 'br',
        'brx', 'bs', 'bs-Cyrl', 'ca', 'ccp', 'ce', 'cgg', 'chr', 'ckb', 'cs',
        'cy', 'da', 'dav', 'de', 'de-AT', 'de-CH', 'dje', 'dsb', 'dyo', 'dz',
        'ebu', 'ee', 'el', 'en', 'en-GB', 'eo', 'es', 'es-419', 'es-AR',
        'es-BO', 'es-CL', 'es-CO', 'es-CR', 'es-DO', 'es-EC', 'es-GT', 'es-HN',
        'es-MX', 'es-NI', 'es-PA', 'es-PE', 'es-PR', 'es-PY', 'es-SV', 'es-US',
        'es-VE', 'et', 'eu', 'ewo', 'fa', 'fa-AF', 'ff', 'fi', 'fil', 'fo',
        'fr', 'fr-BE', 'fr-CA', 'fur', 'fy', 'ga', 'gd', 'gl', 'gsw', 'gu',
        'guz', 'ha', 'he', 'hi', 'hr', 'hsb', 'hu', 'hy', 'id', 'is', 'it',
        'ja', 'jgo', 'jmc', 'ka', 'kab', 'kam', 'kde', 'kea', 'khq', 'ki',
        'kk', 'kln', 'km', 'kn', 'ko', 'ko-KP', 'kok', 'ks', 'ksb', 'ksf',
        'ksh', 'ky', 'lag', 'lb', 'lg', 'ln', 'lo', 'lt', 'lu', 'luo', 'luy',
        'lv', 'mas', 'mer', 'mfe', 'mg', 'mgh', 'mk', 'ml', 'mn', 'mr', 'ms',
        'mt', 'mua', 'my', 'mzn', 'naq', 'nb', 'nd', 'ne', 'nl', 'nmg', 'nn',
        'nyn', 'or', 'pa', 'pl', 'ps', 'pt', 'pt-PT', 'qu', 'rm', 'rn',
        'ro', 'ro-MD', 'rof', 'ru', 'ru-UA', 'rwk', 'saq', 'sbp', 'sd', 'se',
        'se-FI', 'seh', 'ses', 'sg', 'shi', 'shi-Latn', 'si', 'sk', 'sl',
        'smn', 'sn', 'so', 'sq', 'sr', 'sr-Cyrl-BA', 'sr-Cyrl-ME',
        'sr-Cyrl-XK', 'sr-Latn', 'sr-Latn-BA', 'sr-Latn-ME', 'sr-Latn-XK',
        'sv', 'sw', 'sw-CD', 'sw-KE', 'ta', 'te', 'teo', 'tg', 'th', 'ti',
        'tk', 'to', 'tr', 'tt', 'twq', 'tzm', 'ug', 'uk', 'ur', 'ur-IN', 'uz',
        'uz-Cyrl', 'vai', 'vai-Latn', 'vi', 'vun', 'wae', 'wo', 'xog', 'yav',
        'yi', 'yo', 'yo-BJ', 'yue', 'yue-Hans', 'zgh', 'zh', 'zh-Hant',
        'zh-Hant-HK', 'zu',
    ];

    /**
     * Creates a CountryRepository instance.
     *
     * @param string $defaultLocale  The default locale. Defaults to 'en'.
     * @param string $fallbackLocale The fallback locale. Defaults to 'en'.
     * @param string $definitionPath The path to the country definitions.
     *                               Defaults to 'resources/country'.
     */
    public function __construct($defaultLocale = 'en', $fallbackLocale = 'en', $definitionPath = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->fallbackLocale = $fallbackLocale;
        $this->definitionPath = $definitionPath ? $definitionPath : __DIR__ . '/../../resources/country/';
    }

    /**
     * {@inheritdoc}
     */
    public function get($countryCode, $locale = null)
    {
        $countryCode = strtoupper($countryCode);
        $baseDefinitions = $this->getBaseDefinitions();
        if (!isset($baseDefinitions[$countryCode])) {
            throw new UnknownCountryException($countryCode);
        }
        $locale = $locale ?: $this->defaultLocale;
        $locale = Locale::resolve($this->availableLocales, $locale, $this->fallbackLocale);
        $definitions = $this->loadDefinitions($locale);
        $country = new Country([
            'country_code' => $countryCode,
            'name' => $definitions[$countryCode],
            'three_letter_code' => $baseDefinitions[$countryCode][0],
            'numeric_code' => $baseDefinitions[$countryCode][1],
            'currency_code' => $baseDefinitions[$countryCode][2],
            'locale' => $locale,
        ]);

        return $country;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll($locale = null)
    {
        $locale = $locale ?: $this->defaultLocale;
        $locale = Locale::resolve($this->availableLocales, $locale, $this->fallbackLocale);
        $baseDefinitions = $this->getBaseDefinitions();
        $definitions = $this->loadDefinitions($locale);
        $countries = [];
        foreach ($definitions as $countryCode => $countryName) {
            $countries[$countryCode] = new Country([
                'country_code' => $countryCode,
                'name' => $countryName,
                'three_letter_code' => $baseDefinitions[$countryCode][0],
                'numeric_code' => $baseDefinitions[$countryCode][1],
                'currency_code' => $baseDefinitions[$countryCode][2],
                'locale' => $locale,
            ]);
        }

        return $countries;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($locale = null)
    {
        $locale = $locale ?: $this->defaultLocale;
        $locale = Locale::resolve($this->availableLocales, $locale, $this->fallbackLocale);
        $definitions = $this->loadDefinitions($locale);
        $list = [];
        foreach ($definitions as $countryCode => $countryName) {
            $list[$countryCode] = $countryName;
        }

        return $list;
    }

    /**
     * Loads the country definitions for the provided locale.
     *
     * @param string $locale The desired locale.
     *
     * @return array
     */
    protected function loadDefinitions($locale)
    {
        if (!isset($this->definitions[$locale])) {
            $filename = $this->definitionPath . $locale . '.json';
            $this->definitions[$locale] = json_decode(file_get_contents($filename), true);
        }

        return $this->definitions[$locale];
    }

    /**
     * Gets the base country definitions.
     *
     * Contains data common to all locales: three letter code, numeric code.
     *
     * @return array
     *   An array of definitions, keyed by country code.
     *   Each definition is a numerically indexed array containing:
     *   - The three letter code.
     *   - The numeric code.
     *   - The currency code.
     */
    protected function getBaseDefinitions()
    {
        return [
            'AC' => ['ASC', null, 'SHP'],
            'AD' => ['AND', '020', 'EUR'],
            'AE' => ['ARE', '784', 'AED'],
            'AF' => ['AFG', '004', 'AFN'],
            'AG' => ['ATG', '028', 'XCD'],
            'AI' => ['AIA', '660', 'XCD'],
            'AL' => ['ALB', '008', 'ALL'],
            'AM' => ['ARM', '051', 'AMD'],
            'AO' => ['AGO', '024', 'AOA'],
            'AQ' => ['ATA', '010', null],
            'AR' => ['ARG', '032', 'ARS'],
            'AS' => ['ASM', '016', 'USD'],
            'AT' => ['AUT', '040', 'EUR'],
            'AU' => ['AUS', '036', 'AUD'],
            'AW' => ['ABW', '533', 'AWG'],
            'AX' => ['ALA', '248', 'EUR'],
            'AZ' => ['AZE', '031', 'AZN'],
            'BA' => ['BIH', '070', 'BAM'],
            'BB' => ['BRB', '052', 'BBD'],
            'BD' => ['BGD', '050', 'BDT'],
            'BE' => ['BEL', '056', 'EUR'],
            'BF' => ['BFA', '854', 'XOF'],
            'BG' => ['BGR', '100', 'BGN'],
            'BH' => ['BHR', '048', 'BHD'],
            'BI' => ['BDI', '108', 'BIF'],
            'BJ' => ['BEN', '204', 'XOF'],
            'BL' => ['BLM', '652', 'EUR'],
            'BM' => ['BMU', '060', 'BMD'],
            'BN' => ['BRN', '096', 'BND'],
            'BO' => ['BOL', '068', 'BOB'],
            'BQ' => ['BES', '535', 'USD'],
            'BR' => ['BRA', '076', 'BRL'],
            'BS' => ['BHS', '044', 'BSD'],
            'BT' => ['BTN', '064', 'BTN'],
            'BV' => ['BVT', '074', 'NOK'],
            'BW' => ['BWA', '072', 'BWP'],
            'BY' => ['BLR', '112', 'BYN'],
            'BZ' => ['BLZ', '084', 'BZD'],
            'CA' => ['CAN', '124', 'CAD'],
            'CC' => ['CCK', '166', 'AUD'],
            'CD' => ['COD', '180', 'CDF'],
            'CF' => ['CAF', '140', 'XAF'],
            'CG' => ['COG', '178', 'XAF'],
            'CH' => ['CHE', '756', 'CHF'],
            'CI' => ['CIV', '384', 'XOF'],
            'CK' => ['COK', '184', 'NZD'],
            'CL' => ['CHL', '152', 'CLP'],
            'CM' => ['CMR', '120', 'XAF'],
            'CN' => ['CHN', '156', 'CNY'],
            'CO' => ['COL', '170', 'COP'],
            'CP' => ['CPT', null, null],
            'CR' => ['CRI', '188', 'CRC'],
            'CU' => ['CUB', '192', 'CUC'],
            'CV' => ['CPV', '132', 'CVE'],
            'CW' => ['CUW', '531', 'ANG'],
            'CX' => ['CXR', '162', 'AUD'],
            'CY' => ['CYP', '196', 'EUR'],
            'CZ' => ['CZE', '203', 'CZK'],
            'DE' => ['DEU', '276', 'EUR'],
            'DG' => ['DGA', null, 'USD'],
            'DJ' => ['DJI', '262', 'DJF'],
            'DK' => ['DNK', '208', 'DKK'],
            'DM' => ['DMA', '212', 'XCD'],
            'DO' => ['DOM', '214', 'DOP'],
            'DZ' => ['DZA', '012', 'DZD'],
            'EA' => [null, null, 'EUR'],
            'EC' => ['ECU', '218', 'USD'],
            'EE' => ['EST', '233', 'EUR'],
            'EG' => ['EGY', '818', 'EGP'],
            'EH' => ['ESH', '732', 'MAD'],
            'ER' => ['ERI', '232', 'ERN'],
            'ES' => ['ESP', '724', 'EUR'],
            'ET' => ['ETH', '231', 'ETB'],
            'FI' => ['FIN', '246', 'EUR'],
            'FJ' => ['FJI', '242', 'FJD'],
            'FK' => ['FLK', '238', 'FKP'],
            'FM' => ['FSM', '583', 'USD'],
            'FO' => ['FRO', '234', 'DKK'],
            'FR' => ['FRA', '250', 'EUR'],
            'GA' => ['GAB', '266', 'XAF'],
            'GB' => ['GBR', '826', 'GBP'],
            'GD' => ['GRD', '308', 'XCD'],
            'GE' => ['GEO', '268', 'GEL'],
            'GF' => ['GUF', '254', 'EUR'],
            'GG' => ['GGY', '831', 'GBP'],
            'GH' => ['GHA', '288', 'GHS'],
            'GI' => ['GIB', '292', 'GIP'],
            'GL' => ['GRL', '304', 'DKK'],
            'GM' => ['GMB', '270', 'GMD'],
            'GN' => ['GIN', '324', 'GNF'],
            'GP' => ['GLP', '312', 'EUR'],
            'GQ' => ['GNQ', '226', 'XAF'],
            'GR' => ['GRC', '300', 'EUR'],
            'GS' => ['SGS', '239', 'GBP'],
            'GT' => ['GTM', '320', 'GTQ'],
            'GU' => ['GUM', '316', 'USD'],
            'GW' => ['GNB', '624', 'XOF'],
            'GY' => ['GUY', '328', 'GYD'],
            'HK' => ['HKG', '344', 'HKD'],
            'HM' => ['HMD', '334', 'AUD'],
            'HN' => ['HND', '340', 'HNL'],
            'HR' => ['HRV', '191', 'HRK'],
            'HT' => ['HTI', '332', 'USD'],
            'HU' => ['HUN', '348', 'HUF'],
            'IC' => [null, null, 'EUR'],
            'ID' => ['IDN', '360', 'IDR'],
            'IE' => ['IRL', '372', 'EUR'],
            'IL' => ['ISR', '376', 'ILS'],
            'IM' => ['IMN', '833', 'GBP'],
            'IN' => ['IND', '356', 'INR'],
            'IO' => ['IOT', '086', 'USD'],
            'IQ' => ['IRQ', '368', 'IQD'],
            'IR' => ['IRN', '364', 'IRR'],
            'IS' => ['ISL', '352', 'ISK'],
            'IT' => ['ITA', '380', 'EUR'],
            'JE' => ['JEY', '832', 'GBP'],
            'JM' => ['JAM', '388', 'JMD'],
            'JO' => ['JOR', '400', 'JOD'],
            'JP' => ['JPN', '392', 'JPY'],
            'KE' => ['KEN', '404', 'KES'],
            'KG' => ['KGZ', '417', 'KGS'],
            'KH' => ['KHM', '116', 'KHR'],
            'KI' => ['KIR', '296', 'AUD'],
            'KM' => ['COM', '174', 'KMF'],
            'KN' => ['KNA', '659', 'XCD'],
            'KP' => ['PRK', '408', 'KPW'],
            'KR' => ['KOR', '410', 'KRW'],
            'KW' => ['KWT', '414', 'KWD'],
            'KY' => ['CYM', '136', 'KYD'],
            'KZ' => ['KAZ', '398', 'KZT'],
            'LA' => ['LAO', '418', 'LAK'],
            'LB' => ['LBN', '422', 'LBP'],
            'LC' => ['LCA', '662', 'XCD'],
            'LI' => ['LIE', '438', 'CHF'],
            'LK' => ['LKA', '144', 'LKR'],
            'LR' => ['LBR', '430', 'LRD'],
            'LS' => ['LSO', '426', 'LSL'],
            'LT' => ['LTU', '440', 'EUR'],
            'LU' => ['LUX', '442', 'EUR'],
            'LV' => ['LVA', '428', 'EUR'],
            'LY' => ['LBY', '434', 'LYD'],
            'MA' => ['MAR', '504', 'MAD'],
            'MC' => ['MCO', '492', 'EUR'],
            'MD' => ['MDA', '498', 'MDL'],
            'ME' => ['MNE', '499', 'EUR'],
            'MF' => ['MAF', '663', 'EUR'],
            'MG' => ['MDG', '450', 'MGA'],
            'MH' => ['MHL', '584', 'USD'],
            'MK' => ['MKD', '807', 'MKD'],
            'ML' => ['MLI', '466', 'XOF'],
            'MM' => ['MMR', '104', 'MMK'],
            'MN' => ['MNG', '496', 'MNT'],
            'MO' => ['MAC', '446', 'MOP'],
            'MP' => ['MNP', '580', 'USD'],
            'MQ' => ['MTQ', '474', 'EUR'],
            'MR' => ['MRT', '478', 'MRU'],
            'MS' => ['MSR', '500', 'XCD'],
            'MT' => ['MLT', '470', 'EUR'],
            'MU' => ['MUS', '480', 'MUR'],
            'MV' => ['MDV', '462', 'MVR'],
            'MW' => ['MWI', '454', 'MWK'],
            'MX' => ['MEX', '484', 'MXN'],
            'MY' => ['MYS', '458', 'MYR'],
            'MZ' => ['MOZ', '508', 'MZN'],
            'NA' => ['NAM', '516', 'NAD'],
            'NC' => ['NCL', '540', 'XPF'],
            'NE' => ['NER', '562', 'XOF'],
            'NF' => ['NFK', '574', 'AUD'],
            'NG' => ['NGA', '566', 'NGN'],
            'NI' => ['NIC', '558', 'NIO'],
            'NL' => ['NLD', '528', 'EUR'],
            'NO' => ['NOR', '578', 'NOK'],
            'NP' => ['NPL', '524', 'NPR'],
            'NR' => ['NRU', '520', 'AUD'],
            'NU' => ['NIU', '570', 'NZD'],
            'NZ' => ['NZL', '554', 'NZD'],
            'OM' => ['OMN', '512', 'OMR'],
            'PA' => ['PAN', '591', 'USD'],
            'PE' => ['PER', '604', 'PEN'],
            'PF' => ['PYF', '258', 'XPF'],
            'PG' => ['PNG', '598', 'PGK'],
            'PH' => ['PHL', '608', 'PHP'],
            'PK' => ['PAK', '586', 'PKR'],
            'PL' => ['POL', '616', 'PLN'],
            'PM' => ['SPM', '666', 'EUR'],
            'PN' => ['PCN', '612', 'NZD'],
            'PR' => ['PRI', '630', 'USD'],
            'PS' => ['PSE', '275', 'JOD'],
            'PT' => ['PRT', '620', 'EUR'],
            'PW' => ['PLW', '585', 'USD'],
            'PY' => ['PRY', '600', 'PYG'],
            'QA' => ['QAT', '634', 'QAR'],
            'RE' => ['REU', '638', 'EUR'],
            'RO' => ['ROU', '642', 'RON'],
            'RS' => ['SRB', '688', 'RSD'],
            'RU' => ['RUS', '643', 'RUB'],
            'RW' => ['RWA', '646', 'RWF'],
            'SA' => ['SAU', '682', 'SAR'],
            'SB' => ['SLB', '090', 'SBD'],
            'SC' => ['SYC', '690', 'SCR'],
            'SD' => ['SDN', '729', 'SDG'],
            'SE' => ['SWE', '752', 'SEK'],
            'SG' => ['SGP', '702', 'SGD'],
            'SH' => ['SHN', '654', 'SHP'],
            'SI' => ['SVN', '705', 'EUR'],
            'SJ' => ['SJM', '744', 'NOK'],
            'SK' => ['SVK', '703', 'EUR'],
            'SL' => ['SLE', '694', 'SLL'],
            'SM' => ['SMR', '674', 'EUR'],
            'SN' => ['SEN', '686', 'XOF'],
            'SO' => ['SOM', '706', 'SOS'],
            'SR' => ['SUR', '740', 'SRD'],
            'SS' => ['SSD', '728', 'SSP'],
            'ST' => ['STP', '678', 'STN'],
            'SV' => ['SLV', '222', 'USD'],
            'SX' => ['SXM', '534', 'ANG'],
            'SY' => ['SYR', '760', 'SYP'],
            'SZ' => ['SWZ', '748', 'SZL'],
            'TA' => ['TAA', null, 'GBP'],
            'TC' => ['TCA', '796', 'USD'],
            'TD' => ['TCD', '148', 'XAF'],
            'TF' => ['ATF', '260', 'EUR'],
            'TG' => ['TGO', '768', 'XOF'],
            'TH' => ['THA', '764', 'THB'],
            'TJ' => ['TJK', '762', 'TJS'],
            'TK' => ['TKL', '772', 'NZD'],
            'TL' => ['TLS', '626', 'USD'],
            'TM' => ['TKM', '795', 'TMT'],
            'TN' => ['TUN', '788', 'TND'],
            'TO' => ['TON', '776', 'TOP'],
            'TR' => ['TUR', '792', 'TRY'],
            'TT' => ['TTO', '780', 'TTD'],
            'TV' => ['TUV', '798', 'AUD'],
            'TW' => ['TWN', '158', 'TWD'],
            'TZ' => ['TZA', '834', 'TZS'],
            'UA' => ['UKR', '804', 'UAH'],
            'UG' => ['UGA', '800', 'UGX'],
            'UM' => ['UMI', '581', 'USD'],
            'US' => ['USA', '840', 'USD'],
            'UY' => ['URY', '858', 'UYU'],
            'UZ' => ['UZB', '860', 'UZS'],
            'VA' => ['VAT', '336', 'EUR'],
            'VC' => ['VCT', '670', 'XCD'],
            'VE' => ['VEN', '862', 'VEF'],
            'VG' => ['VGB', '092', 'GBP'],
            'VI' => ['VIR', '850', 'USD'],
            'VN' => ['VNM', '704', 'VND'],
            'VU' => ['VUT', '548', 'VUV'],
            'WF' => ['WLF', '876', 'XPF'],
            'WS' => ['WSM', '882', 'WST'],
            'XK' => ['XKK', '983', 'EUR'],
            'YE' => ['YEM', '887', 'YER'],
            'YT' => ['MYT', '175', 'EUR'],
            'ZA' => ['ZAF', '710', 'ZAR'],
            'ZM' => ['ZMB', '894', 'ZMW'],
            'ZW' => ['ZWE', '716', 'USD'],
        ];
    }
}
