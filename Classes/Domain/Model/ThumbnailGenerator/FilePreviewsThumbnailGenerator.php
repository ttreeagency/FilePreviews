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

use FilePreviews\FilePreviews;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Resource\ResourceManager;
use TYPO3\Media\Domain\Model\Thumbnail;
use TYPO3\Media\Domain\Model\ThumbnailGenerator\AbstractThumbnailGenerator;
use TYPO3\Media\Exception;

/**
 * A system-generated preview version of a Document (PDF, AI and EPS)
 */
class FilePreviewsThumbnailGenerator extends AbstractThumbnailGenerator
{
    /**
     * @Flow\Inject
     * @var ResourceManager
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
            $fp = new FilePreviews([
                'api_key' => $this->getOption('apiKey'),
                'api_secret' => $this->getOption('apiSecret')
            ]);

            $uri = $this->resourceManager->getPublicPersistentResourceUri($thumbnail->getOriginalAsset()->getResource());

            $width = $thumbnail->getConfigurationValue('width') ?: $thumbnail->getConfigurationValue('maximumWidth');
            $height = $thumbnail->getConfigurationValue('height') ?: $thumbnail->getConfigurationValue('maximumHeight');

            $response = $fp->generate($uri, [
                'sizes' => [$width, $height],
                'format' => 'jpg',
                'data' => [
                    'original' => $thumbnail->getOriginalAsset()->getResource()->getSha1()
                ]
            ]);
            $responseIdentifier = $response->id;

            $success = false;
            $count = 0;
            while ($success === false) {
                if ($count >= 10) {
                    break;
                }
                $response = $fp->retrieve($responseIdentifier);
                $success = $response->status === 'success';
                sleep(1);
                ++$count;
            }

            if ($success === false || !isset($response->thumbnails[0])) {
                throw new Exception('Unable to process the thumbnail is less than 10 seconds, sorry', 1447891433);
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
