<?php

namespace App\Services\Fintech;

class BanxicoClabeValidator
{
    /**
     * Official Mexican Bank / Institution Codes (Catálogo de Instituciones Bancarias Banxico / ABM)
     */
    public const INSTITUTIONS = [
        '002' => 'BANAMEX (Citibanamex)',
        '006' => 'BANCOMEXT',
        '009' => 'BANOBRAS',
        '012' => 'BBVA MÉXICO',
        '014' => 'SANTANDER',
        '019' => 'BANJERCITO',
        '021' => 'HSBC',
        '030' => 'BAJIO (Banco del Bajío)',
        '032' => 'IXE BANCO',
        '036' => 'INBURSA',
        '037' => 'INTERACCIONES',
        '042' => 'MIFEL',
        '044' => 'SCOTIABANK',
        '058' => 'BANREGIO',
        '059' => 'INVEX',
        '060' => 'BANSI',
        '062' => 'AFIRME',
        '072' => 'BANORTE',
        '102' => 'THE ROYAL BANK OF SCOTLAND',
        '103' => 'AMERICAN EXPRESS',
        '106' => 'BAMSA',
        '108' => 'TOKYO',
        '110' => 'JP MORGAN',
        '112' => 'BMONEX (Monex)',
        '113' => 'VE POR MAS',
        '116' => 'ING',
        '124' => 'DEUTSCHE',
        '126' => 'CREDIT SUISSE',
        '127' => 'BANCO AZTECA',
        '128' => 'AUTOFIN',
        '129' => 'BARCLAYS',
        '130' => 'COMPARTAMOS',
        '131' => 'BANCO FAMSA',
        '132' => 'BMULTIVA (Multiva)',
        '133' => 'ACTINVER',
        '134' => 'WAL-MART',
        '135' => 'NAFIN',
        '136' => 'INTERCAM BANCO',
        '137' => 'BANCOPPEL',
        '138' => 'ABC CAPITAL',
        '139' => 'UBS BANK',
        '140' => 'CONSUBANCO',
        '141' => 'VOLKSWAGEN',
        '143' => 'CIBANCO',
        '145' => 'BBASE (Banco Base)',
        '166' => 'BANSEFI / BANCO DEL BIENESTAR',
        '168' => 'HIPOTECARIA FEDERAL',
        '600' => 'MONEXCB',
        '601' => 'GBM',
        '602' => 'MASARI',
        '605' => 'VALUE',
        '606' => 'ESTRUCTURADORES',
        '607' => 'TIBER',
        '608' => 'VECTOR',
        '610' => 'B&B',
        '614' => 'ACCIVAL',
        '615' => 'MERRILL LYNCH',
        '616' => 'FINAMEX',
        '617' => 'VALMEX',
        '618' => 'UNICA',
        '619' => 'MAPFRE',
        '620' => 'PROFUTURO',
        '621' => 'CB ACTINVER',
        '622' => 'OACTIN',
        '623' => 'SKANDIA',
        '626' => 'CBDEUTSCHE',
        '627' => 'ZURICH',
        '628' => 'ZURICHVI',
        '629' => 'SU CASITA',
        '630' => 'CB INTERCAM',
        '631' => 'CI BOLSA',
        '632' => 'BULLTICK CB',
        '633' => 'STERLING',
        '634' => 'FINCOMUN',
        '636' => 'HDI SEGUROS',
        '637' => 'ORDER',
        '638' => 'NU MÉXICO (Nu)',
        '640' => 'CB JPMORGAN',
        '642' => 'REFORMA',
        '646' => 'STP (Sistema de Transferencias y Pagos)',
        '647' => 'TELECOMM / FINABIEN',
        '648' => 'EVERCORE',
        '649' => 'SKANDIA',
        '651' => 'SEGMTY',
        '652' => 'ASEA',
        '653' => 'KUSPIT',
        '655' => 'SOFIEXPRESS',
        '656' => 'UNAGRA',
        '659' => 'OPCIONES EMPRESARIALES DEL NOROESTE (Spin by OXXO)',
        '670' => 'LIBERTAD',
        '677' => 'CAJA POPULAR MEXICANA',
        '680' => 'CRISTOBAL COLON',
        '683' => 'CAJA TELEFONISTAS',
        '684' => 'TRANSFERENCIAS Y PAGOS (TPM)',
        '685' => 'FONDO (FND)',
        '686' => 'INVERCAP',
        '689' => 'FOMPED',
        '706' => 'KLAR (Servicios Financieros Alternativos)',
        '710' => 'MERCADO PAGO WALLET',
        '846' => 'STP',
        '901' => 'CLS',
        '902' => 'INDEVAL',
    ];

    /**
     * Weights used for Banxico standard Modulo 10 checksum
     */
    private const WEIGHTS = [3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7];

    /**
     * Validate an 18-digit CLABE interbancaria.
     *
     * @param string $clabe
     * @return array{isValid: bool, bankCode: ?string, bankName: ?string, errorMessage: ?string}
     */
    public static function validate(string $clabe): array
    {
        $clabe = trim($clabe);

        // 1. Must be exactly 18 numeric digits
        if (!preg_match('/^\d{18}$/', $clabe)) {
            return [
                'isValid' => false,
                'bankCode' => null,
                'bankName' => null,
                'errorMessage' => 'La CLABE interbancaria debe tener exactamente 18 dígitos numéricos.'
            ];
        }

        // 2. Bank code lookup (first 3 digits)
        $bankCode = substr($clabe, 0, 3);
        $bankName = self::INSTITUTIONS[$bankCode] ?? null;

        if (!$bankName) {
            return [
                'isValid' => false,
                'bankCode' => $bankCode,
                'bankName' => null,
                'errorMessage' => "El código de banco ({$bankCode}) no corresponde a ninguna institución financiera autorizada en México."
            ];
        }

        // 3. Modulo 10 Checksum calculation
        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $digit = (int) $clabe[$i];
            $weight = self::WEIGHTS[$i];
            $sum += ($digit * $weight) % 10;
        }

        $calculatedControlDigit = (10 - ($sum % 10)) % 10;
        $providedControlDigit = (int) $clabe[17];

        if ($calculatedControlDigit !== $providedControlDigit) {
            return [
                'isValid' => false,
                'bankCode' => $bankCode,
                'bankName' => $bankName,
                'errorMessage' => 'El dígito verificador de la CLABE es inválido. Verifique los 18 dígitos de su cuenta.'
            ];
        }

        return [
            'isValid' => true,
            'bankCode' => $bankCode,
            'bankName' => $bankName,
            'errorMessage' => null
        ];
    }

    /**
     * Get institution name from bank code
     */
    public static function getBankName(string $bankCode): ?string
    {
        return self::INSTITUTIONS[$bankCode] ?? null;
    }
}
