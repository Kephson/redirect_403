<?php

namespace EHAERER\Redirect403\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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
    protected $statusCode;

    /**
     * @var string Page id of information page about access
     */
    protected $uriProtectedInfoUid;

    /**
     * @var string Page id of login page
     */
    protected $uriLoginUid;

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
        $extSettings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(self::EXTKEY);
        $this->uriProtectedInfoUid = $extSettings['protected_info_uid'];
        $this->uriLoginUid = $extSettings['login_page_uid'];

        $site = $request->getAttribute('site');
        $siteConfig = $site->getConfiguration();
        $this->checkPageIdsFromSiteConfig($siteConfig);

        $cobj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $uriProtectedInfo = $cobj->typoLink_URL($this->uriProtectedInfoUid);
        $uriLogin = $cobj->typoLink_URL($this->uriLoginUid);

        if ($this->statusCode === 403) {
            /* check whether user is logged in */
            $context = GeneralUtility::makeInstance(Context::class);
            if ($context->getPropertyFromAspect('frontend.user', 'isLoggedIn')) {
                //show page with info that the access restricted page can't be visited because of missing access rights
                return new RedirectResponse($uriProtectedInfo);
            }
            return new RedirectResponse($uriLogin . '?return_url=' . $request->getUri()->getPath(), 403);
        }
        return new NullResponse();
    }

    /**
     * @param array $siteConfig Site config array
     * @return void
     */
    private function checkPageIdsFromSiteConfig($siteConfig)
    {
        if (isset($siteConfig['errorHandling'])) {
            foreach ($siteConfig['errorHandling'] as $errorHandler) {
                if ($errorHandler['errorCode'] === 403) {
                    if (isset($errorHandler['protectedInfoUid'])) {
                        $this->uriProtectedInfoUid = $errorHandler['protectedInfoUid'];
                    }
                    if (isset($errorHandler['loginPageUid'])) {
                        $this->uriLoginUid = $errorHandler['loginPageUid'];
                    }
                }
            }
        }
    }

}
