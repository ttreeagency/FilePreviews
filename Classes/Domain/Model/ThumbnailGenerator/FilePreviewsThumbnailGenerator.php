<?php
namespace Ttree\FilePreviews\Domain\Model\ThumbnailGenerator;

/*
 * This file is part of the Ttree.FilePreviews package.
 *
 * (c) ttree ltd - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Ttree\FilePreviews\Service\ApiClient;
use Ttree\FilePreviews\Service\FilePreviewsService;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Resource\ResourceManager;
use TYPO3\Flow\Utility\Arrays;
use TYPO3\Media\Domain\Model\Thumbnail;
use TYPO3\Media\Domain\Model\ThumbnailGenerator\AbstractThumbnailGenerator;
use TYPO3\Media\Exception;

/**
 * A system-generated preview version of a Document (PDF, AI and EPS)
 */
class FilePreviewsThumbnailGenerator extends AbstractThumbnailGenerator
{
    /**
     * @var ResourceManager
     * @Flow\Inject
     */
    protected $resourceManager;

    /**
     * @param Thumbnail $thumbnail
     * @return boolean
     */
    public function canRefresh(Thumbnail $thumbnail)
    {
        return (
        $this->isExtensionSupported($thumbnail)
        );
    }

    /**
     * @param Thumbnail $thumbnail
     * @return void
     * @throws Exception\NoThumbnailAvailableException
     */
    public function refresh(Thumbnail $thumbnail)
    {
        try {
            $fp = new ApiClient([
                'api_key' => $this->getOption('apiKey'),
                'api_secret' => $this->getOption('apiSecret')
            ]);

            $uri = $this->resourceManager->getPublicPersistentResourceUri($thumbnail->getOriginalAsset()->getResource());

            $width = $thumbnail->getConfigurationValue('width') ?: $thumbnail->getConfigurationValue('maximumWidth');
            $height = $thumbnail->getConfigurationValue('height') ?: $thumbnail->getConfigurationValue('maximumHeight');

            $response = $fp->generate($uri, Arrays::arrayMergeRecursiveOverrule([
                'sizes' => [$width, $height],
                'format' => 'jpg',
                'data' => [
                    'original' => $thumbnail->getOriginalAsset()->getResource()->getSha1()
                ]
            ], $this->getOption('defaultOptions')));
            $responseIdentifier = $response->id;

            $success = false;
            $elapsedTime = 0;
            $maximumWaitingTime = (integer)$this->getOption('maximumWaitingTime');
            $retryInterval = (integer)$this->getOption('retryInterval');
            while ($success === false) {
                if ($elapsedTime >= $maximumWaitingTime) {
                    break;
                }
                $response = $fp->retrieve($responseIdentifier);
                $success = $response->status === 'success';
                sleep($retryInterval);
                $elapsedTime = $elapsedTime + $retryInterval;
            }

            if ($success === false || !isset($response->thumbnails[0])) {
                throw new Exception('Unable to process the thumbnail is less than 20 seconds, sorry', 1447891433);
            }

            $url = $response->thumbnails[0]->url;
            $size = $response->thumbnails[0]->size;

            $resource = $this->resourceManager->importResource($url);
            $thumbnail->setResource($resource);
            $thumbnail->setWidth($size->width);
            $thumbnail->setHeight($size->height);
        } catch (\Exception $exception) {
            $filename = $thumbnail->getOriginalAsset()->getResource()->getFilename();
            $sha1 = $thumbnail->getOriginalAsset()->getResource()->getSha1();
            $message = sprintf('FilePreview.io was unable to generate thumbnail for the given document (filename: %s, SHA1: %s)', $filename, $sha1);
            throw new Exception\NoThumbnailAvailableException($message, 1447883095, $exception);
        }
    }
}
