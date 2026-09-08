<?php

namespace App\Services\Khqr;

/**
 * Builds a KHQR (Cambodia Bakong QR) payload string for an Individual
 * Bakong account, following the EMVCo "Merchant Presented Mode" QR
 * structure that KHQR is based on: a sequence of Tag-Length-Value (TLV)
 * fields terminated by a CRC-16/CCITT-FALSE checksum.
 *
 * Reference tags used (EMVCo core + Bakong's Individual account extension):
 *   00 Payload Format Indicator   01 Point of Initiation Method
 *   29 Merchant Account Info      52 Merchant Category Code
 *   53 Transaction Currency       54 Transaction Amount
 *   58 Country Code               59 Merchant/Account Name
 *   60 Merchant City              62 Additional Data (bill number)
 *   63 CRC
 *
 * Generate a code with this class, then render it with any QR image
 * library (this app uses milon/barcode's DNS2D::getBarcodeSVG($payload,
 * 'QRCODE')) — verify a generated code with a real Bakong-linked banking
 * app before relying on it for real payments.
 */
class KhqrCode
{
    private const PAYLOAD_FORMAT_INDICATOR = '01';

    private const STATIC_QR = '11';

    private const DYNAMIC_QR = '12';

    private const MERCHANT_CATEGORY_CODE = '5999';

    private const COUNTRY_CODE = 'KH';

    private const CURRENCY_USD = '840';

    /**
     * @param  string  $bakongAccountId  the Individual Bakong Account ID (e.g. "name@bank")
     * @param  string  $accountName  display name shown in the payer's banking app
     * @param  string  $merchantCity  city shown in the payer's banking app
     * @param  float|null  $amount  fixed amount for a one-time (dynamic) QR; null for a reusable (static) QR
     * @param  string|null  $billNumber  optional reference (e.g. a sale/invoice number) for reconciliation
     */
    public static function generateIndividual(
        string $bakongAccountId,
        string $accountName,
        string $merchantCity,
        ?float $amount = null,
        ?string $billNumber = null,
    ): string {
        $tags = [
            '00' => self::PAYLOAD_FORMAT_INDICATOR,
            '01' => $amount !== null ? self::DYNAMIC_QR : self::STATIC_QR,
            '29' => self::field('00', $bakongAccountId),
            '52' => self::MERCHANT_CATEGORY_CODE,
            '53' => self::CURRENCY_USD,
        ];

        if ($amount !== null) {
            $tags['54'] = number_format($amount, 2, '.', '');
        }

        $tags['58'] = self::COUNTRY_CODE;
        $tags['59'] = substr(strtoupper($accountName), 0, 25);
        $tags['60'] = substr(strtoupper($merchantCity), 0, 15);

        if ($billNumber !== null && $billNumber !== '') {
            $tags['62'] = self::field('01', substr($billNumber, 0, 25));
        }

        $payload = '';
        foreach ($tags as $id => $value) {
            $payload .= self::field($id, $value);
        }

        $payload .= '6304';

        return $payload.self::crc16($payload);
    }

    private static function field(string $id, string $value): string
    {
        return $id.str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT).$value;
    }

    /**
     * CRC-16/CCITT-FALSE (poly 0x1021, init 0xFFFF) — the checksum algorithm
     * EMVCo-based QR codes (KHQR included) use for their trailing tag 63.
     */
    public static function crc16(string $data): string
    {
        $crc = 0xFFFF;

        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $crc ^= (ord($data[$i]) << 8);

            for ($bit = 0; $bit < 8; $bit++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
