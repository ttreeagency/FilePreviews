# Neos CMS filepreviews.io integration

This package generate thumbnail and extract metadata from different type of document
based on the API of [filepreviews.io].

This package is Composer ready, [PSR-2] and [PSR-4] compliant.

How it work ?
-------------

This Generator call the FilePreviews.io API to generate Thumbnail for many different file formats. Check [filepreviews.io]
website for more informations.

Configuration
-------------

Like any other Thumbnail Generator, you can change default settings. First step, you need to configure your API keys.

```yaml
Neos:
  Media:
    thumbnailGenerator:

      'Ttree\FilePreviews\Domain\Model\ThumbnailGenerator\FilePreviewsThumbnailGenerator':
        apiKey: 'api-key'
        apiSecret: 'api-secret'
        maximumWaitingTime: 30
        defaultOptions:
          format: 'jpg'
        retryInterval: 1
        supportedExtensions: [ 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlxs', 'odt', 'ott', 'odp', 'txt', 'rtf', 'eps' ]
```

- ```supportedExtensions```: check the official documentation of FilePreviews [Supported Formats] and enjoy.
- ```defaultOptions```: check the [API endpoint] documentation.

Acknowledgments
---------------

Development sponsored by [ttree ltd - neos solution provider](http://ttree.ch).

We try our best to craft this package with a lots of love, we are open to sponsoring, support request, ... just contact us.

License
-------

The MIT License (MIT). Please see [LICENSE](LICENSE.txt) for more information.

[PSR-2]: http://www.php-fig.org/psr/psr-2/
[PSR-4]: http://www.php-fig.org/psr/psr-4/
[filepreviews.io]: http://filepreviews.io/
[Supported Formats]: https://filepreviews.io/docs/features/
[API endpoint]: https://filepreviews.io/docs/endpoints/
