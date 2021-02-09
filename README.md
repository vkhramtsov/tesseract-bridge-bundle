Symfony bundle for integration with ``bicycle/tesseract-bridge`` package

[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%207.4.0-8892BF.svg)](https://php.net/)
[![Build Status](https://travis-ci.org/vkhramtsov/tesseract-bridge-bundle.svg?branch=master)](https://travis-ci.org/vkhramtsov/tesseract-bridge-bundle)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/e06ff3df6a574f2caf5596e9fd4df841)](https://www.codacy.com/gh/vkhramtsov/tesseract-bridge-bundle/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=vkhramtsov/tesseract-bridge-bundle&amp;utm_campaign=Badge_Grade)
[![codecov](https://codecov.io/gh/vkhramtsov/tesseract-bridge-bundle/branch/master/graph/badge.svg?token=JBVS2P8RFF)](https://codecov.io/gh/vkhramtsov/tesseract-bridge-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/vkhramtsov/tesseract-bridge-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/vkhramtsov/tesseract-bridge-bundle/?branch=master)
[![Latest stable version on packagist](https://img.shields.io/packagist/v/bicycle/tesseract-bridge-bundle.svg)](https://packagist.org/packages/bicycle/tesseract-bridge-bundle)
[![Total downloads](https://img.shields.io/packagist/dt/bicycle/tesseract-bridge-bundle.svg)](https://packagist.org/packages/bicycle/tesseract-bridge-bundle)
[![Monthly downloads](https://img.shields.io/packagist/dm/bicycle/tesseract-bridge-bundle.svg)](https://packagist.org/packages/bicycle/tesseract-bridge-bundle)
[![License](https://img.shields.io/packagist/l/bicycle/tesseract-bridge-bundle.svg)](https://packagist.org/packages/bicycle/tesseract-bridge-bundle)

## Installation

First af all you need to create configuration, something like:

    bicycle_tesseract_bridge:
      integrations:
        cli:
          path: tesseract
        ffi: # Please note that FFI integration depends on php settings and not available in fpm by default
          path: libtesseract.so.4

And install bundle via [Composer](https://getcomposer.org/):

    $ composer require bicycle/tesseract-bridge-bundle

## Usage

Depend on the configuration you will get services which implement ``Bicycle\Tesseract\BridgeInterface``:

-   **bicycle.tesseract_bridge.integrations.cli** for CLI integration (in case enabled)
-   **bicycle.tesseract_bridge.integrations.ffi** for FFI integration (in case enabled)

## How to contribute

You can contribute to this project by:

-   Opening an [Issue](../../issues) if you found a bug or wish to propose a new feature;
-   Opening [PR](../../pulls) if you want to improve/create/fix something

## License

tesseract-bridge is released under the [MIT License](./LICENSE).
