<?php

declare(strict_types=1);

namespace WelshTidyMouse\BinaryProvider;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use WelshTidyMouse\BinaryFetcher\Contract\BinaryProviderInterface;
use WelshTidyMouse\BinaryFetcher\Tool\ArchiveProcessor;
use WelshTidyMouse\BinaryFetcher\Tool\ArchiveProcessorInterface;
use WelshTidyMouse\BinaryFetcher\Tool\GithubProviderHelperTrait;
use WelshTidyMouse\BinaryFetcher\Type\OsType;
use WelshTidyMouse\BinaryFetcher\Type\SystemArchType;

class BunJsBinaryProvider implements BinaryProviderInterface
{
    use GithubProviderHelperTrait;

    public function __construct(
        protected ArchiveProcessorInterface $archiveProcessor = new ArchiveProcessor(),
        protected Filesystem $filesystem = new Filesystem(),
    ) {
    }

    public static function getName(): string
    {
        return 'bun-js';
    }

    /**
     * @inheritdoc
     */
    public function getDownloadableAssetUrl(string $version, OsType $os, SystemArchType $arch): ?string
    {
        $assetName = $this->getAssetName($os, $arch);
        if (null === $assetName) {
            return null;
        }

        return $this->generateDownloadUrl($version, 'oven-sh/bun', $assetName);
    }

    /**
     * @inheritdoc
     */
    public function getBinaryFilenameFromDownloadedAsset(string $assetFileName, string $downloadDirPath): string
    {
        $binaryFileName = Path::getFilenameWithoutExtension($assetFileName);

        // Delete binary if exists for a fresh installation
        if ($this->filesystem->exists(Path::join($downloadDirPath, $binaryFileName))) {
            $this->filesystem->remove(Path::join($downloadDirPath, $binaryFileName));
        }

        $this->archiveProcessor->unzip($assetFileName, $downloadDirPath);

        $this->filesystem->remove(Path::join($downloadDirPath, $assetFileName));

        $targetBinaryFileName = str_contains($assetFileName, 'windows') ? 'bun.exe' : 'bun';

        $this->filesystem->copy(
            Path::join($downloadDirPath, $binaryFileName, $targetBinaryFileName),
            Path::join($downloadDirPath, $targetBinaryFileName)
        );

        $this->filesystem->remove(Path::join($downloadDirPath, $binaryFileName));

        return $targetBinaryFileName;
    }

    protected function getAssetName(OsType $os, SystemArchType $arch): ?string
    {
        return match (true) {
            OsType::MACOS === $os && SystemArchType::X_64 === $arch => 'bun-darwin-x64.zip',
            OsType::MACOS === $os && SystemArchType::ARM_64 === $arch => 'bun-darwin-aarch64.zip',
            OsType::LINUX === $os && SystemArchType::X_64 === $arch => 'bun-linux-x64.zip',
            OsType::LINUX === $os && SystemArchType::ARM_64 === $arch => 'bun-linux-aarch64.zip',
            OsType::ALPINE_LINUX === $os && SystemArchType::X_64 === $arch => 'bun-linux-x64-musl.zip',
            OsType::ALPINE_LINUX === $os && SystemArchType::ARM_64 === $arch => 'bun-linux-aarch64-musl.zip',
            OsType::WINDOWS === $os => 'bun-windows-x64.zip',
            default => null,
        };
    }
}
