<?php

declare(strict_types=1);

namespace Monsieurbiz\SyliusSearchPlugin\Twig\Extension;

use Monsieurbiz\SyliusSearchPlugin\Exception\MissingLocaleException;
use Monsieurbiz\SyliusSearchPlugin\Exception\NotSupportedTypeException;
use Monsieurbiz\SyliusSearchPlugin\Model\DocumentResult;
use Monsieurbiz\SyliusSearchPlugin\Provider\UrlParamsProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RenderDocumentUrl extends AbstractExtension
{

    public function getFunctions()
    {
        return array(
            new TwigFunction('search_result_url_param', array($this, 'getUrlParams')),
        );
    }

    /**
     * @param DocumentResult $document
     * @return UrlParamsProvider
     * @throws MissingLocaleException
     * @throws NotSupportedTypeException
     */
    public function getUrlParams(DocumentResult $document): UrlParamsProvider {
        switch ($document->getType()) {
            case "product" :
                return new UrlParamsProvider('sylius_shop_product_show', ['slug' => $document->getSlug(), '_locale' => $document->getLocale()]);
                break;
        }

        throw new NotSupportedTypeException(sprintf('Object type "%s" not supported to get URL', $this->getType()));
    }
}
