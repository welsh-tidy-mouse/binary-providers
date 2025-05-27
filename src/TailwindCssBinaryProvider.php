<?php

declare(strict_types=1);

namespace WelshTidyMouse\BinaryProvider;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use WelshTidyMouse\BinaryFetcher\Contract\BinaryProviderInterface;
use WelshTidyMouse\BinaryFetcher\Tool\GithubProviderHelperTrait;
use WelshTidyMouse\BinaryFetcher\Type\OsType;
use WelshTidyMouse\BinaryFetcher\Type\SystemArchType;

class TailwindCssBinaryProvider implements BinaryProviderInterface
{
    use GithubProviderHelperTrait;

    public function __construct(protected Filesystem $filesystem = new Filesystem())
    {
    }

    public static function getName(): string
    {
        return 'tailwind-css';
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

        return $this->generateDownloadUrl($version, 'tailwindlabs/tailwindcss', $assetName);
    }

    /**
     * @inheritdoc
     */
    public function getBinaryFilenameFromDownloadedAsset(string $assetFileName, string $downloadDirPath): string
    {
        $this->filesystem->copy(
            Path::join($downloadDirPath, $assetFileName),
            Path::join($downloadDirPath, 'tailwind')
        );

        return 'tailwind';
    }

    protected function getAssetName(OsType $os, SystemArchType $arch): ?string
    {
        return match (true) {
            OsType::MACOS === $os && SystemArchType::X_64 === $arch => 'tailwindcss-macos-x64',
            OsType::MACOS === $os && SystemArchType::ARM_64 === $arch => 'tailwindcss-macos-arm64',
            OsType::LINUX === $os && SystemArchType::X_64 === $arch => 'tailwindcss-linux-x64',
            OsType::LINUX === $os && SystemArchType::ARM_64 === $arch => 'tailwindcss-linux-arm64',
            OsType::ALPINE_LINUX === $os && SystemArchType::X_64 === $arch => 'tailwindcss-linux-x64-musl',
            OsType::ALPINE_LINUX === $os && SystemArchType::ARM_64 === $arch => 'tailwindcss-linux-arm64-musl',
            OsType::WINDOWS === $os => 'tailwindcss-windows-x64.exe',
            default => null,
        };
    }
}
