<?php

namespace EHAERER\Redirect403\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Routing\InvalidRouteArgumentsException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\LinkHandling\LinkService;

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
     */
    public function handlePageError(
        ServerRequestInterface $request,
        string                 $message,
        array                  $reasons = []
    ): ResponseInterface
    {
        if ($this->statusCode === 403) {
            /** @var Site $site */
            $site = $request->getAttribute('site');
            $siteConfig = $site->getConfiguration();
            $this->checkPageIdsFromSiteConfig($request, $siteConfig);
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
     * @param ServerRequestInterface $request
     * @param array $siteConfig Site config array
     * @return void
     * @throws InvalidRouteArgumentsException
     * @throws SiteNotFoundException
     */
    private function checkPageIdsFromSiteConfig(ServerRequestInterface $request, array $siteConfig): void
    {
        if (isset($siteConfig['errorHandling'])) {
            foreach ($siteConfig['errorHandling'] as $errorHandler) {
                if (isset($errorHandler['protectedInfoLink']) && !empty($errorHandler['protectedInfoLink'])) {
                    $protectedInfoLink = $errorHandler['protectedInfoLink'];
                    $this->uriProtectedInfo = $this->resolveUrl($request, $protectedInfoLink);
                }
                if (isset($errorHandler['loginPageLink']) && !empty($errorHandler['loginPageLink'])) {
                    $loginPageLink = $errorHandler['loginPageLink'];
                    $this->uriLogin = $this->resolveUrl($request, $loginPageLink);
                }
            }
        }
    }


    /**
     * Resolve the URL (currently only page and external URL are supported)
     *
     * @param ServerRequestInterface $request
     * @param string $typoLinkUrl
     * @return string
     * @throws SiteNotFoundException
     * @throws InvalidRouteArgumentsException
     */
    protected function resolveUrl(ServerRequestInterface $request, string $typoLinkUrl): string
    {
        $linkService = GeneralUtility::makeInstance(LinkService::class);
        $urlParams = $linkService->resolve($typoLinkUrl);
        if ($urlParams['type'] !== 'page' && $urlParams['type'] !== 'url') {
            throw new \InvalidArgumentException('PageContentErrorHandler can only handle TYPO3 urls of types "page" or "url"', 1660127039);
        }
        if ($urlParams['type'] === 'url') {
            return $urlParams['url'];
        }

        $this->pageUid = (int)$urlParams['pageuid'];

        // Get the site related to the configured error page
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($this->pageUid);
        // Fall back to current request for the site
        if (!$site instanceof Site) {
            $site = $request->getAttribute('site', null);
        }
        /** @var SiteLanguage $requestLanguage */
        $requestLanguage = $request->getAttribute('language', null);
        // Try to get the current request language from the site that was found above
        if ($requestLanguage instanceof SiteLanguage && $requestLanguage->isEnabled()) {
            try {
                $language = $site->getLanguageById($requestLanguage->getLanguageId());
            } catch (\InvalidArgumentException $e) {
                $language = $site->getDefaultLanguage();
            }
        } else {
            $language = $site->getDefaultLanguage();
        }

        // Build Url
        $uri = $site->getRouter()->generateUri(
            (int)$urlParams['pageuid'],
            ['_language' => $language]
        );

        // Fallback to the current URL if the site is not having a proper scheme and host
        $currentUri = $request->getUri();
        if (empty($uri->getScheme())) {
            $uri = $uri->withScheme($currentUri->getScheme());
        }
        if (empty($uri->getUserInfo())) {
            $uri = $uri->withUserInfo($currentUri->getUserInfo());
        }
        if (empty($uri->getHost())) {
            $uri = $uri->withHost($currentUri->getHost());
        }
        if ($uri->getPort() === null) {
            $uri = $uri->withPort($currentUri->getPort());
        }

        return (string)$uri;
    }

}
