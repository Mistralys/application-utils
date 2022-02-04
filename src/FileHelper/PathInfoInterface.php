<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

interface PathInfoInterface
{
    public function getName() : string;

    public function getPath() : string;

    public function exists() : bool;

    public function isFolder() : bool;

    public function isFile() : bool;

    public function isWritable() : bool;

    public function isReadable() : bool;

    /**
     * @return $this
     */
    public function requireExists();

    /**
     * @return $this
     */
    public function requireReadable();

    /**
     * @return $this
     */
    public function requireWritable();

    /**
     * @return $this
     */
    public function requireIsFolder();

    /**
     * @return $this
     */
    public function requireIsFile();

    /**
     * @return $this
     */
    public function delete();
}
