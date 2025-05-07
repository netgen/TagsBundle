<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Content;

use Generator;
use Ibexa\Contracts\Core\Persistence\Content\Language;
use Ibexa\Contracts\Core\Persistence\Content\Language\Handler as LanguageHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function array_values;
use function iterator_to_array;

/**
 * Simple mock provider for a Language\Handler.
 */
final class LanguageHandlerMock
{
    /**
     * @var \Ibexa\Contracts\Core\Persistence\Content\Language[]
     */
    private array $languages = [];

    public function __construct()
    {
        $this->languages['eng-US'] = new Language(
            [
                'id' => 2,
                'languageCode' => 'eng-US',
                'name' => 'English (American)',
            ],
        );
        $this->languages['ger-DE'] = new Language(
            [
                'id' => 4,
                'languageCode' => 'ger-DE',
                'name' => 'German',
            ],
        );
        $this->languages['eng-GB'] = new Language(
            [
                'id' => 8,
                'languageCode' => 'eng-GB',
                'name' => 'English (United Kingdom)',
            ],
        );
    }

    public function __invoke(TestCase $testCase): LanguageHandler&MockObject
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
                ],
            );

        $mock->expects($testCase::any())
            ->method('loadByLanguageCode')
            ->willReturnMap(
                [
                    ['eng-US', $this->languages['eng-US']],
                    ['ger-DE', $this->languages['ger-DE']],
                    ['eng-GB', $this->languages['eng-GB']],
                ],
            );

        $mock->expects($testCase::any())
            ->method('loadListByLanguageCodes')
            ->willReturnCallback(
                fn (array $languageCodes): array => iterator_to_array(
                    (function () use ($languageCodes): Generator {
                        foreach ($languageCodes as $languageCode) {
                            yield $languageCode => $this->languages[$languageCode];
                        }
                    })(),
                ),
            );

        $mock->expects($testCase::any())
            ->method('loadAll')
            ->willReturn(array_values($this->languages));

        return $mock;
    }
}
