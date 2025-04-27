<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Fiedsch\DownloadsBundle\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentDownloads;
use Contao\ContentModel;
use Contao\ContentText;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Date;
use Contao\Input;
use Contao\Template;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Fiedsch\DownloadsBundle\Model\DownloadsTokensModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(type: TokenDownloadsController::TYPE, category: 'files')]
class TokenDownloadsController extends AbstractContentElementController
{
    const string TYPE = 'token_downloads';

    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly LoggerInterface $contaoErrorLogger, // see https://docs.contao.org/dev/framework/logging/#injecting-the-correct-logger-service
    ) {
    }

    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        // Scope bestimmen, um im Backend einen Platzhalter anzuzeigen, denn sonst
        // bekommen wir (mangels auto_item bzw. Zugriff darauf) eine PageNotFound Exception!
        if ($this->scopeMatcher->isBackendRequest($request)) {
            $backendTemplate = new BackendTemplate('be_wildcard');
            /** @noinspection PhpUndefinedFieldInspection */
            $backendTemplate->wildcard = '### ' . $GLOBALS['TL_LANG']['CTE']['token_downloads'][0] . ' ###';

            return new Response($backendTemplate->parse());
        }

        $token = Input::get('auto_item');
        // $token = $request->query->get('auto_item'); // will not work as Contao does not mark the auto:item parameter as read/used then

        if (null === $token) {
            throw new PageNotFoundException("Seite nicht gefunden");
        }

        // Ablauf:
        // * Anhand des Tokens bestimmen welche Dateien zum Download angeboten werden sollen.
        // * Token und anzuzeigende Dateien werden in tl_downloads_tokens verwaltet (Anzeige als Backend-Tabelle).
        // * Mit diesen Informationen ein dynamisches Downloads-Model erzeugen und rendern.

        /** @noinspection PhpUndefinedFieldInspection */
        $template->token = $token;

        $downloadsTokensModel = DownloadsTokensModel::findByToken($token);
        if (null === $downloadsTokensModel) {
            throw new PageNotFoundException("Seite nicht gefunden");
        }
        if (!$downloadsTokensModel->isHidden()) {
            $contentElement = $this->getDownloadsContentElement($downloadsTokensModel);
            /** @noinspection PhpUndefinedFieldInspection */
            $template->type = "tokens_downloads_valid";
        } else {
            $contentElement = $this->getTextContentElement($downloadsTokensModel);
            /** @noinspection PhpUndefinedFieldInspection */
            $template->type = "tokens_downloads_expired";
        }
        $template->content = $contentElement->generate();

        $this->logAccess($downloadsTokensModel, $request);

        return $template->getResponse();
    }

    /**
     * Crate the downloads content element, that will be displayed if all checks are OK.
     */
    private function getDownloadsContentElement(DownloadsTokensModel $downloadsTokensModel): ContentDownloads
    {
        $contentModel = $this->getContentModel($downloadsTokensModel);
        $contentModel->type = 'downloads'; // is relevant for generating the ce_<type> CSS class

        return new ContentDownloads($contentModel);
    }

    /**
     * Crate the text content element, that will be displayed if the downloads content element can not be shown
     * (e.g. because it is no longer published).
     */
    private function getTextContentElement(DownloadsTokensModel $downloadsTokensModel): ContentText
    {
        $contentModel = $this->getContentModel($downloadsTokensModel);
        $contentModel->type = 'text';  // is relevant for generating the ce_<type> CSS class
        // Note: we need to override these values as otherwise our message would also not be shown
        $contentModel->start = '';
        $contentModel->stop = '';
        // TODO: do not use hard coded class here!
        $contentModel->text = sprintf('<p class="alert alert-error">Ihre Downloads %s!</p>', $this->generateIsHiddenMessage($downloadsTokensModel));

        return new ContentText($contentModel);
    }

    /**
     * Create the base content model, that will be extended in getDownloadsContentElement() or getTextContentElement() respectively.
     */
    private function getContentModel(DownloadsTokensModel $downloadsTokensModel): ContentModel
    {
        $contentModel = new ContentModel();
        $contentModel->headline = $downloadsTokensModel->headline;
        $contentModel->multiSRC = $downloadsTokensModel->multiSRC;
        $contentModel->invisible = false;
        $contentModel->start = $downloadsTokensModel->start;
        $contentModel->stop = $downloadsTokensModel->stop;
        // $contentModel->cssID = ['element-id', 'css-classes foo bar']; // could be used if we extend dca/tl_downloads_tokens.php to contain the required fields

        return $contentModel;
    }

    /**
     * Log access in Contao's backend log
     */
    private function logAccess(DownloadsTokensModel $downloadsTokensModel, Request $request): void
    {
        if ($this->isDownloadRequest($request)) {
            return;
        }

        $statusText = $this->generateStatusText($downloadsTokensModel);
        $contaoLogActionParameter = $downloadsTokensModel->isHidden() ? ContaoContext::ERROR : ContaoContext::GENERAL;
        $contaoLogActionMethod = $downloadsTokensModel->isHidden() ? 'error' : 'info';

        // Zugriffe mitzählen (protokollieren)
        $downloadsTokensModel->access_log = sprintf("%s;%s\n%s",
            Date::parse('Y-m-d, H:i', time()),
            $statusText,
            $downloadsTokensModel->access_log
        );
        $downloadsTokensModel->save();

        // Im System-Log protokollieren
        $this->contaoErrorLogger->$contaoLogActionMethod(sprintf('Access to token downloads with token "%s" (%s)',
            $downloadsTokensModel->token, $statusText),
            ['contao' => new ContaoContext(__METHOD__, $contaoLogActionParameter)]
        );
    }

    /**
     * Message shown in the front end
     */
    private function generateIsHiddenMessage(DownloadsTokensModel $downloadsTokensModel): string
    {
        // TODO: internationalization and localization

        if ($downloadsTokensModel->isNotPublished()) {
            return 'sind nicht (mehr) veröffentlicht';
        }

        if ($downloadsTokensModel->isNotYetVisible()) {
            return sprintf('sind erst ab %s verfügbar', Date::parse('Y-m-d H:i', $downloadsTokensModel->start));
        }

        if ($downloadsTokensModel->isNoLongerVisible()) {
            return sprintf('waren nur bis %s verfügbar', Date::parse('Y-m-d H:i', $downloadsTokensModel->stop));
        }

        return ''; // This should never happen as we only (should) use this message when the downloads are hidden for one of the above reasons
    }

    /**
     * Message shown in the back end
     */
    private function generateStatusText(DownloadsTokensModel $downloadsTokensModel): string
    {
        // TODO: internationalization and localization

        if ($downloadsTokensModel->isNotPublished()) {
            return 'not published';
        }

        if ($downloadsTokensModel->isNotYetVisible()) {
            return sprintf('only published from %s', Date::parse('Y-m-d H:i', $downloadsTokensModel->start));
        }

        if ($downloadsTokensModel->isNoLongerVisible()) {
            return sprintf('only published until %s', Date::parse('Y-m-d H:i', $downloadsTokensModel->stop));
        }

        return 'OK';
    }

    /**
     * Is the current request a download request? Is needed to determine how we log.
     */
    private function isDownloadRequest(Request $request): bool
    {
        return $request->query->has('file');
    }

}