<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Filesystem\Adapter;

use Hyperf\Filesystem\Contract\AdapterFactoryInterface;
use League\Flysystem\AdapterInterface;
use Overtrue\Flysystem\Qiniu\QiniuAdapter;

class QiniuAdapterFactory implements AdapterFactoryInterface
{
    public function make(array $options): AdapterInterface
    {
        return new QiniuAdapter($options['accessKey'], $options['secretKey'], $options['bucket'], $options['domain']);
    }
}
