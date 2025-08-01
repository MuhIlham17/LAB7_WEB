<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Autoloader;

/**
 * Allows loading non-class files in a namespaced manner.
 * Works with Helpers, Views, etc.
 *
 * @see \CodeIgniter\Autoloader\FileLocatorTest
 */
class FileLocator implements FileLocatorInterface
{
    /**
     * The Autoloader to use.
     *
     * @var Autoloader
     */
    protected $autoloader;

    /**
     * List of classnames that did not exist.
     *
     * @var list<class-string>
     */
    private array $invalidClassnames = [];

    public function __construct(Autoloader $autoloader)
    {
        $this->autoloader = $autoloader;
    }

    /**
     * Attempts to locate a file by examining the name for a namespace
     * and looking through the PSR-4 namespaced files that we know about.
     *
     * @param string                $file   The relative file path or namespaced file to
     *                                      locate. If not namespaced, search in the app
     *                                      folder.
     * @param non-empty-string|null $folder The folder within the namespace that we should
     *                                      look for the file. If $file does not contain
     *                                      this value, it will be appended to the namespace
     *                                      folder.
     * @param string                $ext    The file extension the file should have.
     *
     * @return false|string The path to the file, or false if not found.
     */
    public function locateFile(string $file, ?string $folder = null, string $ext = 'php')
    {
        $file = $this->ensureExt($file, $ext);

        // Clears the folder name if it is at the beginning of the filename
        if ($folder !== null && str_starts_with($file, $folder)) {
            $file = substr($file, strlen($folder . '/'));
        }

        // Is not namespaced? Try the application folder.
        if (! str_contains($file, '\\')) {
            return $this->legacyLocate($file, $folder);
        }

        // Standardize slashes to handle nested directories.
        $file = strtr($file, '/', '\\');
        $file = ltrim($file, '\\');

        $segments = explode('\\', $file);

        // The first segment will be empty if a slash started the filename.
        if ($segments[0] === '') {
            unset($segments[0]);
        }

        $paths    = [];
        $filename = '';

        // Namespaces always comes with arrays of paths
        $namespaces = $this->autoloader->getNamespace();

        foreach (array_keys($namespaces) as $namespace) {
            if (substr($file, 0, strlen($namespace) + 1) === $namespace . '\\') {
                $fileWithoutNamespace = substr($file, strlen($namespace));

                // There may be sub-namespaces of the same vendor,
                // so overwrite them with namespaces found later.
                $paths    = $namespaces[$namespace];
                $filename = ltrim(str_replace('\\', '/', $fileWithoutNamespace), '/');
            }
        }

        // if no namespaces matched then quit
        if ($paths === []) {
            return false;
        }

        // Check each path in the namespace
        foreach ($paths as $path) {
            // Ensure trailing slash
            $path = rtrim($path, '/') . '/';

            // If we have a folder name, then the calling function
            // expects this file to be within that folder, like 'Views',
            // or 'libraries'.
            if ($folder !== null && ! str_contains($path . $filename, '/' . $folder . '/')) {
                $path .= trim($folder, '/') . '/';
            }

            $path .= $filename;
            if (is_file($path)) {
                return $path;
            }
        }

        return false;
    }

    /**
     * Examines a file and returns the fully qualified class name.
     */
    public function getClassname(string $file): string
    {
        if (is_dir($file)) {
            return '';
        }

        $php       = file_get_contents($file);
        $tokens    = token_get_all($php);
        $dlm       = false;
        $namespace = '';
        $className = '';

        foreach ($tokens as $i => $token) {
            if ($i < 2) {
                continue;
            }

            if ((isset($tokens[$i - 2][1]) && ($tokens[$i - 2][1] === 'phpnamespace' || $tokens[$i - 2][1] === 'namespace')) || ($dlm && $tokens[$i - 1][0] === T_NS_SEPARATOR && $token[0] === T_STRING)) {
                if (! $dlm) {
                    $namespace = '';
                }

                if (isset($token[1])) {
                    $namespace = $namespace !== '' ? $namespace . '\\' . $token[1] : $token[1];
                    $dlm       = true;
                }
            } elseif ($dlm && ($token[0] !== T_NS_SEPARATOR) && ($token[0] !== T_STRING)) {
                $dlm = false;
            }

            if (($tokens[$i - 2][0] === T_CLASS || (isset($tokens[$i - 2][1]) && $tokens[$i - 2][1] === 'phpclass'))
                && $tokens[$i - 1][0] === T_WHITESPACE
                && $token[0] === T_STRING) {
                $className = $token[1];
                break;
            }
        }

        if ($className === '') {
            return '';
        }

        return $namespace . '\\' . $className;
    }

    /**
     * Searches through all of the defined namespaces looking for a file.
     * Returns an array of all found locations for the defined file.
     *
     * Example:
     *
     *  $locator->search('Config/Routes.php');
     *  // Assuming PSR4 namespaces include foo and bar, might return:
     *  [
     *      'app/Modules/foo/Config/Routes.php',
     *      'app/Modules/bar/Config/Routes.php',
     *  ]
     *
     * @return list<string>
     */
    public function search(string $path, string $ext = 'php', bool $prioritizeApp = true): array
    {
        $path = $this->ensureExt($path, $ext);

        $foundPaths = [];
        $appPaths   = [];

        foreach ($this->getNamespaces() as $namespace) {
            if (isset($namespace['path']) && is_file($namespace['path'] . $path)) {
                $fullPath     = $namespace['path'] . $path;
                $resolvedPath = realpath($fullPath);
                $fullPath     = $resolvedPath !== false ? $resolvedPath : $fullPath;

                if ($prioritizeApp) {
                    $foundPaths[] = $fullPath;
                } elseif (str_starts_with($fullPath, APPPATH)) {
                    $appPaths[] = $fullPath;
                } else {
                    $foundPaths[] = $fullPath;
                }
            }
        }

        if (! $prioritizeApp && $appPaths !== []) {
            $foundPaths = [...$foundPaths, ...$appPaths];
        }

        // Remove any duplicates
        return array_values(array_unique($foundPaths));
    }

    /**
     * Ensures a extension is at the end of a filename
     */
    protected function ensureExt(string $path, string $ext): string
    {
        if ($ext !== '') {
            $ext = '.' . $ext;

            if (! str_ends_with($path, $ext)) {
                $path .= $ext;
            }
        }

        return $path;
    }

    /**
     * Return the namespace mappings we know about.
     *
     * @return array<int, array<string, string>>
     */
    protected function getNamespaces()
    {
        $namespaces = [];

        // Save system for last
        $system = [];

        foreach ($this->autoloader->getNamespace() as $prefix => $paths) {
            foreach ($paths as $path) {
                if ($prefix === 'CodeIgniter') {
                    $system[] = [
                        'prefix' => $prefix,
                        'path'   => rtrim($path, '\\/') . DIRECTORY_SEPARATOR,
                    ];

                    continue;
                }

                $namespaces[] = [
                    'prefix' => $prefix,
                    'path'   => rtrim($path, '\\/') . DIRECTORY_SEPARATOR,
                ];
            }
        }

        return array_merge($namespaces, $system);
    }

    public function findQualifiedNameFromPath(string $path)
    {
        $resolvedPath = realpath($path);
        $path         = $resolvedPath !== false ? $resolvedPath : $path;

        if (! is_file($path)) {
            return false;
        }

        foreach ($this->getNamespaces() as $namespace) {
            $resolvedNamespacePath = realpath($namespace['path']);
            $namespace['path']     = $resolvedNamespacePath !== false ? $resolvedNamespacePath : $namespace['path'];

            if ($namespace['path'] === '') {
                continue;
            }

            if (mb_strpos($path, $namespace['path']) === 0) {
                $className = $namespace['prefix'] . '\\' .
                    ltrim(
                        str_replace(
                            '/',
                            '\\',
                            mb_substr($path, mb_strlen($namespace['path'])),
                        ),
                        '\\',
                    );

                // Remove the file extension (.php)
                /** @var class-string */
                $className = mb_substr($className, 0, -4);

                if (in_array($className, $this->invalidClassnames, true)) {
                    continue;
                }

                // Check if this exists
                if (class_exists($className)) {
                    return $className;
                }

                // If the class does not exist, it is an invalid classname.
                $this->invalidClassnames[] = $className;
            }
        }

        return false;
    }

    /**
     * Scans the defined namespaces, returning a list of all files
     * that are contained within the subpath specified by $path.
     *
     * @return list<string> List of file paths
     */
    public function listFiles(string $path): array
    {
        if ($path === '') {
            return [];
        }

        $files = [];
        helper('filesystem');

        foreach ($this->getNamespaces() as $namespace) {
            $fullPath     = $namespace['path'] . $path;
            $resolvedPath = realpath($fullPath);
            $fullPath     = $resolvedPath !== false ? $resolvedPath : $fullPath;

            if (! is_dir($fullPath)) {
                continue;
            }

            $tempFiles = get_filenames($fullPath, true, false, false);

            if ($tempFiles !== []) {
                $files = array_merge($files, $tempFiles);
            }
        }

        return $files;
    }

    /**
     * Scans the provided namespace, returning a list of all files
     * that are contained within the sub path specified by $path.
     *
     * @return list<string> List of file paths
     */
    public function listNamespaceFiles(string $prefix, string $path): array
    {
        if ($path === '' || ($prefix === '')) {
            return [];
        }

        $files = [];
        helper('filesystem');

        // autoloader->getNamespace($prefix) returns an array of paths for that namespace
        foreach ($this->autoloader->getNamespace($prefix) as $namespacePath) {
            $fullPath     = rtrim($namespacePath, '/') . '/' . $path;
            $resolvedPath = realpath($fullPath);
            $fullPath     = $resolvedPath !== false ? $resolvedPath : $fullPath;

            if (! is_dir($fullPath)) {
                continue;
            }

            $tempFiles = get_filenames($fullPath, true, false, false);

            if ($tempFiles !== []) {
                $files = array_merge($files, $tempFiles);
            }
        }

        return $files;
    }

    /**
     * Checks the app folder to see if the file can be found.
     * Only for use with filenames that DO NOT include namespacing.
     *
     * @param non-empty-string|null $folder
     *
     * @return false|string The path to the file, or false if not found.
     */
    protected function legacyLocate(string $file, ?string $folder = null)
    {
        $path         = APPPATH . ($folder === null ? $file : $folder . '/' . $file);
        $resolvedPath = realpath($path);
        $path         = $resolvedPath !== false ? $resolvedPath : $path;

        if (is_file($path)) {
            return $path;
        }

        return false;
    }
}
