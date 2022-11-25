<?php

declare(strict_types=1);

namespace Sinfonie\Avatar\Controller\Index;

use Psr\Log\LoggerInterface;
use Sinfonie\Avatar\Helper\Avatar;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File as DriverFile;

class Index implements HttpGetActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Avatar
     */
    private $avatarHelper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var DriverFile
     */
    private $file;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Get customer avatar controller
     *
     * @param LoggerInterface $logger
     * @param Avatar $avatarHelper
     * @param Session $customerSession
     * @param RequestInterface $request
     * @param RawFactory $resultRawFactory
     * @param DriverFile $file
     * @param Filesystem $filesystem
     */
    public function __construct(
        LoggerInterface $logger,
        Avatar $avatarHelper,
        Session $customerSession,
        RequestInterface $request,
        RawFactory $resultRawFactory,
        DriverFile $file,
        Filesystem $filesystem,
    ) {
        $this->logger = $logger;
        $this->avatarHelper = $avatarHelper;
        $this->customerSession = $customerSession;
        $this->resultRawFactory = $resultRawFactory;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->request = $request;
    }

    /**
     * Get customer avatar controller
     *
     * @return Raw
     */
    public function execute(): Raw
    {
        try {
            if ($this->customerSession->isLoggedIn()) {
                $id = $this->request->getParam('id');
                if ($id == $this->customerSession->getCustomerId()) {
                    $customer = $this->customerSession->getCustomer();
                    $storedAvatar = $this->getStoredAvatar($customer->getData('avatar'));
                    if ($storedAvatar) {
                        return $storedAvatar;
                    }
                    return $this->getApiAvatar($customer->getName());
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
        return $this->resultRawFactory->create();
    }

    /**
     * Returns api avatar
     *
     * @param string|null $name
     * @return Raw
     */
    protected function getApiAvatar(?string $name = null): Raw
    {
        try {
            $src = $this->avatarHelper->getApiSrc($name);
            $content = $this->file->fileGetContents($src);
            return $this->getAvatarContent($content, 'image/png');
        } catch (FileSystemException $e) {
            $this->logger->error($e->getMessage());
        }
        return $this->resultRawFactory->create();
    }

    /**
     * Returns stored avatar
     *
     * @param string|null $attribute
     * @return Raw|null
     */
    protected function getStoredAvatar(?string $attribute): ?Raw
    {
        $avatarSrc = $this->avatarHelper->getCustomerFilePath($attribute);
        if ($avatarSrc) {
            try {
                $dir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $path = $dir->getAbsolutePath($avatarSrc);
                if ($this->avatarHelper->validatePath($path, $dir)) {
                    $contentType = $this->avatarHelper->getContentType($path);
                    return $this->getAvatarContent($dir->readFile($path), $contentType);
                }
            } catch (FileSystemException $e) {
                $this->logger->error($e->getMessage());
            }
        }
        return null;
    }

    /**
     * Returns raw content result
     *
     * @param string $content
     * @param string $contentType
     * @return Raw
     */
    protected function getAvatarContent(string $content, string $contentType): Raw
    {
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', strlen($content));
        $resultRaw->setContents($content);
        return $resultRaw;
    }
}
