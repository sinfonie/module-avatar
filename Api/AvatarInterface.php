<?php

declare(strict_types=1);

namespace Sinfonie\Avatar\Api;

interface AvatarInterface
{
    /**
     * Returns customer avatar.
     *
     * @param int $customerId
     * @return string
     */
    public function getAvatar($customerId): string;
}
