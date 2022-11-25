<?php

declare(strict_types=1);

namespace Sinfonie\Avatar\Helper;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Url\Encoder;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\Customer\Api\CustomerMetadataInterface;

class Avatar extends AbstractHelper
{
    public const DEFAULT_NAME = 'Customer';
    private const ABSTRACT_API_KEY_PATH = 'sinfonie_avatar/configuration/api_key';
    private const API_URL = 'https://avatars.abstractapi.com/v1';

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Encoder
     */
    private $encoder;

    /**
     * Helper constructor
     *
     * @param Storage $storage
     * @param UrlInterface $urlBuilder
     * @param Context $context
     * @param Encoder $encoder
     */
    public function __construct(
        Storage $storage,
        UrlInterface $urlBuilder,
        Context $context,
        Encoder $encoder
    ) {
        parent::__construct($context);
        $this->storage = $storage;
        $this->urlBuilder = $urlBuilder;
        $this->encoder = $encoder;
    }

    /**
     * Returns filename from path
     *
     * @param string $path
     * @return string
     */
    public function getFilename(string $path): string
    {
        return CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . '/' . ltrim($path, '/');
    }

    /**
     * Returns avatar fronted controller path
     *
     * @param int|null $customerId
     * @return string
     */
    public function getFrontAvatarUrl(?int $customerId): string
    {
        $customerPart = '';
        if ($customerId) {
            $customerPart = DIRECTORY_SEPARATOR . 'id' .  DIRECTORY_SEPARATOR . $customerId;
        }
        return $this->urlBuilder->getBaseUrl() . 'sin_avatar/index/index' . $customerPart;
    }

    /**
     * Returns file path
     *
     * @param string|null $path
     * @return string|null
     */
    public function getCustomerFilePath(?string $path): ?string
    {
        return ($path) ? DIRECTORY_SEPARATOR . CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . $path : null;
    }

    /**
     * Returns file content type
     *
     * @param string $filename
     * @return string
     */
    public function getContentType(string $filename): string
    {
        // phpcs:ignore
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        switch (strtolower($extension)) {
            case 'gif':
                return 'image/gif';
            case 'jpg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            default:
                return 'application/octet-stream';
        }
    }

    /**
     * Method validates file
     *
     * @param string $path
     * @param ReadInterface $dir
     * @return bool
     */
    public function validatePath(string $path, ReadInterface $dir): bool
    {
        if (mb_strpos($path, '..') !== false ||
            (!$dir->isFile($path) && !$this->storage->processStorageFile($path))
        ) {
            return false;
        }
        return true;
    }

    /**
     * Returns customer avatar source
     *
     * @param string|null $src
     * @return string
     */
    public function getStoredAdminSrc(?string $src): string
    {
        if ($src) {
            return $this->urlBuilder->getUrl('customer/index/viewfile', ['file' => $this->encoder->encode($src)]);
        }
        return $this->getApiSrc(self::DEFAULT_NAME);
    }

    /**
     * Returns api service source based on name
     *
     * @param string|null $name
     * @return string
     */
    public function getApiSrc(?string $name = null): string
    {
        if (!$name) {
            $name = self::DEFAULT_NAME;
        }
        $params = [
            'api_key' => $this->scopeConfig->getValue(self::ABSTRACT_API_KEY_PATH),
            'name' => $name
        ];
        $query = http_build_query($params);
        return self::API_URL . '/?' . $query;
    }
}
