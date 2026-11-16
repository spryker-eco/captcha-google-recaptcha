<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\CaptchaGoogleRecaptcha\Config;

use Codeception\Test\Unit;
use SprykerEco\Client\CaptchaGoogleRecaptcha\CaptchaGoogleRecaptchaConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Client
 * @group CaptchaGoogleRecaptcha
 * @group Config
 * @group CaptchaGoogleRecaptchaConfigTest
 * Add your own group annotations below this line
 */
class CaptchaGoogleRecaptchaConfigTest extends Unit
{
    protected const float DEFAULT_SCORE_THRESHOLD = 0.5;

    /**
     * @dataProvider provideStoredScoreThresholds
     */
    public function testReturnsStoredScoreThresholdOrDefaultForValueThatIsNoNumber(mixed $storedScoreThreshold, float $expectedScoreThreshold): void
    {
        // Arrange
        $captchaGoogleRecaptchaConfig = $this->createConfigWithStoredValue($storedScoreThreshold);

        // Act
        $scoreThreshold = $captchaGoogleRecaptchaConfig->getScoreThreshold();

        // Assert
        $this->assertSame($expectedScoreThreshold, $scoreThreshold);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function provideStoredScoreThresholds(): array
    {
        return [
            'stored minimum' => ['0.7', 0.7],
            'not configured' => [null, static::DEFAULT_SCORE_THRESHOLD],
            'emptied setting' => ['', static::DEFAULT_SCORE_THRESHOLD],
            'value that is no number' => ['high', static::DEFAULT_SCORE_THRESHOLD],
        ];
    }

    /**
     * @dataProvider provideStoredAllowedHostnames
     *
     * @param list<string> $expectedAllowedHostnames
     */
    public function testReturnsStoredAllowedHostnamesLowercasedWithoutEmptyEntries(?string $storedAllowedHostnames, array $expectedAllowedHostnames): void
    {
        // Arrange
        $captchaGoogleRecaptchaConfig = $this->createConfigWithStoredValue($storedAllowedHostnames);

        // Act
        $allowedHostnames = $captchaGoogleRecaptchaConfig->getAllowedHostnames();

        // Assert
        $this->assertSame($expectedAllowedHostnames, $allowedHostnames);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function provideStoredAllowedHostnames(): array
    {
        return [
            'not configured' => [null, []],
            'emptied setting' => ['', []],
            'one host' => ['shop.example.com', ['shop.example.com']],
            'hosts separated by commas and spaces' => [' Shop.Example.com, backoffice.example.com ,,mp.example.com ', ['shop.example.com', 'backoffice.example.com', 'mp.example.com']],
        ];
    }

    protected function createConfigWithStoredValue(mixed $storedValue): CaptchaGoogleRecaptchaConfig
    {
        return new class ($storedValue) extends CaptchaGoogleRecaptchaConfig {
            public function __construct(protected mixed $storedValue)
            {
            }

            protected function getModuleConfig(string $key, $default = null, array $configurationScopes = []): mixed
            {
                return $this->storedValue ?? $default;
            }
        };
    }
}
