<?php

declare(strict_types=1);

require_once '../connections/includes.php';

use PHPUnit\Framework\TestCase;

class TestIncludes extends TestCase
{
    private const S = 's';

    /**
     * @dataProvider profilesProvider
     */
    public function testEmptyFirstAfter(array $profiles, int $t, array $expected)
    {
        $this->assertEquals($expected, firstAfter($profiles, self::S, $t));
    }

    public function profilesProvider(): \Generator
    {
        yield 'no profile for stop' => [
            [],
            5,
            [INF, INF, null, null]
        ];

        yield 'INF profile' => [
            [
                self::S => [
                    [INF, INF, null, null]
                ],
            ],
            5,
            [INF, INF, null, null]
        ];

        yield 'unmatched profile' => [
            [
                self::S => [
                    [0, 10, 1, 2],
                ],
            ],
            5,
            [INF, INF, null, null]
        ];

        yield 'unmatched profile + INF' => [
            [
                self::S => [
                    [0, 10, 1, 2],
                    [INF, INF, null, null]
                ],
            ],
            5,
            [INF, INF, null, null]
        ];

        yield 'only matched profile' => [
            [
                self::S => [
                    [6, 10, 1, 2],
                ],
            ],
            5,
            [6, 10, 1, 2]
        ];

        yield 'unmatched + matched profile' => [
            [
                self::S => [
                    [0, 10, 1, 2],
                    [11, 21, 3, 4],
                ],
            ],
            5,
            [11, 21, 3, 4]
        ];

        yield 'two matched profiles' => [
            [
                self::S => [
                    [11, 21, 3, 4],
                    [21, 22, 3, 4],
                ],
            ],
            5,
            [11, 21, 3, 4]
        ];
    }
}
