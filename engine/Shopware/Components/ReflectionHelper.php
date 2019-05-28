<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class ReflectionHelper
{
    /**
     * Create a class instance from a class name string
     *
     * @param string $className
     * @param array $arguments
     * @param string $interface
     * @return object
     * @throws \ReflectionException Class could not be found
     * @see \Shopware\Components\ReflectionHelper::verifyClass()
     *
     */
    public function createInstanceFromNamedArguments($className, $arguments, $interface)
    {
        $reflectionClass = new \ReflectionClass($className);


        if (!$reflectionClass->getConstructor()) {
            return $reflectionClass->newInstance();
        }

        if (!$reflectionClass->implementsInterface($interface)) {
            throw new \RuntimeException(sprintf('Class: "%s" doesn\'t implement interface "%s"', $className, $interface));
        }

        $constructorParams = $reflectionClass->getConstructor()->getParameters();

        $newParams = [];
        foreach ($constructorParams as $constructorParam) {
            $paramName = $constructorParam->getName();

            if (!isset($arguments[$paramName])) {
                if (!$constructorParam->isOptional()) {
                    throw new \RuntimeException(sprintf('Required constructor parameter missing: "$%s".', $paramName));
                }
                $newParams[] = $constructorParam->getDefaultValue();

                continue;
            }

            $newParams[] = $arguments[$paramName];
        }

        return $reflectionClass->newInstanceArgs($newParams);
    }

    /**
     * Verify that a given ReflectionClass object is within the documentroot (docPath)
     * and (optionally) that said class belongs to certain directories.
     *
     * @param string $docPath     Path to the project's document root
     * @param array  $directories Optional set of directories in which the class file should be in
     *
     * @throws \InvalidArgumentException If the class is out of scope (docpath mismatch)   (code: 1)
     * @throws \InvalidArgumentException If the class is out of scope (directory mismatch) (code: 2)
     */
    private function verifyClass(\ReflectionClass $class, $docPath, array $directories = [])
    {
        $fileName = $class->getFileName();
        $fileDir = substr($fileName, 0, strlen($docPath));

        // Trying to execute a class outside of the Shopware DocumentRoot
        if ($fileDir !== $docPath) {
            throw new \InvalidArgumentException(sprintf('Class "%s" out of scope', $class->getFileName()), 1);
        }
        if (empty($directories)) {
            return;
        }

        $fileName = substr($fileName, strlen($docPath));

        $error = true;

        foreach ($directories as $directory) {
            $directory = strtolower(trim($directory, DIRECTORY_SEPARATOR));

            $classDir = substr($fileName, 0, strlen($directory));
            $classDir = trim($classDir, DIRECTORY_SEPARATOR);
            $classDir = strtolower($classDir);

            if ($directory === $classDir) {
                $error = false;
                break;
            }
        }

        if ($error) {
            throw new \InvalidArgumentException(sprintf('Class "%s" out of scope', $class->getFileName()), 2);
        }
    }
}
