<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace CWP\Rpc\Client\Annotation\Parser;

use PhpDocReader\AnnotationException;
use PhpDocReader\PhpDocReader;
use ReflectionException;
use ReflectionProperty;
use Swoft\Annotation\Annotation\Mapping\AnnotationParser;
use Swoft\Annotation\Annotation\Parser\Parser;
use Swoft\Proxy\Exception\ProxyException;
use CWP\Rpc\Client\Annotation\Mapping\Reference;
use CWP\Rpc\Client\Exception\RpcClientException;
use CWP\Rpc\Client\Proxy;
use CWP\Rpc\Client\ReferenceRegister;

/**
 * Class ReferenceParser
 *
 * @since 2.0
 *
 * @AnnotationParser(Reference::class)
 */
class ReferenceParser extends Parser
{
    /**
     * @param int       $type
     * @param Reference $refObject
     *
     * @return array
     * @throws RpcClientException
     * @throws AnnotationException
     * @throws ReflectionException
     * @throws ProxyException
     */
    public function parse(int $type, $refObject): array
    {
        // Parse php document
        $phpReader       = new PhpDocReader();
        $reflectProperty = new ReflectionProperty($this->className, $this->propertyName);
        $propClassType   = $phpReader->getPropertyClass($reflectProperty);

        if (empty($propClassType)) {
            throw new RpcClientException(sprintf(
                '`@Reference`(%s->%s) must to define `@var xxx`',
                $this->className,
                $this->propertyName
            ));
        }

        $refVersion = $refObject->getVersion();
        $className  = Proxy::newClassName($propClassType, $refVersion);

        $this->definitions[$className] = [
            'class' => $className,
        ];

        ReferenceRegister::register($className, $refObject->getPool(), $refVersion);
        return [$className, true];
    }
}
