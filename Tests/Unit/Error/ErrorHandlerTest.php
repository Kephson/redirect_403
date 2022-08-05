<?php

namespace EHAERER\Redirect403\Tests\Unit\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ErrorHandlerTest extends UnitTestCase
{

    /**
     * the extension key
     */
    const EXTKEY = 'redirect_403';

    protected function setUp(): void
    {

    }

    /**
     * @test
     */
    public function isExtKeyValid(): void
    {
        self::assertContainsEquals('redirect_403', self::EXTKEY);
    }
}
