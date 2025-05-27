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

class PnpmBinaryProvider implements BinaryProviderInterface
{
    use GithubProviderHelperTrait;

    const string PNPM = 'pnpm';

    public function __construct(
        protected ArchiveProcessorInterface $archiveProcessor = new ArchiveProcessor(),
        protected Filesystem $filesystem = new Filesystem(),
    ) {
    }

    public static function getName(): string
    {
        return 'pnpm';
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

        return $this->generateDownloadUrl($version, 'pnpm/pnpm', $assetName);
    }

    /**
     * @inheritdoc
     */
    public function getBinaryFilenameFromDownloadedAsset(string $assetFileName, string $downloadDirPath): string
    {
        $this->filesystem->copy(
            Path::join($downloadDirPath, $assetFileName),
            Path::join($downloadDirPath, self::PNPM)
        );
        
        $this->filesystem->remove(Path::join($downloadDirPath, $assetFileName));

        if (str_contains($assetFileName, 'macos')) {
            exec('xattr -d com.apple.quarantine ' . escapeshellarg(Path::join($downloadDirPath, self::PNPM)));
        }

        return self::PNPM;
    }

    protected function getAssetName(OsType $os, SystemArchType $arch): ?string
    {
        return match (true) {
            OsType::MACOS === $os && SystemArchType::X_64 === $arch => 'pnpm-macos-x64',
            OsType::MACOS === $os && SystemArchType::ARM_64 === $arch => 'pnpm-macos-arm64',
            OsType::LINUX === $os && SystemArchType::X_64 === $arch => 'pnpm-linux-x64',
            OsType::LINUX === $os && SystemArchType::ARM_64 === $arch => 'pnpm-linux-arm64',
            OsType::ALPINE_LINUX === $os && SystemArchType::X_64 === $arch => 'pnpm-linuxstatic-x64',
            OsType::ALPINE_LINUX === $os && SystemArchType::ARM_64 === $arch => 'pnpm-linuxstatic-arm64',
            OsType::WINDOWS === $os && SystemArchType::X_64 === $arch => 'pnpm-win-x64.exe',
            OsType::WINDOWS === $os && SystemArchType::ARM_64 === $arch => 'pnpm-win-arm64.exe',
            default => null,
        };
    }
}
