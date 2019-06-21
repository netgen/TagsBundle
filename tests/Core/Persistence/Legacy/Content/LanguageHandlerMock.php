<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Content;

use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * Simple mock provider for a Language\Handler.
 */
class LanguageHandlerMock
{
    private $languages = [];

    public function __construct()
    {
        $this->languages['eng-US'] = new Language(
            [
                'id' => 2,
                'languageCode' => 'eng-US',
                'name' => 'English (American)',
            ]
        );
        $this->languages['ger-DE'] = new Language(
            [
                'id' => 4,
                'languageCode' => 'ger-DE',
                'name' => 'German',
            ]
        );
        $this->languages['eng-GB'] = new Language(
            [
                'id' => 8,
                'languageCode' => 'eng-GB',
                'name' => 'English (United Kingdom)',
            ]
        );
    }

    public function __invoke(TestCase $testCase)
    {
        $mock = $testCase->getMockBuilder(LanguageHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($testCase::any())
            ->method('load')
            ->willReturnMap(
                [
                    [2, $this->languages['eng-US']],
                    [4, $this->languages['ger-DE']],
                    [8, $this->languages['eng-GB']],
                    ['2', $this->languages['eng-US']],
                    ['4', $this->languages['ger-DE']],
                    ['8', $this->languages['eng-GB']],
                ]
            );

        $mock->expects($testCase::any())
            ->method('loadByLanguageCode')
            ->willReturnMap(
                [
                    ['eng-US', $this->languages['eng-US']],
                    ['ger-DE', $this->languages['ger-DE']],
                    ['eng-GB', $this->languages['eng-GB']],
                ]
            );

        $mock->expects($testCase::any())
            ->method('loadListByLanguageCodes')
            ->willReturnCallback(
                function (array $languageCodes): array {
                    return iterator_to_array(
                        (function () use ($languageCodes): Generator {
                            foreach ($languageCodes as $languageCode) {
                                yield $languageCode => $this->languages[$languageCode];
                            }
                        })()
                    );
                }
            );

        $mock->expects($testCase::any())
            ->method('loadAll')
            ->willReturn(array_values($this->languages));

        return $mock;
    }
}
