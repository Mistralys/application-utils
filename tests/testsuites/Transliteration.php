<?php

use PHPUnit\Framework\TestCase;

final class TransliterationTest extends TestCase
{
    public function testFrench()
    {
        $tests = array(
            array(
                'label' => 'French',
                'text' => 'Être où ne pas être, été après été ça est nôtre devise à Pétaouschnock.',
                'expected' => 'etre-ou-ne-pas-etre-ete-apres-ete-ca-est-notre-devise-a-petaouschnock'
            ),
            array(
                'label' => 'Spanish',
                'text' => '¿Que pasarà con mi? ¡La combinación más al año!',
                'expected' => 'que-pasara-con-mi-la-combinacion-mas-al-ano'
            ),
            array(
                'label' => 'German',
                'text' => 'Über Öl Ändern Straße',
                'expected' => 'ueber-oel-aendern-strasse'
            ),
            array(
                'label' => 'Punctuation',
                'text' => 'Test Punctuation.:-;,/()[]{}\\-+%$&§=#\'~*|<>	€„“',
                'expected' => 'test-punctuation-plus-percent-dollar-and-equals-hash-euro'
            ),
            array(
                'label' => 'Unknown character',
                'text' => 'Þ',
                'expected' => ''
            ),
            array(
                'label' => 'Empty string',
                'text' => '',
                'expected' => ''
            ),
            array(
                'label' => 'Whitespace string',
                'text' => "     \n    \r    \t      \r\n   ",
                'expected' => ''
            )
        );
        
        foreach($tests as $test)
        {
            $result = \AppUtils\ConvertHelper::transliterate($test['text']);
            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }
}
