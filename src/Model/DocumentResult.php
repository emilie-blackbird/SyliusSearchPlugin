<?php

declare(strict_types=1);

namespace Monsieurbiz\SyliusSearchPlugin\Model;

use Monsieurbiz\SyliusSearchPlugin\Exception\MissingAttributeException;
use Monsieurbiz\SyliusSearchPlugin\Exception\MissingLocaleException;
use Monsieurbiz\SyliusSearchPlugin\Exception\MissingParamException;
use Monsieurbiz\SyliusSearchPlugin\Exception\MissingPriceException;
use Monsieurbiz\SyliusSearchPlugin\Exception\NotSupportedTypeException;
use Monsieurbiz\SyliusSearchPlugin\generated\Model\Attributes;
use Monsieurbiz\SyliusSearchPlugin\generated\Model\Document;
use Monsieurbiz\SyliusSearchPlugin\generated\Model\Price;
use Monsieurbiz\SyliusSearchPlugin\Provider\UrlParamsProvider;

class DocumentResult extends Document
{
    /**
     * Document ID in elasticsearch
     *
     * @return string
     * @throws MissingParamException
     */
    public function getUniqId(): string
    {
        if (!$this->getType()) {
            throw new MissingParamException('Missing "type" for document');
        }
        if (!$this->getId()) {
            throw new MissingParamException('Missing "ID" for document');
        }
        return sprintf('%s-%d', $this->getType(), $this->getId());
    }

    /**
     * @param string $code
     * @return Attributes
     * @throws MissingAttributeException
     */
    public function getAttribute(string $code): Attributes
    {
        foreach ($this->getAttributes() as $attribute) {
            if ($attribute->getCode() === $code) {
                return $attribute;
            }
        }
        throw new MissingAttributeException(sprintf('Attribute not found for code "%s"', $code));
    }

    /**
     * @param string $channelCode
     * @param string $currencyCode
     * @return Price
     * @throws MissingPriceException
     */
    public function getPriceByChannelAndCurrency(string $channelCode, string $currencyCode): Price
    {
        foreach ($this->getPrice() as $price) {
            if ($price->getChannel() === $channelCode && $price->getCurrency() === $currencyCode) {
                return $price;
            }
        }
        throw new MissingPriceException(sprintf('Price not found for channel "%s" and currency "%s"', $channelCode, $currencyCode));
    }

    /**
     * @param string $channelCode
     * @param string $currencyCode
     * @return Price
     * @throws MissingPriceException
     */
    public function getOriginalPriceByChannelAndCurrency(string $channelCode, string $currencyCode): Price
    {
        foreach ($this->getOriginalPrice() as $price) {
            if ($price->getChannel() === $channelCode && $price->getCurrency() === $currencyCode) {
                return $price;
            }
        }
        throw new MissingPriceException(sprintf('Original price not found for channel "%s" and currency "%s"', $channelCode, $currencyCode));

    }

    /**
     * @return string
     * @throws MissingLocaleException
     */
    public function getLocale(): string
    {
        foreach ($this->getAttributes() as $attribute) {
            if ($attribute->getLocale()) {
                return $attribute->getLocale();
            }
        }

        throw new MissingLocaleException('Locale not found in document');
    }

    /**
     * @return UrlParamsProvider
     * @throws MissingLocaleException
     * @throws NotSupportedTypeException
     */
    public function getUrlParams(): UrlParamsProvider {
        switch ($this->getType()) {
            case "product" :
                return new UrlParamsProvider('sylius_shop_product_show', ['slug' => $this->getSlug(), '_locale' => $this->getLocale()]);
                break;
        }

        throw new NotSupportedTypeException(sprintf('Object type "%s" not supported to get URL', $this->getType()));
    }

    /**
     * @param string $channel
     * @return DocumentResult
     */
    public function addChannel(string $channel): self
    {
        $this->setChannel($this->getChannel() ? array_unique(array_merge($this->getChannel(), [$channel])) : [$channel]);

        return $this;
    }

    /**
     * @param string $channel
     * @param string $currency
     * @param int $value
     * @return DocumentResult
     */
    public function addPrice(string $channel, string $currency, int $value): self
    {
        $price = new Price();
        $price->setChannel($channel)->setCurrency($currency)->setValue($value);
        $this->setPrice($this->getPrice() ? array_merge($this->getPrice(), [$price]) : [$price]);

        return $this;
    }

    /**
     * @param string $channel
     * @param string $currency
     * @param int $value
     * @return DocumentResult
     */
    public function addOriginalPrice(string $channel, string $currency, int $value): self
    {
        $price = new Price();
        $price->setChannel($channel)->setCurrency($currency)->setValue($value);
        $this->setOriginalPrice($this->getOriginalPrice() ? array_merge($this->getOriginalPrice(), [$price]) : [$price]);

        return $this;
    }

    /**
     * @param string $code
     * @param string $name
     * @param array $value
     * @param string $locale
     * @param int $score
     * @return DocumentResult
     */
    public function addAttribute(string $code, string $name, array $value, string $locale, int $score): self
    {
        $attribute = new Attributes();
        $attribute->setCode($code)->setName($name)->setValue($value)->setLocale($locale)->setScore($score);
        $this->setAttributes($this->getAttributes() ? array_merge($this->getAttributes(), [$attribute]) : [$attribute]);

        return $this;
    }
}
