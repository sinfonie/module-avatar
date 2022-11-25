<?php

declare(strict_types=1);

namespace Sinfonie\Avatar\Model\Service;

use Sinfonie\Avatar\Api\AvatarInterface;
use Sinfonie\Avatar\Helper\Avatar as AvatarHelper;

class Avatar implements AvatarInterface
{

    /**
     * @var AvatarHelper
     */
    private $avatarHelper;

    /**
     * Avatar service constructor
     *
     * @param AvatarHelper $avatarHelper
     */
    public function __construct(
        AvatarHelper $avatarHelper,
    ) {
        $this->avatarHelper = $avatarHelper;
    }

    /**
     * @inheritDoc
     */
    public function getAvatar($customerId): string
    {
        return $this->avatarHelper->getFrontAvatarUrl((int) $customerId);
    }
}
