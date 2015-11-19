# Neos CMS filepreviews.io integration 

This package generate thumbnail and extract metadata from different type of document 
based on the API of [filepreviews.io].

**This package is under development and depends on change not currently available in a stable version of Neos**

This package is Composer ready, [PSR-2] and [PSR-4] compliant.

How it work ?
-------------

This Generator call the FilePreviews.io API to generate Thumbnail for dozen of differents file format. Check [filepreviews.io]
website for more informations.

![Thumbnail from an OGG Vorbis File](https://dl.dropboxusercontent.com/s/775z6n54b4goyc6/2015-11-19%20at%2012.40.png)

Configuration
-------------

Like any other Thumbnail Generator, you can change default settings. First step, you need to configure your API keys.

```yaml
TYPO3:
  Media:
    thumbnailGenerator:

      'Ttree\FilePreviews\Domain\Model\ThumbnailGenerator\FilePreviewsThumbnailGenerator':
        apiKey: 'api-key'
        apiSecret: 'api-secret'
        maximumWaitingTime: 30
        defaultOptions:
          format: 'jpg'
        retryInterval: 1
        supportedExtensions: [ 'doc', 'docx', 'txt', 'rtf', 'ogg' ]
```

- ```supportedExtensions```: check the official documentation of FilePreviews [Supported Formats] and enjoy. 
- ```defaultOptions```: check the [API endpoint] documentation.

Acknowledgments
---------------

Development sponsored by [ttree ltd - neos solution provider](http://ttree.ch).

We try our best to craft this package with a lots of love, we are open to sponsoring, support request, ... just contact us.

License
-------

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[PSR-2]: http://www.php-fig.org/psr/psr-2/
[PSR-4]: http://www.php-fig.org/psr/psr-4/
[filepreviews.io]: http://filepreviews.io/
[Supported Formats]: http://filepreviews.io/docs/features.html
[API endpoint]: http://filepreviews.io/docs/endpoints.html
