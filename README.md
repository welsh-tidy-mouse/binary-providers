# Binary Fetcher

[![Binaries Tests](https://github.com/welsh-tidy-mouse/binary-providers/actions/workflows/binary-tests.yml/badge.svg)](https://github.com/welsh-tidy-mouse/binary-providers/actions/workflows/binary-tests.yml)
[![PHP Tests](https://github.com/welsh-tidy-mouse/binary-providers/actions/workflows/php-tests.yml/badge.svg)](https://github.com/welsh-tidy-mouse/binary-providers/actions/workflows/php-tests.yml)
[![Binary Fetcher license](https://img.shields.io/github/license/welsh-tidy-mouse/binary-providers?public)](https://github.com/welsh-tidy-mouse/binary-providers/blob/master/LICENSE)

This repository provides a set of binary providers for use with the [Binary Fetcher](https://github.com/welsh-tidy-mouse/binary-fetcher) library.

Each binary provider defines how to locate and download a specific binary from GitHub releases, tailored to the current platform (OS + architecture). These providers are used by Binary Fetcher to automate the installation of CLI tools across environments.

## 📦 What Is This For?

The [`binary-fetcher`](https://github.com/welsh-tidy-mouse/binary-fetcher) project is a PHP-based tool to download platform-specific binaries with zero configuration. It can be used both:
- **Via CLI** (`bin/binary-fetcher download`)
- **Via PHP** (`BinaryFetcher::download()`)

This repository (`binary-providers`) contains the list of supported binaries through reusable PHP provider classes.


## 🧩 Available Binary Providers

| Provider Class                                                                 | Binary         | Source URL                                                                                     | Notes                                        |
|--------------------------------------------------------------------------------|----------------|------------------------------------------------------------------------------------------------|----------------------------------------------|
| `\WelshTidyMouse\BinaryProvider\BunJsBinaryProvider`                        | `bun`          | [oven-sh/bun](https://github.com/oven-sh/bun/releases)                                        | JavaScript runtime (Node.js alternative)     |
| `\WelshTidyMouse\BinaryProvider\TailwindCssBinaryProvider`                 | `tailwindcss`  | [tailwindlabs/tailwindcss](https://github.com/tailwindlabs/tailwindcss/releases)              | CSS utility framework CLI                    |
| `\WelshTidyMouse\BinaryProvider\SassBinaryProvider`                        | `dart-sass`    | [sass/dart-sass](https://github.com/sass/dart-sass/releases)                                  | Sass compiler (standalone executable)        |

## 🔧 Install

```bash
composer require welsh-tidy-mouse/binary-fetcher
```

## 🚀 How to Use These Providers

Using [Binary Fetcher](https://github.com/welsh-tidy-mouse/binary-fetcher), you can download any of these binaries based on your current OS and architecture.

### From CLI

```bash
bin/binary-fetcher download "\WelshTidyMouse\BinaryProvider\BunJsBinaryProvider"
```

### From PHP

```php
use WelshTidyMouse\BinaryFetcher\BinaryFetcher;
use WelshTidyMouse\BinaryProvider\BunJsBinaryProvider;

$binaryPath = (new BinaryFetcher())->download(BunJsBinaryProvider::class);
```

The binary will be downloaded to a writable location and its path will be returned.

## 🛠️ Development & Contributions

You can add your own binary provider class implementing the interface:

```php
namespace WelshTidyMouse\BinaryFetcher\Contract;

use WelshTidyMouse\BinaryFetcher\Exception\BinaryProviderException;
use WelshTidyMouse\BinaryFetcher\Type\OsType;
use WelshTidyMouse\BinaryFetcher\Type\SystemArchType;

interface BinaryProviderInterface
{
    public function __construct();
    public function getName(): string;
    public function getDownloadableAssetUrl(string $version, OsType $os, SystemArchType $arch): ?string;
    public function getBinaryFilenameFromDownloadedAsset(string $assetFileName, string $downloadDirPath): string;
}
```

Pull requests for additional binary providers are welcome!

## 🧪 Quality

- `composer lint` to run PHPStan
- `composer cs` to fix files with Code Sniffer
- `composer md`to run PHPMD
- `composer check` for all commands above

---

## 🐁 Part of Welsh Tidy Mouse

This package is part of the **Welsh Tidy Mouse** ecosystem. Read more on the main repository: [binary-fetcher](https://github.com/welsh-tidy-mouse/binary-fetcher)
