<?php

declare(strict_types=1);

namespace Sinfonie\Avatar\Ui\Component\Listing\Columns;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Ui\Component\Listing\Columns\Thumbnail;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Sinfonie\Avatar\Helper\Avatar;

class AvatarThumbnail extends Thumbnail
{

    /**
     * @var Avatar
     */
    private $avatarHelper;

    /**
     * Thumbnail constructor
     *
     * @param Avatar $avatarHelper
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Image $imageHelper
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Avatar $avatarHelper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Image $imageHelper,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $imageHelper,
            $urlBuilder,
            $components,
            $data
        );
        $this->avatarHelper = $avatarHelper;
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $src = $this->avatarHelper->getStoredAdminSrc($item['avatar']);
                if (!$item['avatar']) {
                    $src = $this->avatarHelper->getApiSrc($item['name']);
                }
                $item[$fieldName . '_src'] = $src;
                $item[$fieldName . '_alt'] = $item['name'];
            }
        }
        return $dataSource;
    }
}
