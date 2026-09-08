<?php

namespace Tests\Unit;

use App\Services\Khqr\KhqrCode;
use PHPUnit\Framework\TestCase;

class KhqrCodeTest extends TestCase
{
    public function test_crc16_matches_the_standard_ccitt_false_test_vector(): void
    {
        // The canonical CRC-16/CCITT-FALSE check value for the ASCII string "123456789".
        $this->assertSame('29B1', KhqrCode::crc16('123456789'));
    }

    public function test_generated_payload_is_a_well_formed_tlv_string_with_a_matching_crc(): void
    {
        $payload = KhqrCode::generateIndividual(
            bakongAccountId: 'jdoe@bank',
            accountName: 'John Doe',
            merchantCity: 'Phnom Penh',
            amount: 12.34,
            billNumber: 'SALE-42',
        );

        $fields = $this->parseTlv($payload);

        $this->assertSame('01', $fields['00']);
        $this->assertSame('12', $fields['01']); // dynamic, because an amount was given
        $this->assertSame('00' . '09' . 'jdoe@bank', $fields['29']);
        $this->assertSame('5999', $fields['52']);
        $this->assertSame('840', $fields['53']);
        $this->assertSame('12.34', $fields['54']);
        $this->assertSame('KH', $fields['58']);
        $this->assertSame('JOHN DOE', $fields['59']);
        $this->assertSame('PHNOM PENH', $fields['60']);
        $this->assertSame('01' . '07' . 'SALE-42', $fields['62']);

        $payloadUpToCrcTag = substr($payload, 0, -4); // includes the trailing "6304" tag+length itself
        $claimedCrc = substr($payload, -4);
        $this->assertSame(KhqrCode::crc16($payloadUpToCrcTag), $claimedCrc);
    }

    public function test_point_of_initiation_is_static_and_amount_is_omitted_when_no_amount_given(): void
    {
        $payload = KhqrCode::generateIndividual(
            bakongAccountId: 'jdoe@bank',
            accountName: 'John Doe',
            merchantCity: 'Phnom Penh',
        );

        $fields = $this->parseTlv($payload);

        $this->assertSame('11', $fields['01']);
        $this->assertArrayNotHasKey('54', $fields);
    }

    /**
     * @return array<string, string>
     */
    private function parseTlv(string $payload): array
    {
        $withoutCrc = substr($payload, 0, -4);
        $fields = [];
        $i = 0;
        $len = strlen($withoutCrc);

        while ($i < $len) {
            $tag = substr($withoutCrc, $i, 2);
            $valueLength = (int) substr($withoutCrc, $i + 2, 2);
            $value = substr($withoutCrc, $i + 4, $valueLength);
            $fields[$tag] = $value;
            $i += 4 + $valueLength;
        }

        return $fields;
    }
}
