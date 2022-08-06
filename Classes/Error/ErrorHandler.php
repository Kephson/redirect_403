<?php

namespace EHAERER\Redirect403\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

final class ErrorHandler implements PageErrorHandlerInterface
{

    /**
     * the extension key
     */
    const EXTKEY = 'redirect_403';

    /**
     * @var int
     */
    protected $statusCode = 0;

    /**
     * @var string Page id of information page about access
     */
    protected $uriProtectedInfo = '';

    /**
     * @var string Page id of login page
     */
    protected $uriLogin = '';

    /**
     * @var array
     */
    protected $errorHandlerConfiguration;

    public function __construct(int $statusCode, array $configuration)
    {
        $this->statusCode = $statusCode;
        $this->errorHandlerConfiguration = $configuration;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $message
     * @param array $reasons
     * @return ResponseInterface
     * @throws AspectNotFoundException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function handlePageError(
        ServerRequestInterface $request,
        string                 $message,
        array                  $reasons = []
    ): ResponseInterface
    {
        $site = $request->getAttribute('site');
        $siteConfig = $site->getConfiguration();
        $this->checkPageIdsFromSiteConfig($siteConfig);

        if ($this->statusCode === 403) {
            /* check whether user is logged in */
            $context = GeneralUtility::makeInstance(Context::class);
            if ($context->getPropertyFromAspect('frontend.user', 'isLoggedIn') && $this->uriProtectedInfo) {
                /* show page with info that the access restricted page can't be visited because of missing access rights */
                return new RedirectResponse($this->uriProtectedInfo);
            }
            if ($this->uriLogin) {
                return new RedirectResponse($this->uriLogin . '?return_url=' . $request->getUri()->getPath(), 403);
            }
        }
        return new NullResponse();
    }

    /**
     * @param array $siteConfig Site config array
     * @return void
     */
    private function checkPageIdsFromSiteConfig($siteConfig): void
    {
        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        $uriLogin = $contentObject->typoLink_URL($this->uriLoginUid);

        if (isset($siteConfig['errorHandling'])) {
            foreach ($siteConfig['errorHandling'] as $errorHandler) {
                if (isset($errorHandler['protectedInfoUid'])) {
                    $uriProtectedInfoUid = $errorHandler['protectedInfoUid'];
                    $this->uriProtectedInfo = $contentObject->typoLink_URL($uriProtectedInfoUid);
                }
                if (isset($errorHandler['loginPageUid'])) {
                    $uriLoginUid = $errorHandler['loginPageUid'];
                    $this->uriLogin = $contentObject->typoLink_URL($uriLoginUid);
                }
            }
        }
    }

}
